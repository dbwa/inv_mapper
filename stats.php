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
		    font-family: Arial;
		    width: 550px;
		}

		.outer-scontainer {
		    background: #F0F0F0;
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
			 liste_inv = txt_liste_inv.split(',')
			 /*envoie de chaque elem de la liste vers le ajax d'ajout dans la base*/
			 liste_inv.forEach((item, index) => {
/*				  console.log(item); //value
				  console.log(index);*/
				  click_to_flash(item);
				});

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
<footer>
    <div class="container text-center">
        <p><small> v0.2 2021 </small></p>
    </div>
</footer>


</body>

</html>
