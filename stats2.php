<?php
session_start();
include_once(__DIR__ . '/config.php');
include_once("./fonctions.inc.php");

// utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
connect();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Example</title> 

    
    <!-- Inclure Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Pour gerer les dates dans les charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- Inclure l'adaptateur pour Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>


    <!-- Inclure jQuery et DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Inclure les styles CSS pour les boutons DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <!-- Inclure les scripts JS pour les boutons DataTables et les fonctionnalités d'exportation -->
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <!-- Inclure les fichiers CSS pour DataTables Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
    <!-- Inclure les fichiers JS pour DataTables et DataTables Responsive -->
    <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>

    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <style>
        /* Thème sombre */
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
        
        h3{
          font-family: 'Tiny5', Arial, sans-serif;
        }
        
        .stat-value{
        font-family: 'Tiny5', Arial, sans-serif;
        }

    </style>
</head>
<body>
<?php include_once("./navbar.php"); ?>


<div class="container">
    <!-- Statistiques -->
    <h3>Overview</h3>
    <?php include_once("./graphs/overview_user.php"); ?>

    <!-- Graphique historique -->
    <h3>Mon historique de flashs</h3>
    <div class="row">
        <div class="col s12">
            <canvas id="historyChart" style="min-height:350px;"></canvas>
            <?php include_once("./graphs/graph_historique2.php"); ?>
        </div>
    </div>


    <!-- Modal Structure pour ajouter des flash avec une liste -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <h4>Ajouter les noms des invaders flashés</h4>
            <p style="font-size:small;">
                Insérer les noms des invaders au format 'PA_2,PA_34,FTBL_12,etc'. En majuscule, séparés par des vigules.
            </p>
            <div class="input-field">
                <textarea id="malisteaajouter" class="materialize-textarea"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <img id="loading" src="./img/spinner.gif" alt="Updating ..." style="display: none;" />
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Close</a>
            <a href="#!" class="waves-effect waves-green btn" onclick="ajout_flash_depuis_liste()">Ajouter</a>
        </div>
    </div>

    <!-- Tableau -->
    <h3>Mes flashs</h3>
    <div class="light-container">
        <div class="row">
            <div class="col s12">
                <?php include_once("./graphs/tableau_flash2.php"); ?> <!-- date id  = userFalshtable -->
            </div>
        </div>
        <button data-target="myModal" class="btn modal-trigger">Ajouter des flashs</button>
    </div>

    <!-- Tableau -->
    <h3>Mes modifications</h3>
    <div class="light-container">
        <div class="row">
            <div class="col s12">
                <?php include_once("./graphs/tableau_etat_user2.php"); ?>
            </div>
        </div>
    </div>

    <h3>Mes contributions</h3>
    <div class="light-container">
        <div class="row">
            <div class="col s12">
                <?php include_once("./graphs/tableau_mes_ajouts2.php"); ?>
            </div>
        </div>
    </div>

    <h3>Mes photos</h3>
    <div class="light-container">
        <div class="row">
            <div class="col s12">
                <?php include_once("./graphs/tableau_mes_photos2.php"); ?>
            </div>
        </div>
    </div>



</div>


<script>

    // Initialiser le tableau DataTables "Mes flashs"
    var userFlashTable;
    $(document).ready(function() {
        userFlashTable = $('#userFalshtable').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Ajouter 'B' pour les boutons dans le DOM de DataTables
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Exporter en CSV',
                    className: 'btn-export', // Vous pouvez ajouter une classe pour le style si nécessaire
                    exportOptions: {
                        columns: ':visible' // Exporter seulement les colonnes visibles (utile pour les tables responsives)
                    }
                }
            ]
        });
    });


    // Initialiser le tableau DataTables "Mes modifications"
    var tableetat;
    $(document).ready(function() {
        tableetat = $('#tableetat').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Ajouter 'B' pour les boutons dans le DOM de DataTables
            buttons: [
            ]
        });
    });

    // Initialiser le tableau DataTables "Mes contributions"
    var tablepositionuser;
    $(document).ready(function() {
        tablepositionuser = $('#tablepositionuser').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Ajouter 'B' pour les boutons dans le DOM de DataTables
            buttons: [{
                    extend: 'csvHtml5',
                    text: 'Exporter en CSV',
                    className: 'btn-export', // Vous pouvez ajouter une classe pour le style si nécessaire
                    exportOptions: {
                        columns: ':visible' // Exporter seulement les colonnes visibles (utile pour les tables responsives)
                    }
                }
            ]
        });
    });

    // Initialiser le tableau DataTables "Mes photos"
    var tablemesphotos;
    $(document).ready(function() {
        tablemesphotos = $('#tablemesphotos').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Ajouter 'B' pour les boutons dans le DOM de DataTables
            buttons: [{
                    extend: 'csvHtml5',
                    text: 'Exporter en CSV',
                    className: 'btn-export', // Vous pouvez ajouter une classe pour le style si nécessaire
                    exportOptions: {
                        columns: ':visible' // Exporter seulement les colonnes visibles (utile pour les tables responsives)
                    }
                }
            ]
        });
    });

    $(document).ready(function(){
        $('.modal').modal();
    });

</script>



<script type="text/javascript">
    /*pour supprimer un flash du tableau des flash*/
    function click_to_NON_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=faux';
        console.log(dataString);
        $.ajax({
            type: "GET",
            url: "maj_click/maj_flash.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
                /*update_table();*/
                document.getElementById('del_flash_'+inv_name).value = 'Remettre le flash';
                document.getElementById('del_flash_'+inv_name).className = 'btn-small';
                document.getElementById('del_flash_'+inv_name).setAttribute( "onClick", "cancel_click_to_NON_flash('"+ inv_name +"');");
                var buttonCell = userFlashTable.cell($('#del_flash_' + inv_name).closest('td'));
                buttonCell.data("<input id='del_flash_" + inv_name + "' class='btn-small' value='Remettre le flash' onclick='cancel_click_to_NON_flash(\"" + inv_name + "\")' readonly=''>").draw();
           }
        });
    }

    function cancel_click_to_NON_flash(inv_name) {
        click_to_flash(inv_name);
        document.getElementById('del_flash_'+inv_name).value = 'Supprimer le flash';
        document.getElementById('del_flash_'+inv_name).className = 'btn btn-dark';
        document.getElementById('del_flash_'+inv_name).setAttribute( "onClick", "click_to_NON_flash('"+ inv_name +"');");
        var buttonCell = userFlashTable.cell($('#del_flash_' + inv_name).closest('td'));
        buttonCell.data("<input id='del_flash_" + inv_name + "' class='btn-small' value='Supprimer le flash' onclick='click_to_NON_flash(\"" + inv_name + "\")' readonly=''>").draw();
    }

    function click_to_detruit(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=detruit';
        $.ajax({
            type: "GET",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
                location.reload();
           }
        });
    }

    function click_to_reactive(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=OK';
        $.ajax({
            type: "GET",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
                location.reload();
           }
        });
    }

    function click_to_flash(inv_name) {
    var dataString = 'inv_name=' + inv_name + '&flash=vrai';
    $.ajax({
        type: "GET",
        url: "maj_click/maj_flash.php",
        data: dataString,
        cache: false,
        success: function (reponse) {
            //netoyage puis reapplication
       }
    });
    }

    function click_to_flash_multi(list_inv_name) {
        var dataString = 'inv_names=' + list_inv_name ;
        $.ajax({
            url: 'maj_click/ajout_flash_multi.php', 
            method: 'GET',
            data: dataString,
            cache: false,
            success: function (reponse) {
                    reponse = JSON.parse(reponse);
                    var insertedCount = reponse.insertedCount;
                    if (insertedCount > 0) {
                        alert(insertedCount + " invader(s) inséré(s) avec succès");
                        location.reload();
                    } else {
                        alert("Aucun invader inséré.");
                    }
            },
            error: function (xhr, status, error) {
                console.log("Erreur AJAX :", error);
                // Vous pouvez gérer l'erreur ici (par exemple, afficher un message d'erreur à l'utilisateur)
            },
            complete: function (xhr, status) {
                console.log("Requête AJAX terminée avec statut :", status);
                // Code à exécuter une fois la requête AJAX terminée (qu'elle ait réussi ou échoué)
            }
        });
    }



    /*Envoyer les listes vers le serveur*/
    function ajout_flash_depuis_liste(){ 
        $("#loading").show();
         /*recup et netoyage de l input :*/
         console.log(document.getElementById('malisteaajouter').value);
         txt_liste_inv = document.getElementById('malisteaajouter').value.replaceAll(/ /g, "");
         txt_liste_inv = txt_liste_inv.replaceAll("<div>", ",");
         txt_liste_inv = txt_liste_inv.replaceAll("</div>", "");
         txt_liste_inv = txt_liste_inv.replaceAll("<br>", "");
         txt_liste_inv = txt_liste_inv.replaceAll(";", ",");
         txt_liste_inv = txt_liste_inv.replaceAll("/", ",");
         txt_liste_inv = txt_liste_inv.replaceAll("\t", ",");
         txt_liste_inv = txt_liste_inv.replaceAll("-", ",");
         txt_liste_inv = txt_liste_inv.replaceAll(",,", ",");
         txt_liste_inv = txt_liste_inv.replaceAll("_0", "_");
         txt_liste_inv = txt_liste_inv.replaceAll("_0", "_");
         txt_liste_inv = txt_liste_inv.replaceAll("_0", "_");
         console.log(txt_liste_inv);

         //envoie d'une page entiere d'elements vers la base, qui se debrouille ensuite pour couper et crer la table
         click_to_flash_multi(txt_liste_inv);
    }


function delete_position_invader_base(inv_name) {
var dataString = 'inv_name=' + inv_name
    $.ajax({
        type: "GET",
        url: "maj_click/delete_position.php",
        data: dataString,
        cache: false,
        success: function (reponse) {
            //on relance la page entière
           location.reload();
       },
       error: function (reponse){
        console.log(reponse);
       }
    });
}

function get_data(callback) {
    $.ajax({
        type: "GET",
        url: "maj_click/data_export.php",
        cache: false,
        success: function (reponse) {
            callback(reponse); // Appeler la fonction de rappel avec la réponse en tant qu'argument
        },
        error: function (reponse) {
            console.log(reponse);
        }
    });
}

    </script>



</body>
<?php include_once('footer.php'); ?>

</html>