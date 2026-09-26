<?php

// Paramètres de connexion à la base de données
//$host = Nom d'hôte ou adresse IP du serveur de la base de données. Mettre "localhost" si la base est sur le même serveur que le serveur web
$host = "localhost";
//$port = Numéro du port de connexion à la base de données. Devrait être 5432 sauf exception
$port = 3306;
//$dbname = Nom de la base de données
$dbname = "nom_de_la_base";
//$user = Nom de l'utilisateur servant à la connexion à la base de données
$user = "utilisateur";
//$password = Mot de passe de l'utilisateur servant à la connexion à la base de données
$password = "mot_de_passe";

// Les identifiants réels se renseignent dans config.local.php (non versionné).
// Copier config.local.php.example en config.local.php et le remplir.
if (file_exists(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

$center_lat = 48.67895;
$center_lon = 2.5019;


$categories_default = array(
		
		"a_flasher" => array(	"properties" => array("name"=> "A flasher", "marker" => "L.marker(latlng, {icon : blueIcon })", "default_on" => True, "boutons" => array("je_lai", "changer_etat")), 
								"include" => array("etats" => array("Un peu dégradé","Inconnu","Dégradé","OK"), "flash"=> "non flashé")
						),

		"deja_flash" => array(	"properties" => array("name"=> "Déjà flashé", "marker" => "L.marker(latlng, {icon : greenIcon })", 
													"default_on" => False, 
													"boutons" => array("je_lai_pas", "changer_etat")), 
								"include" => array("etats" => "all", "flash"=> "flashé")
							),

		"detruits" => array(	"properties" => array("name"=> "Détruits et inaccessibles", "marker" => "L.marker(latlng, {icon : redIcon })", 
													"default_on" => False, 
													"boutons" => array("je_lai", "changer_etat")), 
								"include" => array("etats" => array("Détruit !","Non visible","Très dégradé","no info", "Inaccessible"), "flash"=> "non flashé")
							),
	);
/*  //exemple de marker differents :
		"exemple_avec_tout_marker_default" => array(	"properties" => array("name"=> "tout_mark_default", "marker" => "default", 
														"default_on" => False, 
														"boutons" => array("je_lai", "changer_etat")), 
								"include" => array("etats" => "all", "flash"=> "all")
							), 

		"exemple_avec_tout_marker_icon" => array(	"properties" => array("name"=> "tout_mark_icon", "marker" => "L.marker(latlng, {icon : blueIcon })", 
														"default_on" => False, 
														"boutons" => array("je_lai", "changer_etat")), 
								"include" => array("etats" => "all", "flash"=> "all")
							),

		"exemple_avec_tout_marker_rond" => array(	"properties" => array("name"=> "tout_mark_rond", "marker" => "L.circleMarker(latlng, {
																    radius: 4,
																    fillColor: #ff0000,
																    color: #000,
																    weight: 1,
																    opacity: 1,
																    fillOpacity: 0.8
																})", 
														"default_on" => False, 
														"boutons" => array("je_lai", "je_lai_pas", "changer_etat")), 
								"include" => array("etats" => "all", "flash"=> "all")
							),*/



//etats dans lesquels l'utilisateur peut modifier un invaders
//exemple, il est marqué OK sur la carte, mais est dans un liue privé, l'utilisateur peut le mettre dans  "Inaccessible" afin de ne pas revenir inutilement 
$transfert_categories_possible = ['OK','Un peu dégradé','Dégradé', "Très dégradé", "Détruit !","Non visible", "Inaccessible"];

// acces a la base des etats et des positions 
// si all, c'est ouvert a tout le monde, inscrit ou pas, sinon c'est que les utilisateurs enregistrés avec leurs données
$access_base = "user_only";  //"user_only" ou "all"

//code magique pour que tout le monde puisse s'inscrire
$code_inscription = '';