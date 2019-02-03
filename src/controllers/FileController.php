<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\File;
use jukeinthebox\models\Piste;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Album;
use jukeinthebox\models\Est_du_genre_album;
use jukeinthebox\models\A_joue_album;

/**
 * Class FileController
 */
class FileController {

	/**
	 * Method that displays the content of the file
	 * @param request
	 * @param response
	 * @param args
	 */
	public function displayFile($request, $response, $args) {
		$tabPistes = [];
		$compteur = 0;
		$compteurGenre = 0;
		$compteurArtiste = 0;
		$compteurAlbum = 0;
		$file = File::get();
		foreach($file as $row) {
			$tabPistes[$compteur]['idFile'] = $row['idFile'];
			$piste = Piste::where('idPiste', '=', $row['idPiste'])->first();
			$tabPistes[$compteur]['idPiste'] = $piste['idPiste'];
    		$tabPistes[$compteur]['nomPiste'] = $piste['nomPiste'];
    		$tabPistes[$compteur]['annéePiste'] = $piste['annéePiste'];
			$tabPistes[$compteur]['imagePiste'] = $piste['imagePiste'];
			$estDuGenrePiste = Est_du_genre_piste::join('genre', 'est_du_genre_piste.idGenre', '=', 'genre.idGenre')->where('idPiste', '=', $tabPistes[$compteur]['idPiste'])->get();
			foreach($estDuGenrePiste as $genrePiste) {
				$tabPistes[$compteur]['genres'][$compteurGenre] = $genrePiste['nomGenre'];
				$compteurGenre++;
			}
			$compteurGenre = 0;
			$aJouePiste = A_joue_piste::join('artiste', 'a_joué_piste.idArtiste', '=', 'artiste.idArtiste')->where('idPiste', '=', $row['idPiste'])->get();
			foreach ($aJouePiste as $artistePiste) {
				$tabPistes[$compteur]['artistes'][$compteurArtiste]["prénom"] = $artistePiste['prénomArtiste'];
				$tabPistes[$compteur]['artistes'][$compteurArtiste]["nom"] = $artistePiste['nomArtiste'];
				$compteurArtiste++;
			}
			$compteurArtiste = 0;
			$albums = Album::join('fait_partie', 'album.idAlbum', '=', 'fait_partie.idAlbum')->where('idPiste', '=', $row['idPiste'])->get();
			foreach ($albums as $album) {
				$tabPistes[$compteur]['albums'][$compteurAlbum]["idAlbum"] = $album["idAlbum"];
				$tabPistes[$compteur]['albums'][$compteurAlbum]["nomAlbum"] = $album["nomAlbum"];
				$tabPistes[$compteur]['albums'][$compteurAlbum]["annéeAlbum"] = $album["annéeAlbum"];
				$tabPistes[$compteur]['albums'][$compteurAlbum]["imageAlbum"] = $album["imageAlbum"];
				$estDuGenreAlbum = Est_du_genre_album::join('genre', 'est_du_genre_album.idGenre', '=', 'genre.idGenre')->where('idAlbum', '=', $album['idAlbum'])->get();
				foreach ($estDuGenreAlbum as $genreAlbum) {
					$tabPistes[$compteur]['albums'][$compteurAlbum]['genres'][$compteurGenre] = $genreAlbum['nomGenre'];
					$compteurGenre++;
				}
				$compteurGenre = 0;
				$aJoueAlbum = A_joue_album::join('artiste', 'a_joué_album.idArtiste', '=', 'artiste.idArtiste')->where('idAlbum', '=', $album['idAlbum'])->get();
				foreach ($aJoueAlbum as $artisteAlbum) {
					$tabPistes[$compteur]['albums'][$compteurAlbum]['artistes'][$compteurArtiste]["prénom"] = $artisteAlbum['prénomArtiste'];
					$tabPistes[$compteur]['albums'][$compteurAlbum]['artistes'][$compteurArtiste]["nom"] = $artisteAlbum['nomArtiste'];
					$compteurArtiste++;
				}
				$compteurArtiste = 0;
				$compteurAlbum++;
			}
			$compteurAlbum = 0;
    		$compteur++;
		}
		$json = ['pistes' => $tabPistes];

		echo json_encode($json);
	}

}