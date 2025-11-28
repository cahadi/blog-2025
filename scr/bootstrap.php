<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\FrontController;
use App\Factories\PostFactory;
use App\Factories\TagFactory;
use App\Factories\TeaFactory;
use App\Factories\UserFactory;
use App\Interfaces\PostFactoryInterface;
use App\Interfaces\PostRepositoryInterface;
use App\Interfaces\TagFactoryInterface;
use App\Interfaces\TagRepositoryInterface;
use App\Interfaces\TeaFactoryInterface;
use App\Interfaces\TeaRepositoryInterface;
use App\Interfaces\UserFactoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\FilePostRepository;
use App\Repositories\FileTagRepository;
use App\Repositories\FileTeaRepository;
use App\Repositories\FileUserRepository;
use App\Repositories\JsonPostRepository;
use App\Repositories\JsonTagRepository;
use App\Repositories\JsonTeaRepository;
use App\Repositories\JsonUserRepository;
use App\Services\FileHandler;
use App\Views\AdminView;
use App\Views\AuthView;
use App\Views\FrontView;
use League\Container\Container;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$container = new Container();

$container->add(Environment::class, function () {
    $loader = new FilesystemLoader(RESOURCES_PATH);
    return new Environment($loader);
});

$container->add(FrontView::class)
    ->addArgument(Environment::class);

$container->add(AuthView::class)
    ->addArgument(Environment::class);

$container->add(AdminView::class)
    ->addArgument(Environment::class);

$container->add(PostFactoryInterface::class, PostFactory::class);
$container->add(TeaFactoryInterface::class, TeaFactory::class);
$container->add(UserFactoryInterface::class, UserFactory::class);
$container->add(TagFactoryInterface::class, TagFactory::class);

$container->add(FileHandler::class);

$storageType = $_ENV['STORAGE_TYPE'] ?? 'json';

if ($storageType === 'file') {
    $container->add(PostRepositoryInterface::class, FilePostRepository::class)
        ->addArguments([PostFactoryInterface::class, FileHandler::class]);

    $container->add(TeaRepositoryInterface::class, FileTeaRepository::class)
        ->addArguments([TeaFactoryInterface::class, FileHandler::class]);

    $container->add(UserRepositoryInterface::class, FileUserRepository::class)
        ->addArguments([UserFactoryInterface::class, FileHandler::class]);

    $container->add(TagRepositoryInterface::class, FileTagRepository::class)
        ->addArguments([TagFactoryInterface::class, FileHandler::class]);
} else {
    $container->add(PostRepositoryInterface::class, JsonPostRepository::class)
        ->addArgument(PostFactoryInterface::class);

    $container->add(TeaRepositoryInterface::class, JsonTeaRepository::class)
        ->addArgument(TeaFactoryInterface::class);

    $container->add(UserRepositoryInterface::class, JsonUserRepository::class)
        ->addArgument(UserFactoryInterface::class);

    $container->add(TagRepositoryInterface::class, JsonTagRepository::class)
        ->addArgument(TagFactoryInterface::class);
}

$container->add(AdminController::class)
    ->addArguments([
        PostRepositoryInterface::class,
        TeaRepositoryInterface::class,
        UserRepositoryInterface::class,
        AdminView::class
    ]);

$container->add(AuthController::class)
    ->addArguments([
        AuthView::class,
        UserRepositoryInterface::class
    ]);

$container->add(FrontController::class)
    ->addArguments([
        PostRepositoryInterface::class,
        TeaRepositoryInterface::class,
        FrontView::class
    ]);

$strategy = (new ApplicationStrategy())->setContainer($container);
$router = (new Router())->setStrategy($strategy);

return $router;