<?php
declare(strict_types=1);

namespace TIS\Payment;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class TicketMailer
{
    public function __construct(private readonly array $config)
    {
    }

    public function send(array $order, string $pdf): void
    {
        $settings = $this->config['mail'];
        $tier = $this->tier($order);
        $mailer = new PHPMailer(true);

        try {
            if (($settings['transport'] ?? 'mail') === 'smtp') {
                $smtp = $settings['smtp'];
                if ($smtp['host'] === '' || $smtp['username'] === '' || $smtp['password'] === '') {
                    throw new RuntimeException('SMTP configuration is incomplete.');
                }
                $mailer->isSMTP();
                $mailer->Host = (string) $smtp['host'];
                $mailer->Port = (int) $smtp['port'];
                $mailer->SMTPAuth = true;
                $mailer->Username = (string) $smtp['username'];
                $mailer->Password = (string) $smtp['password'];
                $mailer->SMTPSecure = strtolower((string) $smtp['encryption']);
            } elseif (($settings['transport'] ?? 'mail') === 'mail') {
                $mailer->isMail();
            } else {
                throw new RuntimeException('Unsupported ticket email transport.');
            }

            $mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $mailer->setFrom((string) $settings['from_email'], (string) $settings['from_name']);
            $mailer->addReplyTo((string) $settings['reply_to']);
            $mailer->addAddress((string) $order['email'], (string) $order['full_name']);
            $mailer->Subject = 'Your ' . $tier['label'] . ' ticket - ' . $order['ticket_number'];
            $mailer->isHTML(true);
            $mailer->Body = $this->html($order);
            $mailer->AltBody = $this->plain($order);
            $filename = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $order['ticket_number']) . '.pdf';
            $mailer->addStringAttachment($pdf, $filename, PHPMailer::ENCODING_BASE64, 'application/pdf');
            $mailer->send();
        } catch (MailException $error) {
            throw new RuntimeException('The ticket email could not be sent.', 0, $error);
        }
    }

    private function downloadUrl(array $order, bool $forceDownload = false): string
    {
        $baseUrl = rtrim((string) $this->config['site_url'], '/');
        $token = rawurlencode((string) $order['ticket_token']);
        return $forceDownload
            ? $baseUrl . '/api/ticket/download.php?token=' . $token . '&download=1'
            : $baseUrl . '/ticket?token=' . $token;
    }

    private function plain(array $order): string
    {
        $tier = $this->tier($order);
        return "Hello {$order['full_name']},\n\n" .
            "Your payment has been confirmed and your {$tier['label']} ticket is ready.\n\n" .
            "Ticket number: {$order['ticket_number']}\nTier: {$tier['label']}\n" .
            "Event: {$this->config['event']['name']}\nDate: {$this->config['event']['date']}\n" .
            "Venue: {$this->config['event']['venue']}\n" .
            'Amount paid: NGN ' . number_format(((int) $order['amount_kobo']) / 100, 2) . "\n" .
            "Payment reference: {$order['reference']}\n\n" .
            'Open your ticket PDF: ' . $this->downloadUrl($order) . "\n" .
            'Direct download: ' . $this->downloadUrl($order, true) . "\n\n" .
            "A PDF copy is also attached to this email. Please present it at the venue entrance.\n";
    }

    private function html(array $order): string
    {
        $safe = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $viewUrl = $this->downloadUrl($order);
        $downloadUrl = $this->downloadUrl($order, true);
        $tier = $this->tier($order);

        return '<!doctype html><html><body style="margin:0;background:#f5f1e6;font-family:Arial,sans-serif;color:#16202f">' .
            '<div style="max-width:620px;margin:0 auto;padding:28px 16px"><div style="background:#101c33;color:#fff;padding:24px 28px;border-radius:18px 18px 0 0;border-top:8px solid #c9a44c">' .
            '<div style="color:#e6bc5c;font-weight:700;letter-spacing:2px;font-size:12px">THE INTELLECTUAL SUMMIT</div>' .
            '<h1 style="margin:12px 0 4px;font-family:Georgia,serif;font-size:30px">Your ' . $safe($tier['label']) . ' ticket is ready</h1>' .
            '<p style="margin:0;color:#d6dbea">Payment confirmed successfully.</p></div>' .
            '<div style="background:#fff;padding:28px;border-radius:0 0 18px 18px"><p>Hello ' . $safe($order['full_name']) . ',</p>' .
            '<p>Your unique ticket for The Intellectual Summit 2026 is attached to this email.</p>' .
            '<div style="background:#fbfaf5;border:1px solid #e7e3d7;border-radius:12px;padding:18px;margin:22px 0">' .
            '<p><strong>Ticket number:</strong> ' . $safe($order['ticket_number']) . '</p><p><strong>Tier:</strong> ' . $safe($tier['label']) . '</p>' .
            '<p><strong>Date:</strong> ' . $safe($this->config['event']['date']) . '</p><p><strong>Venue:</strong> ' . $safe($this->config['event']['venue']) . '</p>' .
            '<p><strong>Amount paid:</strong> NGN ' . number_format(((int) $order['amount_kobo']) / 100, 2) . '</p>' .
            '<p><strong>Reference:</strong> ' . $safe($order['reference']) . '</p></div>' .
            '<p style="margin:24px 0"><a href="' . $safe($viewUrl) . '" target="_blank" style="display:inline-block;background:#c9a44c;color:#101c33;text-decoration:none;font-weight:700;padding:14px 24px;border-radius:999px">View or download your ticket</a></p>' .
            '<p style="font-size:13px;color:#526071">If the button does not open, <a href="' . $safe($downloadUrl) . '" style="color:#101c33;font-weight:700">download the PDF directly</a> or use the PDF attached to this email.</p>' .
            '<p style="font-size:13px;color:#7b8697">Keep your ticket private and present it at the venue entrance.</p></div></div></body></html>';
    }

    private function tier(array $order): array
    {
        $tier = \tis_ticket_tier((string) ($order['tier'] ?? ''), $this->config);
        if ($tier === null) {
            throw new RuntimeException('The ticket email has an unsupported tier.');
        }
        return $tier;
    }
}
