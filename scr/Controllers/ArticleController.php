<?php
namespace App\Controllers;


use App\Models\Articles;
use App\Views\ArticleView;

class ArticleController
{
    public Articles $article;
    public ArticleView $articleView;

    public function __construct(Articles $article, ArticleView $articleView)
    {
        $this->article = $article;
        $this->articleView = $articleView;
    }

    public function showArticlesList()
    {
        $articles = $this->article->all();
        $path = /*$_SERVER['DOCUMENT_ROOT'].*/'../resources/views/index.php';
        $this->articleView->showArticlesList($path, $articles);
    }
}