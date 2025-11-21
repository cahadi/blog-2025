<?php
declare(strict_types=1);

namespace App\Models;

class Post
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $coverImage,
        public string $content,
        public string $category = '',
        public string $filename = ''
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['title'] ?? ''),
            (string)($data['description'] ?? ''),
            (string)($data['cover_image'] ?? ''),
            (string)($data['content'] ?? ''),
            (string)($data['category'] ?? ''),
            (string)($data['filename'] ?? '')
        );
    }
}