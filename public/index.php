<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

date_default_timezone_set('Africa/Algiers');

spl_autoload_register(function (string $class): void {
    $file = APP_ROOT . '/src/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/door-showroom'             => [\App\Controllers\HomepageController::class,     'show'],
        '/door-showroom/collections' => [\App\Controllers\CollectionsController::class,  'show'],
        '/door-showroom/configure'      => [\App\Controllers\ConfiguratorController::class, 'show'],
        '/door-showroom/configure/load' => [\App\Controllers\ConfiguratorController::class, 'load'],
        '/door-showroom/quote'          => [\App\Controllers\ConfiguratorController::class, 'quotePage'],
        '/door-showroom/contact'        => [\App\Controllers\ContactController::class,      'show'],
    ],
    'POST' => [
        '/door-showroom/configure/price' => [\App\Controllers\ConfiguratorController::class, 'price'],
        '/door-showroom/configure/save'  => [\App\Controllers\ConfiguratorController::class, 'save'],
        '/door-showroom/configure/quote' => [\App\Controllers\ConfiguratorController::class, 'quote'],
        '/door-showroom/contact/submit'  => [\App\Controllers\ContactController::class,      'submit'],
    ],
];

// The old /configurator page was a duplicate — send it to the single configurator.
if ($method === 'GET' && $uri === '/door-showroom/configurator') {
    header('Location: /door-showroom/configure', true, 301);
    exit;
}

$handler = $routes[$method][$uri] ?? null;

if ($handler) {
    [$class, $action] = $handler;
    (new $class())->$action();
    exit;
}

if ($method === 'GET' && preg_match('#^/door-showroom/collections/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    (new \App\Controllers\CollectionsController())->detail();
    exit;
}

if ($method === 'GET' && preg_match('#^/door-showroom/products?/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    (new \App\Controllers\ProductPageController())->show();
    exit;
}

http_response_code(404);
echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
