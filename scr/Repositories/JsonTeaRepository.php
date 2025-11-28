<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TeaFactoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Models\Tea;

class JsonTeaRepository implements TeaRepositoryInterface
{
    public function __construct(
        private TeaFactoryInterface $factory,
        private string $file = ROOT_PATH . '/storage/teas.json')
    {}

    public function all(): array
    {
        $teas = $this->read();
        return array_map([$this->factory, 'create'], $teas);
    }

    public function find(int $id): ?Tea
    {
        $teas = $this->read();

        foreach ($teas as $tea) {
            if ($tea['id'] === $id) {
                return $this->factory->create($tea);
            }
        }

        return null;
    }

    public function findByCategory(string $category): array
    {
        $teas = $this->read();
        $filteredTeas = array_filter($teas, fn($tea) => $tea['category'] === $category);

        return array_map([$this->factory, 'create'], $filteredTeas);
    }

    public function create(array $data): Tea
    {
        $teas = $this->read();
        $id = empty($teas) ? 1 : max(array_column($teas, 'id')) + 1;

        $newTea = ['id' => $id] + $data;
        $teas[] = $newTea;

        $this->write($teas);
        return $this->factory->create($newTea);
    }

    public function update(int $id, array $data): ?Tea
    {
        $teas = $this->read();

        foreach ($teas as &$tea) {
            if ($tea['id'] === $id) {
                $tea = ['id' => $id] + $data;
                $this->write($teas);
                return $this->factory->create($tea);
            }
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $teas = $this->read();
        $countBefore = count($teas);

        $teas = array_filter($teas, fn($tea) => $tea['id'] !== $id);

        if (count($teas) === $countBefore) {
            return false;
        }

        $this->write(array_values($teas));
        return true;
    }

    private function read(): array
    {
        if (!file_exists($this->file)) {
            $this->write([]);
        }

        return json_decode(file_get_contents($this->file) ?: '[]', true) ?: [];
    }

    private function write(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}