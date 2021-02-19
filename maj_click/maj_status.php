<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Recuperation des variables POST
$inv_name = $_POST['inv_name'];
$statusout = $_POST['statusout'];

#update du status de l'invader
update_status($inv_name, $statusout);

#pour maj des layers
$reponse = array(
	'a_flasher' => get_geojson_a_flasher(), 
	'deja_flashe' => get_geojson_deja_flashe(),
	'detruits' => get_geojson_detruits()
);

// pour le test : http://192.168.0.10:44/maj_click/maj_status.php?inv_name=PA_1238&statusout=OK
echo json_encode($reponse);
?>