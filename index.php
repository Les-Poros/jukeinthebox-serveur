<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use jukeinthebox\bd\Connection;
use jukeinthebox\controllers\FileController;
use jukeinthebox\controllers\CatalogueController;
use jukeinthebox\controllers\ServeurController;
use jukeinthebox\controllers\JukeboxController;
use jukeinthebox\controllers\StatistiquesController;

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

$app->get('/', function($request, $response, $args){
	$controller = $this['ServeurController'];
	$displayServeur = $controller->displayServeur($request, $response, $args);
})->setName('Home');

$app->get('/ListJukebox', function($request, $response, $args){
	$controller = $this['ServeurController'];
	$ListJukebox = $controller->listJukebox($request, $response, $args);
})->setName('ListJukebox');

$app->post('/ListJukebox', function($request, $response, $args){
	$controller = $this['ServeurController'];
	$ListJukebox = $controller->listJukebox($request, $response, $args);
})->setName('ListJukebox');

$app->get('/CreateJukebox', function($request, $response, $args){
	$controller = $this['ServeurController'];
	$CreateJukebox = $controller->createJukebox($request, $response, $args);
})->setName('CreateJukebox');

$app->get('/File', function($request, $response, $args){
	$controller = $this['FileController'];
	$displayFile = $controller->displayFile($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('File');

$app->post('/addfile', 'FileController:addFile')->setName('addFile');
//Route permettant l'ajout d'une musique dans la bibliothèque
$app->post('/addMusicBiblio', 'CatalogueController:addMusicBiblio')->setName('addMusicBiblio');
$app->post('/deleteMusicBiblio', 'CatalogueController:deleteMusicBiblio')->setName('deleteMusicBiblio');
$app->post('/qrcode', 'JukeboxController:setQrcode')->setName('setQrcode');

$app->get('/validateJukebox', function($request, $response, $args){
	$controller = $this['JukeboxController'];
	$controller->validateToken($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('validateJukebox');

$app->get('/getJukeboxAction', function($request, $response, $args){
	$controller = $this['JukeboxController'];
	$controller->getJukeboxAction($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('getJukebox');

$app->delete('/next', 'FileController:nextFile')->setName('next');

$app->post('/next', 'JukeboxController:nextJuke')->setName('next');

$app->post('/play', 'JukeboxController:play')->setName('play');

$app->post('/pause', 'JukeboxController:pause')->setName('pause');

$app->post('/repeat', 'JukeboxController:repeat')->setName('repeat');

$app->post('/selectCatag', 'JukeboxController:selectCatag')->setName('repeat');

$app->get('/catalogue', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$displayCatalogue = $controller->displayCatalogue($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('Catalogue');

$app->get('/catalogueChoice', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$displayCatalogue = $controller->displayCatalogueChoice($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('CatalogueChoice');

$app->post('/pushStatsMusic', 'StatistiquesController:pushStatsMusic')->setName('pushStatsMusic');

$app->get('/getStats', function($request, $response, $args){
	$controller = $this['StatistiquesController'];
	$controller->getStats($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('getStats');

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});
$app->run();