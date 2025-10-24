<?php
namespace App\Views;

class ArticleView
{
    public function showArticlesList(string $path, array $articles, array $articleContents = [])
    {
        include $path;
    }
}