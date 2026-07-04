<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

#pour maj des layers
$reponse = array(
	'a_flasher' => get_geojson_a_flasher(), 
	'deja_flashe' => get_geojson_deja_flashe(),
	'detruits' => get_geojson_detruits()
);

echo json_encode($reponse);
?>