<?php
require '../vendor/autoload.php';

use App\Controllers\ArticleController;
use App\Models\Articles;
use App\Views\ArticleView;
use App\Core\FileManager;

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$h =new \App\Core\Helper();
$config = require '../config/settings.php';
//$h::dd($config);

$fileManager = new FileManager();

$article = new Articles();
$articleView = new ArticleView();
$articleController = new ArticleController($article, $articleView, $fileManager);

if (!is_dir('../content/pages')) {
    mkdir('../content/pages', 0777, true);
}

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($url) {
    case '/':
        $articleController->showArticlesList();
        break;
    case '/calc':
        echo include_once('../resources/views/calc.php');
        break;
    case '/dir':
        $dirs = $fileManager->listDirs('');
        echo '<pre>' . print_r($dirs, true) . '</pre>';
        break;
    default:
        echo include_once('../resources/views/404.php');
        break;
}