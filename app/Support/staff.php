<?php
declare(strict_types=1);

function tis_staff_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('TISSTAFF');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/** @return array<string,mixed>|null */
function tis_staff_user(): ?array
{
    tis_staff_session_start();
    $staffId = (int) ($_SESSION['tis_staff_id'] ?? 0);
    if ($staffId < 1) {
        return null;
    }

    $query = tis_db()->prepare(
        'SELECT id, username, display_name, role, is_active FROM tis_staff_users WHERE id = ? LIMIT 1'
    );
    $query->execute([$staffId]);
    $user = $query->fetch();
    if (!is_array($user) || (int) $user['is_active'] !== 1) {
        unset($_SESSION['tis_staff_id']);
        return null;
    }

    return $user;
}

/** @return array<string,mixed> */
function tis_require_staff(?string $requiredRole = null): array
{
    $user = tis_staff_user();
    if ($user === null) {
        $next = (string) ($_SERVER['REQUEST_URI'] ?? '/staff-verify');
        header('Location: /staff-login?next=' . rawurlencode($next), true, 303);
        exit;
    }

    if ($requiredRole !== null && (string) $user['role'] !== $requiredRole) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }

    return $user;
}

function tis_staff_csrf_token(): string
{
    tis_staff_session_start();
    if (empty($_SESSION['tis_csrf'])) {
        $_SESSION['tis_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['tis_csrf'];
}

function tis_staff_verify_csrf(): void
{
    tis_staff_session_start();
    $submitted = (string) ($_POST['csrf_token'] ?? '');
    if ($submitted === '' || !hash_equals((string) ($_SESSION['tis_csrf'] ?? ''), $submitted)) {
        http_response_code(419);
        exit('This form expired. Go back, refresh the page, and try again.');
    }
}

function tis_staff_flash(string $type, string $message): void
{
    tis_staff_session_start();
    $_SESSION['tis_staff_flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type:string,message:string}|null */
function tis_staff_take_flash(): ?array
{
    tis_staff_session_start();
    $flash = $_SESSION['tis_staff_flash'] ?? null;
    unset($_SESSION['tis_staff_flash']);
    return is_array($flash) ? $flash : null;
}

function tis_staff_redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function tis_staff_event_key(): string
{
    return 'summit-2026';
}

function tis_staff_escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
