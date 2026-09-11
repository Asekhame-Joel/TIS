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
$secondaryAllocation = (int) ($config['secondary_allocation_kobo'] ?? 0);
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
    $mainSettlement = $checkoutAmount - $fee - $secondaryAllocation;
    $previousAmount = $checkoutAmount - 1;
    $previousSettlement = $previousAmount
        - tis_paystack_fee_kobo($previousAmount, $config)
        - $secondaryAllocation;

    if ($ticketPrice !== $amounts['ticket_price_kobo']) {
        $failures[] = sprintf('%s ticket price is %d; expected %d kobo.', $slug, $ticketPrice, $amounts['ticket_price_kobo']);
    }
    if ($checkoutAmount !== $amounts['checkout_amount_kobo']) {
        $failures[] = sprintf('%s checkout total is %d; expected %d kobo.', $slug, $checkoutAmount, $amounts['checkout_amount_kobo']);
    }
    if ($mainSettlement !== $ticketPrice) {
        $failures[] = sprintf('%s main settlement is %d; expected %d kobo.', $slug, $mainSettlement, $ticketPrice);
    }
    if ($previousSettlement >= $ticketPrice) {
        $failures[] = sprintf('%s checkout total is not the smallest amount that settles exactly.', $slug);
    }

    printf(
        "PASS %-8s customer=%s fee=%s secondary=%s main=%s\n",
        strtoupper($slug),
        tis_money($checkoutAmount),
        tis_money($fee),
        tis_money($secondaryAllocation),
        tis_money($mainSettlement)
    );
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo "All ticket-pricing checks passed.\n";
