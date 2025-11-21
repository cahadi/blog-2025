<?php
declare(strict_types=1);

namespace App\Factories;

use App\Interfaces\TeaFactoryInterface;
use App\Models\Tea;

class TeaFactory implements TeaFactoryInterface
{
    public function create(array $data): Tea
    {
        return Tea::fromArray($data);
    }
}