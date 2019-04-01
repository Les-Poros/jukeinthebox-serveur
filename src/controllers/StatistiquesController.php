<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\StatPistes;
use jukeinthebox\models\StatGenres;
use jukeinthebox\models\Genre;
use \Slim\Views\Twig as twig;

/**
 * Class Statistiques
 */
class StatistiquesController {

	protected $view;

	/**
	 * Constructor of the class StatistiquesController
	 * @param view
	 */
    public function __construct(twig $view) {
        $this->view = $view;
	}

	/**
	 * Method that
	 * @param request
	 * @param response
	 * @param args
	 */
	public function countMoreStatPistes($request, $response, $args)
	{
        $statPistes = StatPistes::where("idJukebox", "=", Jukebox::getIdByBartender($_GET["bartender"]))->where("idPiste","=",$_GET['idPiste'])->first();
        if(isset($statPistes)){
            $statPistes->nbFoisJoue += 1;
        }
        else{
            $statPistes = new StatPistes();
            $statPistes->idJukebox = Jukebox::getIdByBartender($_GET["bartender"]);
            $statPistes->idPiste = $_GET['idPiste'];
            $statPistes->nbFoisJoue = 1;
        }
		$statPistes->save();
    }

    /**
	 * Method that
	 * @param request
	 * @param response
	 * @param args
	 */
	public function countMoreStatGenres($request, $response, $args)
	{
        /*$tabStatGenres = StatGenres::where("idJukebox", "=", Jukebox::getIdByBartender($_GET["bartender"]))->get();
        if(sizeof($tabStatGenres) > 1){
            foreach($tabStatGenres as $statGenres){
                if(sizeof($_GET["genres"]) > 1){
                    foreach($_GET["genres"] as $genre){
                        $statGenres = $statGenres::where("idGenre", "=", Genre::getIdByGenre($genre))->first();
                        if(isset($statGenres)){
                            $statGenres->nbFoisJoue += 1;
                            $statGenres->save();
                        }
                        else{
                            $statGenres = new StatGenres();
                            $statGenres->idJukebox = Jukebox::getIdByBartender($_GET["bartender"]);
                            $statGenres->idGenre = Genre::getIdByGenre($genre);
                            $statGenres->nbFoisJoue = 1;
                            $statGenres->save();
                        }
                    }
                }
                else{
                    $statGenres = $statGenres::where("idGenre", "=", Genre::getIdByGenre($genre))->first();
                        if(isset($statGenres)){
                            $statGenres->nbFoisJoue += 1;
                            $statGenres->save();
                        }
                        else{
                            $statGenres = new StatGenres();
                            $statGenres->idJukebox = Jukebox::getIdByBartender($_GET["bartender"]);
                            $statGenres->idGenre = Genre::getIdByGenre($_GET["genres"][0]);
                            $statGenres->nbFoisJoue = 1;
                            $statGenres->save();
                        }
                }
            }
        }
        else{
            if(sizeof($_GET["genres"]) > 1){
                foreach($_GET["genres"] as $genre){
                    $statGenres = new StatGenres();
                    $statGenres->idJukebox = Jukebox::getIdByBartender($_GET["bartender"]);
                    $statGenres->idGenre = Genre::getIdByGenre($genre);
                    $statGenres->nbFoisJoue = 1;
                    $statGenres->save();
                }
            }
            else{
                return $_GET["genres"];
                $statGenres = new StatGenres();
                $statGenres->idJukebox = Jukebox::getIdByBartender($_GET["bartender"]);
                $statGenres->idGenre = Genre::getIdByGenre($_GET["genres"][0]);
                $statGenres->nbFoisJoue = 1;
                $statGenres->save();
            }
        }*/
        //return Genre::select('nomGenre')->where('nomGenre','=',$_GET["genres"][0])->first();
        return $_GET["genres"];
    }
    
}