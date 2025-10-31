<?php

namespace App\Models;

use App\Core\FileManager;

class Category
{
    private FileManager $fm;

    public function __construct()
    {
        $this->fm = new FileManager();

    }
//    public function getCategories()
//    {
//        $dirs = $this->fm->listDirs('posts');
//        return array_map(fn($d) => basename($d), $dirs);
//    }
    public function getCategories() {
        $dirs = $this->fm->listDirs('posts');
        return array_map(function($d) {
            return basename($d);
        }, $dirs);
    }
}