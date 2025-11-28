<?php


namespace App\Interfaces;


use App\Models\Tag;

interface TagFactoryInterface
{
    public function create(array $data): Tag;
}