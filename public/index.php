<?php

session_start();

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require '../vendor/autoload.php';

$whoops = new Run;
$whoops->pushHandler(new PrettyPageHandler);
$whoops->register();

require_once '../config/settings.php';

$router = require ROOT_PATH . '/scr/bootstrap.php';

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router->get('/', 'App\Controllers\FrontController::index');
$router->get('/catalog', 'App\Controllers\FrontController::showCatalog');
$router->get('/posts', 'App\Controllers\FrontController::showAllPosts');
$router->get('/post/{id}', 'App\Controllers\FrontController::showPost');
$router->get('/tea/{id}', 'App\Controllers\FrontController::showTea');

$router->get('/login', 'App\Controllers\AuthController::showLoginForm');
$router->post('/login', 'App\Controllers\AuthController::login');
$router->get('/logout', 'App\Controllers\AuthController::logout');

$router->get('/admin', 'App\Controllers\AdminController::index');

$response = $router->dispatch($request);
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);