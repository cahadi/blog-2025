<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\FrontController;
use App\Factories\PostFactory;
use App\Factories\TeaFactory;
use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Interfaces\TeaFactoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Repositories\FilePostRepository;
use App\Repositories\FileTeaRepository;
use App\Repositories\JsonPostRepository;
use App\Repositories\JsonTeaRepository;
use App\Services\FileHandler;
use App\Views\AdminView;
use App\Views\AuthView;
use App\Views\FrontView;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$container = new League\Container\Container();

$container->add(Environment::class, function () {
    $loader = new FilesystemLoader(RESOURCES_PATH);
    return new Environment($loader);
});

$container->add(FrontView::class)
    ->addArguments([Environment::class]);

$container->add(AuthView::class)
    ->addArguments([Environment::class]);

$container->add(AdminView::class)
    ->addArguments([Environment::class]);

$container->add(PostFactoryInterface::class, PostFactory::class);
$container->add(TeaFactoryInterface::class, TeaFactory::class);

$storageType = $_ENV['STORAGE_TYPE'] ?? 'json';

if ($storageType === 'file') {
    $container->add(PostRepositoryInterface::class, FilePostRepository::class)
        ->addArguments([PostFactoryInterface::class, FileHandler::class]);
} else {
    $container->add(PostRepositoryInterface::class, JsonPostRepository::class)
        ->addArguments([PostFactoryInterface::class]);
}

if ($storageType === 'file') {
    $container->add(TeaRepositoryInterface::class, FileTeaRepository::class)
        ->addArguments([TeaFactoryInterface::class, FileHandler::class]);
} else {
    $container->add(TeaRepositoryInterface::class, JsonTeaRepository::class)
        ->addArguments([TeaFactoryInterface::class]);
}

$container->add(AdminController::class)
    ->addArgument($container->get(PostRepositoryInterface::class))
    ->addArgument($container->get(AdminView::class));

$container->add(\App\Controllers\AuthController::class)
    ->addArgument($container->get(AuthView::class));

$container->add(FrontController::class)
    ->addArgument($container->get(PostRepositoryInterface::class))
    ->addArgument($container->get(TeaRepositoryInterface::class))
    ->addArgument($container->get(FrontView::class));

$strategy = (new ApplicationStrategy)->setContainer($container);
$router = (new Router)->setStrategy($strategy);

return $router;