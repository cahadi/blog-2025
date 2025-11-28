<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;

interface UserFactoryInterface
{
    public function create(array $data): User;
}