<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$expected = [
    'standard' => [
        'ticket_price_kobo' => 500000,
        'checkout_amount_kobo' => 527919,
    ],
    'premium' => [
        'ticket_price_kobo' => 1000000,
        'checkout_amount_kobo' => 1035533,
    ],
    'vip' => [
        'ticket_price_kobo' => 1500000,
        'checkout_amount_kobo' => 1543148,
    ],
];

$config = tis_config();
$tiers = tis_ticket_tiers($config);
$ownerAllocation = (int) ($config['secondary_allocation_kobo'] ?? 0);
$failures = [];

foreach ($expected as $slug => $amounts) {
    $tier = $tiers[$slug] ?? null;
    if (!is_array($tier)) {
        $failures[] = sprintf('%s tier is missing.', $slug);
        continue;
    }

    $ticketPrice = (int) $tier['ticket_price_kobo'];
    $checkoutAmount = (int) $tier['checkout_amount_kobo'];
    $fee = tis_paystack_fee_kobo($checkoutAmount, $config);
    $partnerSettlement = $checkoutAmount - $fee - $ownerAllocation;
    $previousAmount = $checkoutAmount - 1;
    $previousPartnerSettlement = $previousAmount
        - tis_paystack_fee_kobo($previousAmount, $config)
        - $ownerAllocation;

    if ($ticketPrice !== $amounts['ticket_price_kobo']) {
        $failures[] = sprintf('%s ticket price is %d; expected %d kobo.', $slug, $ticketPrice, $amounts['ticket_price_kobo']);
    }
    if ($checkoutAmount !== $amounts['checkout_amount_kobo']) {
        $failures[] = sprintf('%s checkout total is %d; expected %d kobo.', $slug, $checkoutAmount, $amounts['checkout_amount_kobo']);
    }
    if ($partnerSettlement !== $ticketPrice) {
        $failures[] = sprintf('%s partner settlement is %d; expected %d kobo.', $slug, $partnerSettlement, $ticketPrice);
    }
    if ($previousPartnerSettlement >= $ticketPrice) {
        $failures[] = sprintf('%s checkout total is not the smallest amount that settles exactly.', $slug);
    }

    printf(
        "PASS %-8s customer=%s fee=%s owner=%s partner=%s\n",
        strtoupper($slug),
        tis_money($checkoutAmount),
        tis_money($fee),
        tis_money($ownerAllocation),
        tis_money($partnerSettlement)
    );
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "All ticket-pricing checks passed.\n";
