<?php
namespace App\Models;

class Articles
{
    public array $articles;
    public function __construct()
    {
        $this->articles = [
            ['title'=>'1', 'description'=>'1', 'content'=>'1', 'author'=>'author1'],
            ['title'=>'2', 'description'=>'2', 'content'=>'2', 'author'=>'author2'],
            ['title'=>'3', 'description'=>'3', 'content'=>'3', 'author'=>'author3'],
            ['title'=>'4', 'description'=>'4', 'content'=>'4', 'author'=>'author4'],
        ];
    }
    public function all()
    {
        return $this->articles;
    }
}