<?php
namespace App\Views;


class ArticleView
{
    protected $path;

    public function showArticlesList(string $path, array $articles)
    {
        print $this->path = include_once($path);
    }
}