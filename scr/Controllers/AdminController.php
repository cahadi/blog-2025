<?php

namespace App\Controllers;

use App\Interfaces\PostRepositoryInterface;
use App\Views\AdminView;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminController
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private AdminView $adminView
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username'])) {
            return $this->redirect('/login');
        }

        $posts = $this->postRepository->all();
        $html = $this->adminView->showDashboard($posts);

        return $this->createResponse($html);
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