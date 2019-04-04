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

		$url = $request->getUri()->getBasePath();

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
			foreach ($albumQuerry as $value) array_push($albums, $value->getOriginal()['nomAlbum']);
			
			array_push($tableauPistes, [
				'image' => $piste->getOriginal()['imagePiste'],
				'titre' => $piste->getOriginal()['nomPiste'],
				'genres' => $genres,
				'artistes' => $artistes,
				'annee' => $piste->getOriginal()['annéePiste'],
				'album' => $albumQuerry->first() ? $albumQuerry->first()->getAttributes()['nomAlbum'] : null
			]);
		}
		
		if($request->getMethod() == "POST") {
			// Ne fonctionne pas sur un groupe (work in progress)
			$piste = $request->getParams();

			$nomPiste = filter_input(INPUT_POST, 'nomPiste', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomPisteAlbum = filter_input(INPUT_POST, 'nomPisteAlbum', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$imagePiste = filter_input(INPUT_POST, 'imagePiste', FILTER_SANITIZE_URL);
			$anneePiste = filter_input(INPUT_POST, 'anneePiste', FILTER_SANITIZE_NUMBER_INT);
			$anneeAlbum = filter_input(INPUT_POST, 'anneeAlbum', FILTER_SANITIZE_NUMBER_INT);
			
			$nomAlbum = filter_input(INPUT_POST, 'nomAlbum',FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$imageAlbum = filter_input(INPUT_POST, 'imageAlbum', FILTER_SANITIZE_URL);


			if(isset( $piste['personne'])) {
				$nbArtistes = filter_input(INPUT_POST,'nbArtistesPiste', FILTER_SANITIZE_NUMBER_INT) ?? 1;
				$nomGenre = filter_input(INPUT_POST, 'genrePiste', FILTER_SANITIZE_STRING);
			} else {
				$nbArtistes = filter_input(INPUT_POST,'nbArtistesAlbum', FILTER_SANITIZE_NUMBER_INT) ?? 1;
				$nomGenre = filter_input(INPUT_POST, 'genreAlbum', FILTER_SANITIZE_STRING);
			}

			for($i = 1 ; $i <= $nbArtistes; $i++) {
				$nomArtistes[] = filter_input(INPUT_POST, 'nomArtiste'.$i, FILTER_SANITIZE_STRING);
				$prenomArtistes[] = filter_input(INPUT_POST, 'prenomArtiste'.$i, FILTER_SANITIZE_STRING);
			}


			if($piste['ajout'] == 'musique' && $piste['personne'] == 'Artiste') {
				// Ajout d'un musique avec un ou plusieurs artistes
				$champsRequis = ['nomPiste', 'anneePiste', 'genrePiste', 'nomAlbum'];
				for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
				$areAllFieldsOK = true;
	
	
				foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);
				if($areAllFieldsOK){
					try{	

						Piste::query()->firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste])->save();
	
						$piste = Piste::select('idPiste')->where('nomPiste','like',  $nomPiste)->first();

						Album::query()->firstOrCreate(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum])->save();
						$album = Album::select('idAlbum')->where('nomAlbum','like',$nomAlbum)->first();
						Fait_partie::query()->firstOrCreate(['idPiste'=> $piste->idPiste,'idAlbum'=>$album->idAlbum])->save();
						
						
						for($i = 0 ; $i < $nbArtistes; $i++) {
							Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[$i], 'prénomArtiste' => $prenomArtistes[$i]])->save();
							$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[$i])->first();
							A_joue_piste::query()->firstOrCreate(['idPiste'=>$piste->idPiste,'idArtiste'=> $artiste->idArtiste])->save();
							A_joue_album::query()->firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste'=>$artiste->idArtiste])->save();
						}

						$nomGenre = explode(",", $nomGenre);
						foreach ($nomGenre as $nom) {
							Genre::query()->firstOrCreate(['nomGenre' => $nom])->save();
							$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
							Est_du_genre_piste::query()->firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre])->save();
						}
						
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
			elseif($piste['ajout'] == 'musique' && $piste['personne'] == 'Groupe') {
				// Ajout d'un musique avec un groupe
				$champsRequis = ['nomPiste', 'anneePiste', 'genrePiste', 'nomAlbum'];
				for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
				$areAllFieldsOK = true;

				foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);

				if($areAllFieldsOK){
	
					try{
	
						Piste::query()->firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste])->save();
	
						$piste = Piste::select('idPiste')->where('nomPiste','like',  $nomPiste)->first();

						Album::query()->firstOrCreate(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum])->save();
						$album = Album::select('idAlbum')->where('nomAlbum','like', $nomAlbum)->first();

						$nomGenre = explode(",", $nomGenre);
						foreach ($nomGenre as $nom) {
							Genre::query()->firstOrCreate(['nomGenre' => $nom])->save();
							$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
							Est_du_genre_album::query()->firstOrCreate(['idAlbum'=> $album->getOriginal()['idAlbum'], 'idGenre'=> $genre->idGenre])->save();
						}

						Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[0], 'prénomArtiste' => ""])->save();
						$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[0])->first();

						A_joue_piste::query()->firstOrCreate(['idPiste'=>$piste->idPiste,'idArtiste'=> $artiste->idArtiste])->save();
						Est_du_genre_piste::query()->firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre])->save();
						

						
						Fait_partie::query()->firstOrCreate(['idPiste'=> $piste->idPiste,'idAlbum'=> $album->getOriginal()['idAlbum']])->save();
						A_joue_album::query()->firstOrCreate(['idAlbum' => $album->getOriginal()['idAlbum'], 'idArtiste'=>$artiste->idArtiste])->save();

	
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
			//l'erreur est là, aussi
			elseif($piste['ajout'] == 'album' && $piste['personneAlbum'] == 'Artiste') {
				//var_dump($piste); die;
				$champsRequis = ['nomAlbum', 'anneeAlbum', 'genreAlbum'];
				for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
				$areAllFieldsOK = true;

				foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);
				//var_dump($nomAlbum); die;

				if($areAllFieldsOK){
	
					try{
						$pisteAlbum = Piste::where('nomPiste', 'like', $nomPisteAlbum)->first();
						if($pisteAlbum) {

							//Insertion de l'album
							Album::query()->firstOrCreate(['nomAlbum' => $nomAlbum,'imageAlbum' => $imageAlbum,'annéeAlbum' => $anneeAlbum])->save();
							$album = Album::select('idAlbum')->where('nomAlbum','like',  $nomAlbum)->first();
		
							for($i = 0 ; $i < $nbArtistes; $i++) {
								Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[$i], 'prénomArtiste' => $prenomArtistes[$i]])->save();
								$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[$i])->first();
								A_joue_album::query()->firstOrCreate(['idAlbum'=> $album->idAlbum,'idArtiste'=> $artiste->idArtiste])->save();
							}
							
							$nomGenre = explode(",", $nomGenre);
							foreach ($nomGenre as $nom) {
								Genre::query()->firstOrCreate(['nomGenre' => $nom])->save();
								$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
								Est_du_genre_album::query()->firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre])->save();
							}
						} else $error = "L'album n'a pas été ajouté, vérifiez vos informations.";
					}
					catch(\Exception $e){
						print($e);
						die;
						$error = "L'album n'a pas été ajouté, vérifiez vos informations.";
						$url = $request->getUri()->getBasePath();
						return $this->view->render($response, 'AddPiste.html.twig', [
							'url' => $url,
							'error'=> $error
						]);
					}
					$url = $request->getUri()->getBasePath();
					return $this->view->render($response, 'AddPiste.html.twig', [
						'url' => $url,
						'error' => $error
					]);
				}
			}
			//$piste['ajout'] == 'album' && $piste['entite'] == 'groupe'
			else {
				$champsRequis = ['nomAlbum', 'anneeAlbum', 'genreAlbum'];
				for($i = 1; $i <= $nbArtistes; $i++) array_push($champsRequis, 'nomArtiste'.$i);
				$areAllFieldsOK = true;

				foreach($champsRequis as $champs) $areAllFieldsOK &= isset($piste[$champs]);

				if($areAllFieldsOK){
	
					try{
						$pisteAlbum = Piste::where('nomPiste', 'like', $nomPisteAlbum)->first();
						if($pisteAlbum) {

							//Insertion de l'album
							Album::query()->firstOrCreate(['nomAlbum' => $nomAlbum,'imageAlbum' => $imageAlbum,'annéeAlbum' => $anneeAlbum])->save();
							$album = Album::select('idAlbum')->where('nomAlbum','like',  $nomAlbum)->first();
		
							//Insertion du groupe
							Artiste::query()->firstOrCreate(['nomArtiste' => $nomArtistes[0], 'prénomArtiste' =>''])->save();
							$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[0])->first();
							//Lié un album à des artistes 
							A_joue_album::query()->firstOrCreate(['idAlbum'=>$album->idAlbum,'idArtiste'=> $artiste->idArtiste])->save();

							$nomGenre = explode(",", $nomGenre);
							foreach ($nomGenre as $nom) { //Insertion des genres
								Genre::query()->firstOrCreate(['nomGenre' => $nom])->save();
								$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
								//Lié un genre à un album
								Est_du_genre_album::query()->firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre])->save();
							}
						} else $error = "L'album n'a pas été ajouté, vérifiez vos informations.";
					}
					catch(\Exception $e){
						print($e);
						$error = "L'album n'a pas été ajoutée, vérifiez vos informations.";
						$url = $request->getUri()->getBasePath();
						return $this->view->render($response, 'AddPiste.html.twig', [
							'url' => $url,
							'error'=> $error
						]);
					}
						
					$url = $request->getUri()->getBasePath();
					return $this->view->render($response, 'AddPiste.html.twig', [
						'url' => $url,
						'error' => $error
					]);
				}		
			}
		}
		
		$url = $request->getUri()->getBasePath();
		return $this->view->render($response, 'ListMusic.html.twig', [
			'url' => $url,
			//'error' => $error,
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

	/**
	 * Methode pour éditer une musique
	 * @param request
	 * @param response
	 * @param args
	 */

	public function infoMusic($request, $response, $args)
	{
		
		$param = $request->getParams();
		$url = $request->getUri()->getBasePath();
		$data = [];
		$piste = Piste::where('nomPiste','like',$param['title'])->first();

		$artistes = [];
		$artistesQuerry = Piste::join('a_joué_piste','piste.idPiste','a_joué_piste.idPiste')
				->join('artiste', 'a_joué_piste.idArtiste','artiste.idArtiste' )
				->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
				->get();
		foreach ($artistesQuerry as $value) array_push($artistes, $value->getOriginal()['nomArtiste']);

		$genres = [];
		$genresQuerry = Piste::join('est_du_genre_piste','piste.idPiste','est_du_genre_piste.idPiste')
			->join('genre', 'est_du_genre_piste.idGenre','genre.idGenre' )
			->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
			->get();
		foreach ($genresQuerry as $value) array_push($genres, $value->getOriginal()['nomGenre']);

		$albums = [];
		$albumQuerry = Piste::join('fait_partie','piste.idPiste','fait_partie.idPiste')
			->join('album', 'fait_partie.idAlbum','album.idAlbum' )
			->where('piste.idPiste','=', $piste->getOriginal()['idPiste'])
			->get();
		foreach ($albumQuerry as $value) array_push($albums, $value->getOriginal()['nomAlbum']);
		


		$data = [
			'titre' => $param['title'],
			'image' => $piste->getOriginal()['imagePiste'],
			'genres' => $genres,
			'artistes' => $artistes,
			'annee' => $piste->getOriginal()['annéePiste'],
			'album' => $albumQuerry->first() ? $albumQuerry->first()->getAttributes()['nomAlbum'] : null,
			'imageAlbum' => $albumQuerry->first() ? $albumQuerry->first()->getAttributes()['imageAlbum'] : null,
			'anneeAlbum' => $albumQuerry->first() ? $albumQuerry->first()->getAttributes()['annéeAlbum'] : null
		];

		var_dump($data);

		return $this->view->render($response, 'InfoPiste.html.twig', [
			'url' => $url,
			'data' => $data
		]);
	}

	public function getArtistes($request, $response, $args) {
		$param = $request->getParams();

		$nomArtistes = [];
		$prenomArtistes = [];

		//$artistesQuerry = Artiste::where('nomArtiste','like', '%'.$param['name'].'%')->get();
		$artistesQuerry = Artiste::all();
		foreach ($artistesQuerry as $value) {
			array_push($nomArtistes, $value->getOriginal()['nomArtiste']);
			array_push($prenomArtistes, $value->getOriginal()['prénomArtiste']);
		}
		$response->getBody()->write(json_encode(['nom' => $nomArtistes, 'prenom' => $prenomArtistes]));
		
		return $response;
	}

	public function getPistes($request, $response, $args) {
		$param = $request->getParams();

		$pistes = [];

		//$pistesQuerry = Piste::where('nomPiste','like', '%'.$param['name'].'%')->get();
		$pistesQuerry = Piste::all();
		foreach ($pistesQuerry as $value) array_push($pistes, $value->getOriginal()['nomPiste']);

		$response->getBody()->write(json_encode($pistes));
		
		return $response;
	}

	public function getGenres($request, $response, $args) {
		$param = $request->getParams();

		$genres = [];

		//$genresQuerry = Genre::where('nomGenre','like', '%'.$param['name'].'%')->get();
		$genresQuerry = Genre::all();
		foreach ($genresQuerry as $value) array_push($genres, $value->getOriginal()['nomGenre']);

		$response->getBody()->write(json_encode($genres));
		
		return $response;
	}

	public function editMusic($request, $response, $args) {
		$param = $request->getParams();

		if($request->getMethod() == "POST") {
			// Ne fonctionne pas sur un groupe (work in progress)
			$piste = $request->getParams();

			// Required
			$nomPiste = filter_input(INPUT_POST, 'nomPiste', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomPisteOriginal = filter_input(INPUT_POST, 'nomPisteOriginal', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomGenre = filter_input(INPUT_POST, 'genrePiste', FILTER_SANITIZE_STRING);
			$anneePiste = filter_input(INPUT_POST, 'anneePiste', FILTER_SANITIZE_NUMBER_INT);
			$nomAlbum = filter_input(INPUT_POST, 'nomAlbum',FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomAlbumOriginal = filter_input(INPUT_POST, 'nomAlbumOriginal',FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

			// Optional
			$imagePiste = filter_input(INPUT_POST, 'imagePiste', FILTER_SANITIZE_URL);
			$anneeAlbum = filter_input(INPUT_POST, 'anneeAlbum', FILTER_SANITIZE_NUMBER_INT);
			$imageAlbum = filter_input(INPUT_POST, 'imageAlbum', FILTER_SANITIZE_URL);
			

			if($nomPiste && $nomPisteOriginal && $nomGenre && $anneePiste && $nomAlbum && $nomAlbumOriginal) {
				try{
					$piste = Piste::select('idPiste')->where('nomPiste','like',  $nomPisteOriginal)->first();
					Piste::where('idPiste', 'like', $piste->idPiste)
						->update(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste]);
					
					Album::where('nomAlbum', 'like',  $nomAlbumOriginal)
						->update(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum]);


					$nomGenre = explode(", ", $nomGenre);
					foreach ($nomGenre as $nom) {
						
						Est_du_genre_piste::where('idPiste', '=', $piste->idPiste)->delete();
						$genre = Genre::where('nomGenre', 'like', $nom)->first();
						var_dump($genre);
						if($genre) Est_du_genre_piste::create(['idPiste' => $piste->idPiste, 'idGenre' => $genre->getOriginal()['idGenre']]);
					}
					
				}
				catch(\Exception $e){
					print($e);
					die;
					$error = "La piste n'a pas été modifiée, vérifiez vos informations.";
					$url = $request->getUri()->getBasePath();
					return $this->view->render($response, 'InfoPiste.html.twig', [
						'url' => $url,
						'error'=> $error
					]);
				}
			}
		}
		$data = [
			'titre' => $nomPiste,
			'image' => $imagePiste,
			'genres' => $nomGenre,
			'artistes' => $artistes,
			'annee' => $anneePiste,
			'album' => $imageAlbum,
			'imageAlbum' => $imageAlbum,
			'anneeAlbum' => $anneeAlbum
		];
		
		
		return $this->view->render($response, 'InfoPiste.html.twig', [
			'url' => $url,
			'error'=> $error,
			'data' => $data
		]);
	}
}