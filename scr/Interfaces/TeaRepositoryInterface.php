<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Tea;

interface TeaRepositoryInterface
{
    public function all(): array;
    public function find(int $id): ?Tea;
    public function findByCategory(string $category): array;
    public function create(array $data): Tea;
    public function update(int $id, array $data): ?Tea;
    public function delete(int $id): bool;
}