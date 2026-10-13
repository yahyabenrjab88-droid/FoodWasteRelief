<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    
    $appRoot  = rtrim(str_replace('\\', '/', dirname(dirname(__FILE__))), '/');
    $base     = str_replace($docRoot, '', $appRoot);
    define('BASE_URL', $base === '/' ? '' : $base);
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Mailer.php';

foreach (glob(__DIR__ . '/../models/*.php') as $f) {
    require_once $f;
}

foreach (glob(__DIR__ . '/../controllers/*.php') as $f) {
    require_once $f;
}

$router = new Router();

$router->get('/', 'HomeController', 'index');
$router->get('/about', 'HomeController', 'about');
$router->get('/contact', 'ContactController', 'index');
$router->post('/contact', 'ContactController', 'submit');

$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'loginPost');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'registerPost');
$router->get('/logout', 'AuthController', 'logout');

$router->get('/forgot-password', 'PasswordResetController', 'showForgot');
$router->post('/forgot-password', 'PasswordResetController', 'processForgot');
$router->get('/reset-password/{token}', 'PasswordResetController', 'showReset');
$router->post('/reset-password/{token}', 'PasswordResetController', 'processReset');

$router->get('/products', 'ProductController', 'index');
$router->get('/products/{id}', 'ProductController', 'show');

$router->get('/cart', 'CartController', 'index');
$router->post('/cart/add', 'CartController', 'add');
$router->post('/cart/update', 'CartController', 'update');
$router->post('/cart/remove', 'CartController', 'remove');
$router->post('/cart/checkout', 'CartController', 'checkout');

$router->get('/orders', 'OrderController', 'index');
$router->get('/orders/{id}', 'OrderController', 'show');
$router->post('/business/orders/{id}/status', 'OrderController', 'updateStatus');

$router->get('/dashboard', 'DashboardController', 'index');
$router->get('/admin/dashboard', 'DashboardController', 'index');

$router->get('/business/products', 'ProductController', 'manage');
$router->get('/business/products/create', 'ProductController', 'create');
$router->post('/business/products/store', 'ProductController', 'store');
$router->get('/business/products/{id}/edit', 'ProductController', 'edit');
$router->post('/business/products/{id}/update', 'ProductController', 'update');
$router->post('/business/products/{id}/delete', 'ProductController', 'delete');

$router->get('/storage', 'StorageController', 'index');
$router->get('/storage/create', 'StorageController', 'create');
$router->post('/storage/store', 'StorageController', 'store');
$router->get('/storage/{id}/edit', 'StorageController', 'edit');
$router->post('/storage/{id}/update', 'StorageController', 'update');
$router->post('/storage/{id}/delete', 'StorageController', 'delete');

$router->get('/donations', 'DonationController', 'index');
$router->post('/donations/send', 'DonationController', 'send');
$router->post('/donations/request', 'DonationController', 'request');
$router->post('/donations/{id}/approve', 'DonationController', 'approve');
$router->post('/donations/{id}/reject', 'DonationController', 'reject');

$router->get('/admin/products', 'ProductController', 'adminIndex');

$router->get('/admin/orders', 'OrderController', 'adminIndex');
$router->get('/admin/donations', 'DonationController', 'adminIndex');

$router->get('/admin/users', 'UserController', 'index');
$router->get('/admin/users/{id}/edit', 'UserController', 'edit');
$router->post('/admin/users/{id}/update', 'UserController', 'update');
$router->post('/admin/users/{id}/delete', 'UserController', 'delete');
$router->post('/admin/users/{id}/toggle-active', 'UserController', 'toggleActive');

$router->get('/admin/businesses', 'BusinessController', 'index');
$router->post('/admin/businesses/{id}/approve', 'BusinessController', 'approve');
$router->post('/admin/businesses/{id}/reject', 'BusinessController', 'reject');

$router->get('/admin/associations', 'AssociationController', 'index');
$router->post('/admin/associations/{id}/approve', 'AssociationController', 'approve');
$router->post('/admin/associations/{id}/reject', 'AssociationController', 'reject');

$router->get('/admin/contact', 'ContactController', 'manage');
$router->post('/admin/contact/{id}/read', 'ContactController', 'markRead');

$router->get('/admin/activity', 'ActivityLogController', 'index');

$router->get('/statistics', 'StatisticsController', 'index');
$router->get('/statistics/data', 'StatisticsController', 'data');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
