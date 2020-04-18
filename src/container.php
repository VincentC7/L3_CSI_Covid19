<?php

use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\TwigExtension;

$container = $app->getContainer();

$container['view'] = function ($container) {
    $dir = dirname(__DIR__);
    $view = new \Slim\Views\Twig($dir . '/src/views', [
        'cache' => false //$dir . '/tmp/cache'
    ]);

    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new Environment($_SERVER));
    $view->addExtension(new TwigExtension($router, $uri));

    return $view;
};