<?php

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

#update du status de l'invader
echo delete_position($inv_name);

?>