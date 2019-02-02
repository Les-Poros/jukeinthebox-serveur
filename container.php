<?php

require_once __DIR__ . '/vendor/autoload.php';
use jukeinthebox\controllers\FileController;
use jukeinthebox\controllers\CatalogueController;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$container = new \Slim\Container($configuration);

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('src/views');
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));
    return $view;
};

$container['CatalogueController'] = function ($c){
    return new CatalogueController();
};

$container['FileController'] = function ($c){
    $view = $c->get('view');
    return new FileController($view);
};