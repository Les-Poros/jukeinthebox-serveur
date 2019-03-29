<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Piste;
use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Album;
use jukeinthebox\models\Artiste;
use jukeinthebox\models\Genre;
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
		
		/*$AjouePiste = A_joue_piste::join('piste','a_joué_piste.idPiste','piste.idPiste')
		->join('artiste','a_joué_piste.idArtiste','artiste.idArtiste')
		->get();

		$EstDuGenrePiste = Est_du_genre_piste::join('piste','est_du_genre_piste.idPiste','piste.idPiste')
		->join('genre', 'est_du_genre_piste.idGenre','genre.idGenre' )
		->get();*/

		//var_dump($AjouePiste);
		//$AjouePiste = $AjouePiste->A_joue_piste->getOriginal();
		/*foreach ($AjouePiste as $key => $value) {
			var_dump($value->getOriginal());
		}*/
		
		$pistes = Piste::all();
		$tableauPistes = [];
		foreach ($pistes as $piste) {
			// Gestion des genres
			$genres = [];
			$genresQuerry = Piste::join('est_du_genre_piste','piste.idPiste','est_du_genre_piste.idPiste')
			->join('genre', 'est_du_genre_piste.idGenre','genre.idGenre' )
			->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
			->get();
			foreach ($genresQuerry as $value) array_push($genres, $value->getOriginal()['nomGenre']);

			// Gestion des artistes
			$artistes = [];
			$artistesQuerry = Piste::join('a_joué_piste','piste.idPiste','a_joué_piste.idPiste')
			->join('artiste', 'a_joué_piste.idArtiste','artiste.idArtiste' )
			->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
			->get();
			foreach ($artistesQuerry as $value) array_push($artistes, $value->getOriginal()['nomArtiste']);

			// Gestion de l'album
			$albumQuerry = Piste::join('fait_partie','piste.idPiste','fait_partie.idPiste')
			->join('album', 'fait_partie.idAlbum','album.idAlbum' )
			->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
			->get();

			//var_dump($albumQuerry->first()->getAttributes('nomAlbum'));


			array_push($tableauPistes, [
				'titre' => $piste->getOriginal()['nomPiste'],
				'genres' => $genres,
				'artistes' => $artistes,
				'annee' => $piste->getOriginal()['annéePiste'],
				'album' => $albumQuerry->first() ? $albumQuerry->first()->getAttributes()['nomAlbum'] : null
			]);
		}
		$url = $request->getUri()->getBasePath();
		$method = $request->getMethod();
		$nomPiste ='';
		$imagePiste ='';
		$anneePiste ='';
		$group = false;
		$nomArtiste ='';//Groupe ou personne ?
		$genre ='';
		$album = '';
		$imageAlbum ='';

		$piste = $request->getParams();

		
		if($method == "POST")
		{
			
			//requete
			
			if(isset($piste['nomPiste']) && isset($piste['anneePiste']) && isset($piste['genrePiste']) && isset($piste['nomArtiste'] ) && isset($piste['album'])){
				try{
					$nomPiste = filter_var($piste['nomPiste'], FILTER_SANITIZE_STRING);
					$imagePiste = filter_var($piste['imagePiste'], FILTER_SANITIZE_URL);
					$imageAlbum = filter_var($piste['imageAlbum'], FILTER_SANITIZE_URL);
					$anneePiste = filter_var($piste['anneePiste'], FILTER_SANITIZE_NUMBER_INT);
					$anneeAlbum = filter_var($piste['anneeAlbum'],FILTER_SANITIZE_NUMBER_INT);
					$nomArtiste = filter_var($piste['nomArtiste'], FILTER_SANITIZE_STRING);
					$prenomArtiste = filter_var($piste['prenomArtiste'], FILTER_SANITIZE_STRING);
					$nomGenre = filter_var($piste['genrePiste'], FILTER_SANITIZE_STRING);
					$album = filter_var($piste['album'], FILTER_SANITIZE_STRING);

				

					$donneePiste = Piste::query()->firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste])->save();
					$artiste = Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtiste, 'prénomArtiste' => $prenomArtiste])->save();
					$piste = Piste::select('idPiste')->where('nomPiste','like', $nomPiste)->first();
					$idPiste = $piste->idPiste;
					$artiste = Artiste::select('idArtiste')->where('nomArtiste','like', $nomArtiste)->first();
					$idArtiste = $artiste->idArtiste;
					
					$AjouePiste = A_joue_piste::query()->firstOrCreate(['idPiste'=>$idPiste,'idArtiste'=>$idArtiste])->save();
					$donneeGenre = Genre::query()->firstOrCreate(['nomGenre' => $nomGenre])->save();
					$genre = Genre::select('idGenre')->where('nomGenre','like', $nomGenre)->first();
					$idGenre = $genre->idGenre;
					$estDuGenre_Piste = Est_du_genre_piste::query()->firstOrCreate(['idPiste'=>$idPiste, 'idGenre'=>$idGenre]);

				}
				catch(\Exception $e){
					$error = "La piste n'a pas été ajoutée, vérifiez vos informations.";
					$url = $request->getUri()->getBasePath();
					return $this->view->render($response, 'AddPiste.html.twig', [
						'url' => $url,
						'error'=> $error
					]);
				}
				
			}

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
			'tableauPistes' => $tableauPistes
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