<?php
declare(strict_types=1);

namespace TIS\Payment;

use Dompdf\Dompdf;
use Dompdf\Options;

final class TicketPdf
{
    public static function render(array $order, array $config): string
    {
        $safe = static fn (mixed $value): string => htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        $paidAt = strtotime((string) ($order['paid_at'] ?? ''));
        $paidAtDisplay = $paidAt ? date('j M Y, g:i A', $paidAt) : (string) ($order['paid_at'] ?? 'Confirmed');
        $rawChannel = strtolower((string) ($order['channel'] ?: 'paystack'));
        $channel = [
            'ussd' => 'USSD',
            'qr' => 'QR',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'card' => 'Card',
        ][$rawChannel] ?? ucwords(str_replace('_', ' ', $rawChannel));
        $amount = \tis_money((int) $order['amount_kobo']);
        $tier = \tis_ticket_tier((string) ($order['tier'] ?? ''), $config);
        if ($tier === null) {
            throw new \RuntimeException('The ticket PDF has an unsupported tier.');
        }

        $tierSlug = strtolower((string) ($order['tier'] ?? ''));
        $tierBenefits = [
            'standard' => ['Event access', 'TIS-IUO curated food pack', 'Branded lanyard'],
            'premium' => ['Event access', 'TIS-IUO curated food pack', 'Branded lanyard', 'TIS T-shirt'],
            // `vip` remains the internal identifier; customers see the Deluxe label above.
            'vip' => ['Event access', 'TIS-IUO curated food pack', 'Branded lanyard', 'Souvenir item', 'TIS T-shirt', 'Priority seating'],
        ];
        $benefitsHtml = implode('', array_map(
            static fn (string $benefit): string => '<li>' . $safe($benefit) . '</li>',
            $tierBenefits[$tierSlug] ?? []
        ));
        $tierStyles = [
            'standard' => ['accent' => '#8291ad', 'accent_dark' => '#526582', 'accent_soft' => '#e9edf4', 'hero' => '#172641', 'number' => '01'],
            'premium' => ['accent' => '#d4ad50', 'accent_dark' => '#a7791f', 'accent_soft' => '#f3e6c4', 'hero' => '#13223e', 'number' => '02'],
            'vip' => ['accent' => '#e0b64f', 'accent_dark' => '#9b6e18', 'accent_soft' => '#f5e8c7', 'hero' => '#101d35', 'number' => '03'],
        ];
        $style = $tierStyles[$tierSlug] ?? $tierStyles['premium'];

        $attendee = $safe($order['full_name']);
        $ticketNumber = $safe($order['ticket_number']);
        $tierLabel = $safe($tier['label']);
        $eventName = $safe($config['event']['name']);
        $eventDate = $safe($config['event']['date']);
        $venue = $safe($config['event']['venue']);
        $eventTimestamp = strtotime((string) $config['event']['date']);
        $eventDateShort = $safe($eventTimestamp ? strtoupper(date('j M', $eventTimestamp)) : $config['event']['date']);
        $venueParts = array_values(array_filter(array_map('trim', explode(',', (string) $config['event']['venue']))));
        $venueShort = $safe(strtoupper((string) ($venueParts[array_key_last($venueParts)] ?? $config['event']['venue'])));
        $amountPaid = $safe($amount);
        $paymentChannel = $safe($channel);
        $paymentReference = $safe($order['reference']);
        $confirmedAt = $safe($paidAtDisplay);
        $accent = $style['accent'];
        $accentDark = $style['accent_dark'];
        $accentSoft = $style['accent_soft'];
        $hero = $style['hero'];
        $tierNumber = $style['number'];

        $html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{$tierLabel} - {$ticketNumber}</title>
<style>
@page { margin: 0; }
* { box-sizing: border-box; }
body { margin: 0; background: #0f1b31; color: #172238; font-family: DejaVu Sans, sans-serif; }
.sheet { position: relative; width: 749px; height: 1058px; margin: 22px; overflow: hidden; background: #fbfaf5; border: 1px solid rgba(255,255,255,.22); }
.top-rail { height: 9px; background: {$accent}; }
.hero { position: relative; height: 286px; overflow: hidden; padding: 31px 36px; color: #fff; background: {$hero}; }
.brand-lockup { position: relative; height: 48px; }
.brand-mark { position: absolute; left: 0; top: 0; width: 45px; height: 39px; }
.brand-mark span { position: absolute; bottom: 0; display: block; width: 11px; }
.brand-mark .bar-one { left: 0; height: 20px; background: #737b8e; }
.brand-mark .bar-two { left: 15px; height: 29px; background: #102746; }
.brand-mark .bar-three { left: 30px; height: 39px; background: #c29a3c; }
.brand-name { position: absolute; left: 59px; top: 2px; color: #fff; font-family: DejaVu Serif, serif; font-size: 16px; font-weight: 700; letter-spacing: .35px; }
.brand-tagline { position: absolute; left: 60px; top: 26px; color: {$accent}; font-size: 7px; font-weight: 700; letter-spacing: 3px; }
.verified-pill { position: absolute; right: 0; top: 3px; padding: 8px 13px; color: #fff; border: 1px solid rgba(255,255,255,.32); border-radius: 20px; font-size: 7px; font-weight: 700; letter-spacing: 1.5px; }
.hero-copy { position: absolute; left: 36px; bottom: 41px; width: 470px; z-index: 3; }
.tier-line { color: {$accent}; font-size: 9px; font-weight: 700; letter-spacing: 2.2px; }
.hero h1 { margin: 10px 0 7px; color: #fff; font-family: DejaVu Serif, serif; font-size: 34px; font-weight: 700; line-height: 1.1; }
.event-name { color: rgba(255,255,255,.7); font-size: 9px; letter-spacing: 1.2px; }
.event-seal { position: absolute; right: 38px; bottom: 64px; width: 112px; height: 112px; padding-top: 26px; color: #fff; text-align: center; border: 1px solid {$accent}; border-radius: 56px; background: rgba(7,16,33,.34); z-index: 3; }
.event-seal .seal-top,.event-seal .seal-bottom { display: block; color: {$accent}; font-size: 6.5px; font-weight: 700; letter-spacing: 1.5px; }
.event-seal strong { display: block; margin: 5px 0 4px; font-family: DejaVu Serif, serif; font-size: 21px; line-height: 1; }
.curve-gold,.curve-paper { position: absolute; right: -126px; border-radius: 50%; z-index: 1; }
.curve-gold { bottom: -168px; width: 520px; height: 204px; border: 15px solid {$accent}; }
.curve-paper { bottom: -191px; width: 548px; height: 220px; border: 20px solid #fbfaf5; }
.ticket-body { padding: 29px 36px 0; }
.identity { width: 100%; margin: 0 0 22px; border-collapse: collapse; border: 1px solid #ded8ca; border-left: 7px solid {$accent}; background: #fff; }
.identity td { padding: 18px 20px; vertical-align: middle; }
.identity .attendee-cell { width: 57%; border-right: 1px solid #e6e0d4; }
.identity .ticket-cell { width: 43%; background: {$accentSoft}; }
.label { margin-bottom: 7px; color: #718098; font-size: 7px; font-weight: 700; letter-spacing: 1.7px; }
.identity .name { font-family: DejaVu Serif, serif; font-size: 20px; font-weight: 700; line-height: 1.2; }
.identity .ticket-number { color: {$accentDark}; font-size: 13px; font-weight: 700; letter-spacing: .4px; }
.details { width: 100%; margin: 0; border-collapse: collapse; table-layout: fixed; background: #fbfaf5; }
.details td { width: 50%; height: 76px; padding: 15px 18px; vertical-align: top; border: 1px solid #e1dccf; }
.details .value { color: #172238; font-size: 11px; font-weight: 700; line-height: 1.4; }
.details .featured-value { color: {$accentDark}; font-size: 13px; }
.details .reference-value { font-size: 10px; letter-spacing: .25px; }
.admission-note { position: relative; margin-top: 22px; padding: 17px 20px 17px 64px; color: #27334a; border: 1px solid #e2d6b8; background: {$accentSoft}; }
.admission-icon { position: absolute; left: 19px; top: 16px; width: 29px; height: 29px; padding-top: 7px; color: #fff; text-align: center; border-radius: 15px; background: {$accentDark}; font-size: 11px; font-weight: 700; }
.admission-note strong { display: block; margin-bottom: 5px; color: #172238; font-size: 9px; letter-spacing: 1.2px; }
.admission-note p { margin: 0; color: #526077; font-size: 8px; line-height: 1.5; }
.benefits { margin: 14px 0; padding: 12px 14px; border: 1px solid #ded8ca; background: #fbfaf5; }
.benefits .label { margin-bottom: 7px; }
.benefits ul { margin: 0; padding: 0; list-style: none; }
.benefits li { display: inline-block; width: 48%; margin: 0 0 5px; color: #526077; font-size: 8px; }
.benefits li:before { content: "✓ "; color: {$accentDark}; font-weight: 700; }
.footer { position: absolute; left: 36px; right: 36px; bottom: 25px; padding-top: 13px; border-top: 1px solid #ded8ca; color: #718098; font-size: 7px; }
.footer-right { position: absolute; right: 0; top: 13px; color: #172238; font-weight: 700; letter-spacing: 1px; }
.tier-index { position: absolute; right: 34px; bottom: 68px; color: rgba(23,34,56,.07); font-family: DejaVu Serif,serif; font-size: 92px; font-weight: 700; }
</style>
</head>
<body>
<div class="sheet">
    <div class="top-rail"></div>
    <header class="hero">
        <div class="brand-lockup">
            <div class="brand-mark"><span class="bar-one"></span><span class="bar-two"></span><span class="bar-three"></span></div>
            <div class="brand-name">THE INTELLECTUAL SUMMIT</div>
            <div class="brand-tagline">GLOBAL RELEVANCE</div>
            <div class="verified-pill">EARLY BIRD / VERIFIED</div>
        </div>
        <div class="hero-copy">
            <div class="tier-line">{$tierLabel}</div>
            <h1>Your Summit 2026 Ticket</h1>
            <div class="event-name">{$eventName}</div>
        </div>
        <div class="event-seal"><span class="seal-top">SUMMIT 2026</span><strong>{$eventDateShort}</strong><span class="seal-bottom">{$venueShort}</span></div>
        <div class="curve-gold"></div><div class="curve-paper"></div>
    </header>
    <main class="ticket-body">
        <table class="identity"><tr>
            <td class="attendee-cell"><div class="label">ATTENDEE</div><div class="name">{$attendee}</div></td>
            <td class="ticket-cell"><div class="label">TICKET NUMBER</div><div class="ticket-number">{$ticketNumber}</div></td>
        </tr></table>
        <table class="details">
            <tr><td><div class="label">EVENT DATE</div><div class="value">{$eventDate}</div></td><td><div class="label">VENUE</div><div class="value">{$venue}</div></td></tr>
            <tr><td><div class="label">TIER</div><div class="value featured-value">{$tierLabel}</div></td><td><div class="label">AMOUNT PAID</div><div class="value featured-value">{$amountPaid}</div></td></tr>
            <tr><td><div class="label">PAYMENT CHANNEL</div><div class="value">{$paymentChannel}</div></td><td><div class="label">CONFIRMED</div><div class="value">{$confirmedAt}</div></td></tr>
            <tr><td colspan="2"><div class="label">PAYMENT REFERENCE</div><div class="value reference-value">{$paymentReference}</div></td></tr>
        </table>
        <div class="benefits"><div class="label">YOUR PACKAGE INCLUDES</div><ul>{$benefitsHtml}</ul></div>
        <div class="admission-note"><div class="admission-icon">1</div><strong>KEEP THIS TICKET SAFE - ADMIT ONE</strong><p>Present this ticket and a matching name at the venue entrance.</p></div>
    </main>
    <div class="tier-index">{$tierNumber}</div>
    <footer class="footer">tickets@theintellectualsummit.com &nbsp; | &nbsp; theintellectualsummit.com<span class="footer-right">OFFICIAL ATTENDEE TICKET</span></footer>
</div>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }
}
