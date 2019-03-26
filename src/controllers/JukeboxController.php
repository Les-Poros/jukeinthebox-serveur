<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Bibliotheque;
use \Slim\Views\Twig as twig;
use jukeinthebox\views\Home;
use jukeinthebox\views\ListJukebox;
use jukeinthebox\views\AddJukebox;

/**
 * Class JukeboxController
 */
class JukeboxController {

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

    public function setQrcode($request, $response, $args) {
	
		//On récupère le jukebox
		$jukebox = Jukebox::where("tokenActivation","=",$_POST["bartender"])->first();
		$jukebox->qr_code2 = $jukebox->qr_code;
		$jukebox->qr_code = $_POST['qrcode'];
	
		$jukebox->save();
	}

	public function validateToken($request, $response, $args) {
	
		if(isset($_GET["token"]))
			$jukebox = Jukebox::getIdByQrcode($_GET["token"]);
		else if(isset($_GET["bartender"]))
			$jukebox = Jukebox::getIdByBartender($_GET["bartender"]);
		else $jukebox=null;
	
		if($jukebox == null)
			echo json_encode( ['validate' => false]);
		else
			echo json_encode( ['validate' => true]);
	}
	
}