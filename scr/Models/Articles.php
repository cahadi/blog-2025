<?php
namespace App\Models;

class Articles
{
    public array $articles;
    public function __construct()
    {
        $this->articles = [
            ['title'=>'name1', 'description'=>'description1', 'content'=>'content1', 'author'=>'author1', 'price'=>'price1'],
            ['title'=>'name2', 'description'=>'description2', 'content'=>'content2', 'author'=>'author2', 'price'=>'price2'],
            ['title'=>'name3', 'description'=>'description3', 'content'=>'content3', 'author'=>'author3', 'price'=>'price3'],
            ['title'=>'name4', 'description'=>'description4', 'content'=>'content4', 'author'=>'author4', 'price'=>'price4'],
        ];
    }
    public function all()
    {
        return $this->articles;
    }
}