<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\FileManager;
use App\Traits\Helper;

class Post
{
    use Helper;

    private FileManager $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager();
    }

    public function getAllPosts(?string $category = null): array
    {
        if ($category) {
            return $this->fileManager->listFiles("posts/{$category}");
        }

        $posts = $this->fileManager->listFiles('posts');
        $categories = $this->getCategories();

        foreach ($categories as $category) {
            $posts = array_merge($posts, $this->fileManager->listFiles("posts/{$category}"));
        }

        return array_unique($posts);
    }

    public function getCategories(): array
    {
        $dirs = $this->fileManager->listDirs('posts');

        return array_map(fn($dir) => basename($dir), $dirs);
    }

    public function getPost(string $path): ?array
    {
        $content = $this->fileManager->read($path);

        if (!$content) {
            return null;
        }

        $parts = explode("\n---\n", $content, 2);
        $meta = json_decode($parts[0], true) ?: [];

        return [
            'meta' => $meta,
            'body' => $parts[1] ?? ''
        ];
    }

    public function getPostsInCategory(string $categorySlug): array
    {
        $postsList = $this->getAllPosts($categorySlug);
        $posts = [];

        foreach ($postsList as $post) {
            $postData = $this->getPost($post);
            if ($postData) {
                $posts[] = $postData;
            }
        }

        return $posts;
    }
}