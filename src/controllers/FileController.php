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
		$idJukeBox = $args['idJukebox'];
		$tabPistes = [];
		$compteur = 0;
		$compteurGenre = 0;
		$compteurArtiste = 0;
		$compteurAlbum = 0;
		$file = File::where('idJukebox', '=', $idJukeBox)->get();
		foreach($file as $row) {
			$tabPistes[$compteur]['idFile'] = $row['idFile'];
			$piste = Piste::where('idPiste', '=', $row['idPiste'])->first();
			$tabPistes[$compteur]['piste']['idPiste'] = $piste['idPiste'];
    		$tabPistes[$compteur]['piste']['nomPiste'] = $piste['nomPiste'];
    		$tabPistes[$compteur]['piste']['annéePiste'] = $piste['annéePiste'];
			$tabPistes[$compteur]['piste']['imagePiste'] = $piste['imagePiste'];
			$estDuGenrePiste = Est_du_genre_piste::join('genre', 'est_du_genre_piste.idGenre', '=', 'genre.idGenre')->where('idPiste', '=', $tabPistes[$compteur]['piste']['idPiste'])->get();
			foreach($estDuGenrePiste as $genrePiste) {
				$tabPistes[$compteur]['piste']['genres'][$compteurGenre] = $genrePiste['nomGenre'];
				$compteurGenre++;
			}
			$compteurGenre = 0;
			$aJouePiste = A_joue_piste::join('artiste', 'a_joué_piste.idArtiste', '=', 'artiste.idArtiste')->where('idPiste', '=', $row['idPiste'])->get();
			foreach ($aJouePiste as $artistePiste) {
				$tabPistes[$compteur]['piste']['artistes'][$compteurArtiste]["prénom"] = $artistePiste['prénomArtiste'];
				$tabPistes[$compteur]['piste']['artistes'][$compteurArtiste]["nom"] = $artistePiste['nomArtiste'];
				$compteurArtiste++;
			}
			$compteurArtiste = 0;
			$albums = Album::join('fait_partie', 'album.idAlbum', '=', 'fait_partie.idAlbum')->where('idPiste', '=', $row['idPiste'])->get();
			foreach ($albums as $album) {
				$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]["idAlbum"] = $album["idAlbum"];
				$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]["nomAlbum"] = $album["nomAlbum"];
				$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]["annéeAlbum"] = $album["annéeAlbum"];
				$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]["imageAlbum"] = $album["imageAlbum"];
				$estDuGenreAlbum = Est_du_genre_album::join('genre', 'est_du_genre_album.idGenre', '=', 'genre.idGenre')->where('idAlbum', '=', $album['idAlbum'])->get();
				foreach ($estDuGenreAlbum as $genreAlbum) {
					$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]['genres'][$compteurGenre] = $genreAlbum['nomGenre'];
					$compteurGenre++;
				}
				$compteurGenre = 0;
				$aJoueAlbum = A_joue_album::join('artiste', 'a_joué_album.idArtiste', '=', 'artiste.idArtiste')->where('idAlbum', '=', $album['idAlbum'])->get();
				foreach ($aJoueAlbum as $artisteAlbum) {
					$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]['artistes'][$compteurArtiste]["prénom"] = $artisteAlbum['prénomArtiste'];
					$tabPistes[$compteur]['piste']['albums'][$compteurAlbum]['artistes'][$compteurArtiste]["nom"] = $artisteAlbum['nomArtiste'];
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

	/**
	 * Method that displays that add a music to the file
	 * @param request
	 * @param response
	 * @param args
	 */
	public function addFile($request, $response, $args) {
		$idJukeBox = $args['idJukebox'];
		$file = new File();
		$file->idPiste = $_POST['id'];
		$file->idJukebox = $idJukeBox;
		$file->save();
	}

	/**
	 * Method that displays that delete a music to the file
	 * @param request
	 * @param response
	 * @param args
	 */
	public function nextFile($request, $response, $args) {
		$idJukeBox = $args['idJukebox'];
		$file = File::where('idJukebox', '=', $idJukeBox)->first()->delete();
	}

}