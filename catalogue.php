<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
$conf = parse_ini_file("conf/conf.ini");
$dbh = new PDO('mysql:host=localhost;dbname=rimet2u', $conf["id"], $conf["mdp"],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$tabPistes = [];
$compteur = 0;
$compteurGenre = 0;
$compteurArtiste = 0;
$compteurAlbum = 0;
foreach ($dbh->query('SELECT * from piste ORDER BY idPiste') as $row) {
    $tabPistes[$compteur]['idPiste'] = $row['idPiste'];
    $tabPistes[$compteur]['nomPiste'] = $row['nomPiste'];
    $tabPistes[$compteur]['annéePiste'] = $row['annéePiste'];
    $tabPistes[$compteur]['imagePiste'] = $row['imagePiste'];
    foreach ($dbh->query('SELECT * from est_du_genre_piste NATURAL JOIN genre where idPiste=' . $tabPistes[$compteur]['idPiste']) as $genrePiste) {
        $tabPistes[$compteur]['genres'][$compteurGenre] = $genrePiste['nomGenre'];
        $compteurGenre++;
    }
    $compteurGenre = 0;
    foreach ($dbh->query('SELECT * from a_joué_piste NATURAL JOIN artiste where idPiste=' . $row['idPiste']) as $artistePiste) {
        $tabPistes[$compteur]['artistes'][$compteurArtiste]["prénom"] = $artistePiste['prénomArtiste'];
        $tabPistes[$compteur]['artistes'][$compteurArtiste]["nom"] = $artistePiste['nomArtiste'];
        $compteurArtiste++;
    }
    $compteurArtiste = 0;
    foreach ($dbh->query('SELECT * from album NATURAL JOIN fait_partie where idPiste=' . $row['idPiste']) as $album) {
        $tabPistes[$compteur]['albums'][$compteurAlbum]["idAlbum"] = $album["idAlbum"];
        $tabPistes[$compteur]['albums'][$compteurAlbum]["nomAlbum"] = $album["nomAlbum"];
        $tabPistes[$compteur]['albums'][$compteurAlbum]["annéeAlbum"] = $album["annéeAlbum"];
        $tabPistes[$compteur]['albums'][$compteurAlbum]["imageAlbum"] = $album["imageAlbum"];
        foreach ($dbh->query('SELECT * from est_du_genre_album NATURAL JOIN genre where idAlbum=' . $album['idAlbum']) as $genreAlbum) {
            $tabPistes[$compteur]['albums'][$compteurAlbum]['genres'][$compteurGenre] = $genreAlbum['nomGenre'];
            $compteurGenre++;
        }
        $compteurGenre = 0;
        foreach ($dbh->query('SELECT * from a_joué_album NATURAL JOIN artiste where idAlbum=' . $album['idAlbum']) as $artisteAlbum) {
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

$array = ['pistes' => $tabPistes];
$json = ['catalogue' => $array];

header('Content-type: application/json');
echo json_encode($json);