<?php
declare(strict_types=1);

$candidates = array_filter([
    getenv('TIS_APP_BOOTSTRAP') ?: null,
    dirname(__DIR__) . '/app/bootstrap.php',
    isset($_SERVER['DOCUMENT_ROOT'])
        ? dirname(rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/')) . '/tis-app/app/bootstrap.php'
        : null,
]);

foreach ($candidates as $bootstrap) {
    if (is_file($bootstrap)) {
        require $bootstrap;
        return;
    }
}

http_response_code(500);
exit('The website application is not installed correctly.');
