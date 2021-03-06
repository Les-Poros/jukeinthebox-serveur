<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Album;
use jukeinthebox\models\Artiste;
use jukeinthebox\models\Genre;
use jukeinthebox\models\Est_du_genre_album;
use jukeinthebox\models\A_joue_album;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Contenu_bibliotheque;
use jukeinthebox\models\Fait_partie;
use jukeinthebox\models\Bibliotheque;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Piste;
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

		// Gestion des requêtes depuis AddPiste.html.twig
		if($request->getMethod() == "POST") {
			$piste = $request->getParams();
			

			$nomPiste = filter_input(INPUT_POST, 'nomPiste', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$idsPistesAlbum = filter_input(INPUT_POST, 'idsPistesAlbum', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY) ?: [];
			$imagePiste = filter_input(INPUT_POST, 'imagePiste', FILTER_SANITIZE_URL) ?: "https://www.gap-tallard-durance.fr/fileadmin/_processed_/5/a/csm_AdobeStock_cle_sol_03179e2243.jpg";
			$anneePiste = filter_input(INPUT_POST, 'anneePiste', FILTER_SANITIZE_NUMBER_INT);
			$anneeAlbum = filter_input(INPUT_POST, 'anneeAlbum', FILTER_SANITIZE_NUMBER_INT) ?: $anneePiste;
			
			$nomAlbum = filter_input(INPUT_POST, 'nomAlbum',FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$imageAlbum = filter_input(INPUT_POST, 'imageAlbum', FILTER_SANITIZE_URL) ?: "https://www.gap-tallard-durance.fr/fileadmin/_processed_/5/a/csm_AdobeStock_cle_sol_03179e2243.jpg";

			if(isset($piste['personne'])) {
				$nbArtistes = filter_input(INPUT_POST,'nbArtistesPiste', FILTER_SANITIZE_NUMBER_INT) ?: 1;
				$idsGenres = filter_input(INPUT_POST, 'idsGenrePiste', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY) ?: [];
			} else {
				$nbArtistes = filter_input(INPUT_POST,'nbArtistesAlbum', FILTER_SANITIZE_NUMBER_INT) ?: 1;
				$idsGenres = filter_input(INPUT_POST, 'idsGenreAlbum', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY) ?: [];
			}

			for($i = 1 ; $i <= $nbArtistes; $i++) {
				$nomArtistes[] = filter_input(INPUT_POST, 'nomArtiste'.$i, FILTER_SANITIZE_STRING);
				$prenomArtistes[] = filter_input(INPUT_POST, 'prenomArtiste'.$i, FILTER_SANITIZE_STRING);
			}

			// Enregistre la musique des artistes
			if($piste['ajout'] == 'musique' && $piste['personne'] == 'Artiste') {
				// Ajout d'un musique avec un ou plusieurs artistes
				if($nomPiste && $anneePiste && $nomAlbum && $nbArtistes >= 1){
					try{
						$piste =Piste::firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste]);
						$album = Album::firstOrCreate(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum]);
						Fait_partie::firstOrCreate(['idPiste'=> $piste->idPiste,'idAlbum'=>$album->idAlbum]);
						
						// Ajout des relations sur les artistes
						for($i = 0 ; $i < $nbArtistes; $i++) {
							Artiste::firstOrCreate(['nomArtiste' => $nomArtistes[$i], 'prénomArtiste' => $prenomArtistes[$i]]);
							$artiste = Artiste::select('idArtiste')->where('nomArtiste','like',  $nomArtistes[$i])->first();
							A_joue_piste::firstOrCreate(['idPiste'=>$piste->idPiste,'idArtiste'=> $artiste->idArtiste]);
							A_joue_album::firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste'=>$artiste->idArtiste]);
						}
						
						// Ajout des relations sur les genres
						foreach (Genre::whereIn('idGenre', $idsGenres)->get()->toArray() as $nom) {
							$nom = trim($nom['nomGenre']);
							Genre::firstOrCreate(['nomGenre' => $nom]);
							$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
							Est_du_genre_piste::firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre]);
							Est_du_genre_album::firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre]);
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
			// Enregiste la musique d'un groupe
			elseif($piste['ajout'] == 'musique' && $piste['personne'] == 'Groupe') {
				// Ajout d'un musique avec un groupe
				if($nomPiste && $anneePiste && $nomAlbum && $nbArtistes >= 1){
					try{
						$piste = Piste::firstOrCreate(['nomPiste' => $nomPiste,'imagePiste' => $imagePiste,'annéePiste' => $anneePiste]);
						$album = Album::firstOrCreate(['nomAlbum' => $nomAlbum, 'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum]);
						
						// Ajout des relations sur les artistes
						foreach (Genre::whereIn('idGenre', $idsGenres)->get()->toArray() as $nom) {
							$nom = trim($nom['nomGenre']);
							Genre::firstOrCreate(['nomGenre' => $nom]);
							$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
							Est_du_genre_piste::firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre]);
							Est_du_genre_album::firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre]);
						}

						// Ajout des relations sur le groupe
						$artiste = Artiste::firstOrCreate(['nomArtiste' => $nomArtistes[0], 'prénomArtiste' => ""]);
						A_joue_piste::firstOrCreate(['idPiste'=>$piste->idPiste,'idArtiste'=> $artiste->idArtiste]);
						Fait_partie::firstOrCreate(['idPiste'=> $piste->idPiste,'idAlbum'=> $album->getOriginal()['idAlbum']]);
						A_joue_album::firstOrCreate(['idAlbum' => $album->getOriginal()['idAlbum'], 'idArtiste'=>$artiste->idArtiste]);
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
			// Enregistre un ablum
			else {
				// Vérification des champs
				if(count($idsPistesAlbum) >= 1 && $nomAlbum && $nbArtistes >= 1 && count($idsGenres))  {
					try{
						if(Piste::whereIn('idPiste', $idsPistesAlbum)->get()) {
							$albums = Album::where('nomAlbum', 'like', $nomAlbum)
								->join('a_joué_album', 'album.idAlbum', '=', 'a_joué_album.idAlbum')
								->join('artiste', 'artiste.idArtiste','=','a_joué_album.idArtiste')
								->get()
								->groupBy(function ($item, $key) {return $item->getOriginal()['idAlbum'];})
								->toArray();
							if(empty($albums)) {
								$albumComplet = true;
								$idAlbumComplet = 0;
								foreach ($albums as $key => $album) {
									$nomArtistesTemp = $nomArtistes;
									foreach ($album as $artiste) {
										$artistePresent = false;
										if(in_array($artiste['nomArtiste'], $nomArtistesTemp)) {
											$artistePresent = true;
											$nomArtistesTemp = array_diff($nomArtistesTemp, array($artiste['nomArtiste']));
										}
										$albumComplet &= $artistePresent;
									}
									$albumComplet &= (count($album) == count($nomArtistes));
									if($albumComplet) $idAlbumComplet = $key;
								}
								if($idAlbumComplet !=0) {
									// Cas : l'album existe déjà
									// Nous ajoutons juste les pistes
									foreach(Piste::whereIn('idPiste', $idsPistesAlbum)->get()->toArray() as $piste) {
										Fait_partie::firstOrCreate(['idPiste' => $piste['idPiste'], 'idAlbum' => $idAlbumComplet]);

									}
								} else {
									// Cas : un album de ce nom là existe, mais pas avec les mêmes artistes
									// Nous crééons un nouvel album avec ces artistes
									// puis nous ajoutons les pistes et les relations album <-> artiste 
									$album = Album::firstOrCreate(['nomAlbum' => $nomAlbum,'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum]);

									foreach (Genre::whereIn('idGenre', $idsGenres)->get()->toArray() as $nom) {
										$nom = trim($nom['nomGenre']);
										Genre::firstOrCreate(['nomGenre' => $nom]);
										$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
										Est_du_genre_album::firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre]);
									}
									foreach(Piste::whereIn('idPiste', $idsPistesAlbum)->get()->toArray() as $piste) {
										Fait_partie::firstOrCreate(['idPiste' => $piste['idPiste'], 'idAlbum' => $album->idAlbum]);
									}
									foreach(Artiste::whereIn('nomArtiste', $nomArtistes)->get()->toArray() as $artiste) {
										A_joue_album::firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste' => $artiste['idArtiste']]);
									}
								}
							} else {
								// Cas Aucun album ne porte ce nom là
								// Nous crééons un nouvel album avec ces artistes
								// puis nous ajoutons les pistes et les relations album <-> artiste 
								$album = Album::firstOrCreate(['nomAlbum' => $nomAlbum,'imageAlbum' => $imageAlbum, 'annéeAlbum' => $anneeAlbum]);
								foreach (Genre::whereIn('idGenre', $idsGenres)->get()->toArray() as $nom) {
									$nom = trim($nom['nomGenre']);
									Genre::firstOrCreate(['nomGenre' => $nom]);
									$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
									Est_du_genre_album::firstOrCreate(['idAlbum'=> $album->idAlbum, 'idGenre'=> $genre->idGenre]);
								}
								foreach(Piste::whereIn('idPiste', $idsPistesAlbum)->get()->toArray() as $piste) {
									Fait_partie::firstOrCreate(['idPiste' => $piste['idPiste'], 'idAlbum' => $album->idAlbum]);
								}
								foreach(Artiste::whereIn('nomArtiste', $nomArtistes)->get()->toArray() as $artiste) {
										A_joue_album::firstOrCreate(['idAlbum' => $album->idAlbum, 'idArtiste' => $artiste['idArtiste']]);
								}
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
				}
			}
		}
		// Récupère l'ensemble des pistes avec leurs genres, artistes et albums, et la convertie en tableau indéxé par l'id de piste
		$querry = Piste::join('est_du_genre_piste','piste.idPiste','est_du_genre_piste.idPiste')
			->join('genre', 'est_du_genre_piste.idGenre','genre.idGenre' )
			->join('a_joué_piste','piste.idPiste','a_joué_piste.idPiste')
			->join('artiste', 'a_joué_piste.idArtiste','artiste.idArtiste' )
			->join('fait_partie','piste.idPiste','fait_partie.idPiste')
			->join('album', 'fait_partie.idAlbum','album.idAlbum' )
			->get()
			->groupBy('idPiste')
			->toArray();


		$array = array();
		foreach($querry as $piste) { // Itération sur chaque piste
			foreach ($piste as $val) { // itération sur chaque exemplaire de la piste
				if (!array_key_exists($val['idPiste'], $array)) {
					// Si la piste n'est pas encore dans le tableau, 
					// Créer les entrées necessaires
					$array[$val['idPiste']] = $val;
					$array[$val['idPiste']]['nomGenre'] = [$array[$val['idPiste']]['nomGenre']];
					$array[$val['idPiste']]['nomArtiste'] = [$array[$val['idPiste']]['nomArtiste']];
					$array[$val['idPiste']]['nomAlbum'] = [$array[$val['idPiste']]['nomAlbum']];
				} else {
					// Sinon compléter les champs
					$array[$val['idPiste']]['nomGenre'][] = $val['nomGenre'];
					$array[$val['idPiste']]['nomArtiste'][] = $val['nomArtiste'];
					$array[$val['idPiste']]['nomAlbum'][] = $val['nomAlbum'];
				}
			}
			// $array[$val['idPiste']]['nomGenre'] = ['Rock', 'Rock', 'Funk', 'Rock'], d'où le array_unique()
			// pour revenir sur un exemplaire de chaque. Idem pour les nomArtiste et nomAlbum
			$tableauPistes[] = [
				'image' => $val['imagePiste'],
				'titre' => $val['nomPiste'],
				'genres' => array_unique($array[$val['idPiste']]['nomGenre']),
				'artistes' => array_unique($array[$val['idPiste']]['nomArtiste']),
				'annee' => $val['annéePiste'],
				'album' => array_unique($array[$val['idPiste']]['nomAlbum'])
			];
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

		

		return $this->view->render($response, 'InfoPiste.html.twig', [
			'url' => $url,
			'data' => $data
		]);
	}

	/**
	 * Methode pour récupérer un tableau des noms et prenoms d'artistes
	 * @param request
	 * @param response
	 * @param args
	 */
	public function getArtistes($request, $response, $args) {
		$param = $request->getParams();

		$nomArtistes = [];
		$prenomArtistes = [];

		$artistesQuerry = Artiste::all();
		foreach ($artistesQuerry as $value) {
			array_push($nomArtistes, $value->getOriginal()['nomArtiste']);
			array_push($prenomArtistes, $value->getOriginal()['prénomArtiste']);
		}
		$response->getBody()->write(json_encode(['nom' => $nomArtistes, 'prenom' => $prenomArtistes]));
		
		return $response;
	}

	/**
	 * Methode pour récupérer la liste des pistes en couple (id, titre)
	 * @param request
	 * @param response
	 * @param args
	 */
	public function getPistes($request, $response, $args) {
		$param = $request->getParams();

		$pistes = [];

		$pistesQuerry = empty($param['term']) ? Piste::all() : Piste::where('nomPiste','like', '%'.$param['term'].'%')->get();
		//$pistesQuerry = Piste::all();
		foreach ($pistesQuerry as $value) array_push($pistes, ['id' => $value->getOriginal()['idPiste'], 'text' => $value->getOriginal()['nomPiste']]);

		$response->getBody()->write(json_encode(['results' => $pistes]));
		
		return $response;
	}

	/**
	 * Methode pour récupérer la liste des Genres
	 * @param request
	 * @param response
	 * @param args
	 */
	public function getGenres($request, $response, $args) {
		$param = $request->getParams();

		$genres = [];

		//$genresQuerry = Genre::where('nomGenre','like', '%'.$param['name'].'%')->get();
		$genresQuerry = empty($param['term']) ? Genre::all() : Genre::where('nomGenre','like', '%'.$param['term'].'%')->get();
		foreach ($genresQuerry as $value) array_push($genres, ['id' => $value->getOriginal()['idGenre'], 'text' => $value->getOriginal()['nomGenre']]);

		$response->getBody()->write(json_encode(['results' => $genres]));
		
		return $response;
	}

	/**
	 * Methode pour récupérer la liste des titres d'albums
	 * @param request
	 * @param response
	 * @param args
	 */
	public function getAlbums($request, $response, $args) {
		$albums = [];
		foreach (Album::all() as $value) array_push($albums, $value->getOriginal()['nomAlbum']);
		$response->getBody()->write(json_encode($albums));
		
		return $response;
	}

	/**
	 * Methode pour modifier une musique
	 * @param request
	 * @param response
	 * @param args
	 */
	public function editMusic($request, $response, $args) {
		$param = $request->getParams();
		$url = $request->getUri()->getBasePath();

		if($request->getMethod() == "POST") {
			$piste = $request->getParams();

			// Required
			$nomPiste = filter_input(INPUT_POST, 'nomPiste', FILTER_SANITIZE_STRING,  FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomPisteOriginal = filter_input(INPUT_POST, 'nomPisteOriginal', FILTER_SANITIZE_STRING,  FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomGenre = filter_input(INPUT_POST, 'genrePiste', FILTER_SANITIZE_STRING);
			$anneePiste = filter_input(INPUT_POST, 'anneePiste', FILTER_SANITIZE_NUMBER_INT);
			$nomAlbum = filter_input(INPUT_POST, 'nomAlbum',FILTER_SANITIZE_STRING,  FILTER_FLAG_NO_ENCODE_QUOTES);
			$nomAlbumOriginal = filter_input(INPUT_POST, 'nomAlbumOriginal',FILTER_SANITIZE_STRING,  FILTER_FLAG_NO_ENCODE_QUOTES);

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

					// Ajout des relations sur les genres
					foreach (Genre::whereIn('idGenre', $idsGenres)->get()->toArray() as $nom) {
						$nom = trim($nom['nomGenre']);
						Genre::firstOrCreate(['nomGenre' => $nom]);
						$genre = Genre::select('idGenre')->where('nomGenre','like', $nom)->first();
						Est_du_genre_piste::firstOrCreate(['idPiste'=> $piste->idPiste, 'idGenre'=> $genre->idGenre]);
					}
					
				}
				catch(\Exception $e){
					print($e);
					die;
					$error = "La piste n'a pas été modifiée, vérifiez vos informations.";
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
			'annee' => $anneePiste,
			'album' => $imageAlbum,
			'imageAlbum' => $imageAlbum,
			'anneeAlbum' => $anneeAlbum
		];
		
		
		return $this->view->render($response, 'InfoPiste.html.twig', [
			'url' => $url,
			'data' => $data
		]);
	}
  
  /**
     * Method that displays the content of the catalogue
     * @param request
     * @param response
     * @param args
     */
    public function displayCatalogue($request, $response, $args)
    {
        $search = "";
        $page = 0;
        $size = 10;
        $nomCatag = "Global";
        $predef=1;

        //page de pagination
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        //taille de pagination
        if (isset($_GET["size"])) {
            $size = $_GET["size"];
        }
        //mot de recherche
        if (isset($_GET["piste"])) {
            $search = $_GET["piste"];
        }
        //requete venant du client
        if (isset($_GET["token"])) {
            $catag = Bibliotheque::where("idBibliotheque", "=", Jukebox::getBibliActByQrcode($_GET["token"]))->first();
            if (isset($catag)) {
                $nomCatag = $catag->titre;
                $predef=$catag->predef;
            }
            //La liste des musiques que l'on peut ajouter a la file depuis le mobile client
            $pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste')->where("idBibliotheque", "=", Jukebox::getBibliActByQrcode($_GET["token"]));
        } else
        //requete venant du barman
        if (isset($_GET["bartender"])) {
            $catag = Bibliotheque::where("idBibliotheque", "=", Jukebox::getBibliActByBartender($_GET["bartender"]))->first(); 
            if (isset($catag)) {
                $nomCatag = $catag->titre;
                $predef=$catag->predef;
            }
            if (isset($_GET["addCatag"])) {
                if($predef)
                {
                    $array = ['pistes' => [], "nomCatag" => $nomCatag,'predef'=>$predef,"pagination" => []];
                    $json = ['catalogue' => $array];
                    echo json_encode($json);
                    exit;
                }
                else
                //La liste des musiques que l'on peut ajouter au catalogue depuis le mobile barman
                $pistes = Piste::wherenotin('a_joué_piste.idPiste', function ($query) {$query->select('idPiste')->from('contenu_bibliotheque')->where('idBibliotheque', '=', Jukebox::getBibliActByBartender($_GET["bartender"]));})
                    ->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                    ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste');

            } else {
                //La liste des musiques que l'on a dans le catalogue depuis le mobile barman
                $pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                    ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste')->where("idBibliotheque", "=", Jukebox::getBibliActByBartender($_GET["bartender"]));
            }
        }
     

        if(!isset($pistes))
            $pistes = Piste::where('nomPiste', 'like', "%$search%");
        else
           //si recherche par artiste ET titre
           if (strpos($search, "-")) {
            $search = explode("-", $search);
            $pistes = $pistes->where(function ($query) use ($search) {
                $query->where('nomPiste', 'like', "%$search[1]%")
                    ->where('nomArtiste', 'like', "%$search[0]%");
            })->groupBy("piste.idPiste");
        } else {
            $pistes = $pistes->where(function ($query) use ($search) {
                $query->where('nomPiste', 'like', "%$search%")
                    ->orWhere('nomArtiste', 'like', "%$search%");
            })->groupBy("piste.idPiste");
        }

        //création json
        $pagination = $this->pagination($pistes,$page,$size);
        $tabPistes = $this->createJsonCatag($pistes->skip($page * $size)->take($size)->get());
        $array = ['pistes' => $tabPistes, "nomCatag" => $nomCatag,'predef'=>$predef,"pagination" => $pagination];
        $json = ['catalogue' => $array];

        echo json_encode($json);
    }

    public function pagination($items,$page,$size){
        $totalCount = $items->get()->count();
        $items = $items->skip($page * $size)->take($size)->get();
        $count=$items->count();
       
        //creation pagination
        $lastpage = floor($totalCount / $size);
        if (!fmod($totalCount, $size) && $totalCount!=0) {
            $lastpage = $lastpage - 1;
        }

        $prev = $page - 1;
        if ($prev < 0) {
            $prev = 0;
        }

        $next = $page + 1;
        if ($next > $lastpage) {
            $next = $lastpage;
        }
        return ["first" => 0, "prev" => $prev, "act" => $page, "next" => $next, "last" => $lastpage , "size" => (int)$size, "count" => $count,"total"=>$totalCount];
    }

    public function createJsonCatag($pistes)
    {
        $tabPistes = [];
        $compteur = 0;
        $compteurGenre = 0;
        $compteurArtiste = 0;
        $compteurAlbum = 0;
        foreach ($pistes as $row) {
            $tabPistes[$compteur]['idPiste'] = $row['idPiste'];
            $tabPistes[$compteur]['nomPiste'] = $row['nomPiste'];
            $tabPistes[$compteur]['annéePiste'] = $row['annéePiste'];
            $tabPistes[$compteur]['imagePiste'] = $row['imagePiste'];
            $estDuGenrePiste = Est_du_genre_piste::join('genre', 'est_du_genre_piste.idGenre', '=', 'genre.idGenre')->where('idPiste', '=', $tabPistes[$compteur]['idPiste'])->get();
            foreach ($estDuGenrePiste as $genrePiste) {
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
          
        }  return $tabPistes;
    }

    public function displayCatalogueChoice($request, $response, $args)
    {
        $search = "";
        $page = 0;
        $size = 10;

        //page de pagination
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        //taille de pagination
        if (isset($_GET["size"])) {
            $size = $_GET["size"];
        }
        //mot de recherche
        if (isset($_GET["piste"])) {
            $search = $_GET["piste"];
        }
        $catag=Bibliotheque::where("idBibliotheque", "=", Jukebox::getBibliByBartender($_GET["bartender"]))->orWhere("predef","=",1)->where('titre', 'like', "%$search%"); 
    
        //création json
        $pagination = $this->pagination($catag,$page,$size);
        $array = ['catags' => $catag->skip($page * $size)->take($size)->get()->toArray(),"pagination" => $pagination];
        $json = ['catalogue' => $array];

        echo json_encode($json);
	}
	

    public function integrerCatag($request, $response, $args)
    {
        

        //On récupère la bibliothèque du JukeBox
        $getBibliothequeBarman = Jukebox::getBibliByBartender($_POST["bartender"]);
        $pistes = Contenu_bibliotheque::where("idBibliotheque","=",$_POST["id"])->wherenotin('idPiste', function ($query) {$query->select('idPiste')->from('contenu_bibliotheque')->where('idBibliotheque', '=', Jukebox::getBibliByBartender($_POST["bartender"]));})->get()->toArray();
        foreach($pistes as $piste){
        $addContenu = new Contenu_bibliotheque();
        $addContenu->idPiste = $piste["idPiste"];
        $addContenu->idBibliotheque = $getBibliothequeBarman;
        $addContenu->save();
        }
    }
  
}