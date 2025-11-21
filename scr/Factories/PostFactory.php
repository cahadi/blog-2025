<?php

namespace App\Factories;

use App\Interfaces\PostFactoryInterface;
use App\Models\Post;
use Michelf\MarkdownExtra;

class PostFactory implements PostFactoryInterface
{
    public function create(array $data): Post
    {
        $post = Post::fromArray($data);
        $post->content = (string)MarkdownExtra::defaultTransform($data['content']);
        return $post;
    }
}