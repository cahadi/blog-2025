<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\FileManager;
use App\Models\Articles;
use App\Views\ArticleView;

class ArticleController
{
    private Articles $articleModel;
    private ArticleView $articleView;
    private FileManager $fileManager;

    public function __construct(
        Articles $articleModel,
        ArticleView $articleView,
        ?FileManager $fileManager = null
    ) {
        $this->articleModel = $articleModel;
        $this->articleView = $articleView;
        $this->fileManager = $fileManager ?? new FileManager();
    }

    public function showArticlesList(): void
    {
        $articlesFiles = $this->fileManager->listFiles('posts', '.md');
        $articlesDir = $this->fileManager->listDirs('');

        $articleContents = [];
        $filePath = 'posts/tea.md';
        $content = $this->fileManager->read($filePath);

        if ($content !== false) {
            $articleContents[$filePath] = $content;
        }

        $articles = $this->articleModel->all();
        $viewPath = '../resources/front/catalog.php';

        $this->articleView->showArticlesList($viewPath, $articles, $articleContents);
    }
}