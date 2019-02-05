<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use jukeinthebox\bd\Connection;
use jukeinthebox\controllers\FileController;
use jukeinthebox\controllers\CatalogueController;

$ini = parse_ini_file('src/conf/conf.ini');

$db = new DB();

$db->addConnection([
	'driver' => $ini['driver'],
	'host' => $ini['host'],
	'database' => $ini['dbname'],
	'username' => $ini['username'],
	'password' => $ini['password'],
	'charset' => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix' => ''
]);

/*Connection::setConfig('src/conf/conf.ini');
$db = Connection::makeConnection();*/

$db->setAsGlobal();
$db->bootEloquent();

session_start();

require('container.php');

$app = new \Slim\App($container);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function($request, $response, $args){
	$controller = $this['FileController'];
	$displayFile = $controller->displayFile($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('File');

$app->post('/addfile', 'FileController:addFile')->setName('addFile');

$app->delete('/next', 'FileController:nextFile')->setName('next');

$app->get('/catalogue', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$displayCatalogue = $controller->displayCatalogue($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('Catalogue');

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();