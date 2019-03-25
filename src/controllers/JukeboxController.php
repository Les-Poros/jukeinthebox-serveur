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
		
		$jukebox->qr_code = $_POST['qrcode'];
	
		$jukebox->save();
	}
	
}