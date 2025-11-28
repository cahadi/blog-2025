<?php

namespace App\Repositories;

use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;

class JsonPostRepository implements PostRepositoryInterface
{
    public function __construct(
        private PostFactoryInterface $factory,
        private string $file = ROOT_PATH . '/storage/posts.json'
    ) {}

    public function all(): array
    {
        $posts = $this->read();
        return array_map([$this->factory, 'create'], $posts);
    }

    private function read(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $data = file_get_contents($this->file);
        return json_decode($data, true) ?: [];
    }

    public function find(int $id): ?Post
    {
        $posts = $this->read();
        foreach ($posts as $post) {
            if ($post['id'] === $id) {
                return $this->factory->create($post);
            }
        }
        return null;
    }

    public function create(array $data): Post
    {
        $posts = $this->read();
        $id = empty($posts) ? 1 : max(array_column($posts, 'id')) + 1;

        $newPost = [
            'id' => $id,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'cover_image' => $data['cover_image'] ?? '',
            'content' => $data['content'] ?? '',
            'category' => $data['category'] ?? '',
            'tags' => $data['tags'] ?? [],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $posts[] = $newPost;
        $this->write($posts);
        return $this->factory->create($newPost);
    }

    private function write(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function update(int $id, array $data): ?Post
    {
        $posts = $this->read();
        foreach ($posts as &$post) {
            if ($post['id'] === $id) {
                $post['title'] = $data['title'] ?? $post['title'];
                $post['description'] = $data['description'] ?? $post['description'];
                $post['cover_image'] = $data['cover_image'] ?? $post['cover_image'];
                $post['content'] = $data['content'] ?? $post['content'];
                $post['category'] = $data['category'] ?? $post['category'];
                $post['tags'] = $data['tags'] ?? $post['tags'] ?? [];
                $this->write($posts);
                return $this->factory->create($post);
            }
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $posts = $this->read();
        $originalCount = count($posts);
        $posts = array_filter($posts, fn($post) => $post['id'] !== $id);
        if (count($posts) === $originalCount) {
            return false;
        }
        $this->write(array_values($posts));
        return true;
    }
}