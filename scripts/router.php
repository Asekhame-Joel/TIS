<?php
declare(strict_types=1);

$public = dirname(__DIR__) . '/public';
$path = rawurldecode(parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$candidate = realpath($public . $path);

if ($candidate !== false && str_starts_with($candidate, realpath($public)) && is_file($candidate)) {
    return false;
}

if ($path === '/') {
    require $public . '/index.php';
    return true;
}

$page = $public . '/' . trim($path, '/') . '.php';
if (is_file($page)) {
    require $page;
    return true;
}

http_response_code(404);
echo 'Page not found.';
return true;
