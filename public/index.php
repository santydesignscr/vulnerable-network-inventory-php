<?php
/**
 * Front Controller - Entry Point
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * DO NOT USE IN PRODUCTION
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Manual autoload if composer not available
spl_autoload_register(function ($class) {
    $prefix = 'NetInventory\\';
    $base_dir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize application
$app = \NetInventory\App::getInstance();
$router = new \NetInventory\Router($app);

// ============================================================================
// ROUTES DEFINITION
// ============================================================================

// Home / Dashboard
$router->get('/', function() use ($app) {
    $controller = new \NetInventory\Controller\DashboardController($app);
    return $controller->index();
});

// Authentication
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');

// Devices
$router->get('/devices', 'DeviceController@index');
$router->get('/devices/create', 'DeviceController@create');
$router->post('/devices/create', 'DeviceController@store');
$router->get('/devices/:id', 'DeviceController@view');
$router->get('/devices/:id/edit', 'DeviceController@edit');
$router->post('/devices/:id/edit', 'DeviceController@update');
$router->post('/devices/:id/delete', 'DeviceController@delete');
$router->get('/devices/export', 'DeviceController@export');

// Configurations
$router->get('/configs/:id', 'ConfigController@view');
$router->post('/configs/upload/:deviceId', 'ConfigController@upload');
$router->get('/configs/:id/download', 'ConfigController@download');
$router->post('/configs/:id/delete', 'ConfigController@delete');
$router->get('/configs/compare', 'ConfigController@compare');

// IP Assignments
$router->get('/ip', 'IpController@index');
$router->post('/ip/assign', 'IpController@assign');
$router->post('/ip/:id/delete', 'IpController@delete');
$router->get('/ip/check', 'IpController@checkAvailability');

// Search
$router->get('/search', function() use ($app) {
    $controller = new \NetInventory\Controller\DashboardController($app);
    return $controller->search();
});

// Dispatch the request
$router->dispatch();
