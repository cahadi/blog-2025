<?php
namespace App\Controllers;

use App\Models\Articles;
use App\Views\ArticleView;
use App\Core\FileManager;

class ArticleController
{
    public Articles $article;
    public ArticleView $articleView;
    protected FileManager $fileManager;

    public function __construct(Articles $article, ArticleView $articleView, ?FileManager $fileManager = null)
    {
        $this->article = $article;
        $this->articleView = $articleView;
        $this->fileManager = $fileManager ?? new FileManager();
    }

    public function showArticlesList()
    {
        $articlesFiles = $this->fileManager->listFiles('content/posts/tea', '.md');

        $articleContents = [];
        $filePath = '../../../content/posts/tea.md';
        $content = $this->fileManager->read('posts/tea.md');
        if ($content !== false) {
            $articleContents[$filePath] = $content;
        }

        $articles = $this->article->all();

        $path = '../resources/views/index.php';

        $this->articleView->showArticlesList($path, $articles, $articleContents);
    }
}