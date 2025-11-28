<?php


namespace App\Factories;


use App\Interfaces\UserFactoryInterface;
use App\Models\User;

class UserFactory implements UserFactoryInterface
{

    public function create(array $data): User
    {
        return User::fromArray($data);
    }
}