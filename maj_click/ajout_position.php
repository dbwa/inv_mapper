<?php
// utilisez cela pour debeugger:
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Action modifiant des donnees : jeton CSRF obligatoire.
csrf_check();

// Action reservee aux utilisateurs connectes.
require_login();

// Recuperation des variables POST
$inv_name = $_POST['inv_name'];
$lat = $_POST['lat'];
$lon = $_POST['lon'];

#ajout de l'invader
ajout_position($inv_name, $lat, $lon);

//$categories =eval(get_categories($categories_default));
$categories =$categories_default;

$reponse = array();
foreach ($categories as $key => &$val) 
{
    $reponse["Layer_" . $key] = get_geojson_invaders_from_config($categories, $key);
}

echo json_encode($reponse);
?>