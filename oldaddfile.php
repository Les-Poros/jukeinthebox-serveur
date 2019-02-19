<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');

$conf = parse_ini_file("conf/conf.ini");
$dbh = new PDO('mysql:host=localhost;dbname=rimet2u', $conf["id"], $conf["mdp"],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$dbh->query('INSERT INTO file VALUES (NULL,\''.$_POST['id'].'\')');