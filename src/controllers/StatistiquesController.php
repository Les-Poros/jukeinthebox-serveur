<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\StatGenres;
use jukeinthebox\models\StatPistes;
use jukeinthebox\models\Genre;
use jukeinthebox\models\Piste;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Artiste;
use \Slim\Views\Twig as twig;

/**
 * Class Statistiques
 */
class StatistiquesController
{

    protected $view;

    /**
     * Constructor of the class StatistiquesController
     * @param view
     */
    public function __construct(twig $view)
    {
        $this->view = $view;
    }

    /**
     * Method that push stats of musics
     * @param request
     * @param response
     * @param args
     */
    public function pushStatsMusic($request, $response, $args)
    {
        $statPistes = StatPistes::where("idJukebox", "=", Jukebox::getIdByBartender($_POST["bartender"]))->where("idPiste", "=", $_POST['idPiste'])->first();
        if (isset($statPistes)) {
            $statPistes->nbFoisJoue += 1;
        } else {
            $statPistes = new StatPistes();
            $statPistes->idJukebox = Jukebox::getIdByBartender($_POST["bartender"]);
            $statPistes->idPiste = $_POST['idPiste'];
            $statPistes->nbFoisJoue = 1;
        }
        $statPistes->save();
        foreach ( Piste::where("idPiste","=",$_POST['idPiste'])->first()->est_du_genre_piste()->get()->toArray() as $estDuGenre ) {
            $statGenre = StatGenres::where("idJukebox", "=", Jukebox::getIdByBartender($_POST["bartender"]))->where("idGenre", "=", $estDuGenre["idGenre"])->first();
            if (isset($statGenre)) {
                $statGenre->nbFoisJoue += 1;
            } else {
                $statGenre = new StatGenres();
                $statGenre->idJukebox = Jukebox::getIdByBartender($_POST["bartender"]);
                $statGenre->idGenre = $estDuGenre["idGenre"];
                $statGenre->nbFoisJoue = 1;
            }
            $statGenre->save();
        }
    }

    /**
     * Method that get stats
     * @param request
     * @param response
     * @param args
     */
    public function getStats($request, $response, $args)
    {
        $tabStat = [];
        $compteurForPiste = 0;
        $compteurForGenre = 0;
        $statPistes = StatPistes::where("idJukebox", "=", Jukebox::getIdByBartender($_GET["bartender"]))->orderBy('nbFoisJoue','desc')->take(3)->get();
        foreach ($statPistes as $statP){
            if (isset($statP)){
                $piste = Piste::where('idPiste','=',$statP["idPiste"])->first();
                $tabStat['pistes'][$compteurForPiste]['nomPiste'] = $piste['nomPiste'];
                $tabStat['pistes'][$compteurForPiste]['imagePiste'] = $piste['imagePiste'];
                $tabStat['pistes'][$compteurForPiste]['nbFoisJoue'] = $statP['nbFoisJoue'];
                $compteurForArtiste = 0;
                foreach($piste->a_joue_piste()->get() as $artiste){
                    $nomArt = Artiste::where('idArtiste','=', $artiste['idArtiste'])->first();
                    $tabStat['pistes'][$compteurForPiste]['artistes'][$compteurForArtiste] = $nomArt;
                    $compteurForArtiste++;
                }
                $compteurForPiste++;
            }
        }
        $statGenres = StatGenres::where("idJukebox", "=", Jukebox::getIdByBartender($_GET["bartender"]))->orderBy('nbFoisJoue','desc')->take(5)->get();
        foreach ($statGenres as $statG){
            if (isset($statG)){
                $genre = Genre::where('idGenre','=',$statG["idGenre"])->first();
                $tabStat['genres'][$compteurForGenre]['nomGenre'] = $genre['nomGenre'];
                $tabStat['genres'][$compteurForGenre]['nbFoisJoue'] = $statG["nbFoisJoue"];
                $compteurForGenre++;
            }
        }
        echo json_encode($tabStat);
    }

}
