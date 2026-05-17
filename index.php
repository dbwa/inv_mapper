<!DOCTYPE html>
<html lang="fr">
<?php
session_start();
include_once(__DIR__ . '/config.php');
include_once(__DIR__ . '/fonctions.inc.php');

// Si la session a expiré (navigateur fermé, session GC'd...), on la restaure
// depuis le cookie "remember_token" pour ne pas avoir à se reconnecter.
restore_session_from_cookie();

// utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?> 

<head>
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="dbwa">

    <title>Carte d'invasion</title>
    <link rel="apple-touch-icon" sizes="180x180" href="./apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicon-16x16.png">
    <link rel="manifest" href="./site.webmanifest">

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
          type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/geojson.js" type="text/javascript"></script>

    <!-- lightbox pour lire les images-->
    <link href="css/lightbox.css" rel="stylesheet" />
    <script src="js/lightbox.js"></script>
    <script>
    lightbox.option({
      'resizeDuration': 200,
      'wrapAround': true,
      'disableScrolling': true,
      'fitImagesInViewport': true
    })
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <style>
        /* Nouveau style */
        body {
            background-color: #181c1f; /* Gris bleu très foncé */
            color: #e0e0e0; /* Gris clair */
        }

        /* Style pour les card panels */
        .card-panel {
            background: linear-gradient(to right, #46a2da99, #ebecf066); /* Exemple de dégradé bleu */
            color: #ffffff; /* Couleur du texte */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre légère */
            border-radius: 4px; /* Coins arrondis */
            font-size: 1em; /* Taille de la police plus grande */
            font-weight: bold; /* Texte en gras */
            margin-bottom: 5px; /* Espace en dessous du texte */
            margin: 0 0 10px; /* Marges pour espacer le titre du chiffre */
            white-space: nowrap; /* Empêche le texte de passer à la ligne suivante */
            overflow: hidden; /* Cache le débordement de texte */
            text-overflow: ellipsis; /* Ajoute des points de suspension si le texte déborde */
        }

        /* Style pour les chiffres dans les card panels */
        .card-panel h5 {
            font-size: 2.5em; /* Taille de la police plus grande pour les chiffres */
            font-weight: 700; /* Police plus épaisse */
            margin: 0; /* Enlève les marges par défaut */
            white-space: nowrap; /* Empêche le texte de passer à la ligne suivante */
            overflow: hidden; /* Cache le débordement de texte */
            text-overflow: ellipsis; /* Ajoute des points de suspension si le texte déborde */
        }

        table.dataTable {
            color: #e0e0e0; /* Gris clair pour le texte du tableau */
        }
        /* Personnaliser le graphique */
        .chart-container {
            color: #e0e0e0;
        }

        /* Personnaliser DataTables */
        .light-container {
            background-color: #2b2b36; /* Couleur de fond plus claire pour le conteneur */
            padding: 15px; /* Espace intérieur pour créer de l'espace autour de la table */
            border-radius: 4px; /* Coins arrondis pour le conteneur */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre douce pour un effet de profondeur */
            margin: 20px 0; /* Marges verticales pour l'espace autour du conteneur */
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            color: #e0e0e0; /* Gris clair pour les éléments de formulaire */
            background-color: #323232; /* Gris moyen pour les champs de saisie et de sélection */
            border: none;s
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #e0e0e0 !important; /* Gris clair pour la pagination */
            background-color: transparent !important;
        }
        .dataTables_wrapper .dataTables_info {
            color: #e0e0e0 !important; /* Gris clair pour la pagination */
            background-color: transparent !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #323232 !important; /* Gris clair pour le bouton de page actuel */
            background-color: #424242 !important; /* Gris foncé pour le bouton de page actuel */
            border: none !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            background-color: #19191e;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > td:first-child:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > th:first-child:before {
            background-color: #e0e0e0; /* Grey background */
            color: #333; /* Darker text color for visibility */
            border: 1px solid #333; /* Darker border color for visibility */
            box-shadow: null; /* Adjusted box shadow for consistency */
        }
        .dataTables_wrapper .dt-button {
            color: #424242; /* Gris clair pour le bouton de page actuel */
            background-color: #323232; /* Gris foncé pour le bouton de page actuel */
        }
        table.striped>tbody>tr:nth-child(odd) { background-color: #24242e; } /* Nuance pour les lignes impaires */
        table.striped>tbody>tr:nth-child(even) { background-color: #19191e; } /* Nuance pour les lignes paires */

        .dataTables_wrapper .btn-export.buttons-csv {
            background-color : #19191e;
        }

        .ligne{
            max-width: 105px;
            display: inline-block;
        }

        .btn-small {
            inherit: body;
        }

        .modal {
            background: linear-gradient(to right, #3a6a8aee, #65666aee); /* Exemple de dégradé bleu */
            color: #ffffff; /* Couleur du texte */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre légère */
            border-radius: 4px; /* Coins arrondis */
            font-size: 1em; /* Taille de la police plus grande */
        }
        .modal textarea {
            color: #fff;
        }

        #uploadModal {
            color: #000000;
        }

        .tabs {
            background: linear-gradient(to right, #3a6a8aee, #65666aee);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre légère */
            border-radius: 4px; /* Coins arrondis */
        }


        .tabs .tab a {
            font-size: 0.8em; /* Taille de la police plus grande */
            color: #ffffff; /* Couleur du texte */   
        }
        .tabs .tab a:hover, .tabs .tab a.active {
             color: #fff;
             background-color: #26a69a80; 
        }


        .leaflet-container .leaflet-control-search {
            float: right !important;
        }

        /* Style pour la barre de navigation (ajustez la hauteur selon vos besoins) */
        nav {
            height: 10vh; /* Hauteur de la barre de navigation */
        }

        [name='to_flash']{
            background-color: #0b1574 !important;
            font-size: 1em !important;
        }

        [name='to_detruit']{
            background-color: #e2620e !important;
            font-size: 1em !important;
        }
        
        [name='to_flash']{
            background-color: #0b1574 !important;
            font-size: 1em !important;
        }
                

    </style>


    <style type="text/css">

        /* images lightbox */
        .lb-outerContainer {
            max-width: 720px; 
            max-height: 720px;
        }
        .lb-dataContainer {
            max-width: 220px; /* For the text below image */
        }

        
        /*disposition*/
        .vertical-center{margin: 0; position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); transform: translate(-50%, -50%)}
        p{font-size: medium}

        /*grande image*/
        .cover_1 .img_bg{background-repeat:no-repeat;background-size:cover!important;background-position:center center}
        .cover_1 .img_bg,.cover_1 {min-height:600px;height:100vh} 
        .cover_1 .heading{color:#fff;font-weight:300;font-size:30px;line-height:1.5} 



        /*boutons*/
        .btn.btn-primary.btn-outline-primary{border-width:2px;cursor:pointer}
        .btn.btn-outline-white{border:2px solid #fff;background:none;color:#fff;text-decoration:none}
        .btn.btn-outline-white:hover{background:#FFA518;color:#000;border:2px solid transparent}

        .btn-outline-orange{border:2px solid #FFA518;background:none;color:#fff;text-decoration:none}
        .btn-outline-orange:hover{background:#FFA518;color:#000;border:2px solid transparent}




    </style>

<!-- <link rel="stylesheet" href="./index_v3.css"> -->

</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">


<?php
    include_once("./fonctions.inc.php");
?>

<?php

//recup des valeurs pour la suite :

connect();
if (isset($_GET['lat'])) $lat = $_GET['lat'];
if (isset($_GET['lng'])) $lng = $_GET['lng'];

?>



<!-- cartos Section -->
<!-- <section id="cartos" class="container cartos-section"> -->

        <?php
        if (empty($_SESSION['login_user'])){ //pour tout le monde 
            ?>

<div class="site-wrap">

<div class="main-wrap " id="section-home">
<div class="cover_1 overlay bg-light">
<div class="img_bg" style="background-image: url(https://images.pexels.com/photos/2603464/pexels-photo-2603464.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260); background-position: 50% 0px;" data-stellar-background-ratio="0.5">
<div id="vertical-center">
<div class="row align-items-center justify-content-center text-center vertical-center">
<h2 class="heading">Bienvenue sur Invader Mapper</h2>
<p><a href="adherent.php" class="smoothscroll btn btn-outline-white px-5 py-3">Se connecter</a></p>
<p><a href="register.php" class="smoothscroll btn btn-outline-white px-5 py-3" style="opacity:0.7;"><small>Créer un compte</small></a></p>
</div>
</div>
</div>
</div> 



<?php include_once('footer.php'); ?>
</div>

</div>

              <?php
        }
        else { //si adhérent sa carte :
             ?>
            <section id="main" style="width: 100%; height: 90vh;">
                <?php
                    include_once("./navbar.php");
                ?>
                <div id="emimapajax" style="width: 100%; height: 100%;"></div>
                <?php $pdo = connect(); include_once("map/map_emission_ajax.php"); ?>
            </section>
 

        <!-- modal pour que l'utilisateur puisse proposer et partager ses propres images  -->
        <div id="uploadModal" class="modal">
            <div class="modal-content">
                <h4>Partager une photo de <span class="invader-name"></span></h4>
                
                <div class="row">
                    <form id="uploadForm" class="col s12">
                        <!-- Info utilisateur -->
                        <div class="input-field">
                            <input id="photographer" type="text" value="<?php echo $_SESSION['login_user']; ?>">
                            <label for="photographer" class="active">Photographe</label>
                        </div>

                        <!-- Upload photo -->
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Choisir photo</span>
                                <input type="file" accept="image/*" id="photoInput" capture="environment">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Aucune photo sélectionnée">
                            </div>
                        </div>

                        <!-- Prévisualisation -->
                        <div id="imagePreview" style="display: none; margin: 10px 0;">
                            <img id="preview" style="max-width: 100%; max-height: 300px;">
                            <a class="btn-flat waves-effect waves-red" onclick="removeImage()">
                                ❌ Supprimer
                            </a>
                        </div>

                        <!-- Conditions -->
                        <p>
                            <label>
                                <input type="checkbox" id="termsCheck" class="filled-in" required/>
                                <span>J'accepte les  
                                    <i class="material-icons tiny tooltipped" 
                                       data-position="top" 
                                       data-tooltip="- La photo sera soumise à validation par un modérateur avant publication (évitez les photos où il est possible de distinguer des visages)<br>- La photo sera partagée sous licence Creative Commons BY-SA 4.0<br>- La photo validée sera visible par tous les utilisateurs<br>- Cette licence est irrévocable : une fois la photo publiée, elle ne pourra plus être retirée">
                                        conditions de partage (lire).
                                    </i>
                                </span>
                            </label>
                        </p>

                        <!-- Crédit photo -->
                        <p>
                            <label>
                                <input type="checkbox" id="creditCheck" class="filled-in" checked/>
                                <span>Je souhaite être crédité(e) comme photographe</span>
                            </label>
                        </p>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-red btn-flat">Annuler</a>
                <a href="#!" class="waves-effect waves-green btn" id="submitBtn" onclick="submitPhoto()" disabled>Envoyer</a>
            </div>
        </div>


         <?php }
        ?>
    </div>

</body>

    <style type="text/css">
        /*leaflet*/
        .leaflet-control{text-decoration:none; opacity: 80%; font-family: Nunito}
    </style>

<script>

document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.modal');
    M.Modal.init(elems);
    
    // Init tooltips
    var tooltips = document.querySelectorAll('.tooltipped');
    M.Tooltip.init(tooltips, {html: true});
});


        // Prévisualisation de l'image
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            updateSubmitButton();
        }
        reader.readAsDataURL(file);
    }
});

// Écoute des changements sur la checkbox des conditions
document.getElementById('termsCheck').addEventListener('change', updateSubmitButton);

</script>

</html>
