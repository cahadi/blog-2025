<?php
declare(strict_types=1);

namespace App\Core;
use App\Traits\Helper;
class FileManager
{
    use Helper;
    private string $contentDir;

    public function __construct(string $contentDir = CONTENT_PATH)
    {
        $this->contentDir = rtrim(realpath($contentDir), '/');
    }

    public function read(string $path): string|false
    {
        $fullPath = $this->getFullPath($path);

        if (!$this->isPathValid($fullPath) || !file_exists($fullPath)) {
            return false;
        }

        return file_get_contents($fullPath);
    }

    public function write(string $path, string $content): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);

        if (!$this->isPathValid($dir)) {
            return false;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return file_put_contents($fullPath, $content) !== false;
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);

        if (!$this->isPathValid($fullPath) || !file_exists($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }

    public function listFiles(string $dir, string $extension = '.md'): array
    {
        $fullDir = $this->getFullPath($dir);

        if (!is_dir($fullDir)) {
            return [];
        }

        $files = glob($fullDir . '/*' . $extension);

        return array_map(fn($file) => str_replace($this->contentDir . '/', '', $file), $files);
    }

    public function listDirs(string $dir): array
    {
        $fullDir = $this->getFullPath($dir);
        if (!is_dir($fullDir)) {
            return [];
        }

        return array_filter(glob($fullDir . '/*'), 'is_dir');
    }

    private function getFullPath(string $path): string
    {
        return $this->contentDir . '/' . ltrim($path, '/');
    }

    private function isPathValid(string $path): bool
    {
        return str_starts_with(realpath($path) ?: $path, $this->contentDir);
    }
}