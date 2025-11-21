<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\PostRepositoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Views\FrontView;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FrontController
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private TeaRepositoryInterface $teaRepository,
        private FrontView $frontView
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response($this->frontView->index());
    }

    public function showCatalog(ServerRequestInterface $request): ResponseInterface
    {
        $teas = $this->teaRepository->all();
        return $this->response($this->frontView->catalog($teas));
    }

    public function showAllPosts(ServerRequestInterface $request): ResponseInterface
    {
        $posts = $this->postRepository->all();
        return $this->response($this->frontView->posts($posts));
    }

    public function showPost(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $post = $this->postRepository->find((int)$args['id']);

        if (!$post) {
            return $this->response($this->frontView->error404(), 404);
        }

        return $this->response($this->frontView->post($post));
    }

    public function showTea(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $tea = $this->teaRepository->find((int)$args['id']);

        if (!$tea) {
            return $this->response($this->frontView->error404(), 404);
        }

        return $this->response($this->frontView->tea($tea));
    }

    private function response(string $content, int $status = 200): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($content);
        return $response->withStatus($status);
    }
}