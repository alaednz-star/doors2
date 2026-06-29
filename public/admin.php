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

require_once APP_ROOT . '/src/helpers.php';

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/door-showroom/admin'                    => [\App\Controllers\Admin\DashboardController::class, 'index'],
        '/door-showroom/admin/login'              => [\App\Controllers\Admin\LoginController::class,     'showLogin'],
        '/door-showroom/admin/csrf'               => [\App\Controllers\Admin\LoginController::class,     'csrfToken'],
        '/door-showroom/admin/categories'         => [\App\Controllers\Admin\CategoryController::class,  'index'],
        '/door-showroom/admin/categories/create'  => [\App\Controllers\Admin\CategoryController::class,  'create'],
        '/door-showroom/admin/products'           => [\App\Controllers\Admin\ProductController::class,   'index'],
        '/door-showroom/admin/products/create'    => [\App\Controllers\Admin\ProductController::class,   'create'],
        '/door-showroom/admin/pricing'            => [\App\Controllers\Admin\PricingController::class,   'index'],
        '/door-showroom/admin/pricing/create'     => [\App\Controllers\Admin\PricingController::class,   'create'],
        '/door-showroom/admin/quotes'             => [\App\Controllers\Admin\QuoteController::class,      'index'],
        '/door-showroom/admin/quotes/create'      => [\App\Controllers\Admin\QuoteController::class,      'create'],
        '/door-showroom/admin/collections'        => [\App\Controllers\Admin\CollectionController::class, 'index'],
        '/door-showroom/admin/collections/create' => [\App\Controllers\Admin\CollectionController::class, 'create'],
        '/door-showroom/admin/colors'             => [\App\Controllers\Admin\ColorController::class,      'index'],
        '/door-showroom/admin/colors/create'      => [\App\Controllers\Admin\ColorController::class,      'create'],
        '/door-showroom/admin/construction-types'        => [\App\Controllers\Admin\ConstructionTypeController::class, 'index'],
        '/door-showroom/admin/construction-types/create' => [\App\Controllers\Admin\ConstructionTypeController::class, 'create'],
        '/door-showroom/admin/usages'             => [\App\Controllers\Admin\UsageController::class,       'index'],
        '/door-showroom/admin/usages/create'      => [\App\Controllers\Admin\UsageController::class,       'create'],
        '/door-showroom/admin/media'              => [\App\Controllers\Admin\MediaController::class,       'index'],
        '/door-showroom/admin/media/upload'       => [\App\Controllers\Admin\MediaController::class,       'upload'],
        '/door-showroom/admin/settings'           => [\App\Controllers\Admin\SettingsController::class,   'index'],
    ],
    'POST' => [
        '/door-showroom/admin/login'              => [\App\Controllers\Admin\LoginController::class,      'handleLogin'],
        '/door-showroom/admin/logout'             => [\App\Controllers\Admin\LoginController::class,      'handleLogout'],
        '/door-showroom/admin/categories/store'   => [\App\Controllers\Admin\CategoryController::class,   'store'],
        '/door-showroom/admin/products/store'     => [\App\Controllers\Admin\ProductController::class,    'store'],
        '/door-showroom/admin/pricing/store'      => [\App\Controllers\Admin\PricingController::class,    'store'],
        '/door-showroom/admin/quotes/store'       => [\App\Controllers\Admin\QuoteController::class,      'store'],
        '/door-showroom/admin/collections/store'  => [\App\Controllers\Admin\CollectionController::class, 'store'],
        '/door-showroom/admin/colors/store'       => [\App\Controllers\Admin\ColorController::class,      'store'],
        '/door-showroom/admin/construction-types/store' => [\App\Controllers\Admin\ConstructionTypeController::class, 'store'],
        '/door-showroom/admin/usages/store'       => [\App\Controllers\Admin\UsageController::class,       'store'],
        '/door-showroom/admin/media/store'        => [\App\Controllers\Admin\MediaController::class,       'store'],
        '/door-showroom/admin/media/store-form'   => [\App\Controllers\Admin\MediaController::class,       'storeForm'],
        '/door-showroom/admin/settings/update'    => [\App\Controllers\Admin\SettingsController::class,   'update'],
    ],
];

$handler = $routes[$method][$uri] ?? null;

if ($handler) {
    [$class, $action] = $handler;
    (new $class())->$action();
    exit;
}

/* ── Parametric routes ── */
$patterns = [
    'GET' => [
        '#^/door-showroom/admin/categories/(\d+)/edit$#'  => [\App\Controllers\Admin\CategoryController::class, 'edit'],
        '#^/door-showroom/admin/products/(\d+)/edit$#'    => [\App\Controllers\Admin\ProductController::class,   'edit'],
        '#^/door-showroom/admin/pricing/(\d+)/edit$#'     => [\App\Controllers\Admin\PricingController::class,  'edit'],
        '#^/door-showroom/admin/quotes/(\d+)$#'                    => [\App\Controllers\Admin\QuoteController::class,      'show'],
        '#^/door-showroom/admin/quotes/(\d+)/edit$#'               => [\App\Controllers\Admin\QuoteController::class,      'edit'],
        '#^/door-showroom/admin/collections/(\d+)/edit$#'          => [\App\Controllers\Admin\CollectionController::class, 'edit'],
        '#^/door-showroom/admin/colors/(\d+)/edit$#'               => [\App\Controllers\Admin\ColorController::class,      'edit'],
        '#^/door-showroom/admin/construction-types/(\d+)/edit$#'   => [\App\Controllers\Admin\ConstructionTypeController::class, 'edit'],
        '#^/door-showroom/admin/usages/(\d+)/edit$#'               => [\App\Controllers\Admin\UsageController::class,            'edit'],
        '#^/door-showroom/admin/media/(\d+)/preview$#'              => [\App\Controllers\Admin\MediaController::class,       'preview'],
    ],
    'POST' => [
        '#^/door-showroom/admin/categories/(\d+)/update$#'         => [\App\Controllers\Admin\CategoryController::class,   'update'],
        '#^/door-showroom/admin/categories/(\d+)/delete$#'         => [\App\Controllers\Admin\CategoryController::class,   'delete'],
        '#^/door-showroom/admin/categories/(\d+)/toggle$#'         => [\App\Controllers\Admin\CategoryController::class,   'toggle'],
        '#^/door-showroom/admin/products/(\d+)/update$#'           => [\App\Controllers\Admin\ProductController::class,    'update'],
        '#^/door-showroom/admin/products/(\d+)/delete$#'           => [\App\Controllers\Admin\ProductController::class,    'delete'],
        '#^/door-showroom/admin/products/(\d+)/toggle$#'           => [\App\Controllers\Admin\ProductController::class,    'toggle'],
        '#^/door-showroom/admin/products/(\d+)/images/reorder$#'   => [\App\Controllers\Admin\ProductController::class,    'reorderImages'],
        '#^/door-showroom/admin/images/(\d+)/delete$#'             => [\App\Controllers\Admin\ProductController::class,    'deleteImage'],
        '#^/door-showroom/admin/images/(\d+)/cover$#'              => [\App\Controllers\Admin\ProductController::class,    'setCover'],
        '#^/door-showroom/admin/pricing/(\d+)/update$#'            => [\App\Controllers\Admin\PricingController::class,   'update'],
        '#^/door-showroom/admin/pricing/(\d+)/delete$#'            => [\App\Controllers\Admin\PricingController::class,   'delete'],
        '#^/door-showroom/admin/pricing/(\d+)/toggle$#'            => [\App\Controllers\Admin\PricingController::class,   'toggle'],
        '#^/door-showroom/admin/pricing/(\d+)/available$#'         => [\App\Controllers\Admin\PricingController::class,   'toggleAvailable'],
        '#^/door-showroom/admin/quotes/(\d+)/update$#'             => [\App\Controllers\Admin\QuoteController::class,     'update'],
        '#^/door-showroom/admin/quotes/(\d+)/status$#'             => [\App\Controllers\Admin\QuoteController::class,     'updateStatus'],
        '#^/door-showroom/admin/quotes/(\d+)/delete$#'             => [\App\Controllers\Admin\QuoteController::class,     'delete'],
        '#^/door-showroom/admin/collections/(\d+)/update$#'        => [\App\Controllers\Admin\CollectionController::class, 'update'],
        '#^/door-showroom/admin/collections/(\d+)/delete$#'        => [\App\Controllers\Admin\CollectionController::class, 'delete'],
        '#^/door-showroom/admin/collections/(\d+)/toggle$#'        => [\App\Controllers\Admin\CollectionController::class, 'toggle'],
        '#^/door-showroom/admin/colors/(\d+)/update$#'             => [\App\Controllers\Admin\ColorController::class,     'update'],
        '#^/door-showroom/admin/colors/(\d+)/delete$#'             => [\App\Controllers\Admin\ColorController::class,     'delete'],
        '#^/door-showroom/admin/colors/(\d+)/toggle$#'             => [\App\Controllers\Admin\ColorController::class,     'toggle'],
        '#^/door-showroom/admin/colors/(\d+)/image/delete$#'       => [\App\Controllers\Admin\ColorController::class,     'deleteImage'],
        '#^/door-showroom/admin/construction-types/(\d+)/update$#' => [\App\Controllers\Admin\ConstructionTypeController::class, 'update'],
        '#^/door-showroom/admin/construction-types/(\d+)/delete$#' => [\App\Controllers\Admin\ConstructionTypeController::class, 'delete'],
        '#^/door-showroom/admin/construction-types/(\d+)/toggle$#' => [\App\Controllers\Admin\ConstructionTypeController::class, 'toggle'],
        '#^/door-showroom/admin/usages/(\d+)/update$#'             => [\App\Controllers\Admin\UsageController::class,            'update'],
        '#^/door-showroom/admin/usages/(\d+)/delete$#'             => [\App\Controllers\Admin\UsageController::class,            'delete'],
        '#^/door-showroom/admin/usages/(\d+)/toggle$#'             => [\App\Controllers\Admin\UsageController::class,            'toggle'],
        '#^/door-showroom/admin/media/(\d+)/delete$#'               => [\App\Controllers\Admin\MediaController::class,       'delete'],
        '#^/door-showroom/admin/media/(\d+)/assign$#'               => [\App\Controllers\Admin\MediaController::class,       'assign'],
        '#^/door-showroom/admin/media/(\d+)/alt$#'                  => [\App\Controllers\Admin\MediaController::class,       'updateAlt'],
    ],
];

foreach ($patterns[$method] ?? [] as $pattern => [$class, $action]) {
    if (preg_match($pattern, $uri, $m)) {
        (new $class())->$action((int)$m[1]);
        exit;
    }
}

http_response_code(404);
require APP_ROOT . '/src/Views/admin/404.php';
