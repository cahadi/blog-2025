<?php
declare(strict_types=1);

namespace App\Models;

class Tag
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $createdAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['name'] ?? ''),
            (string)($data['slug'] ?? ''),
            (string)($data['created_at'] ?? '')
        );
    }
}