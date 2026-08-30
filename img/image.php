<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Recuperation des variables POST
$inv_name = $_POST['inv_name'];
$num = (int)$_POST['num'];

#update du status de l'invader
if (is_null($num)){
	$num=1;
} elseif ((int)$num > 3) {
	$num=1;
} else {
	$num = (int)$num;
}

#pour maj des layers
$reponse = array(
	'a_flasher' => get_geojson_a_flasher(), 
	'deja_flashe' => get_geojson_deja_flashe(),
	'detruits' => get_geojson_detruits()
);

// pour le test : http://192.168.0.10:44/maj_click/maj_flash.php?inv_name=PA_1238$flash=vrai
echo json_encode($reponse);
?>