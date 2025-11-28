<?php

namespace App\Views;

use Twig\Environment;

class FrontView
{
    public Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): string
    {
        return $this->twig->render('/front/pages/index.twig');
    }

    public function catalog(array $teas): string
    {
        return $this->twig->render('/front/teas/catalog.twig', [
            'products' => $teas,
            'show_pagination' => count($teas) > 12
        ]);
    }

    public function posts(array $posts): string
    {
        return $this->twig->render('/front/posts/post_list.twig', [
            'articles' => $posts,
            'show_pagination' => count($posts) > 12
        ]);
    }

    public function post(object $post): string
    {
        return $this->twig->render('/front/posts/post_content.twig', [
            'post' => $post,
            'tags' => $post->tags ?? []
        ]);
    }

    public function tea(object $tea): string
    {
        return $this->twig->render('/front/teas/tea_detail.twig', ['tea' => $tea]);
    }

    public function error404(): string
    {
        return $this->twig->render('/front/errors/404.twig');
    }
}