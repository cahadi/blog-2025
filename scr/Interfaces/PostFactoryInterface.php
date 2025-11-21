<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Post;

interface PostFactoryInterface
{
    public function create(array $data): Post;
}