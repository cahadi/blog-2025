<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Tea;

interface TeaFactoryInterface
{
    public function create(array $data): Tea;
}