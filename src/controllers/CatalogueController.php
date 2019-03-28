<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Album;
use jukeinthebox\models\A_joue_album;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Contenu_bibliotheque;
use jukeinthebox\models\Est_du_genre_album;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Piste;

/**
 * Class CatalogueController
 */
class CatalogueController
{

    /**
     * Method that displays the content of the catalogue
     * @param request
     * @param response
     * @param args
     */
    public function displayCatalogue($request, $response, $args)
    {
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
        $page = 0;
        $size = 10;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        if (isset($_GET["size"])) {
            $size = $_GET["size"];
        }
        $nomCatag = "Global";
        if (isset($_GET["piste"])) {
            $search = $_GET["piste"];
        }
        if (isset($_GET["token"])) {
            $catag = Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox", "=", Jukebox::getIdByQrcode($_GET["token"]))->first();
            if (isset($catag)) {
                $nomCatag = $catag->titre;
            }
            //La liste des musiques que l'on peut ajouter a la file depuis le mobile client
            $pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste')->where("idBibliotheque", "=", Jukebox::getIdByQrCode($_GET["token"]));
        } else
        if (isset($_GET["bartender"])) {
            $catag = Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox", "=", Jukebox::getIdByBartender($_GET["bartender"]))->first();
            if (isset($catag)) {
                $nomCatag = $catag->titre;
            }
            if (isset($_GET["addCatag"])) {
                //La liste des musiques que l'on peut ajouter au catalogue depuis le mobile barman
                $pistes = Piste::wherenotin('a_joué_piste.idPiste', function ($query) {$query->select('idPiste')->from('contenu_bibliotheque')->where('idBibliotheque', '=', Jukebox::getIdByBartender($_GET["bartender"]));})
                    ->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                    ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste');

            } else {
                //La liste des musiques que l'on a dans le catalogue depuis le mobile barman
                $pistes = Contenu_bibliotheque::join('piste', 'contenu_bibliotheque.idPiste', '=', 'piste.idPiste')->join('a_joué_piste', 'piste.idPiste', '=', 'a_joué_piste.idPiste')
                    ->join('artiste', 'artiste.idArtiste', '=', 'a_joué_piste.idArtiste')->where("idBibliotheque", "=", Jukebox::getIdByBartender($_GET["bartender"]));
            }

        } else {
            $pistes = Piste::all()->get();
        }
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

        $count = $pistes->get()->count();
        $pistes = $pistes->skip($page * $size)->take($size)->get();

        //creation du json
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
        }
        $lastpage = floor($count / $size);
        if (!fmod($count, $size)) {
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

        $pagination = ["first" => 0,"prev"=>$prev, "act" => $page, "next"=>$next , "last" => $lastpage];

        $array = ['pistes' => $tabPistes, "nomCatag" => $nomCatag, "size" => $size, "count" => $count, "pagination" => $pagination];
        $json = ['catalogue' => $array];

        echo json_encode($json);
    }

    /**
     * Method that displays that add a music to the bibliotheque
     * @param request
     * @param response
     * @param args
     */
    public function addMusicBiblio($request, $response, $args)
    {
        $addContenu = new Contenu_bibliotheque();

        //On récupère la bibliothèque du JukeBox
        $getBibliotheque = Jukebox::join('bibliotheque', 'jukebox.idBibliotheque', '=', 'bibliotheque.idBibliotheque')->where("idJukebox", "=", Jukebox::getIdByBartender($_POST["bartender"]))->first()->idBibliotheque;

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
    public function deleteMusicBiblio($request, $response, $args)
    {

        Contenu_bibliotheque::where('idPiste', '=', $_POST['id'])->first()->delete();

    }
}
