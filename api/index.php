<?php

// Vercel serverless entry point. All non-static requests are routed here.
// The application's routes/links are built around the /door-showroom base
// path, so we normalise the incoming URI to that prefix and then hand off
// to the appropriate front controller (public site or admin panel).

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Ensure the path carries the /door-showroom prefix the app routes expect.
if ($path === '/' || $path === '') {
    $path = '/door-showroom';
} elseif (strpos($path, '/door-showroom') !== 0) {
    $path = '/door-showroom' . $path;
}

$query = $_SERVER['QUERY_STRING'] ?? '';
$_SERVER['REQUEST_URI'] = $path . ($query !== '' ? '?' . $query : '');

// The front controllers define APP_ROOT and run their own routing.
if (strpos($path, '/door-showroom/admin') === 0) {
    require dirname(__DIR__) . '/public/admin.php';
} else {
    require dirname(__DIR__) . '/public/index.php';
}
