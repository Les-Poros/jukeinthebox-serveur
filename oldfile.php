<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');

$conf = parse_ini_file("conf/conf.ini");
$dbh = new PDO('mysql:host=localhost;dbname=rimet2u', $conf["id"], $conf["mdp"],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$json = '{ "pistes" :[';
foreach ($dbh->query('SELECT * from file ORDER BY idFile') as $row) {
    $piste = $dbh->query('SELECT * from piste where idPiste=' . $row['idPiste'])->fetch();
    $json .= '{
        "idFile":"' . $row['idFile'] . '",
        "piste":{
            "id":"' . $piste['idPiste'] . '",
            "nom":"' . $piste['nomPiste'] . '",
            "année":"' . $piste['annéePiste'] . '",
            "image":"' . $piste['imagePiste'] . '",
            "genres":[';
    foreach ($dbh->query('SELECT * from est_du_genre_piste NATURAL JOIN genre where idPiste=' . $piste['idPiste']) as $genrePiste) {
        $json .= '"' . $genrePiste["nomGenre"] . '",';
    }
    $json = enleveVirgule($json) . '],
            "artistes":[';
    foreach ($dbh->query('SELECT * from a_joué_piste NATURAL JOIN artiste where idPiste=' . $piste['idPiste']) as $artistePiste) {
        $json .= '{
                    "nom":"' . $artistePiste["nomArtiste"] . '",
                    "prénom":"' . $artistePiste["prénomArtiste"] . '"
                    },';
    }
    $json = enleveVirgule($json) . '],
            "albums":[';
    foreach ($dbh->query('SELECT * from album NATURAL JOIN fait_partie where idPiste=' . $piste['idPiste']) as $album) {
        $json .= '{
                    "id":"' . $album['idAlbum'] . '",
                    "nom":"' . $album['nomAlbum'] . '",
                    "année":"' . $album['annéeAlbum'] . '",
                    "image":"' . $album['imageAlbum'] . '",
                    "genres":[';
        foreach ($dbh->query('SELECT * from est_du_genre_album NATURAL JOIN genre where idAlbum=' . $album['idAlbum']) as $genreAlbum) {
            $json .= '"' . $genreAlbum["nomGenre"] . '",';
        }
        $json = enleveVirgule($json) . '],
                    "artistes":[';
        foreach ($dbh->query('SELECT * from a_joué_album NATURAL JOIN artiste where idAlbum=' . $album['idAlbum']) as $artisteAlbum) {
            $json .= '{
                        "nom":"' . $artisteAlbum["nomArtiste"] . '",
                        "prénom":"' . $artisteAlbum["prénomArtiste"] . '"
                        },';
        }
        $json = enleveVirgule($json) . ']},';
    }
    $json = enleveVirgule($json) . ']';
    $json .= '}},';
}

$json = enleveVirgule($json) . ']}';

echo $json;

function enleveVirgule($json)
{
    // On enlève la dernière virgule
    if (substr($json, -1, 1) == ',') {
        $json = substr($json, 0, -1);
    }
    return $json;
}
