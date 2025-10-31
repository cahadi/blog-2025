<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\FileManager;
use App\Models\Category;
use App\Models\Post;
use App\Traits\Helper;
use App\Views\FrontView;

class FrontController
{
    use Helper;

    private FrontView $view;
    private Category $categoryModel;
    private Post $postModel;
    private FileManager $fileManager;

    public function __construct(
        FrontView $view,
        Category $categoryModel,
        Post $postModel
    ) {
        $this->view = $view;
        $this->categoryModel = $categoryModel;
        $this->postModel = $postModel;
        $this->fileManager = new FileManager();
    }

    public function index(): void
    {
        /*
        $content = $this->renderTemplate('home.php', [
            'title' => 'Главная страница',
            'description' => 'Добро пожаловать на наш сайт'
        ]);*/

        $this->view->render('home.php', [
            'meta' => [
                'title' => 'Главная - Мой сайт',
                'description' => 'Добро пожаловать на наш сайт'
            ]
        ]);
    }

    public function showPostsInCategory(string $categorySlug): void
    {
        $categories = $this->categoryModel->getCategories();
        $posts = $this->postModel->getPostsInCategory($categorySlug);

        $this->view->render('posts.php', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $categorySlug
        ]);
    }

    public function showAllPosts(): void
    {
        $categories = $this->categoryModel->getCategories();
        $posts = $this->postModel->getAllPosts();

        $this->view->render('posts.php', [
            'posts' => $posts,
            'categories' => $categories
        ]);
    }

    public function showPost(string $postSlug): void
    {
        $post = $this->postModel->getPost("posts/{$postSlug}.md");

        if (!$post) {
            $this->page404();
            return;
        }

        $content = $this->renderTemplate('post.php', [
            'post' => $post
        ]);

        $this->view->render('layout.php', [
            'content' => $content,
            'meta' => [
                'title' => $post['meta']['title'] ?? 'Пост',
                'description' => $post['meta']['description'] ?? ''
            ]
        ]);
    }

    public function page404(): void
    {
        http_response_code(404);
        $this->view->render('404.php');
    }

    private function renderTemplate(string $template, array $data = []): string
    {
        ob_start();
        extract($data);
        include $this->view->getTemplatePath() . $template;
        return ob_get_clean();
    }
}