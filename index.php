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

    <style type="text/css">
        
        /*disposition*/
        .vertical-center{margin: 0; position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); transform: translate(-50%, -50%)}
        p{font-size: medium}

        .fin{margin-top: 62px}

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


        /*footer*/
        .ftco-footer{background:#121212;padding:7em 0;font-size:15px;font-weight:400}
        .ftco-footer .footer-widget h3{font-size:20px;color:#FFA518}
        .ftco-footer .btn {font-size:20px;color:#ffe2e6;font-size: small}
        .footer-widget{padding: 0px 25px 25px;}
    </style>

<link rel="stylesheet" href="./index_v3.css">

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
<div class="img_bg" style="background-image: url(https://images.pexels.com/photos/2603464/pexels-photo-2603464.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260); background-position: 50% -25px;" data-stellar-background-ratio="0.5">
<div id="vertical-center">
<div class="row align-items-center justify-content-center text-center vertical-center">
<h2 class="heading">Bienvenue sur Invaders Mapper</h2>
<p><a href="adherent.php" class="smoothscroll btn btn-outline-white px-5 py-3">Se connecter</a></p>
</div>
</div>
</div>
</div> 

<footer class="ftco-footer">
<div class="container">
<div class="row">
<div class="col-md-6 mb-6">

<div class="footer-widget">

<h3 class="mb-4">A propos</h3>
<p>Invaders mapper est un moyen simple de localiser et gerer les invaders pour l'application flashInvaders </p>
<p><a href="https://play.google.com/store/apps/details?id=com.ltu.flashInvader&hl=fr" class="btn btn-outline-orange">Télécharger l'application</a></p>

</div>
</div>


<div class="col-md-6">
<div class="footer-widget">
<h3 class="mb-4">Suivre le projet </h3>

<p><a href="https://github.com/dbwa/inv_mapper"><span class="fa fa-github"></span><small> inv_mapper</small></a></p>

</div>

</div>
</div>
<div class="row fin">
<div class="col-md-12 text-center">
<p>

v0.3.1 <script>document.write(new Date().getFullYear());</script> Invaders Mapper

</p>
</div>
</div>
</div>
</footer>
</div>

</div>

              <?php
        }
        else { //si adhérent sa carte :
             ?>

            <section id="main" style="width: 100%; height: 100%;">


                    <div id="emimapajax" style="width: 100%; height: 95%;"></div>
                    <?php include_once("map/map_emission_ajax.php"); ?>

               <a class="btn btn-primary page-scroll" href="logout.php" title="Se déconnecter" style="margin-left:1%; width: 49%; height: 5%;" ><i class="fa fa-sign-out fa-lg"></i> Se déconnecter </a>
                <a class="btn btn-primary page-scroll" href="stats.php" title="Mes stats" style="width: 49%; height: 5%;"><i class="fa fa-chart-pie fa-lg"></i> Mes stats </a>

            </section>

         <?php }
        ?>
    </div>


</body>

    <style type="text/css">
        /*leaflet*/
        .leaflet-control{text-decoration:none; opacity: 80%; font-family: Montserrat}
    </style>

</html>
