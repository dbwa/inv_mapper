<?php

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();

// Recuperation des variables POST
$inv_name = $_GET['inv_name'];

#update du status de l'invader
echo delete_position($inv_name);

?>