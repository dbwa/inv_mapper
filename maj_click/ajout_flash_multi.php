<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Recuperation des variables POST
$inv_names = $_GET['inv_names'];

$insertedCount = ajout_flash_multi($inv_names);

#pour maj des layers
$reponse = array(
    /*'a_flasher' => get_geojson_a_flasher(), 
    'deja_flashe' => get_geojson_deja_flashe(),
    'detruits' => get_geojson_detruits(),*/
    'insertedCount' => $insertedCount // Ajouter le nombre d'invaders insérés à la réponse
);

echo json_encode($reponse);

?>