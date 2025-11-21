<?php

namespace App\Views;

use Twig\Environment;

class AdminView
{

    public Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function showDashboard(array $posts = []): string
    {
        return $this->twig->render('back/pages/dashboard.twig', [
            'posts' => $posts,
            'username' => $_SESSION['username'] ?? null
        ]);
    }
}