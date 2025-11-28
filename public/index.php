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

$router = require ROOT_PATH . '/scr/bootstrap.php';

$router->get('/', 'App\Controllers\FrontController::index');
$router->get('/catalog', 'App\Controllers\FrontController::showCatalog');
$router->get('/posts', 'App\Controllers\FrontController::showAllPosts');
$router->get('/post/{id}', 'App\Controllers\FrontController::showPost');
$router->get('/tea/{id}', 'App\Controllers\FrontController::showTea');

$router->post('/favorites/add', 'App\Controllers\FavoriteController::addToFavorites');
$router->post('/favorites/remove', 'App\Controllers\FavoriteController::removeFromFavorites');
$router->get('/favorites', 'App\Controllers\FavoriteController::getFavorites');

$router->get('/login', 'App\Controllers\AuthController::showLoginForm');
$router->post('/login', 'App\Controllers\AuthController::login');
$router->get('/logout', 'App\Controllers\AuthController::logout');

$router->get('/admin', 'App\Controllers\AdminController::index');
$router->get('/admin/posts', 'App\Controllers\AdminController::showAllPosts');
$router->get('/admin/teas', 'App\Controllers\AdminController::showAllTeas');
$router->get('/admin/users', 'App\Controllers\AdminController::showAllUsers');

$router->get('/admin/post/edit/{id}', 'App\Controllers\AdminController::editPost');
$router->get('/admin/post/create', 'App\Controllers\AdminController::createPost');
$router->post('/admin/post/save', 'App\Controllers\AdminController::savePost');
$router->post('/admin/post/delete/{id}', 'App\Controllers\AdminController::deletePost');

$router->get('/admin/tea/edit/{id}', 'App\Controllers\AdminController::editTea');
$router->get('/admin/tea/create', 'App\Controllers\AdminController::createTea');
$router->post('/admin/tea/save', 'App\Controllers\AdminController::saveTea');
$router->post('/admin/tea/delete/{id}', 'App\Controllers\AdminController::deleteTea');

$router->get('/admin/user/edit/{id}', 'App\Controllers\AdminController::editUser');
$router->get('/admin/user/create', 'App\Controllers\AdminController::createUser');
$router->post('/admin/user/save', 'App\Controllers\AdminController::saveUser');
$router->post('/admin/user/delete/{id}', 'App\Controllers\AdminController::deleteUser');

$router->get('/api/teas', 'App\Controllers\ApiController::getTeas');
$router->get('/api/posts', 'App\Controllers\ApiController::getPosts');
$router->get('/api/tea/{id}', 'App\Controllers\ApiController::getTea');
$router->get('/api/post/{id}', 'App\Controllers\ApiController::getPost');
$router->post('/api/login', 'App\Controllers\ApiController::login');

$response = $router->dispatch($request);
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);