<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\UserFactoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class JsonUserRepository implements UserRepositoryInterface
{

    public function __construct(
        private UserFactoryInterface $factory,
        private string $file = ROOT_PATH . '/storage/users.json')
    {}

    public function all(): array
    {
        $users = $this->read();
        return array_map([$this->factory, 'create'], $users);
    }

    private function read(): array
    {
        $data = file_get_contents($this->file);
        return json_decode($data, true) ?: [];
    }

    private function write(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function find(int $id): ?User
    {
        $users = $this->read();
        foreach ($users as $user)
        {
            if ($user['id'] === $id)
            {
                return $this->factory->create($user);
            }
        }
        return null;
    }

    public function findByUsername(string $username): ?User
    {
        $users = $this->read();
        foreach ($users as $user)
        {
            $filteredUser =+ array_filter($users, fn($username) => $user['username'] === $username);
        }
        return array_map([$this->factory, 'create'], $filteredUser);
    }

    public function findByEmail(string $email): ?User
    {
        $users = $this->read();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $this->factory->create($user);
            }
        }
        return null;
    }

    public function create(array $data): User
    {
        $users = $this->read();
        $id = empty($users) ? 1 : max(array_column($users, 'id')) + 1;
        $newUser = ['id' => $id] + $data;
        $users[] = $newUser;
        $this->write($users);
        $newUser['filename'] = 'user_' . $id;
        return $this->factory->create($newUser);
    }

    public function update(int $id, array $data): ?User
    {
        $users = $this->read();
        foreach ($users as &$user) {
            if ($user['id'] === $id) {
                $user = ['id' => $id] + $data;
                $this->write($users);
                $user['filename'] = 'tea_' . $id;
                return $this->factory->create($user);
            }
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $users = $this->read();
        $originalCount = count($users);
        $users = array_filter($users, fn($user) => $user['id'] !== $id);
        if (count($users) === $originalCount) {
            return false;
        }
        $this->write(array_values($users));
        return true;
    }
}