<?php

namespace App\Repositories;

use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use App\Services\FileHandler; // Импортируем новый класс
use Exception;

class FilePostRepository implements PostRepositoryInterface
{
    private FileHandler $fileHandler; // Используем FileHandler
    private PostFactoryInterface $factory;

    // Принимаем FileHandler через конструктор
    public function __construct(PostFactoryInterface $factory, FileHandler $fileHandler)
    {
        $this->factory = $factory;
        $this->fileHandler = $fileHandler;
    }

    public function all(): array
    {
        $posts = [];
        $filePaths = $this->fileHandler->getAllPostFilePaths(); // Используем FileHandler

        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath); // Используем FileHandler
            if ($parsed) {
                // Генерируем ID на основе пути к файлу для уникальности
                $id = crc32($filePath); // Простой способ генерации ID из пути
                $data = array_merge($parsed['metadata'], [
                    'id' => $id,
                    'content' => $parsed['body'],
                    'filename' => $parsed['filename'],
                    'category' => $parsed['category']
                ]);
                $posts[] = $this->factory->create($data);
            }
        }
        // Сортировка по ID (можно изменить на дату или другое поле)
        usort($posts, fn($a, $b) => $b->id <=> $a->id);
        return $posts;
    }

    public function find(int $id): ?Post
    {
        // Для поиска по ID нужно пройти по всем файлам
        $filePaths = $this->fileHandler->getAllPostFilePaths(); // Используем FileHandler

        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath); // Используем FileHandler
            if ($parsed && crc32($filePath) === $id) { // Сравниваем ID
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
        // 1. Извлекаем поля
        $title = $data['title'] ?? 'Untitled';
        $description = $data['description'] ?? '';
        $cover_image = $data['cover_image'] ?? '';
        $content = $data['content'] ?? '';
        $category = $data['category'] ?? null;
        $filename = $data['filename'] ?? 'post_' . time(); // Или генерировать уникальное имя

        // 2. Подготовка метаданных
        $metadata = [
            'title' => $title,
            'description' => $description,
            'cover_image' => $cover_image,
        ];

        // 3. Проверка уникальности имени файла
        $targetFile = $this->fileHandler->getFilePath($filename, $category); // Используем FileHandler
        $counter = 1;
        while ($this->fileHandler->fileExists($targetFile)) { // Используем FileHandler
            $filename = $data['filename'] . '_' . $counter; // Или используйте другую логику
            $targetFile = $this->fileHandler->getFilePath($filename, $category); // Используем FileHandler
            $counter++;
        }

        // 4. Создание содержимого файла
        $fileContent = $this->fileHandler->buildFileContent($metadata, $content); // Используем FileHandler

        // 5. Запись в файл
        if (!$this->fileHandler->writeFile($targetFile, $fileContent)) { // Используем FileHandler
            throw new Exception("Could not write to file: $targetFile");
        }

        // 6. Возврат созданного Post
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
        // Найти файл по ID
        $filePaths = $this->fileHandler->getAllPostFilePaths(); // Используем FileHandler
        $foundFile = null;
        $parsedExisting = null;
        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath); // Используем FileHandler
            if ($parsed && crc32($filePath) === $id) {
                $foundFile = $filePath;
                $parsedExisting = $parsed;
                break;
            }
        }

        if (!$foundFile || !$parsedExisting) {
            return null;
        }

        // Подготовка обновленных метаданных и содержимого
        $updatedMetadata = array_merge($parsedExisting['metadata'], [
            'title' => $data['title'] ?? $parsedExisting['metadata']['title'] ?? '',
            'description' => $data['description'] ?? $parsedExisting['metadata']['description'] ?? '',
            'cover_image' => $data['cover_image'] ?? $parsedExisting['metadata']['cover_image'] ?? '',
        ]);
        $updatedContent = $data['content'] ?? $parsedExisting['body'];

        // Определение новой/старой категории и имени файла
        $newCategory = $data['category'] ?? $parsedExisting['category'];
        $newFilename = $data['filename'] ?? $parsedExisting['filename'];

        // Если категория или имя файла изменились, нужно переименовать/переместить файл
        $oldFilePath = $foundFile;
        $newFilePath = $this->fileHandler->getFilePath($newFilename, $newCategory); // Используем FileHandler

        // Если файл нужно переместить
        if ($oldFilePath !== $newFilePath) {
            // Проверка на существование нового файла
            if ($this->fileHandler->fileExists($newFilePath)) { // Используем FileHandler
                throw new Exception("File already exists at new path: $newFilePath");
            }
            // Переименование/перемещение
            if (!$this->fileHandler->renameFile($oldFilePath, $newFilePath)) { // Используем FileHandler
                throw new Exception("Could not move file from $oldFilePath to $newFilePath");
            }
            $targetFileForWrite = $newFilePath;
        } else {
            $targetFileForWrite = $oldFilePath;
        }

        // Создание нового содержимого
        $newFileContent = $this->fileHandler->buildFileContent($updatedMetadata, $updatedContent); // Используем FileHandler

        // Запись в файл
        if (!$this->fileHandler->writeFile($targetFileForWrite, $newFileContent)) { // Используем FileHandler
            // В случае неудачи, возможно, стоит откатить rename, если он был
            throw new Exception("Could not update file: $targetFileForWrite");
        }

        // Возврат обновленного Post
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
        // Найти файл по ID
        $filePaths = $this->fileHandler->getAllPostFilePaths(); // Используем FileHandler
        $foundFile = null;
        foreach ($filePaths as $filePath) {
            $parsed = $this->fileHandler->readPostFile($filePath); // Используем FileHandler
            if ($parsed && crc32($filePath) === $id) {
                $foundFile = $filePath;
                break;
            }
        }

        if (!$foundFile || !$this->fileHandler->deleteFile($foundFile)) { // Используем FileHandler
            return false;
        }
        // Удалить директорию, если она пуста (опционально)
        $this->fileHandler->removeEmptyDir(dirname($foundFile)); // Используем FileHandler
        return true;
    }
}