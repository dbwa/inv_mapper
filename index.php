<!DOCTYPE html>
<html lang="fr">

<?php
session_start();
include_once(__DIR__ . '/config.php');


// utilisez cela pour debeugger:
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
?>

<head>
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

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/geojson.js" type="text/javascript"></script>

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
<section id="cartos" class="container cartos-section">

    <div class="alert alert-warning alert-dismissible fade in" role="alert">En cours de fabrication
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

        <?php
        if (empty($_SESSION['login_user'])){ //pour tout le monde                    
	          echo '<a class="btn btn-primary page-scroll" href="adherent.php"><i class="fa fa-lock fa-lg"></i>  Se connecter</a>
              <p> Bienvenue sur invader mapper </p>

              ';
        }
        else { //si adhérent sa carte :
        	 ?>
			<!-- pour plus tard -->

            <a class="btn btn-primary page-scroll" href="logout.php" title="Se déconnecter"><i class="fa fa-sign-out fa-lg"></i> Se déconnecter </a>
            <a class="btn btn-primary page-scroll" href="stats.php" title="Mes stats"><i class="fa fa-chart-pie fa-lg"></i> Mes stats </a>
            <div class="col-md-9">
                <div id="emimapajax" style="width: 100%;height: 600px; margin-top: 10px;"></div>
                <?php include_once("map/map_emission_ajax.php"); ?>
            </div>

         <?php }
        ?>
    </div>
</section>




<!-- Footer -->
<footer>
    <div class="container text-center">
        <p><small> v0.2 2021 </small></p>
    </div>
</footer>


</body>

</html>
