<?php
declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public string $password,
        public string $role = 'user',
        public string $createdAt = '',
        public bool $isActive = true
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['username'] ?? ''),
            (string)($data['email'] ?? ''),
            (string)($data['password'] ?? ''),
            (string)($data['role'] ?? 'user'),
            (string)($data['created_at'] ?? ''),
            (bool)($data['is_active'] ?? true)
        );
    }
}