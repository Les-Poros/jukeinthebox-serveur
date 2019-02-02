<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Piste;
use jukeinthebox\models\Est_du_genre_piste;

/**
 * Class CatalogueController
 */
class CatalogueController {

	/**
	 * Method that displays the catalogue
	 * @param request
	 * @param response
	 * @param args
	 */
	public function displayCatalogue($request, $response, $args) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
		header('Content-type: application/json');
		$tabPistes = [];
		$compteur = 0;
		$compteurGenre = 0;
		$compteurArtiste = 0;
		$compteurAlbum = 0;
		$search = "";
		if (isset($_GET["piste"])) {
			$search = $_GET["piste"];
		}
		$pistes = Piste::where('nomPiste', 'like', "%$search%")->get();
		foreach($pistes as $row) {
			$tabPistes[$compteur]['idPiste'] = $row['idPiste'];
    		$tabPistes[$compteur]['nomPiste'] = $row['nomPiste'];
    		$tabPistes[$compteur]['annéePiste'] = $row['annéePiste'];
			$tabPistes[$compteur]['imagePiste'] = $row['imagePiste'];
			$estDuGenrePiste = Est_du_genre_piste::join('genre', 'est_du_genre_piste.idGenre', '=', 'genre.idGenre')->where('idPiste', '=', $tabPistes[$compteur]['idPiste'])->get();
			foreach($estDuGenrePiste as $genrePiste) {
				$tabPistes[$compteur]['genres'][$compteurGenre] = $genrePiste['nomGenre'];
				$compteurGenre++;
			}
			$compteurGenre = 0;
    		$compteur++;
		}
		$array = ['pistes' => $tabPistes];
		$json = ['catalogue' => $array];

		return json_encode($json);
	}

}