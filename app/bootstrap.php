<?php
declare(strict_types=1);

define('TIS_APP_ROOT', __DIR__);
define('TIS_PROJECT_ROOT', dirname(__DIR__));

$autoload = TIS_PROJECT_ROOT . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Composer dependencies are missing. Run composer install.');
}
require $autoload;

if (class_exists(Dotenv\Dotenv::class) && is_file(TIS_PROJECT_ROOT . '/.env')) {
    Dotenv\Dotenv::createImmutable(TIS_PROJECT_ROOT)->safeLoad();
}

require TIS_APP_ROOT . '/Support/helpers.php';

ini_set('display_errors', '0');
date_default_timezone_set('Africa/Lagos');
