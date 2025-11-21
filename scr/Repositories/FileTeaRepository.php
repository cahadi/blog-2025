<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TeaFactoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Models\Tea;
use App\Services\FileHandler;
use Exception;

class FileTeaRepository implements TeaRepositoryInterface
{
    private FileHandler $fileHandler;
    private TeaFactoryInterface $factory;
    private string $teasDirectory;

    public function __construct(TeaFactoryInterface $factory, FileHandler $fileHandler)
    {
        $this->factory = $factory;
        $this->fileHandler = $fileHandler;
        $this->teasDirectory = ROOT_PATH . '/content/teas';
        $this->ensureTeasDirectoryExists();
    }

    public function all(): array
    {
        $teas = [];
        $filePaths = $this->getAllTeaFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readTeaFile($filePath);
            if ($parsed) {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'name' => $parsed['metadata']['name'] ?? '',
                    'description' => $parsed['metadata']['description'] ?? '',
                    'image' => $parsed['metadata']['image'] ?? '',
                    'price' => (float)($parsed['metadata']['price'] ?? 0.0),
                    'category' => $parsed['category'],
                    'type' => $parsed['metadata']['type'] ?? '',
                    'origin' => $parsed['metadata']['origin'] ?? '',
                    'brew_time' => $parsed['metadata']['brew_time'] ?? '',
                    'caffeine_level' => (int)($parsed['metadata']['caffeine_level'] ?? 0)
                ]);
                $teas[] = $this->factory->create($data);
            }
        }

        return $teas;
    }

    public function find(int $id): ?Tea
    {
        $filePaths = $this->getAllTeaFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readTeaFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'name' => $parsed['metadata']['name'] ?? '',
                    'description' => $parsed['metadata']['description'] ?? '',
                    'image' => $parsed['metadata']['image'] ?? '',
                    'price' => (float)($parsed['metadata']['price'] ?? 0.0),
                    'category' => $parsed['category'],
                    'type' => $parsed['metadata']['type'] ?? '',
                    'origin' => $parsed['metadata']['origin'] ?? '',
                    'brew_time' => $parsed['metadata']['brew_time'] ?? '',
                    'caffeine_level' => (int)($parsed['metadata']['caffeine_level'] ?? 0)
                ]);
                return $this->factory->create($data);
            }
        }

        return null;
    }

    public function findByCategory(string $category): array
    {
        $teas = $this->all();
        return array_filter($teas, fn($tea) => $tea->category === $category);
    }

    public function create(array $data): Tea
    {
        $name = $data['name'] ?? 'Untitled';
        $description = $data['description'] ?? '';
        $image = $data['image'] ?? '';
        $price = (float)($data['price'] ?? 0.0);
        $category = $data['category'] ?? '';
        $type = $data['type'] ?? '';
        $origin = $data['origin'] ?? '';
        $brewTime = $data['brew_time'] ?? '';
        $caffeineLevel = (int)($data['caffeine_level'] ?? 0);

        $metadata = [
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'price' => $price,
            'type' => $type,
            'origin' => $origin,
            'brew_time' => $brewTime,
            'caffeine_level' => $caffeineLevel
        ];

        $filename = $this->generateFilename($name);
        $targetFile = $this->getTeaFilePath($filename, $category);

        $counter = 1;
        while (file_exists($targetFile)) {
            $filename = $this->generateFilename($name) . '_' . $counter;
            $targetFile = $this->getTeaFilePath($filename, $category);
            $counter++;
        }

        $fileContent = $this->buildTeaFileContent($metadata, '');

        if (!file_put_contents($targetFile, $fileContent)) {
            throw new Exception("Could not write to file: $targetFile");
        }

        $newId = crc32($targetFile);
        $finalData = array_merge($metadata, [
            'id' => $newId,
            'category' => $category
        ]);

        return $this->factory->create($finalData);
    }

    public function update(int $id, array $data): ?Tea
    {
        $filePaths = $this->getAllTeaFilePaths();
        $foundFile = null;
        $parsedExisting = null;

        foreach ($filePaths as $filePath) {
            $parsed = $this->readTeaFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $foundFile = $filePath;
                $parsedExisting = $parsed;
                break;
            }
        }

        if (!$foundFile || !$parsedExisting) {
            return null;
        }

        $updatedMetadata = array_merge($parsedExisting['metadata'], [
            'name' => $data['name'] ?? $parsedExisting['metadata']['name'] ?? '',
            'description' => $data['description'] ?? $parsedExisting['metadata']['description'] ?? '',
            'image' => $data['image'] ?? $parsedExisting['metadata']['image'] ?? '',
            'price' => (float)($data['price'] ?? $parsedExisting['metadata']['price'] ?? 0.0),
            'type' => $data['type'] ?? $parsedExisting['metadata']['type'] ?? '',
            'origin' => $data['origin'] ?? $parsedExisting['metadata']['origin'] ?? '',
            'brew_time' => $data['brew_time'] ?? $parsedExisting['metadata']['brew_time'] ?? '',
            'caffeine_level' => (int)($data['caffeine_level'] ?? $parsedExisting['metadata']['caffeine_level'] ?? 0)
        ]);

        $newCategory = $data['category'] ?? $parsedExisting['category'];
        $newFilename = $this->generateFilename($updatedMetadata['name']);

        $oldFilePath = $foundFile;
        $newFilePath = $this->getTeaFilePath($newFilename, $newCategory);

        if ($oldFilePath !== $newFilePath) {
            if (file_exists($newFilePath)) {
                throw new Exception("File already exists at new path: $newFilePath");
            }

            if (!rename($oldFilePath, $newFilePath)) {
                throw new Exception("Could not move file from $oldFilePath to $newFilePath");
            }
        }

        $fileContent = $this->buildTeaFileContent($updatedMetadata, '');

        if (!file_put_contents($newFilePath, $fileContent)) {
            throw new Exception("Could not update file: $newFilePath");
        }

        $finalData = array_merge($updatedMetadata, [
            'id' => $id,
            'category' => $newCategory
        ]);

        return $this->factory->create($finalData);
    }

    public function delete(int $id): bool
    {
        $filePaths = $this->getAllTeaFilePaths();
        $foundFile = null;

        foreach ($filePaths as $filePath) {
            $parsed = $this->readTeaFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $foundFile = $filePath;
                break;
            }
        }

        if (!$foundFile || !unlink($foundFile)) {
            return false;
        }

        $this->removeEmptyDirectory(dirname($foundFile));
        return true;
    }

    private function getAllTeaFilePaths(): array
    {
        $files = [];
        if (!is_dir($this->teasDirectory)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->teasDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function readTeaFile(string $filePath): ?array
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $parts = preg_split('/^---\s*$/m', $content, 2);

        if (count($parts) < 2) {
            return [
                'metadata' => [],
                'body' => trim($content),
                'filename' => basename($filePath, '.md'),
                'category' => $this->getTeaCategoryFromPath($filePath)
            ];
        }

        $metadata = json_decode(trim($parts[0]), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return [
            'metadata' => $metadata,
            'body' => trim($parts[1] ?? ''),
            'filename' => basename($filePath, '.md'),
            'category' => $this->getTeaCategoryFromPath($filePath)
        ];
    }

    private function getTeaFilePath(string $filename, string $category): string
    {
        $dir = $this->teasDirectory;
        if ($category) {
            $dir .= '/' . $category;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $filename . '.md';
    }

    private function buildTeaFileContent(array $metadata, string $content): string
    {
        $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return $metadataJson . "\n---\n" . $content;
    }

    private function getTeaCategoryFromPath(string $filePath): string
    {
        $relativePath = substr($filePath, strlen($this->teasDirectory) + 1);
        $dir = dirname($relativePath);

        return ($dir !== '.') ? $dir : '';
    }

    private function generateFilename(string $name): string
    {
        $transliterated = transliterator_transliterate('Russian-Latin/BGN; Any-Lower', $name);
        $normalized = preg_replace('/[^a-z0-9]/', '_', $transliterated);
        return preg_replace('/_+/', '_', $normalized);
    }

    private function ensureTeasDirectoryExists(): void
    {
        if (!is_dir($this->teasDirectory)) {
            mkdir($this->teasDirectory, 0755, true);
        }
    }

    private function removeEmptyDirectory(string $dir): void
    {
        if (is_dir($dir) && count(scandir($dir)) === 2) {
            rmdir($dir);
        }
    }
}