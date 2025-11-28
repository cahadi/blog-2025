<?php

namespace App\Factories;

use App\Interfaces\TagFactoryInterface;
use App\Models\Tag;

class TagFactory implements TagFactoryInterface
{
    public function create(array $data): Tag
    {
        return Tag::fromArray($data);
    }
}