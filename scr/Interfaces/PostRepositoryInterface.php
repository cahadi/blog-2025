<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Post;

interface PostRepositoryInterface
{
    public function all(): array;
    public function find(int $id): ?Post;
    public function create(array $data): Post;
    public function update(int $id, array $data): ?Post;
    public function delete(int $id): bool;
}