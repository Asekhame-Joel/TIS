<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit("This script may only be run from the terminal.\n");
}

$username = strtolower(trim((string) ($argv[1] ?? '')));
$displayName = trim((string) ($argv[2] ?? ''));
$role = strtolower(trim((string) ($argv[3] ?? 'gate')));

if (!preg_match('/^[a-z0-9._-]{3,64}$/', $username)) {
    exit("Usage: php scripts/create-staff-user.php username \"Display Name\" admin|gate\n");
}
if ($displayName === '' || strlen($displayName) > 100 || !in_array($role, ['admin', 'gate'], true)) {
    exit("Provide a display name of up to 100 characters and the role admin or gate.\n");
}

fwrite(STDOUT, 'Password (at least 12 characters): ');
if (DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec')) {
    shell_exec('stty -echo');
}
$password = rtrim((string) fgets(STDIN), "\r\n");
if (DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec')) {
    shell_exec('stty echo');
}
fwrite(STDOUT, "\n");

if (strlen($password) < 12) {
    exit("Password must contain at least 12 characters.\n");
}

$insert = tis_db()->prepare(
    'INSERT INTO tis_staff_users (username, display_name, password_hash, role) VALUES (?, ?, ?, ?)'
);
try {
    $insert->execute([$username, $displayName, password_hash($password, PASSWORD_DEFAULT), $role]);
} catch (PDOException $error) {
    if ((string) $error->getCode() === '23000') {
        exit("That username already exists.\n");
    }
    throw $error;
}

fwrite(STDOUT, "Created {$role} user {$username}.\n");
