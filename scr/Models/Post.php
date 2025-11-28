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
        public string $filename = '',
        public array $tags = [],
        public string $createdAt = ''
    ) {}

    public static function fromArray(array $data): self
    {
        $tags = isset($data['tags']) ? (array)$data['tags'] : [];

        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['title'] ?? ''),
            (string)($data['description'] ?? ''),
            (string)($data['cover_image'] ?? ''),
            (string)($data['content'] ?? ''),
            (string)($data['category'] ?? ''),
            (string)($data['filename'] ?? ''),
            $tags,
            (string)($data['created_at'] ?? date('Y-m-d H:i:s'))
        );
    }

    public function addTag(string $tag): void
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    public function removeTag(string $tag): void
    {
        $this->tags = array_filter($this->tags, fn($t) => $t !== $tag);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    public function getTagNames(): array
    {
        return $this->tags;
    }
}