<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/_bootstrap.php';

use TIS\Payment\TicketPdf;
use TIS\Payment\TicketService;

try {
    $token = trim((string) ($_GET['token'] ?? ''));
    if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
        http_response_code(404);
        exit('Ticket not found.');
    }

    $config = tis_config();
    $order = (new TicketService(tis_db(), $config))->findByToken($token);
    if ($order === null) {
        http_response_code(404);
        exit('Ticket not found.');
    }

    $pdf = TicketPdf::render($order, $config);
    $filename = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $order['ticket_number']) . '.pdf';
    $disposition = (string) ($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline';
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, no-store, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
} catch (Throwable $error) {
    tis_log($error->getMessage());
    http_response_code(500);
    exit('The ticket could not be downloaded right now.');
}
