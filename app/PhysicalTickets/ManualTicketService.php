<?php
declare(strict_types=1);

namespace TIS\PhysicalTickets;

use PDO;
use RuntimeException;
use Throwable;
use TIS\Payment\TicketService;

final class ManualTicketService
{
    public function __construct(private PDO $db, private array $config)
    {
    }

    /**
     * Assign a random available physical code and create its downloadable ticket.
     * Both records are committed together, so a code can never be assigned twice.
     *
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function create(array $input, string $eventKey, string $eventName, int $staffId): array
    {
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $phone = trim((string) ($input['phone'] ?? ''));
        $referrerName = trim((string) ($input['referrer_name'] ?? ''));
        $tierSlug = strtolower(trim((string) ($input['tier'] ?? '')));
        $channel = strtolower(trim((string) ($input['channel'] ?? '')));
        $manualReference = trim((string) ($input['manual_payment_reference'] ?? ''));
        $amountKobo = (int) ($input['amount_kobo'] ?? 0);

        $nameLength = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
        if ($nameLength < 2 || $nameLength > 100) {
            throw new RuntimeException('Enter an attendee name between 2 and 100 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            throw new RuntimeException('Enter a valid attendee email address.');
        }
        if (!preg_match('/^[0-9+()\-\s]{7,24}$/', $phone)) {
            throw new RuntimeException('Enter a valid attendee phone number.');
        }
        if ((function_exists('mb_strlen') ? mb_strlen($referrerName) : strlen($referrerName)) > 100) {
            throw new RuntimeException('The referrer name must not exceed 100 characters.');
        }
        if ((function_exists('mb_strlen') ? mb_strlen($manualReference) : strlen($manualReference)) > 100) {
            throw new RuntimeException('The manual payment reference must not exceed 100 characters.');
        }
        if (!in_array($channel, ['bank_transfer', 'cash', 'pos', 'other'], true)) {
            throw new RuntimeException('Choose a valid manual payment method.');
        }

        $tier = \tis_ticket_tier($tierSlug, $this->config);
        if ($tier === null) {
            throw new RuntimeException('Choose a valid ticket category.');
        }
        $expectedAmount = (int) ($tier['ticket_price_kobo'] ?? 0);
        if ($expectedAmount < 1 || $amountKobo !== $expectedAmount) {
            throw new RuntimeException('The amount received must exactly match the selected ticket price.');
        }

        $reference = 'TIS-MAN-' . strtoupper(bin2hex(random_bytes(8)));
        $ticketToken = bin2hex(random_bytes(24));
        $clientHash = hash('sha256', 'manual:' . $staffId . ':' . $reference . ':' . $email);
        $randomFunction = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'RANDOM()' : 'RAND()';
        $lockClause = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';

        $this->db->beginTransaction();
        try {
            $available = $this->db->prepare(
                "SELECT id, code FROM tis_physical_tickets WHERE event_key = ? AND status = 'unsold' " .
                "AND order_id IS NULL ORDER BY {$randomFunction} LIMIT 1{$lockClause}"
            );
            $available->execute([$eventKey]);
            $physicalTicket = $available->fetch();
            if (!is_array($physicalTicket)) {
                throw new RuntimeException('No unsold physical ticket codes are available. Generate more codes first.');
            }

            $insertOrder = $this->db->prepare(
                "INSERT INTO tis_orders (reference, full_name, email, phone, referrer_name, tier, amount_kobo, " .
                "ticket_price_kobo, secondary_allocation_kobo, payment_method, status, channel, paid_at, " .
                "ticket_number, ticket_token, client_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'manual', " .
                "'success', ?, CURRENT_TIMESTAMP, ?, ?, ?)"
            );
            $insertOrder->execute([
                $reference,
                $fullName,
                $email,
                $phone,
                $referrerName !== '' ? $referrerName : null,
                $tierSlug,
                $amountKobo,
                $expectedAmount,
                $channel,
                (string) $physicalTicket['code'],
                $ticketToken,
                $clientHash,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $assign = $this->db->prepare(
                "UPDATE tis_physical_tickets SET status = 'sold', sold_at = CURRENT_TIMESTAMP, order_id = ?, " .
                "manual_payment_reference = ?, sold_by = ?, updated_at = CURRENT_TIMESTAMP " .
                "WHERE id = ? AND event_key = ? AND status = 'unsold' AND order_id IS NULL"
            );
            $assign->execute([
                $orderId,
                $manualReference !== '' ? $manualReference : null,
                $staffId,
                (int) $physicalTicket['id'],
                $eventKey,
            ]);
            if ($assign->rowCount() !== 1) {
                throw new RuntimeException('That physical ticket was assigned elsewhere. Please try again.');
            }

            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        $ticketService = new TicketService($this->db, $this->config);
        try {
            $ticketService->queueTicketProcessing($reference);
        } catch (Throwable $error) {
            \tis_log('Manual ticket email could not be queued for ' . $reference . ': ' . $error->getMessage());
        }

        $order = $ticketService->findByReference($reference);
        if (!is_array($order)) {
            throw new RuntimeException('The manual ticket was saved but could not be loaded.');
        }
        return $order;
    }
}
