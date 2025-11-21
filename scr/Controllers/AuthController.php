<?php

namespace App\Controllers;

use App\Views\AuthView;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{

    private AuthView $authView;

    public function __construct(AuthView $authView)
    {
        $this->authView = $authView;
    }

    public function showLoginForm(ServerRequestInterface $request): ResponseInterface
    {
        if (isset($_SESSION['username'])) {
            return $this->redirect('/admin');
        }

        $html = $this->authView->showLoginForm();
        return $this->createResponse($html);
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->authenticate($username, $password)) {
            $_SESSION['username'] = $username;
            return $this->redirect('/admin');
        }

        return $this->redirect('/login?error=1');
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        unset($_SESSION['username']);
        session_destroy();
        return $this->redirect('/login');
    }

    private function authenticate(string $username, string $password): bool
    {
        $validUsers = [
            'admin' => 'admin',
            'editor' => 'editor123'
        ];

        return isset($validUsers[$username]) && $validUsers[$username] === $password;
    }

    private function createResponse(string $content): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($content);
        return $response;
    }

    private function redirect(string $uri): ResponseInterface
    {
        return (new Response())
            ->withStatus(302)
            ->withHeader('Location', $uri);
    }
}