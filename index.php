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

$app->post('/DeleteJukebox/{id}', function($request, $response, $args){
	$controller = $this['ServeurController'];
	$DeleteJukebox = $controller->deleteJukebox($request, $response, $args);
})->setName('DeleteJukebox');

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

$app->get('/ListCatalogue', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$listCatalogue = $controller->listCatalogue($request, $response, $args);
})->setName('ListCatalogue');

$app->post('/ListCatalogue', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$ListCatalogue = $controller->listCatalogue($request, $response, $args);
})->setName('ListCatalogue');

$app->get('/CreateMusic', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$CreateMusic = $controller->createMusic($request, $response, $args);
})->setName('CreateMusic');

$app->post('/CreateAlbum', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$CreateAlbum = $controller->createAlbum($request, $response, $args);
})->setName('CreateAlbum');

$app->post('/next', 'JukeboxController:nextJuke')->setName('next');

$app->post('/play', 'JukeboxController:play')->setName('play');

$app->post('/pause', 'JukeboxController:pause')->setName('pause');

$app->post('/modeBlindtest', 'JukeboxController:modeBlindtest')->setName('modeBlindtest');

$app->post('/modeNormal', 'JukeboxController:modeNormal')->setName('modeNormal');

$app->post('/repeat', 'JukeboxController:repeat')->setName('repeat');

$app->post('/selectCatag', 'JukeboxController:selectCatag')->setName('selectCatag');
$app->post('/integrerCatag', 'CatalogueController:integrerCatag')->setName('integrerCatag');

$app->get('/catalogue', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$displayCatalogue = $controller->displayCatalogue($request, $response, $args);
	return $response->withHeader(
		'Content-Type',
		'application/json'
	);
})->setName('Catalogue');

$app->get('/InfoMusic', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$param = $request->getParams();
	if(!isset($param['title']) || empty($param['title']))
		return $controller->listCatalogue($request, $response, $args);

	$InfoMusic = $controller->infoMusic($request, $response, $args);
})->setName('InfoMusic');

$app->get('/GetArtistes', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$GetArtistes = $controller->getArtistes($request, $response, $args);
})->setName('GetArtistes');

$app->get('/GetPistes', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$GetPistes = $controller->getPistes($request, $response, $args);
})->setName('GetPistes');

$app->get('/GetGenres', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$GetGenres = $controller->getGenres($request, $response, $args);
})->setName('GetGenres');

$app->post('/EditMusic', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$EditMusic = $controller->editMusic($request, $response, $args);
})->setName('EditMusic');

$app->get('/GetAlbums', function($request, $response, $args){
	$controller = $this['CatalogueController'];
	$controller->getAlbums($request, $response, $args);
})->setName('GetAlbums');

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