<?php
declare(strict_types=1);

require '../vendor/autoload.php';

use App\Controllers\ArticleController;
use App\Controllers\FrontController;
use App\Core\FileManager;
use App\Core\Helper;
use App\Models\Articles;
use App\Models\Category;
use App\Models\Post;
use App\Views\ArticleView;
use App\Views\FrontView;

// Инициализация обработки ошибок
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

// Конфигурация
$config = require '../config/settings.php';

// Инициализация зависимостей
$fileManager = new FileManager();
$helper = new Helper();

// Модели
$articleModel = new Articles();
$categoryModel = new Category();
$postModel = new Post();

// Представления
$articleView = new ArticleView();
$frontView = new FrontView();

// Контроллеры
$articleController = new ArticleController($articleModel, $articleView, $fileManager);
$frontController = new FrontController($frontView, $categoryModel, $postModel);

// Создание директории страниц если не существует
if (!is_dir('../content/pages')) {
    mkdir('../content/pages', 0777, true);
}

// Обработка маршрутов
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router = [
    '/' => fn() => $frontController->index(),
    '/catalog' => fn() => $articleController->showArticlesList(),
    '/category/coding' => fn() => $frontController->showPostsInCategory('coding'),
    '/posts' => fn() => $frontController->showAllPosts(),
    '/calc' => fn() => include_once('../resources/front/calc.php'),
    '/dir' => fn() => $helper->dd($fileManager->listDirs('')),
    '/files' => fn() => $helper->dd($fileManager->listFiles('')),
];

$matched = false;
foreach ($router as $route => $handler) {
    if ($route === $url) {
        $handler();
        $matched = true;
        break;
    }
}

if (!$matched && str_starts_with($url, '/post/')) {
    $slug = str_replace('/post/', '', $url);
    $frontController->showPost($slug);
    $matched = true;
}

if (!$matched) {
    $frontController->page404();
}