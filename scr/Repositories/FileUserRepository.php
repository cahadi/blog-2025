<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\UserFactoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Services\FileHandler;
use Exception;

class FileUserRepository implements UserRepositoryInterface
{
    private FileHandler $fileHandler;
    private UserFactoryInterface $factory;

    public function __construct(UserFactoryInterface $factory, FileHandler $fileHandler)
    {
        $this->factory = $factory;
        $this->fileHandler = $fileHandler;
    }

    public function all(): array
    {
        $users = [];
        $filePaths = $this->getAllUserFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
            if ($parsed) {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'username' => $parsed['metadata']['username'] ?? '',
                    'email' => $parsed['metadata']['email'] ?? '',
                    'password' => $parsed['metadata']['password'] ?? '',
                    'role' => $parsed['metadata']['role'] ?? 'user',
                    'created_at' => $parsed['metadata']['created_at'] ?? '',
                    'is_active' => (bool)($parsed['metadata']['is_active'] ?? true),
                    'category' => $parsed['category']
                ]);
                $users[] = $this->factory->create($data);
            }
        }

        return $users;
    }

    public function find(int $id): ?User
    {
        $filePaths = $this->getAllUserFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'username' => $parsed['metadata']['username'] ?? '',
                    'email' => $parsed['metadata']['email'] ?? '',
                    'password' => $parsed['metadata']['password'] ?? '',
                    'role' => $parsed['metadata']['role'] ?? 'user',
                    'created_at' => $parsed['metadata']['created_at'] ?? '',
                    'is_active' => (bool)($parsed['metadata']['is_active'] ?? true)
                ]);
                return $this->factory->create($data);
            }
        }

        return null;
    }

    public function findByUsername(string $username): ?User
    {
        $filePaths = $this->getAllUserFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
            if ($parsed && ($parsed['metadata']['username'] ?? '') === $username) {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'username' => $username
                ]);
                return $this->factory->create($data);
            }
        }

        return null;
    }

    public function findByEmail(string $email): ?User
    {
        $filePaths = $this->getAllUserFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
            if ($parsed && ($parsed['metadata']['email'] ?? '') === $email) {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'email' => $email
                ]);
                return $this->factory->create($data);
            }
        }

        return null;
    }

    public function create(array $data): User
    {
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'user';
        $isActive = (bool)($data['is_active'] ?? true);

        $metadata = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $filename = $this->generateFilename($username);
        $category = $data['category'] ?? '';
        $targetFile = $this->getUserFilePath($filename, $category);

        $counter = 1;
        while (file_exists($targetFile)) {
            $filename = $this->generateFilename($username) . '_' . $counter;
            $targetFile = $this->getUserFilePath($filename, $category);
            $counter++;
        }

        $fileContent = $this->buildUserFileContent($metadata, '');

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

    public function update(int $id, array $data): ?User
    {
        $filePaths = $this->getAllUserFilePaths();
        $foundFile = null;
        $parsedExisting = null;

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
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
            'username' => $data['username'] ?? $parsedExisting['metadata']['username'] ?? '',
            'email' => $data['email'] ?? $parsedExisting['metadata']['email'] ?? '',
            'role' => $data['role'] ?? $parsedExisting['metadata']['role'] ?? 'user',
            'is_active' => (bool)($data['is_active'] ?? $parsedExisting['metadata']['is_active'] ?? true),
        ]);

        $newCategory = $data['category'] ?? $parsedExisting['category'];
        $newFilename = $this->generateFilename($updatedMetadata['username']);

        $oldFilePath = $foundFile;
        $newFilePath = $this->getUserFilePath($newFilename, $newCategory);

        if ($oldFilePath !== $newFilePath) {
            if (file_exists($newFilePath)) {
                throw new Exception("File already exists at new path: $newFilePath");
            }

            if (!rename($oldFilePath, $newFilePath)) {
                throw new Exception("Could not move file from $oldFilePath to $newFilePath");
            }
        }

        $fileContent = $this->buildUserFileContent($updatedMetadata, '');

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
        $filePaths = $this->getAllUserFilePaths();
        $foundFile = null;

        foreach ($filePaths as $filePath) {
            $parsed = $this->readUserFile($filePath);
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

    private function getAllUserFilePaths(): array
    {
        $files = [];
        if (!is_dir($this->usersDirectory)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->usersDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function readUserFile(string $filePath): ?array
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
                'category' => $this->getUserCategoryFromPath($filePath)
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
            'category' => $this->getUserCategoryFromPath($filePath)
        ];
    }

    private function getUserFilePath(string $filename, string $category): string
    {
        $dir = $this->usersDirectory;
        if ($category) {
            $dir .= '/' . $category;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $filename . '.md';
    }

    private function buildUserFileContent(array $metadata, string $content): string
    {
        $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return $metadataJson . "\n---\n" . $content;
    }

    private function generateFilename(string $username): string
    {
        $transliterated = transliterator_transliterate('Russian-Latin/BGN; Any-Lower', $username);
        $normalized = preg_replace('/[^a-z0-9]/', '_', $transliterated);
        return preg_replace('/_+/', '_', $normalized);
    }

    private function getUserCategoryFromPath(string $filePath): string
    {
        $relativePath = substr($filePath, strlen($this->usersDirectory) + 1);
        $dir = dirname($relativePath);

        return ($dir !== '.') ? $dir : '';
    }

    private function removeEmptyDirectory(string $dir): void
    {
        if (is_dir($dir) && count(scandir($dir)) === 2) {
            rmdir($dir);
        }
    }
}