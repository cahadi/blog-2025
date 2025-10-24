<?php

namespace App\Core;

use \App\Core\Helper;

class FileManager
{
    private $contentDir;

    public function __construct($contentDir = CONTENT_PATH)
    {
        $this->contentDir = rtrim(realpath($contentDir), '/');
    }

    public function read($path)
    {
        $fullPath = $this->contentDir . '/' . ltrim($path, '/');
        if (strpos(realpath($fullPath), $this->contentDir) !== 0) {
            return false;
        }
        if (!file_exists($fullPath)) return false;
        return file_get_contents($fullPath);
    }

    public function write($path, $content)
    {
        $fullPath = $this->contentDir . '/' . ltrim($path, '/');
        if (strpos(realpath(dirname($fullPath)) ?: $fullPath, $this->contentDir) !== 0) {
            return false;
        }
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        return file_put_contents($fullPath, $content) !== false;
    }

    public function delete($path)
    {
        $fullPath = $this->contentDir . '/' . ltrim($path, '/');
        if (strpos(realpath($fullPath), $this->contentDir) !== 0) {
            return false;
        }
        return file_exists($fullPath) && unlink($fullPath);
    }

    public function listFiles($dir, $extension = '.md')
    {
        $fullDir = $this->contentDir . '/' . ltrim($dir, '/');
        if (!is_dir($fullDir)) return [];
        $files = glob($fullDir . '/*' . $extension);
        return array_map(function ($f) {
            return str_replace($this->contentDir . '/', '', $f);
        }, $files);
    }

    public function listDirs($dir)
    {
        $fullDir = $this->contentDir . '/' . ltrim($dir, '/');
        if (!is_dir($fullDir)) return [];
        return array_filter(glob($fullDir . '/*'), 'is_dir');
    }
}