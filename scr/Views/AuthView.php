<?php

namespace App\Views;

use Twig\Environment;

class AuthView
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function showLoginForm(array $errors = []): string
    {
        return $this->twig->render('back/auth/login.twig', [
            'error' => !empty($_GET['error']),
            'errors' => $errors
        ]);
    }

}