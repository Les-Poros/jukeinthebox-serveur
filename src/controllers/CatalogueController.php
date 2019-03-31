<?php

namespace jukeinthebox\controllers;

use jukeinthebox\models\Album;
use jukeinthebox\models\A_joue_album;
use jukeinthebox\models\A_joue_piste;
use jukeinthebox\models\Contenu_bibliotheque;
use jukeinthebox\models\Est_du_genre_album;
use jukeinthebox\models\Est_du_genre_piste;
use jukeinthebox\models\Jukebox;
use jukeinthebox\models\Bibliotheque;
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
