<?php
declare(strict_types=1);

namespace TIS\Payment;

use PDO;
use RuntimeException;
use Throwable;

final class TicketService
{
    private PDO $db;
    private array $config;

    public function __construct(PDO $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function findByReference(string $reference): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM tis_orders WHERE reference = ? LIMIT 1');
        $statement->execute([$reference]);
        $order = $statement->fetch();
        return is_array($order) ? $order : null;
    }

    public function findByToken(string $token): ?array
    {
        $statement = $this->db->prepare("SELECT * FROM tis_orders WHERE ticket_token = ? AND status = 'success' LIMIT 1");
        $statement->execute([$token]);
        $order = $statement->fetch();
        return is_array($order) ? $order : null;
    }

    public function verifyAndFinalize(PaystackClient $paystack, string $reference): array
    {
        $response = $paystack->verify($reference);
        $payment = $response['data'] ?? null;
        if (!is_array($payment)) {
            throw new RuntimeException('Paystack verification did not include transaction details.');
        }

        if (($payment['status'] ?? '') !== 'success') {
            return ['status' => (string) ($payment['status'] ?? 'pending'), 'order' => null];
        }

        return ['status' => 'success', 'order' => $this->finalize($payment)];
    }

    public function markUnsuccessful(string $reference, string $status): void
    {
        if (!in_array($status, ['failed', 'abandoned', 'reversed'], true)) {
            return;
        }
        $statement = $this->db->prepare(
            "UPDATE tis_orders SET status = 'failed', failure_reason = ?, updated_at = CURRENT_TIMESTAMP " .
            "WHERE reference = ? AND status = 'pending'"
        );
        $statement->execute(['Paystack status: ' . $status, $reference]);
    }

    public function finalize(array $payment): array
    {
        $reference = (string) ($payment['reference'] ?? '');
        if ($reference === '') {
            throw new RuntimeException('The verified transaction has no reference.');
        }

        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('SELECT * FROM tis_orders WHERE reference = ? FOR UPDATE');
            $statement->execute([$reference]);
            $order = $statement->fetch();
            if (!is_array($order)) {
                throw new RuntimeException('This transaction was not created by the ticket website.');
            }

            $tier = \tis_ticket_tier((string) $order['tier'], $this->config);
            if ($tier === null) {
                throw new RuntimeException('The ticket order has an unsupported tier.');
            }

            $expectedAmount = (int) $order['amount_kobo'];
            if ((int) ($payment['amount'] ?? 0) !== $expectedAmount) {
                throw new RuntimeException('The verified transaction amount does not match the ticket total.');
            }
            if (strtoupper((string) ($payment['currency'] ?? '')) !== strtoupper((string) $this->config['currency'])) {
                throw new RuntimeException('The verified transaction currency is invalid.');
            }

            $paymentEmail = strtolower((string) ($payment['customer']['email'] ?? ''));
            if ($paymentEmail !== '' && $paymentEmail !== strtolower((string) $order['email'])) {
                throw new RuntimeException('The verified customer does not match this ticket order.');
            }

            $verifiedSplitCode = (string) ($payment['split']['split_code'] ?? '');
            $expectedSplitCode = \tis_ticket_split_code((string) $order['tier'], $this->config);
            if ($verifiedSplitCode !== $expectedSplitCode) {
                throw new RuntimeException('The verified transaction does not use the configured ticket split.');
            }

            if ($order['status'] !== 'success') {
                $ticketNumber = $this->ticketNumber($tier);
                $ticketToken = bin2hex(random_bytes(24));
                $channel = substr((string) ($payment['channel'] ?? 'paystack'), 0, 32);
                $paystackId = isset($payment['id']) ? (string) $payment['id'] : null;
                $paidAt = (string) ($payment['paid_at'] ?? date('Y-m-d H:i:s'));
                $paidAt = date('Y-m-d H:i:s', strtotime($paidAt) ?: time());

                $update = $this->db->prepare(
                    "UPDATE tis_orders SET status = 'success', paystack_id = ?, channel = ?, paid_at = ?, " .
                    'ticket_number = ?, ticket_token = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                );
                $update->execute([$paystackId, $channel, $paidAt, $ticketNumber, $ticketToken, $order['id']]);
            }

            $statement->execute([$reference]);
            $order = $statement->fetch();
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        return $this->findByReference($reference) ?? $order;
    }

    /**
     * Queue verification and email delivery outside a customer request or webhook.
     * The unique reference makes this operation idempotent when Paystack retries events.
     */
    public function queueTicketProcessing(string $reference): void
    {
        $statement = $this->db->prepare(
            "INSERT INTO tis_ticket_jobs (reference, status, available_at) VALUES (?, 'queued', CURRENT_TIMESTAMP) " .
            "ON DUPLICATE KEY UPDATE " .
            "status = IF(status = 'complete', 'complete', 'queued'), " .
            "available_at = IF(status = 'complete', available_at, LEAST(available_at, CURRENT_TIMESTAMP)), " .
            "locked_at = NULL, last_error = NULL"
        );
        $statement->execute([$reference]);
    }

    /**
     * Process a small batch from cron. Slow Paystack and SMTP work deliberately lives here,
     * not in the webhook or the page a customer is waiting on.
     *
     * @return array{processed:int,completed:int,requeued:int}
     */
    public function processQueuedJobs(PaystackClient $paystack, int $limit = 5): array
    {
        $summary = ['processed' => 0, 'completed' => 0, 'requeued' => 0];
        $limit = max(1, min($limit, 25));

        for ($index = 0; $index < $limit; $index++) {
            $job = $this->claimNextJob();
            if ($job === null) {
                break;
            }

            $summary['processed']++;
            try {
                $order = $this->findByReference((string) $job['reference']);
                if ($order === null) {
                    $this->completeJob((int) $job['id']);
                    $summary['completed']++;
                    continue;
                }

                if ($order['status'] === 'pending') {
                    $result = $this->verifyAndFinalize($paystack, (string) $job['reference']);
                    if ($result['status'] === 'success' && is_array($result['order'])) {
                        $order = $result['order'];
                    } elseif (in_array($result['status'], ['failed', 'abandoned', 'reversed'], true)) {
                        $this->markUnsuccessful((string) $job['reference'], $result['status']);
                        $this->completeJob((int) $job['id']);
                        $summary['completed']++;
                        continue;
                    } else {
                        $this->releaseJob((int) $job['id'], 60, 'Waiting for Paystack confirmation.');
                        $summary['requeued']++;
                        continue;
                    }
                }

                if ($order['status'] !== 'success') {
                    $this->completeJob((int) $job['id']);
                    $summary['completed']++;
                    continue;
                }

                $this->attemptEmail((int) $order['id']);
                $updated = $this->findByReference((string) $job['reference']) ?? $order;
                if (!empty($updated['email_sent_at'])) {
                    $this->completeJob((int) $job['id']);
                    $summary['completed']++;
                } else {
                    $this->releaseJob((int) $job['id'], 300, 'Ticket email delivery is pending retry.');
                    $summary['requeued']++;
                }
            } catch (Throwable $error) {
                $this->releaseJob((int) $job['id'], 300, $error->getMessage());
                $summary['requeued']++;
                \tis_log('Ticket job failed for ' . $job['reference'] . ': ' . $error->getMessage());
            }
        }

        return $summary;
    }

    public function attemptEmail(int $orderId): void
    {
        $claim = $this->db->prepare(
            'UPDATE tis_orders SET email_lock_at = CURRENT_TIMESTAMP, email_attempts = email_attempts + 1 ' .
            'WHERE id = ? AND email_sent_at IS NULL AND (email_lock_at IS NULL OR email_lock_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE))'
        );
        $claim->execute([$orderId]);
        if ($claim->rowCount() !== 1) {
            return;
        }

        $statement = $this->db->prepare('SELECT * FROM tis_orders WHERE id = ? LIMIT 1');
        $statement->execute([$orderId]);
        $order = $statement->fetch();
        if (!is_array($order)) {
            return;
        }

        try {
            $pdf = TicketPdf::render($order, $this->config);
            (new TicketMailer($this->config))->send($order, $pdf);
            $sent = $this->db->prepare('UPDATE tis_orders SET email_sent_at = CURRENT_TIMESTAMP, email_lock_at = NULL, email_last_error = NULL WHERE id = ?');
            $sent->execute([$orderId]);
        } catch (Throwable $error) {
            $failed = $this->db->prepare('UPDATE tis_orders SET email_lock_at = NULL, email_last_error = ? WHERE id = ?');
            $failed->execute([substr($error->getMessage(), 0, 250), $orderId]);
            \tis_log('Ticket email failed for order ' . $orderId . ': ' . $error->getMessage());
        }
    }

    private function claimNextJob(): ?array
    {
        $select = $this->db->query(
            "SELECT * FROM tis_ticket_jobs WHERE " .
            "(status = 'queued' AND available_at <= CURRENT_TIMESTAMP) " .
            "OR (status = 'processing' AND locked_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)) " .
            'ORDER BY available_at ASC, id ASC LIMIT 1'
        );
        $job = $select->fetch();
        if (!is_array($job)) {
            return null;
        }

        $claim = $this->db->prepare(
            "UPDATE tis_ticket_jobs SET status = 'processing', locked_at = CURRENT_TIMESTAMP, attempts = attempts + 1 " .
            "WHERE id = ? AND (" .
            "(status = 'queued' AND available_at <= CURRENT_TIMESTAMP) " .
            "OR (status = 'processing' AND locked_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)))"
        );
        $claim->execute([(int) $job['id']]);

        return $claim->rowCount() === 1 ? $job : null;
    }

    private function completeJob(int $jobId): void
    {
        $statement = $this->db->prepare(
            "UPDATE tis_ticket_jobs SET status = 'complete', completed_at = CURRENT_TIMESTAMP, locked_at = NULL, last_error = NULL WHERE id = ?"
        );
        $statement->execute([$jobId]);
    }

    private function releaseJob(int $jobId, int $delaySeconds, string $error): void
    {
        $delaySeconds = max(1, min($delaySeconds, 3600));
        $statement = $this->db->prepare(
            "UPDATE tis_ticket_jobs SET status = 'queued', locked_at = NULL, " .
            "available_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL {$delaySeconds} SECOND), last_error = ? WHERE id = ?"
        );
        $statement->execute([substr($error, 0, 250), $jobId]);
    }

    public function publicTicket(array $order): array
    {
        $tier = \tis_ticket_tier((string) $order['tier'], $this->config);
        if ($tier === null) {
            throw new RuntimeException('The ticket order has an unsupported tier.');
        }

        $token = rawurlencode((string) $order['ticket_token']);

        return [
            'ticket_number' => (string) $order['ticket_number'],
            'tier' => (string) $tier['label'],
            'amount_display' => \tis_money((int) $order['amount_kobo']),
            'reference' => (string) $order['reference'],
            // Keep browser links on the current host so localhost, preview and live URLs all work.
            'view_url' => '/ticket?token=' . $token,
            'download_url' => '/api/ticket/download.php?token=' . $token . '&download=1',
            'email_sent' => !empty($order['email_sent_at']),
        ];
    }

    private function ticketNumber(array $tier): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = '';
        for ($i = 0; $i < 7; $i++) {
            $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return 'TIS26-' . (string) $tier['ticket_prefix'] . '-' . $suffix;
    }
}
