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

	public function getJukeboxAction($request, $response, $args) {
	
		if(isset($_GET["token"]))
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByQrcode($_GET["token"]))->first();
		else if(isset($_GET["bartender"]))
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByBartender($_GET["bartender"]))->first();
		else $jukebox=null;
		echo json_encode($jukebox->action);
	}

			/**
	 * Method that change action to play
	 * @param request
	 * @param response
	 * @param args
	 */
	public function play($request, $response, $args) {
		if(isset($_POST["token"])){
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByQrcode($_GET["token"]))->first();
			$jukebox->action = "play";
			$jukebox->save();
		}
		else{
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByBartender($_GET["bartender"]))->first();
			$jukebox->action = "play";
			$jukebox->save();
		}
		
	}

		/**
		 * Method that change action to pause
		 * @param request
		 * @param response
		 * @param args
		 */
		public function pause($request, $response, $args) {
			if(isset($_POST["token"])){
				$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByQrcode($_GET["token"]))->first();
				$jukebox->action = "pause";
				$jukebox->save();
			}
			else{
				$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByBartender($_GET["bartender"]))->first();
				$jukebox->action = "pause";
				$jukebox->save();

			}
	}

	/**
		 * Method that change action to repeat
		 * @param request
		 * @param response
		 * @param args
		 */
		public function repeat($request, $response, $args) {
			if(isset($_POST["token"])){
				$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByQrcode($_GET["token"]))->first();
				$jukebox->action = "repeat";
				$jukebox->save();
			}
			else{
				$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByBartender($_GET["bartender"]))->first();
				$jukebox->action = "repeat";
				$jukebox->save();

			}
		}

	/**
	 * Method that displays that delete a music to the file
	 * @param request
	 * @param response
	 * @param args
	 */
	public function nextJuke($request, $response, $args) {
		if(isset($_POST["token"])){
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByQrcode($_GET["token"]))->first();
			$jukebox->action = "next";
			$jukebox->save();
		}
		else{
			$jukebox = Jukebox::where('idJukebox', '=', Jukebox::getIdByBartender($_GET["bartender"]))->first();
			$jukebox->action = "next";
			$jukebox->save();
		}
	}
	

	public function selectCatag($request, $response, $args)
	{
	
			//On récupère la bibliothèque du JukeBox
			$jukebox = Jukebox::where("idJukebox", "=", Jukebox::getIdByBartender($_POST["bartender"]))->first();

			$jukebox->bibliAct = $_POST['id'];

			$jukebox->save();
	}

}