<?php

namespace App\Views;

use App\Models\Post;
use App\Models\Tea;
use App\Models\User;
use Twig\Environment;

class AdminView
{
    public function __construct(private Environment $twig) {}

    public function showDashboard(array $data = []): string
    {
        return $this->twig->render('back/pages/dashboard.twig', [
            'posts' => $data['posts'] ?? [],
            'teas' => $data['teas'] ?? [],
            'users' => $data['users'] ?? []
        ]);
    }

    public function posts(array $posts): string
    {
        return $this->twig->render('/back/pages/lists/posts.twig', [
            'posts' => $posts,
            'username' => $_SESSION['username'] ?? null,
            'show_pagination' => count($posts) > 12
        ]);
    }

    public function teas(array $teas): string
    {
        return $this->twig->render('/back/pages/lists/teas.twig', [
            'teas' => $teas,
            'username' => $_SESSION['username'] ?? null,
            'show_pagination' => count($teas) > 12
        ]);
    }

    public function users(array $users): string
    {
        return $this->twig->render('/back/pages/lists/users.twig', [
            'users' => $users,
            'username' => $_SESSION['username'] ?? null,
            'show_pagination' => count($users) > 12
        ]);
    }

    public function postForm(?Post $post = null): string
    {
        $isEdit = $post !== null;

        return $this->twig->render('back/pages/forms/post_form.twig', [
            'isEdit' => $isEdit,
            'post' => $post ?? new Post(0, '', '', '', ''),
        ]);
    }

    public function teaForm(?Tea $tea = null): string
    {
        $isEdit = $tea !== null;

        return $this->twig->render('back/pages/forms/tea_form.twig', [
            'isEdit' => $isEdit,
            'tea' => $tea ?? new Tea(0, '', '', '', 0.0),
        ]);
    }

    public function userForm(?User $user = null): string
    {
        $isEdit = $user !== null;

        return $this->twig->render('back/pages/forms/user_form.twig', [
            'isEdit' => $isEdit,
            'user' => $user ?? new User(0, '', '', ''),
        ]);
    }
}