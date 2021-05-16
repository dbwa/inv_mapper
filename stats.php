<!DOCTYPE html>
<html lang="fr">

<?php
session_start();
include_once(__DIR__ . '/config.php');
include_once("./fonctions.inc.php");

// utilisez cela pour debeugger:
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
connect();
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
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <!-- Custom Theme JavaScript -->
    <script src="js/geojson.js" type="text/javascript"></script>
    

    <!-- pour faire les graphs -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
    <script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>

	<style>

		body {
			background-color:#121212;
		    font-family: Arial;
		    width: 550px;
		}

		h2, h3, h4{color:#FFA518;}

		.modal-content {
		    background: #343434;
		    color:#fff;
		    border: #e0dfdf 1px solid;
		    padding: 20px;
		    border-radius: 2px;
		}

		.outer-scontainer {
		    background: #343434;
		    color:#fff;
		    border: #e0dfdf 1px solid;
		    padding: 20px;
		    border-radius: 2px;
		}

		.input-row {
		    margin-top: 0px;
		    margin-bottom: 20px;
		}

		.btn-submit {
		    background: #333;
		    border: #1d1d1d 1px solid;
		    color: #f0f0f0;
		    font-size: 0.9em;
		    width: 100px;
		    border-radius: 2px;
		    cursor: pointer;
		}

		.outer-scontainer table {
		    border-collapse: collapse;
		    width: 100%;
		}

		.outer-scontainer th {
		    border: 1px solid #dddddd;
		    padding: 8px;
		    text-align: left;
		}

		.outer-scontainer td {
		    border: 1px solid #dddddd;
		    padding: 8px;
		    text-align: left;
		}

		#response {
		    padding: 10px;
		    margin-bottom: 10px;
		    border-radius: 2px;
		    display: none;
		}

		.success {
		    background: #c7efd9;
		    border: #bbe2cd 1px solid;
		}

		.error {
		    background: #fbcfcf;
		    border: #f3c6c7 1px solid;
		}

		div#response.display-block {
		    display: block;
		}

		.btn{
			font-size: 0.8em;
			width: :100%;
		}


		/*pour le text input*/

		.input,
		.textarea {
		  border: 1px solid #ccc;
		  font-family: inherit;
		  font-size: inherit;
		  padding: 1px 6px;
		}

		.input-wrap {
		  position: relative;
		}
		.input-wrap .input {
		  position: absolute;
		  width: 100%;
		  left: 0;
		}
		.width-machine {
		  /*   Sort of a magic number to add extra space for number spinner */
		  padding: 0 1rem;
		}

		.textarea {
		  display: block;
		  width: 100%;
		  overflow: hidden;
		  resize: both;
		  min-height: 40px;
		  line-height: 20px;
		}

		.textarea[contenteditable]:empty::before {
		  content: "PA_1,PA_2,...";
		  color: gray;
		}

        /*boutons*/
        .btn.btn-primary.btn-outline-primary{border-width:2px;cursor:pointer}
        .btn.btn-outline-white{border:2px solid #fff;background:none;color:#fff;text-decoration:none}
        .btn.btn-outline-white:hover{background:#FFA518;color:#000;border:2px solid transparent}

        .btn-outline-orange{border:2px solid #FFA518;background:none;color:#fff;text-decoration:none}
        .btn-outline-orange:hover{background:#FFA518;color:#000;border:2px solid transparent}

		.btn-primary{border:2px solid #00b8ff;background:none;color:#fff;text-decoration:none; padding:1px; margin-right: 2px; margin-bottom: 1px}
		.btn-warning, .btn-default{border:2px solid #f0ad4e;background:none;color:#fff;text-decoration:none; padding:1px; margin-right: 2px; margin-bottom: 1px}
		.btn-dark{border:2px solid #7aff00;background:none;color:#fff;text-decoration:none; padding:1px; margin-right: 2px; margin-bottom: 1px}

		#myBtn, .page-scroll {border:2px solid #00b8ff;background:none;color:#fff;text-decoration:none; padding:5px 15px 5px; margin-right: 15px}

		.modal-content .btn {padding:5px 15px 5px; margin-right: 5px}

        /*footer*/
        .ftco-footer{background:#121212;padding:7em 0;font-size:15px;font-weight:400}
        .ftco-footer .footer-widget h3{font-size:20px;color:#FFA518}
        .ftco-footer .btn {font-size:20px;color:#ffe2e6;font-size: small}
        .footer-widget{padding: 0px 25px 25px;}

        /*autres*/
        .ct-label{color:#000; font-size: small; font-weight: bold; font-family: Montserrat}

	</style>
	<script type="text/javascript">
	    /*pour mettre a jour le tableau des flash en entier*/
/*	    function update_table() {
	        var dataString = 'origin=stats';
	        console.log(dataString);
	        $.ajax({
	            type: "POST",
	            url: "maj_click/graphs/tableau_flash.php",
	            data: dataString,
	            cache: false,
	            success: function (reponse) {
	                //netoyage puis reapplication
	           }
	        });
	    }
*/
		/*pour supprimer un flash du tableau des flash*/
	    function click_to_NON_flash(inv_name) {
	        var dataString = 'inv_name=' + inv_name + '&flash=faux';
	        console.log(dataString);
	        $.ajax({
	            type: "POST",
	            url: "maj_click/maj_flash.php",
	            data: dataString,
	            cache: false,
	            success: function (reponse) {
	                //netoyage puis reapplication
	                /*update_table();*/
	                document.getElementById('del_flash_'+inv_name).value = 'Remettre le flash';
	                document.getElementById('del_flash_'+inv_name).className = 'btn btn-success';
	                document.getElementById('del_flash_'+inv_name).setAttribute( "onClick", "cancel_click_to_NON_flash('"+ inv_name +"');");
	           }
	        });
	    }

	    function cancel_click_to_NON_flash(inv_name) {
	    	click_to_flash(inv_name);
            document.getElementById('del_flash_'+inv_name).value = 'Supprimer le flash';
            document.getElementById('del_flash_'+inv_name).className = 'btn btn-dark';
            document.getElementById('del_flash_'+inv_name).setAttribute( "onClick", "click_to_NON_flash('"+ inv_name +"');");
   		}

	    function click_to_detruit(inv_name) {
	        var dataString = 'inv_name=' + inv_name + '&statusout=detruit';
	        $.ajax({
	            type: "POST",
	            url: "maj_click/maj_status.php",
	            data: dataString,
	            cache: false,
	            success: function (reponse) {
	                //netoyage puis reapplication
	           }
	        });
	    }

	    function click_to_reactive(inv_name) {
	        var dataString = 'inv_name=' + inv_name + '&statusout=OK';
	        $.ajax({
	            type: "POST",
	            url: "maj_click/maj_status.php",
	            data: dataString,
	            cache: false,
	            success: function (reponse) {
	                //netoyage puis reapplication
	           }
	        });
	    }

	    function click_to_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=vrai';
        $.ajax({
            type: "POST",
            url: "maj_click/maj_flash.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
           }
        });
   		}

	    function click_to_flash_multi(list_inv_name) {
        $.ajax({
			url:'maj_click/ajout_flash_multi.php',
			method:'POST',
			dataType: 'json',
			 processData: false,
			contentType: 'application/json',
			data:JSON.stringify({
			    "inv_names":list_inv_name
			 }),
			 success: function (reponse) {
                //netoyage puis reapplication
           	}

			});
   		}


		/*Envoyer les listes vers le serveur*/
		function ajout_flash_depuis_liste(){
			$("#loading").show();
			 /*recup et netoyage de l input :*/
			 console.log(document.getElementById('malisteaajouter').innerHTML);
			 txt_liste_inv = document.getElementById('malisteaajouter').innerHTML.replaceAll(/ /g, "");
			 txt_liste_inv = txt_liste_inv.replaceAll("<div>", ",");
			 txt_liste_inv = txt_liste_inv.replaceAll("</div>", "");
			 txt_liste_inv = txt_liste_inv.replaceAll("<br>", "");
			 txt_liste_inv = txt_liste_inv.replaceAll(",,", ",");
			 console.log(txt_liste_inv);

			 //envoie d'une page entiere d'elements vers la base, qui se debrouille ensuite pour couper et crer la table
			 click_to_flash_multi(txt_liste_inv);

			 /*refresh de la page pour voir les resultats*/
			 location.reload();
		}

	</script>
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">




<!-- Section graph -->
<section id="main" class="container cartos-section">

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
			
            <a class="btn btn-primary page-scroll" href="logout.php" title="Se déconnecter"><i class="fa fa-sign-out fa-lg"></i> Se déconnecter </a>
            <a class="btn btn-primary page-scroll" href="index.php" title="Ma carte"><i class="fa fa-glob fa-lg"></i> Ma carte </a>
            <div class="col-md-12">
 


                    <h2>Mes flashs</h2>


                    <div class="ct-chart ct-major-tenth" id="pie_chart_total"></div>
        			<?php include_once("./graphs/graph_total.php"); ?>

        			<br>


                        <div class="outer-scontainer">
                    		<h3>Mes flashs</h3>

							<div id='tableflash'></div>
		        			<?php include_once("./graphs/tableau_flash.php"); ?>
		        			<br>
                            <div class="row">
								<button id="myBtn" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" style="float:right;">Ajouter une liste de flashs</button>


								<!-- Modal -->
								<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								    <div class="modal-dialog">
								        <div class="modal-content">
								            <div class="modal-header">
								                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								                 <h4 class="modal-title" id="myModalLabel">Ajouter les noms des invaders flashés</h4>
								                 <p style="color:grey; font-size:small;">
								                 Le nom devra etre au format 'PA_2,PA_34,...'. C'est a dire sans 0 apres les '_', et séparés par des virgules. 
								                 </p>

								            </div>
								            <div class="modal-body">
											<span class="textarea"  id="malisteaajouter" role="textbox" style="overflow: auto; max-height:400px; max-width:100%; min-height:150px; min-width:100%;" contenteditable></span>

								            </div>
								            <div class="modal-footer">
								                <img id="loading" src="./img/spinner.gif" alt="Updating ..." style="display: none;" />
								                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								                <button type="button" class="btn btn-primary" onclick="ajout_flash_depuis_liste()">Ajouter</button>

								            </div>
								        </div>
								    </div>
								</div>

                            </div>

                        </div>
                    <br>

                        <div class="outer-scontainer">
                    		<h3>Mes modifications</h3>
							<div id='tableetatuser'></div>
		        			<?php include_once("./graphs/tableau_etat_user.php"); ?>
                        </div>





            </div>

         <?php }
        ?>
    </div>
</section>




<!-- Footer -->

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

</body>

</html>
