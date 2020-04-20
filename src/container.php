<?php

use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\TwigExtension;

$container = $app->getContainer();

$container['pdo'] = function ($container){
    $db_info = parse_ini_file(__DIR__ . '/../conf/conf.ini');
    try{
        $pdo = new PDO($db_info['driver'].":host=" . $db_info['host'] . ";dbname=" . $db_info['database'] ,$db_info['username'],$db_info['password']);
    }catch (PDOException $e){
        echo "La connection a la base de données à echoué";
    }
    return $pdo;
};

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