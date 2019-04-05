<?php

require_once __DIR__ . '/vendor/autoload.php';
use jukeinthebox\controllers\FileController;
use jukeinthebox\controllers\CatalogueController;
use jukeinthebox\controllers\ServeurController;
use jukeinthebox\controllers\JukeboxController;
use jukeinthebox\controllers\StatistiquesController;

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
    $view = $c->get('view');
    return new CatalogueController($view);
};

$container['JukeboxController'] = function ($c){
    $view = $c->get('view');
    return new JukeboxController($view);
};

$container['FileController'] = function ($c){
    return new FileController();
};

$container['ServeurController'] = function ($c){
    $view = $c->get('view');
    return new ServeurController($view);
};

$container['StatistiquesController'] = function ($c){
    $view = $c->get('view');
    return new StatistiquesController($view);
};