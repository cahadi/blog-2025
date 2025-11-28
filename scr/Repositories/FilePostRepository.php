<?php

namespace App\Repositories;

use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use Exception;
use App\Services\FileHandler;

class FilePostRepository implements PostRepositoryInterface
{
    private FileHandler $fileHandler;
    private PostFactoryInterface $factory;

    public function __construct(PostFactoryInterface $factory, FileHandler $fileHandler)
    {
        $this->factory = $factory;
        $this->fileHandler = $fileHandler;
    }

    public function all(): array
    {
        $posts = [];
        $filePaths = $this->fileHandler->getAllPostFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath);
            if ($parsed) {
                $id = crc32($filePath);
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'content' => $parsed['body'],
                    'filename' => $parsed['filename'],
                    'category' => $parsed['category']
                ]);
                $posts[] = $this->factory->create($data);
            }
        }
        usort($posts, fn($a, $b) => $b->id <=> $a->id);
        return $posts;
    }

    public function find(int $id): ?Post
    {
        $filePaths = $this->fileHandler->getAllPostFilePaths();

        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'content' => $parsed['body'],
                    'filename' => $parsed['filename'],
                    'category' => $parsed['category']
                ]);
                return $this->factory->create($data);
            }
        }
        return null;
    }

    public function create(array $data): Post
    {
        $title = $data['title'] ?? 'Untitled';
        $description = $data['description'] ?? '';
        $cover_image = $data['cover_image'] ?? '';
        $content = $data['content'] ?? '';
        $category = $data['category'] ?? null;
        $filename = $data['filename'] ?? 'post_' . time();

        $metadata = [
            'title' => $title,
            'description' => $description,
            'cover_image' => $cover_image,
        ];

        $targetFile = $this->fileHandler->getFilePath($filename, $category);
        $counter = 1;
        while ($this->fileHandler->fileExists($targetFile)) {
            $filename = $data['filename'] . '_' . $counter;
            $targetFile = $this->fileHandler->getFilePath($filename, $category);
            $counter++;
        }

        $fileContent = $this->fileHandler->buildFileContent($metadata, $content);

        if (!$this->fileHandler->writeFile($targetFile, $fileContent)) {
            throw new Exception("Could not write to file: $targetFile");
        }

        $newId = crc32($targetFile);
        $finalData = array_merge($metadata, [
            'id' => $newId,
            'content' => $content,
            'filename' => $filename,
            'category' => $category
        ]);
        return $this->factory->create($finalData);
    }

    public function update(int $id, array $data): ?Post
    {
        $filePaths = $this->fileHandler->getAllPostFilePaths();
        $foundFile = null;
        $parsedExisting = null;
        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath);
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
            'title' => $data['title'] ?? $parsedExisting['metadata']['title'] ?? '',
            'description' => $data['description'] ?? $parsedExisting['metadata']['description'] ?? '',
            'cover_image' => $data['cover_image'] ?? $parsedExisting['metadata']['cover_image'] ?? '',
        ]);
        $updatedContent = $data['content'] ?? $parsedExisting['body'];

        $newCategory = $data['category'] ?? $parsedExisting['category'];
        $newFilename = $data['filename'] ?? $parsedExisting['filename'];

        $oldFilePath = $foundFile;
        $newFilePath = $this->fileHandler->getFilePath($newFilename, $newCategory);

        if ($oldFilePath !== $newFilePath) {
            if ($this->fileHandler->fileExists($newFilePath)) {
                throw new Exception("File already exists at new path: $newFilePath");
            }
            if (!$this->fileHandler->renameFile($oldFilePath, $newFilePath));
            $targetFileForWrite = $newFilePath;
        } else {
            $targetFileForWrite = $oldFilePath;
        }

        $newFileContent = $this->fileHandler->buildFileContent($updatedMetadata, $updatedContent);

        if (!$this->fileHandler->writeFile($targetFileForWrite, $newFileContent)) {
            throw new Exception("Could not update file: $targetFileForWrite");
        }
        $finalData = array_merge($updatedMetadata, [
            'id' => $id,
            'content' => $updatedContent,
            'filename' => $newFilename,
            'category' => $newCategory
        ]);
        return $this->factory->create($finalData);
    }

    public function delete(int $id): bool
    {
        $filePaths = $this->fileHandler->getAllPostFilePaths();
        $foundFile = null;
        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath);
            if ($parsed && crc32($filePath) === $id) {
                $foundFile = $filePath;
                break;
            }
        }

        if (!$foundFile || !$this->fileHandler->deleteFile($foundFile)) {
            return false;
        }
        $this->fileHandler->removeEmptyDir(dirname($foundFile));
        return true;
    }
}