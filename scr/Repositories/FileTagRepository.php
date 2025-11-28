<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TagFactoryInterface;
use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;
use App\Services\FileHandler;
use Exception;

class FileTagRepository implements TagRepositoryInterface
{
    private FileHandler $fileHandler;
    private TagFactoryInterface $factory;
    private string $tagsDirectory;

    public function __construct(TagFactoryInterface $factory, FileHandler $fileHandler)
    {
        $this->factory = $factory;
        $this->fileHandler = $fileHandler;
        $this->tagsDirectory = ROOT_PATH . '/content/tags';
        $this->ensureTagsDirectoryExists();
    }

    public function all(): array
    {
        $tags = [];
        $filePaths = $this->getAllTagFilePaths();
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed)
            {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'name' => $parsed['metadata']['name'] ?? '',
                    'slug' => $parsed['metadata']['slug'] ?? '',
                    'created_at' => $parsed['metadata']['created_at'] ?? ''
                ]);
                $tags[] = $this->factory->create($data);
            }
        }
        return $tags;
    }

    public function find(int $id): ?Tag
    {
        $filePaths = $this->getAllTagFilePaths();
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed && crc32($filePath) === $id)
            {
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'name' => $parsed['metadata']['name'] ?? '',
                    'slug' => $parsed['metadata']['slug'] ?? '',
                    'created_at' => $parsed['metadata']['created_at'] ?? ''
                ]);
                return $this->factory->create($data);
            }
        }
        return null;
    }

    public function findByName(string $name): ?Tag
    {
        $filePaths = $this->getAllTagFilePaths();
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed && ($parsed['metadata']['name'] ?? '') === $name)
            {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'name' => $name
                ]);
                return $this->factory->create($data);
            }
        }
        return null;
    }

    public function findBySlug(string $slug): ?Tag
    {
        $filePaths = $this->getAllTagFilePaths();
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed && ($parsed['metadata']['slug'] ?? '') === $slug)
            {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'slug' => $slug
                ]);
                return $this->factory->create($data);
            }
        }
        return null;
    }

    public function create(array $data): Tag
    {
        $name = $data['name'] ?? '';
        $slug = $this->generateSlug($name);
        $createdAt = date('Y-m-d H:i:s');
        $metadata = [
            'name' => $name,
            'slug' => $slug,
            'created_at' => $createdAt
        ];
        $filename = $slug;
        $targetFile = $this->getTagFilePath($filename);
        $counter = 1;
        while (file_exists($targetFile))
        {
            $filename = $slug . '_' . $counter;
            $targetFile = $this->getTagFilePath($filename);
            $counter++;
        }
        $fileContent = $this->buildTagFileContent($metadata, '');
        if (!file_put_contents($targetFile, $fileContent))
        {
            throw new Exception("Could not write to file: $targetFile");
        }
        $newId = crc32($targetFile);
        $finalData = array_merge($metadata, [
            'id' => $newId
        ]);
        return $this->factory->create($finalData);
    }

    public function update(int $id, array $data): ?Tag
    {
        $filePaths = $this->getAllTagFilePaths();
        $foundFile = null;
        $parsedExisting = null;
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed && crc32($filePath) === $id)
            {
                $foundFile = $filePath;
                $parsedExisting = $parsed;
                break;
            }
        }
        if (!$foundFile || !$parsedExisting)
        {
            return null;
        }
        $updatedMetadata = array_merge($parsedExisting['metadata'], [
            'name' => $data['name'] ?? $parsedExisting['metadata']['name'] ?? '',
            'slug' => $this->generateSlug($data['name'] ?? $parsedExisting['metadata']['name'] ?? '')
        ]);
        $newFilename = $updatedMetadata['slug'];
        $oldFilePath = $foundFile;
        $newFilePath = $this->getTagFilePath($newFilename);
        if ($oldFilePath !== $newFilePath)
        {
            if (file_exists($newFilePath))
            {
                throw new Exception("File already exists at new path: $newFilePath");
            }
            if (!rename($oldFilePath, $newFilePath))
            {
                throw new Exception("Could not move file from $oldFilePath to $newFilePath");
            }
        }
        $fileContent = $this->buildTagFileContent($updatedMetadata, '');
        if (!file_put_contents($newFilePath, $fileContent))
        {
            throw new Exception("Could not update file: $newFilePath");
        }
        $finalData = array_merge($updatedMetadata, [
            'id' => $id
        ]);
        return $this->factory->create($finalData);
    }

    public function delete(int $id): bool
    {
        $filePaths = $this->getAllTagFilePaths();
        $foundFile = null;
        foreach ($filePaths as $filePath)
        {
            $parsed = $this->readTagFile($filePath);
            if ($parsed && crc32($filePath) === $id)
            {
                $foundFile = $filePath;
                break;
            }
        }
        if (!$foundFile || !unlink($foundFile))
        {
            return false;
        }
        return true;
    }

    public function attachToPost(int $tagId, int $postId): bool
    {
        $tag = $this->find($tagId);
        $post = $this->getPostById($postId);
        if (!$tag || !$post)
        {
            return false;
        }
        $postFilePath = $this->getPostFilePath($postId);
        $postData = $this->readPostFile($postFilePath);
        if (!$postData)
        {
            return false;
        }
        $updatedMetadata = array_merge($postData['metadata'], [
            'tags' => array_merge($postData['metadata']['tags'] ?? [], [$tagId])]);
        $fileContent = $this->buildTagFileContent($updatedMetadata, $postData['body']);
        return (bool)file_put_contents($postFilePath, $fileContent);
    }

    public function detachFromPost(int $tagId, int $postId): bool
    {
        $postFilePath = $this->getPostFilePath($postId);
        $postData = $this->readPostFile($postFilePath);
        if (!$postData)
        {
            return false;
        }
        $currentTags = $postData['metadata']['tags'] ?? [];
        $updatedTags = array_filter($currentTags, fn($id) => $id !== $tagId);
        $updatedMetadata = array_merge($postData['metadata'], [
            'tags' => array_values($updatedTags)
        ]);
        $fileContent = $this->buildTagFileContent($updatedMetadata, $postData['body']);
        return (bool)file_put_contents($postFilePath, $fileContent);
    }

    public function getPostTags(int $postId): array
    {
        $postFilePath = $this->getPostFilePath($postId);
        $postData = $this->readPostFile($postFilePath);
        if (!$postData)
        {
            return [];
        }
        $tagIds = $postData['metadata']['tags'] ?? [];
        $tags = [];
        foreach ($tagIds as $tagId)
        {
            $tag = $this->find($tagId);
            if ($tag)
            {
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    public function getPostsByTag(int $tagId): array
    {
        return [];
    }

    private function getAllTagFilePaths(): array
    {
        $files = [];
        if (!is_dir($this->tagsDirectory))
        {
            return $files;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tagsDirectory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getExtension() === 'md')
            {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function readTagFile(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        if ($content === false)
        {
            return null;
        }
        $parts = preg_split('/^---\s*$/m', $content, 2);
        if (count($parts) < 2)
        {
            return [
                'metadata' => [],
                'body' => trim($content),
                'filename' => basename($filePath, '.md')
            ];
        }
        $metadata = json_decode(trim($parts[0]), true);
        if (json_last_error() !== JSON_ERROR_NONE)
        {
            return null;
        }
        return [
            'metadata' => $metadata,
            'body' => trim($parts[1] ?? ''),
            'filename' => basename($filePath, '.md')
        ];
    }

    private function getTagFilePath(string $filename): string
    {
        $dir = $this->tagsDirectory;
        if (!is_dir($dir))
        {
            mkdir($dir, 0755, true);
        }
        return $dir . '/' . $filename . '.md';
    }

    private function buildTagFileContent(array $metadata, string $content): string
    {
        $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return $metadataJson . "\n---\n" . $content;
    }

    private function generateSlug(string $name): string
    {
        $transliterated = transliterator_transliterate('Russian-Latin/BGN; Any-Lower', $name);
        $slug = preg_replace('/[^a-z0-9]/', '_', $transliterated);
        return preg_replace('/_+/', '_', $slug);
    }

    private function ensureTagsDirectoryExists(): void
    {
        if (!is_dir($this->tagsDirectory))
        {
            mkdir($this->tagsDirectory, 0755, true);
        }
    }

    private function getPostFilePath(int $postId): string
    {
        return '';
    }

    private function getPostById(int $postId): ?object
    {
        return null;
    }
}