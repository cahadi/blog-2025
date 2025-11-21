<?php

namespace App\Repositories;

use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;

class JsonPostRepository implements PostRepositoryInterface
{
    private string $file;
    private PostFactoryInterface $factory;

    public function __construct(PostFactoryInterface $factory, string $file = ROOT_PATH . '/storage/posts.json')
    {
        $this->factory = $factory;
        $this->file = $file;
    }

    public function all(): array
    {
        $posts = $this->read();
        return array_map([$this->factory, 'create'], $posts);
    }

    private function read(): array
    {
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
        $id = max(array_column($posts, 'id')) + 1;
        $newPost = ['id' => $id] + $data;
        $posts[] = $newPost;
        $this->write($posts);
        $newPost['filename'] = 'post_' . $id;
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
                $post = ['id' => $id] + $data;
                $this->write($posts);
                $post['filename'] = 'post_' . $id;
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