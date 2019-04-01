<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Jukebox;
use jukeinthebox\models\StatGenres;
use jukeinthebox\models\StatPistes;
use jukeinthebox\models\Genre;
use jukeinthebox\models\Piste;
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
     * Method that
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

}
