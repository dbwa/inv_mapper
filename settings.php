<?php
session_start();
include_once(__DIR__ . '/config.php');
include_once("./fonctions.inc.php");

$username = $_SESSION['login_name'];

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
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <!-- Pour gerer les dates dans les charts -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> -->
    <!-- Inclure l'adaptateur pour Moment.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script> -->


    <!-- Inclure jQuery et DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <!-- Inclure les styles CSS pour les boutons DataTables -->
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css"> -->
    <!-- Inclure les scripts JS pour les boutons DataTables et les fonctionnalités d'exportation -->
    <!-- <script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script> -->
    <!-- Inclure les fichiers CSS pour DataTables Responsive -->
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css"> -->
    <!-- Inclure les fichiers JS pour DataTables et DataTables Responsive -->
    <!-- <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script> -->

    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <!-- pour hash le password -->
    <script src="js/CryptoJS.js"></script>

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

    </style>
</head>
<body>
<?php include_once("./navbar.php"); ?>

<br>
<div class="container">
    <div class="row">
        <div class="col s12">
            <!-- Tabs navigation -->
            <ul class="tabs">
                <li class="tab col s3"><a href="#settings-compte">Mon compte</a></li>
                <li class="tab col s3"><a href="#settings-password">Mot de passe</a></li>
                <li class="tab col s3"><a href="#settings-colors">Couleurs des Invaders</a></li>
                <li class="tab col s3"><a href="#settings-api">API</a></li>
                <li class="tab col s3"><a href="#settings-subscription">Synchronisation</a></li>
            </ul>
        </div>

        <!-- Tab content -->
        <div id="settings-compte" class="col s12 light-container">
            <?php include_once("./modules/compte.php"); ?>
        </div>
        <div id="settings-password" class="col s12">
            <!-- Formulaire de changement de mot de passe -->
            <div id="settings-password" class="col s12  light-container">
            <h5>Changer de mot de passe</h5>
                <form id="change-password-form">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="current-password" type="password" class="validate" required>
                            <label for="current-password">Mot de passe actuel</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="new-password" type="password" class="validate" required>
                            <label for="new-password">Nouveau mot de passe</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="confirm-new-password" type="password" class="validate" required>
                            <label for="confirm-new-password">Confirmer le nouveau mot de passe</label>
                        </div>
                        <div class="col s12">
                            <button class="btn waves-effect waves-light" type="submit" name="action">Changer le mot de passe
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="settings-colors" class="col s12 light-container">
            <h5>Couleurs des Invaders</h5>
            <p>C'est pour plus tard. Le but serait de pouvoir personnaliser sa carte, avec ses catégories, etc etc. C'est en cours de construction, mais j'ai des difficultés techniques dessus.</p>
        </div>
        <div id="settings-api" class="col s12 light-container">
            <?php include_once("./api/settings_api_tab.php"); ?>
        </div>
        <div id="settings-subscription" class="col s12 light-container">
            <h5>Synchronisation</h5>
            <p>Pour se synchroniser avec les flash tel que le jeu les comptes.  </p>
            <?php include_once("./modules/synchronisation.php"); ?>
        </div>
    </div>
</div>

<script>
    //gestion des tabs
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.tabs');
        var options = {};
        var instances = M.Tabs.init(elems, options);
    });

    //modif du password
    document.addEventListener('DOMContentLoaded', function() {
        // Lorsque le formulaire password est soumis
        document.getElementById('change-password-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var currentPassword = CryptoJS.SHA1(document.getElementById('current-password').value).toString();
            var newPassword = CryptoJS.SHA1(document.getElementById('new-password').value).toString();
            var confirmNewPassword = CryptoJS.SHA1(document.getElementById('confirm-new-password').value).toString();

            // Vérifiez si les nouveaux mots de passe correspondent
            if(newPassword !== confirmNewPassword) {
                alert("Les nouveaux mots de passe ne correspondent pas.");
                return;
            }

            // Préparez les données à envoyer au serveur
            var dataString = 'username=' + encodeURIComponent(<?php echo json_encode((string)$username, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '""'; ?>) + '&currentpass='+ currentPassword + '&newpassword='+ newPassword;
            console.log(dataString);
            $.ajax({
                    type: "GET",
                    url: "AjaxUpdatePassword.php",
                    data: dataString,
                    cache: false,
                    beforeSend: function () {
                        /*$("#login").val('Enregistrement...');*/
                    },
                    success: function (data) {
                        if (data) {
                            console.log(data);
                            data = JSON.parse(data); 
                            if (data.success){
                                alert("Votre mot de passe a été changé avec succès.");
                                document.getElementById('current-password').value = '';
                                document.getElementById('new-password').value = '';
                                document.getElementById('confirm-new-password').value = '';
                            }
                            else if (data.raison == 'erreur password'){
                                alert("Erreur dans le mot de passe actuel.");
                            }
                            else{
                                alert("Erreur !");
                                console.log(data.success);
                                console.log(data.raison);
                                console.log(data.login);
                            }
                        }
                        else {
                                alert("Erreur lors du changement du mot de passe");
                        }
                    }
                });
        });
    });



</script>


</body>
<?php include_once('footer.php'); ?>

</html>