<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Bibliotheque;

/**
 * Class ServeurController
 */
class ServeurController {
    /**
	 * Method that displays the content of the file
	 * @param request
	 * @param response
	 * @param args
	 */

    public function displayServeur($request, $response, $args) {
			$url = $request->getUri()->getBasePath();
			$html = <<<HTML
			<!DOCTYPE html>

			<html>

			<head>
					<meta charset="UTF-8">
					<link rel="stylesheet" href="{$url}/css/jukeinthebox.css">
			</head>

			<body>
					<div>	
					<h1>JukeInTheBox</h1>
									<a href="{$url}/ListJukebox" class="homeButton">Gérer les jukebox</a>
									<button type="button" disabled>Gérer le catalogue</button>

					</div>
			</body>

			</html>
HTML;
			echo $html;
	}


	public function listJukebox($request, $response, $args){
		$listJukebox = Jukebox::get();
		$url = $request->getUri()->getBasePath().'/CreateJukebox';
		$html = <<<HTML
			<div>
				<h1>Liste des jukebox</h1>
                <a href="$url">Ajouter un jukebox</a>
HTML;

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
			$html .= <<<HTML
			<div>
			<p>Le jukebox n°$jukebox->idJukebox a été créé pour $nomClient à l'adresse $adresseClient.<br>
			Clé d'api à envoyer : $token <br>
			à l'adresse mail $mailClient</p>
			</div>
HTML;
		}


		$html .= <<<HTML
				<table>
					<tr>
						<td>&nbsp;</td>
						<td>Clé d'activation</td>
						<td>Est activé ?</td>
						<td>Token QRCode</td>
						<td>Client</td>
						<td>Mail</td>
						<td>Adresse</td>
					</tr>
HTML;
		foreach ($listJukebox as $j) {
			$html .= <<<HTML
				<tr>
					<td>Jukebox n°$j->idJukebox</td> <td>$j->tokenActivation</td>
HTML;
			if($j->estActive == 1){
				$html .= <<<HTML
					<td>oui</td>
HTML;
			}else{
				$html .= <<<HTML
					<td>non</td>
HTML;
			}
			$html .= <<<HTML
					<td>$j->qr_code</td>
					<td>$j->nomClient</td>
					<td>$j->mailClient</td>
					<td>$j->adresseClient</td>
				</tr>			
HTML;
		}
		$html .= <<<HTML
				</table>
			</div>
HTML;
		echo $html;
	}

	public function createJukebox($request, $response, $args){
		$url = $request->getUri()->getBasePath().'/ListJukebox';
		$html = <<<HTML
			<div>
				<h1>Ajouter un Jukebox</h1>
				<form action='$url' method='post'>
					<label>Nom du client</label>
					<input type='text' name="nomClient" required>
					<label>Mail du client</label>
					<input type='email' name="mailClient" required>
					<label>Adresse du client</label>
					<input type='text' name="adresseClient" required>
					<button type="submit">Ajouter le jukebox</button>
				</form>
			</div>
HTML;
		echo $html;
	}

	

}