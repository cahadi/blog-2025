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
        $html = $this->frontView->index();
        return $this->createResponse($html);
    }

    public function showCatalog(ServerRequestInterface $request): ResponseInterface
    {
        $teas = $this->teaRepository->all();
        $html = $this->frontView->catalog($teas);
        return $this->createResponse($html);
    }

    public function showAllPosts(ServerRequestInterface $request): ResponseInterface
    {
        $posts = $this->postRepository->all();
        $html = $this->frontView->posts($posts);
        return $this->createResponse($html);
    }

    public function showPost(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $post = null;
        if (isset($args['id'])) {
            $post = $this->postRepository->find((int)$args['id']);

            if (!$post) {
                $html = $this->frontView->error404();
                return $this->createResponse($html)->withStatus(404);
            }

            $html = $this->frontView->post($post);
            return $this->createResponse($html);
        }
    }
    public function showTea(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $tea = null;
        if (isset($args['id'])) {
            $tea = $this->teaRepository->find((int)$args['id']);

            if (!$tea) {
                $html = $this->frontView->error404();
                return $this->createResponse($html)->withStatus(404);
            }

            $html = $this->frontView->tea($tea);
            return $this->createResponse($html);
        }
    }

    private function createResponse(string $content): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($content);
        return $response;
    }
}