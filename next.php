<?php
$conf=parse_ini_file("conf/conf.ini");
$dbh = new PDO('mysql:host=localhost;dbname=rimet2u', $conf["id"], $conf["mdp"]);
$dbh->query('DELETE FROM file LIMIT 1;');