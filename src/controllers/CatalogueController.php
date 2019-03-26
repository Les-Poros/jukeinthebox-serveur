<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Piste;
use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Album;
use jukeinthebox\models\Est_du_genre_album;
use jukeinthebox\models\A_joue_album;
use jukeinthebox\models\Contenu_bibliotheque;
use jukeinthebox\models\Bibliotheque;
use \Slim\Views\Twig as twig;
use jukeinthebox\views\Home;
use \jukeinthebox\views\ListMusic;
use \jukeinthebox\views\AddPiste;

/**
 * Class CatalogueController
 */
class CatalogueController {

	protected $view;

	/**
	 * Constructor of the class HomeController
	 * @param view
	 */
    public function __construct(twig $view) {
        $this->view = $view;
	}
	
	
	/**
	 * Method that displays the content of the catalogue
	 * @param request
	 * @param response
	 * @param args
	 */
	public function displayCatalogue($request, $response, $args) {
		//header('Access-Control-Allow-Origin: *');
		//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
		//header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
		//header('Content-type: application/json');
		$tabPistes = [];
		$compteur = 0;
		$compteurGenre = 0;
		$compteurArtiste = 0;
		$compteurAlbum = 0;
		$search = "";
		$nomCatag="Global";
		if (isset($_GET["piste"])) {
			$search = $_GET["piste"];
		}
		if(isset($_GET["token"]))
		{
			$nomCatag=Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox","=",Jukebox::getIdByQrcode($_GET["token"]))->first()->titre;
			$pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->where("idBibliotheque","=",Jukebox::getIdByQrcode($_GET["token"]))->where('nomPiste', 'like', "%$search%")->get();
		}
		else 
		if(isset($_GET["bartender"]))
		{
			$nomCatag=Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox","=",Jukebox::getIdByBartender($_GET["bartender"]))->first()->titre;
			if(isset($_GET["addCatag"]))
			$pistes = Piste::wherenotin('idPiste',function($query){$query->select('idPiste')->from('contenu_bibliotheque')->where('idBibliotheque', '=',Jukebox::getIdByBartender($_GET["bartender"]));})->where('nomPiste', 'like', "%$search%")->get();
			else
			$pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->where("idBibliotheque","=",Jukebox::getIdByBartender($_GET["bartender"]))->where('nomPiste', 'like', "%$search%")->get();
		}
		else
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
		$array = ['pistes' => $tabPistes,"nomCatag"=>$nomCatag];
		$json = ['catalogue' => $array];

		echo json_encode($json);
	}

	/**
	 * Method that displays that add a music to the bibliotheque
	 * @param request
	 * @param response
	 * @param args
	 */

	public function addMusicBiblio($request, $response, $args) {
		$addContenu = new Contenu_bibliotheque();
	
		//On récupère la bibliothèque du JukeBox
		$getBibliotheque = Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox","=",Jukebox::getIdByBartender($_POST["bartender"]))->first()->idBibliotheque;
		
		$addContenu->idPiste = $_POST['id'];
		
		$addContenu->idBibliotheque = $getBibliotheque;
	
		$addContenu->save();
	}

	/**
	 * Method that displays that delete a music from the bibliotheque
	 * @param request
	 * @param response
	 * @param args
	 */
	public function deleteMusicBiblio($request, $response, $args) {
	
		Contenu_bibliotheque::where('idPiste','=',$_POST['id'])->first()->delete();
	}

	/**
	 * Method that displays the musics from the bibiotheque
	 * @param request
	 * @param response
	 * @param args
	 */
	public function listCatalogue($request, $response, $args) {	
		//$listJukebox = Jukebox::get();
		$url = $request->getUri()->getBasePath();
		$nomPiste ='';
		$imagePiste ='';
		$anneePiste ='';
		$nomArtiste ='';//Groupe ou personne ?
		$genre ='';
		$album = '';
		$imageAlbum ='';


		//requete
		if(isset($_POST['nomPiste']) && isset($_POST['anneePiste']) && isset($_POST['genre']) && isset($_POST['nomArtiste']) && isset($_POST['album'])){
			//$idPiste = '';
			$nomPiste = $_POST['nomPiste'];
			$imagePiste = $_POST['imagePiste'];
			$anneePiste = $_POST['anneePiste'];
			$nomArtiste = $_POST['nomArtiste'];
			$prenomArtiste = $_POST['prenomArtiste'];
			$genre = $_POST['genre'];
			$album = $_POST['album'];
			$piste = Piste::firstOrCreate(['nomPiste' => $nomPiste],['nomPiste' => $nomPiste]);
			$artiste = Artiste::firstOrCreate(['nomArtiste' => $nomArtiste],['prenomArtiste' => $prenomArtiste]);





			/*$jukebox = new Jukebox();
			$bibliothque = new Bibliotheque();
			$bibliothque->save();
			$jukebox->idBibliotheque = $bibliothque->idBibliotheque;
			$token = md5(time() . mt_rand());
			$jukebox->qr_code='';
			$jukebox->tokenActivation = $token;
			$jukebox->nomClient = $nomClient;
			$jukebox->mailClient = $mailClient;
			$jukebox->adresseClient = $adresseClient;
			$jukebox->save();*/

			$idJukebox = $jukebox->idJukebox;
		}
		/*return $this->view->render($response, 'ListJukebox.html.twig', [
			'url' => $url,
			'nomClient' => $nomClient,
			'mailClient' => $mailClient,
			'adresseClient' => $adresseClient,
			'token' => $token,
			'idJukebox' => $idJukebox,
			'listJukebox' => $listJukebox
		]);*/
		
		$url = $request->getUri()->getBasePath();
		return $this->view->render($response, 'ListMusic.html.twig', [
			'url' => $url,
		]);
	}

	/**
	 * Methode pour ajouter une musique dans un catalogue
	 * @param request
	 * @param response
	 * @param args
	 */

	public function createMusic($request, $response, $args) {	
		$url = $request->getUri()->getBasePath();
		return $this->view->render($response, 'AddPiste.html.twig', [
			'url' => $url,
		]);
	}
}