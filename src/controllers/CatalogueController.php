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
use jukeinthebox\models\Fait_partie;
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
		$tableauPistes = [];
		foreach (Piste::all() as $piste) {
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
			
			$albums = [];
			$albumQuerry = Piste::join('fait_partie','piste.idPiste','fait_partie.idPiste')
				->join('album', 'fait_partie.idAlbum','album.idAlbum' )
				->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
				->get();
			foreach ($albumQuerry as $value)
			{
				array_push($albums, $value->getOriginal()['nomAlbum']);

			} 
				


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
		$nomArtiste = '';//Groupe ou personne ?
		$nomArtistes = [];
		$prenomArtiste = '';
		$prenomArtistes = [];
		$anneeAlbum = '';
		$genre ='';
		$nomAlbum = '';
		$imageAlbum ='';

		$piste = $request->getParams();
		if(isset($piste['nbArtistes'])) $nbArtistes = $piste['nbArtistes'];

		// Ne fonctionne pas sur un groupe
		if($method == "POST")
		{
			$champsRequis = ['nomPiste', 'anneePiste', 'genrePiste', 'nomAlbum'];
			for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
			$areAllFieldsOK = true;


			foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);
			if($areAllFieldsOK){

				try{
					$nomPiste = filter_var($piste['nomPiste'], FILTER_SANITIZE_STRING);
					$imagePiste = filter_var($piste['imagePiste'], FILTER_SANITIZE_URL);
					$anneePiste = filter_var($piste['anneePiste'], FILTER_SANITIZE_NUMBER_INT);
					$imageAlbum = filter_var($piste['imageAlbum'], FILTER_SANITIZE_URL);

					for($i = 1; $i <= $nbArtistes; $i++) {
						array_push($nomArtistes, filter_var($piste['nomArtiste'.$i], FILTER_SANITIZE_STRING));
						array_push($prenomArtistes, filter_var($piste['prenomArtiste'.$i], FILTER_SANITIZE_STRING));
					}

					$anneeAlbum = filter_var($piste['anneeAlbum'],FILTER_SANITIZE_NUMBER_INT);
					$nomGenre = filter_var($piste['genrePiste'], FILTER_SANITIZE_STRING);
					$nomAlbum = filter_var($piste['nomAlbum'], FILTER_SANITIZE_STRING);


					Piste::query()->firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste])->save();

					$piste = Piste::select('idPiste')->where('nomPiste','like',  $nomPiste)->first();

					for($i = 0 ; $i < $nbArtistes; $i++) {
						Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[$i], 'prénomArtiste' => $prenomArtistes[$i]])->save();
						$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[$i])->first();
						A_joue_piste::query()->firstOrCreate(['idPiste'=>$piste->idPiste,'idArtiste'=> $artiste->idArtiste])->save();
					}
					
					Genre::query()->firstOrCreate(['nomGenre' => $nomGenre])->save();
					$genre = Genre::select('idGenre')->where('nomGenre','like', $nomGenre)->first();
					Est_du_genre_piste::query()->firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre])->save();
					Album::query()->firstOrCreate(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum])->save();
					
					$album = Album::select('idAlbum')->where('nomAlbum','like',$nomAlbum)->first();
					Fait_partie::query()->firstOrCreate(['idPiste'=> $piste->idPiste,'idAlbum'=>$album->idAlbum])->save();
					A_joue_album::query()->firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste'=>$artiste->idArtiste])->save();

				}
				catch(\Exception $e){
					print($e);
					die;
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
		//die;
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

	public function createAlbum($request, $response, $args) {	


		
			$champsRequis = ['titreAlbum', 'anneeAlbum2', 'genreAlbum2'];
			for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
			$areAllFieldsOK = true;


			foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);
			if($areAllFieldsOK){

				try{
					$nomAlbum = filter_var($piste['titreAlbum'], FILTER_SANITIZE_STRING);
					$imageAlbum = filter_var($piste['imageAlbum2'], FILTER_SANITIZE_URL);
					$anneeAlbum = filter_var($piste['anneeAlbum2'], FILTER_SANITIZE_NUMBER_INT);
					$genreAlbum = filter_var($piste['genreAlbum2'], FILTER_SANITIZE_STRING);

					for($i = 1; $i <= $nbArtistes; $i++) {
						array_push($nomArtistes, filter_var($piste['nomArtisteAlbum'.$i], FILTER_SANITIZE_STRING));
						array_push($prenomArtistes, filter_var($piste['prenomArtisteAlbum'.$i], FILTER_SANITIZE_STRING));
					}

					Album::query()->firstOrCreate(['nomAlbum' => $titreAlbum,'imageAlbum' => $imageAlbum,'annéeAlbum' => $anneeAlbum])->save();
					$album = Album::select('idAlbum')->where('nomAlbum','like',  $nomAlbum)->first();

					for($i = 0 ; $i < $nbArtistes; $i++) {
						Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[$i], 'prénomArtiste' => $prenomArtistes[$i]])->save();
						$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[$i])->first();
						A_joue_album::query()->firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste'=>$artiste->idArtiste])->save();	
					}
					
					Genre::query()->firstOrCreate(['nomGenre' => $nomGenre])->save();
					$genre = Genre::select('idGenre')->where('nomGenre','like', $nomGenre)->first();
					Est_du_genre_album::query()->firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre])->save();
					
				}
				catch(\Exception $e){
					print($e);
					die;
					$error = "L'album n'a pas été ajoutée, vérifiez vos informations.";
					$url = $request->getUri()->getBasePath();
					return $this->view->render($response, 'AddPiste.html.twig', [
						'url' => $url,
						'error'=> $error
					]);
				}
				
			}

		}


		

}