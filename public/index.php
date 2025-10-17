<?php
require '../vendor/autoload.php';

use App\Controllers\ArticleController;
use App\Models\Articles;
use App\Views\ArticleView;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$article = new Articles();
$article_view = new ArticleView();
$article_controller = new ArticleController($article, $article_view);

$url = $_SERVER['REQUEST_URI'];
switch ($url)
{
    case '/':
        $article_controller->showArticlesList();
        break;
    case '/calc':
        echo include_once ('../resources/views/calc.php');
        break;
    default:
        echo include_once('../resources/views/404.php');
        break;
}