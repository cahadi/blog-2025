<?php
declare(strict_types=1);

namespace App\Services;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileHandler
{
    public function __construct(
        private string $postsDirectory = ROOT_PATH . '/content/posts'
    ) {
        $this->ensureDirectoryExists();
    }

    public function readPostFile(string $filePath): ?array
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
                'category' => $this->getCategoryFromPath($filePath)
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
            'category' => $this->getCategoryFromPath($filePath)
        ];
    }

    public function getAllPostFilePaths(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->postsDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->postsDirectory)) {
            mkdir($this->postsDirectory, 0755, true);
        }
    }

    private function getCategoryFromPath(string $filePath): string
    {
        $relativePath = substr($filePath, strlen($this->postsDirectory) + 1);
        $dir = dirname($relativePath);

        return ($dir !== '.') ? $dir : '';
    }
}