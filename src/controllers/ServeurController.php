<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Bibliotheque;
use \Slim\Views\Twig as twig;
use jukeinthebox\views\Home;
use jukeinthebox\views\ListJukebox;
use jukeinthebox\views\AddJukebox;

/**
 * Class ServeurController
 */
class ServeurController {

	protected $view;

	/**
	 * Constructor of the class HomeController
	 * @param view
	 */
    public function __construct(twig $view) {
        $this->view = $view;
	}
	
    /**
	 * Method that displays the content of the file
	 * @param request
	 * @param response
	 * @param args
	 */

    public function displayServeur($request, $response, $args) {
			$url = $request->getUri()->getBasePath();
			return $this->view->render($response, 'Home.html.twig', [
				'url' => $url,
			]);
	}


	public function listJukebox($request, $response, $args){
		$listJukebox = Jukebox::get();
		$url = $request->getUri()->getBasePath();
		$nomClient ='';
		$mailClient ='';
		$adresseClient ='';
		$token ='';
		$idJukebox ='';

		if(isset($_POST['nomClient']) && isset($_POST['mailClient']) && isset($_POST['adresseClient'])){
			$nomClient=$_POST['nomClient'];
			$mailClient=$_POST['mailClient'];
			$adresseClient=$_POST['adresseClient'];
			$jukebox = new Jukebox();
			$bibliothque = new Bibliotheque();
			$bibliothque->save();
			$jukebox->idBibliotheque = $bibliothque->idBibliotheque;
			$token = md5(time() . mt_rand());
			$jukebox->qr_code='';
			$jukebox->tokenActivation = $token;
			$jukebox->nomClient = $nomClient;
			$jukebox->mailClient = $mailClient;
			$jukebox->adresseClient = $adresseClient;
			$jukebox->save();

			$idJukebox = $jukebox->idJukebox;
		}
		return $this->view->render($response, 'ListJukebox.html.twig', [
			'url' => $url,
			'nomClient' => $nomClient,
			'mailClient' => $mailClient,
			'adresseClient' => $adresseClient,
			'token' => $token,
			'idJukebox' => $idJukebox,
			'listJukebox' => $listJukebox
		]);
	}

	public function createJukebox($request, $response, $args){
		$url = $request->getUri()->getBasePath();
		return $this->view->render($response, 'AddJukebox.html.twig', [
			'url' => $url,
		]);
	}

	

}