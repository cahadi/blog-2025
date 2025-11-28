<?php
declare(strict_types=1);

namespace App\Models;

class Tea
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $image,
        public float $price,
        public string $category = '',
        public string $type = '',
        public string $origin = '',
        public string $brewTime = '',
        public int $caffeineLevel = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['name'] ?? ''),
            (string)($data['description'] ?? ''),
            (string)($data['image'] ?? ''),
            (float)($data['price'] ?? 0.0),
            (string)($data['category'] ?? ''),
            (string)($data['type'] ?? ''),
            (string)($data['origin'] ?? ''),
            (string)($data['brew_time'] ?? ''),
            (int)($data['caffeine_level'] ?? 0)
        );
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->price, 0, '', ' ') . ' ₽';
    }
}