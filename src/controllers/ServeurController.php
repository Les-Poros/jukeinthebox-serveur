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
					<link rel="stylesheet" href="{$url}/src/css/jukeinthebox.css">
			</head>

			<body>
					<div class="mainContainer">	
					<h1>JukeInTheBox</h1>
						<div class="buttons">
									<a href="{$url}/ListJukebox"><div class="button">Gérer les jukebox</div></a>
									<a href="{$url}/"><div class="button">Gérer le catalogue</div></a>
						</div>
					</div>
			</body>

			</html>
HTML;
			echo $html;
	}


	public function listJukebox($request, $response, $args){
		$listJukebox = Jukebox::get();
		$url = $request->getUri()->getBasePath();
		$html = <<<HTML
		<!DOCTYPE html>
		<html>

		<head>
				<meta charset="UTF-8">
				<link rel="stylesheet" href="{$url}/src/css/jukeinthebox.css">
				<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		</head>

		<body>
			<div class="mainContainer">
				<h1>Liste des jukebox</h1>
				<div class="buttons">
					<a href="{$url}/"><div class="button">Accueil</div></a>
					<a href="{$url}/CreateJukebox"><div class="button">Ajouter un jukebox</div></a>
				</div>
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
			<div class="newJukebox">
			<p>Le jukebox n°$jukebox->idJukebox a été créé pour $nomClient à l'adresse $adresseClient.<br>
			Clé d'api à envoyer : $token <br>
			à l'adresse mail $mailClient</p>
			</div>
HTML;
		}


		$html .= <<<HTML
				<table class="table table-hover">
					<tr>
						<th scope="col">&nbsp;</th>
						<th scope="col">Clé d'activation</th>
						<th scope="col">Est activé ?</th>
						<th scope="col">Token QRCode</th>
						<th scope="col">Client</th>
						<th scope="col">Mail</th>
						<th scope="col">Adresse</th>
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
		</body>

		</html>
HTML;
		echo $html;
	}

	public function createJukebox($request, $response, $args){
		$url = $request->getUri()->getBasePath();
		$html = <<<HTML
		<!DOCTYPE html>
		<html>

		<head>
				<meta charset="UTF-8">
				<link rel="stylesheet" href="{$url}/src/css/jukeinthebox.css">
		</head>

		<body>
			<div class="mainContainer">
				<h1>Ajouter un Jukebox</h1>
                <a href="{$url}/"><div class="buttonAlone">Accueil</div></a>
				<form action='{$url}/ListJukebox' method='post'>
					<div class="itemForm">
						<label>Nom du client</label>
						<input type='text' name="nomClient" required>
					</div>
					<div class="itemForm">
						<label>Mail du client</label>
						<input type='email' name="mailClient" required>
					</div>
					<div class="itemForm">
						<label>Adresse du client</label>
						<input type='text' name="adresseClient" required>
					</div>
					<button type="submit">Ajouter le jukebox</button>
				</form>
			</div>
		</body>

		</html>
HTML;
		echo $html;
	}

	

}