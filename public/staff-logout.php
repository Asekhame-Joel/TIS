<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

tis_require_method('POST');
tis_require_staff();
tis_staff_verify_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
tis_staff_redirect('/staff-login');
