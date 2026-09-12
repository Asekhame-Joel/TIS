<?php
declare(strict_types=1);

$candidates = array_filter([
    getenv('TIS_APP_BOOTSTRAP') ?: null,
    isset($_SERVER['DOCUMENT_ROOT'])
        ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') . '/app/bootstrap.php'
        : null,
    dirname(__DIR__) . '/app/bootstrap.php',
]);

foreach ($candidates as $bootstrap) {
    if (is_file($bootstrap)) {
        require $bootstrap;
        return;
    }
}

http_response_code(500);
exit('The website application is not installed correctly.');
