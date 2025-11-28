<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\PostRepositoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Views\AdminView;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminController
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private TeaRepositoryInterface $teaRepository,
        private UserRepositoryInterface $userRepository,
        private AdminView $adminView
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $posts = $this->postRepository->all();
        $teas = $this->teaRepository->all();
        $users = $this->userRepository->all();
        $html = $this->adminView->showDashboard([
            'posts' => $posts,
            'teas' => $teas,
            'users' => $users
        ]);
        return $this->createResponse($html);
    }

    public function showAllPosts(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $posts = $this->postRepository->all();
        $html = $this->adminView->posts($posts);
        return $this->createResponse($html);
    }

    public function showAllTeas(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $teas = $this->teaRepository->all();
        $html = $this->adminView->teas($teas);
        return $this->createResponse($html);
    }

    public function showAllUsers(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $users = $this->userRepository->all();
        $html = $this->adminView->users($users);
        return $this->createResponse($html);
    }

    public function editPost(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $post = null;
        if (isset($args['id']))
        {
            $post = $this->postRepository->find((int)$args['id']);
            $html = $this->adminView->postForm($post);
            return $this->createResponse($html);
        }
    }

    public function createPost(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $html = $this->adminView->postForm(null);
        return $this->createResponse($html);
    }

    public function savePost(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $data = $request->getParsedBody();
        if (isset($data['id']) && !empty($data['id']))
        {
            $this->postRepository->update((int)$data['id'], $data);
        }
        else
        {
            $this->postRepository->create($data);
        }
        return $this->redirect('/admin/posts');
    }

    public function deletePost(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        if (isset($args['id']))
        {
            $this->postRepository->delete((int)$args['id']);
        }
        return $this->redirect('/admin/posts');
    }

    public function editTea(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $tea = null;
        if (isset($args['id']))
        {
            $tea = $this->teaRepository->find((int)$args['id']);
            $html = $this->adminView->teaForm($tea);
            return $this->createResponse($html);
        }
    }

    public function createTea(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $html = $this->adminView->teaForm(null);
        return $this->createResponse($html);
    }

    public function saveTea(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $data = $request->getParsedBody();
        if (isset($data['id']) && !empty($data['id']))
        {
            $this->teaRepository->update((int)$data['id'], $data);
        }
        else
        {
            $this->teaRepository->create($data);
        }
        return $this->redirect('/admin/teas');
    }

    public function deleteTea(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        if (isset($args['id']))
        {
            $this->teaRepository->delete((int)$args['id']);
        }
        return $this->redirect('/admin/teas');
    }

    public function editUser(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $user = null;
        if (isset($args['id']))
        {
            $user = $this->userRepository->find((int)$args['id']);
            $html = $this->adminView->userForm($user);
            return $this->createResponse($html);
        }
    }

    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $html = $this->adminView->userForm(null);
        return $this->createResponse($html);
    }

    public function saveUser(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $data = $request->getParsedBody();
        if (isset($data['id']) && !empty($data['id']))
        {
            $this->userRepository->update((int)$data['id'], $data);
        }
        else
        {
            $this->userRepository->create($data);
        }
        return $this->redirect('/admin/users');
    }

    public function deleteUser(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        if (isset($args['id']))
        {
            $this->userRepository->delete((int)$args['id']);
        }
        return $this->redirect('/admin/users');
    }

    public function showAllOrders(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($_SESSION['username']))
        {
            return $this->redirect('/login');
        }
        $html = $this->adminView->orders([]);
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