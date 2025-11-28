<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Tag;

interface TagRepositoryInterface
{
    public function all(): array;
    public function find(int $id): ?Tag;
    public function findByName(string $name): ?Tag;
    public function findBySlug(string $slug): ?Tag;
    public function create(array $data): Tag;
    public function update(int $id, array $data): ?Tag;
    public function delete(int $id): bool;
    public function attachToPost(int $tagId, int $postId): bool;
    public function detachFromPost(int $tagId, int $postId): bool;
    public function getPostTags(int $postId): array;
    public function getPostsByTag(int $tagId): array;
}