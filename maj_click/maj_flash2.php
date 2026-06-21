<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Recuperation des variables POST
$inv_name = $_GET['inv_name'];
$flash = $_GET['flash'];

#update du status de l'invader
if ($flash == 'vrai'){
	ajout_flash($inv_name);
} elseif ($flash == 'faux') {
	suppri_flash($inv_name);
}

#pour maj des layers
/*$reponse = array(
	'a_flasher' => get_geojson_a_flasher(), 
	'deja_flashe' => get_geojson_deja_flashe(),
	'detruits' => get_geojson_detruits()
);
*/


//$categories =eval(get_categories($categories_default));
$categories =$categories_default;

$reponse = array();
foreach ($categories as $key => &$val) 
{
    $reponse["Layer_" . $key] = get_geojson_invaders_from_config($categories, $key);
}

// pour le test : http://192.168.0.10:44/maj_click/maj_flash.php?inv_name=PA_1238$flash=vrai
echo json_encode($reponse);
?>