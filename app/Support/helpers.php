<?php
declare(strict_types=1);

function tis_config(): array
{
    static $config;
    if (is_array($config)) {
        return $config;
    }

    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') : '';
    $localConfigPath = TIS_PROJECT_ROOT . '/config.local.php';
    $privateConfigPath = $documentRoot !== ''
        ? dirname($documentRoot) . '/tis-private/config.php'
        : dirname(TIS_PROJECT_ROOT) . '/tis-private/config.php';
    $environmentConfigPath = $_ENV['TIS_CONFIG_PATH'] ?? (getenv('TIS_CONFIG_PATH') ?: '');
    $configPath = $environmentConfigPath !== '' ? $environmentConfigPath : $privateConfigPath;

    if (is_file($configPath)) {
        $config = require $configPath;
    } elseif (is_file($localConfigPath)) {
        // Local development may use a small override file.
        $defaults = require TIS_PROJECT_ROOT . '/config/defaults.php';
        $localConfig = require $localConfigPath;
        $config = array_replace_recursive($defaults, $localConfig);
    } else {
        throw new RuntimeException('The private TIS configuration file is missing.');
    }

    if (!is_array($config)) {
        throw new RuntimeException('The private TIS configuration must return an array.');
    }

    return $config;
}

function tis_db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = tis_config()['database'];
    if ($db['name'] === '' || $db['user'] === '') {
        throw new RuntimeException('Ticket database configuration is incomplete.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        (int) $db['port'],
        $db['name'],
        $db['charset']
    );

    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function tis_json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        tis_json_response(['success' => false, 'message' => 'The submitted checkout data is invalid.'], 400);
    }

    return $data;
}

function tis_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function tis_require_method(string $method): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== strtoupper($method)) {
        header('Allow: ' . strtoupper($method));
        tis_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function tis_site_url(string $path = ''): string
{
    $base = rtrim((string) tis_config()['site_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function tis_paystack_fee_kobo(int $amountKobo, ?array $config = null): int
{
    if ($amountKobo < 0) {
        throw new RuntimeException('A payment amount cannot be negative.');
    }

    $config ??= tis_config();
    $fee = $config['paystack_fee'] ?? null;
    if (!is_array($fee)) {
        throw new RuntimeException('Paystack fee configuration is missing.');
    }

    $basisPoints = (int) ($fee['percentage_basis_points'] ?? 0);
    $fixedKobo = (int) ($fee['fixed_kobo'] ?? 0);
    $waiverThreshold = (int) ($fee['fixed_waiver_threshold_kobo'] ?? 0);
    $capKobo = (int) ($fee['cap_kobo'] ?? 0);
    if ($basisPoints < 0 || $basisPoints >= 10000 || $fixedKobo < 0 || $waiverThreshold < 0 || $capKobo < 1) {
        throw new RuntimeException('Paystack fee configuration is invalid.');
    }

    // Paystack rounds a fractional percentage charge up to the next kobo.
    $percentageFee = intdiv(($amountKobo * $basisPoints) + 9999, 10000);
    $fixedFee = $amountKobo < $waiverThreshold ? 0 : $fixedKobo;

    return min($capKobo, $percentageFee + $fixedFee);
}

function tis_ticket_checkout_amount(array $tier, ?array $config = null): int
{
    $config ??= tis_config();
    $ticketPrice = (int) ($tier['ticket_price_kobo'] ?? 0);
    $secondaryAllocation = (int) ($config['secondary_allocation_kobo'] ?? 0);
    if ($ticketPrice < 1 || $secondaryAllocation < 0) {
        throw new RuntimeException('Ticket settlement configuration is invalid.');
    }

    $low = $ticketPrice + $secondaryAllocation;
    $feeCap = (int) (($config['paystack_fee']['cap_kobo'] ?? 0));
    $high = $low + max(1, $feeCap);

    // Find the smallest whole-kobo charge that covers the fee and both settlements.
    while ($low < $high) {
        $middle = intdiv($low + $high, 2);
        $mainSettlement = $middle - tis_paystack_fee_kobo($middle, $config) - $secondaryAllocation;
        if ($mainSettlement >= $ticketPrice) {
            $high = $middle;
        } else {
            $low = $middle + 1;
        }
    }

    $mainSettlement = $low - tis_paystack_fee_kobo($low, $config) - $secondaryAllocation;
    if ($mainSettlement !== $ticketPrice) {
        throw new RuntimeException('The configured Paystack fee cannot produce the exact ticket settlement.');
    }

    return $low;
}

function tis_ticket_tiers(?array $config = null): array
{
    $config ??= tis_config();
    $tiers = $config['ticket_tiers'] ?? null;
    if (!is_array($tiers) || $tiers === []) {
        throw new RuntimeException('Ticket tier configuration is missing.');
    }

    $secondaryAllocation = (int) ($config['secondary_allocation_kobo'] ?? 0);
    foreach ($tiers as $slug => &$tier) {
        if (!is_string($slug) || !preg_match('/^[a-z][a-z0-9_]{1,23}$/', $slug) || !is_array($tier)) {
            throw new RuntimeException('Ticket tier configuration is invalid.');
        }
        $tier['slug'] = $slug;
        $tier['secondary_allocation_kobo'] = $secondaryAllocation;
        if (
            trim((string) ($tier['label'] ?? '')) === '' ||
            (int) ($tier['ticket_price_kobo'] ?? 0) < 100 ||
            !preg_match('/^[A-Z0-9]{2,6}$/', (string) ($tier['ticket_prefix'] ?? ''))
        ) {
            throw new RuntimeException('Ticket tier configuration is invalid for ' . $slug . '.');
        }
        $tier['checkout_amount_kobo'] = tis_ticket_checkout_amount($tier, $config);
    }
    unset($tier);

    return $tiers;
}

function tis_ticket_tier(string $slug, ?array $config = null): ?array
{
    $normalized = strtolower(trim($slug));
    $tiers = tis_ticket_tiers($config);
    return isset($tiers[$normalized]) ? $tiers[$normalized] : null;
}

function tis_money(int $amountKobo, bool $includeDecimals = true): string
{
    $amount = $amountKobo / 100;
    return 'NGN ' . number_format($amount, $includeDecimals ? 2 : 0);
}

function tis_client_hash(): string
{
    return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

function tis_assert_payment_config(): void
{
    $config = tis_config();
    if (!preg_match('/^sk_(test|live)_[A-Za-z0-9]+$/', (string) $config['paystack_secret_key'])) {
        throw new RuntimeException('Paystack has not been configured on the server.');
    }
    if (!preg_match('/^SPL_[A-Za-z0-9]+$/', (string) $config['paystack_split_code'])) {
        throw new RuntimeException('The Paystack split group has not been configured on the server.');
    }

    $isTestKey = str_starts_with((string) $config['paystack_secret_key'], 'sk_test_');
    if (($config['environment'] === 'test') !== $isTestKey) {
        throw new RuntimeException('The Paystack key does not match the configured environment.');
    }

    tis_ticket_tiers($config);
}

function tis_log(string $message): void
{
    error_log('[TIS payments] ' . preg_replace('/[\r\n]+/', ' ', $message));
}

function tis_render_header(string $activePage = '', array $options = []): void
{
    $minimalHeader = (bool) ($options['minimal'] ?? false);
    require TIS_APP_ROOT . '/Views/partials/header.php';
}

function tis_render_footer(array $options = []): void
{
    $minimalFooter = (bool) ($options['minimal'] ?? false);
    $founderCredit = (bool) ($options['founder_credit'] ?? false);
    require TIS_APP_ROOT . '/Views/partials/footer.php';
}
