<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_staff_session_start();
if (tis_staff_user() !== null) {
    tis_staff_redirect('/staff-verify');
}

$error = '';
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '/staff-verify');
if (!preg_match('~^/staff-[a-z-]+(?:\?[A-Za-z0-9_=&%.-]*)?$~', $next)) {
    $next = '/staff-verify';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    tis_staff_verify_csrf();
    $username = strtolower(trim((string) ($_POST['username'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $query = tis_db()->prepare(
        'SELECT id, password_hash FROM tis_staff_users WHERE username = ? AND is_active = 1 LIMIT 1'
    );
    $query->execute([$username]);
    $candidate = $query->fetch();

    if (is_array($candidate) && password_verify($password, (string) $candidate['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['tis_staff_id'] = (int) $candidate['id'];
        unset($_SESSION['tis_csrf']);
        tis_db()->prepare('UPDATE tis_staff_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')
            ->execute([(int) $candidate['id']]);
        tis_staff_redirect($next);
    }
    usleep(300000);
    $error = 'The username or password is incorrect.';
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff login | The Intellectual Summit</title>
<link rel="stylesheet" href="<?= tis_asset('/assets/css/staff.css') ?>"></head>
<body class="staff-login-page"><main class="login-card">
  <div class="staff-brand"><span class="staff-mark" aria-hidden="true"><i></i><i></i><i></i></span><span>The Intellectual Summit</span></div>
  <p class="staff-eyebrow">Authorised access</p><h1>Staff login</h1>
  <p class="staff-muted">Sign in to manage or verify physical tickets.</p>
  <?php if ($error !== ''): ?><div class="staff-alert error" role="alert"><?= tis_staff_escape($error) ?></div><?php endif; ?>
  <form method="post" class="staff-form">
    <input type="hidden" name="csrf_token" value="<?= tis_staff_escape(tis_staff_csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= tis_staff_escape($next) ?>">
    <label>Username<input name="username" autocomplete="username" required maxlength="64" autofocus></label>
    <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
    <button class="staff-button primary" type="submit">Sign in</button>
  </form>
</main></body></html>
