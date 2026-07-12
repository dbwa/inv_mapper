<?php
session_start();
include_once(__DIR__ . '/config.php');
include_once("./fonctions.inc.php");

// utilisez cela pour debeugger:
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
connect();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de positionnement des invaders par ville</title>

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

        .point-name {
            cursor: pointer; /* Curseur change lorsqu'il est survolé */
            white-space: nowrap; /* Empêcher les sauts de ligne automatiques */
            overflow: hidden; /* Masquer le débordement de texte */
            text-overflow: ellipsis; /* Afficher "..." pour le texte qui déborde */
            max-width: 200px; /* Largeur maximale pour chaque cellule de nom */
        }

        .point-name.found {
            color: green; /* Couleur verte pour les points trouvés */
        }

        .point-name.not-found {
            color: red; /* Couleur rouge pour les points non trouvés */
        }

        .point-name{
            display: inline-block;
        }

        /* Tooltip text */
        .point-name .tooltipimage {
          visibility: hidden;
          width: 120px;
          background-color: black;
          color: #fff;
          text-align: center;
          padding: 5px 0;
          border-radius: 6px;
         
          /* Position the tooltip text - see examples below! */
          position: absolute;
          z-index: 1;
        }

        /* Show the tooltip text when you mouse over the tooltip container */
        .point-name:hover .tooltipimage {
          visibility: visible;
        }


    </style>
</head>
<body>
<?php include_once("./navbar.php"); ?>

<div class="container"> 
    <h2>Travaux de positionnement des invaders</h2>
    <p>Le placement des invaders sur la carte se fait progressivement. Nous pouvons suivre ici l'avancement de ces travaux. En vert, ce sont les invaders placés, et en rouge, ceux qu'ils restent à faire. Si vous avez des positions, n'hésitez pas à les envoyer afin qu'ils soient ajoutés au site.</p>
    <br>
        <?php
        // Calcul du total global
        $sql_total_pos = "SELECT COUNT(*) AS total_pos FROM positions";
        $result_total_pos = $pdo->query($sql_total_pos);
        $row_total_pos = $result_total_pos->fetch(PDO::FETCH_ASSOC);
        $total_pos_global = $row_total_pos["total_pos"];

        $sql_total = "SELECT COUNT(*) AS total FROM etat";
        $result_total = $pdo->query($sql_total);
        $row_total = $result_total->fetch(PDO::FETCH_ASSOC);
        $total_global = $row_total["total"];

        echo "<p><b> $total_pos_global / $total_global invaders positionnés dans le monde, soit ". round(($total_pos_global / $total_global)*100, 1) .' %<b>';
        ?>
    </p>



    <h3>État de positionnement des invaders par ville</h3>
      <div class="light-container">
        <div class="row">
            <div class="col s12">  
            <table id='scorePosition' class='striped'>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Invaders</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>

            <?php
            // Incluez ici votre objet PDO global
            global $pdo;

            $sql = "SELECT
                        coalesce(v.fr_ville, SUBSTRING_INDEX(e.inv_name, '_', 1)) AS ville,
                        e.inv_name,
                        CONCAT('./img_invader/', e.image1) as image_url,
                        CASE
                            WHEN p.inv_name IS NOT NULL THEN 'found'
                            ELSE 'not-found'
                        END AS position_status
                    FROM
                        etat e
                    LEFT JOIN
                        positions p ON e.inv_name = p.inv_name
                    LEFT JOIN
                        villes v on v.short_name = SUBSTRING_INDEX(e.inv_name, '_', 1)
                    ORDER BY
                        ville, cast(SUBSTRING_INDEX(e.inv_name, '_', -1) as int)";

            $result = $pdo->query($sql);

            $current_ville = "";
            $total_points = 0;
            $found_points = 0;
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $ville = $row["ville"];
                $inv_name = $row["inv_name"];
                $image_url = $row["image_url"];
                $position_status = $row["position_status"];

                // Si la ville change, commencez une nouvelle ligne
                if ($current_ville != $ville) {
                    if ($current_ville != "") {
                        echo "</td>";
                        echo "<td>$found_points / $total_points</td>";
                        echo "</tr>";
                    }
                    echo "<tr>";
                    echo "<td>$ville</td><td>";
                    $current_ville = $ville;
                    $total_points = 0;
                    $found_points = 0;
                }
                
                // Incrémentez le total des points
                $total_points++;

                // Incrémentez les points trouvés
                if ($position_status === "found") {
                    $found_points++;
                }

                // Affichez le nom du point
                echo "<div class='point-name $position_status'>$inv_name&#160;";
                
                // Affichez l'image du point avec classe 'point-image'
                if ($image_url) {
                    echo "<span class='tooltipimage'><img loading='lazy' src='$image_url' alt='$inv_name' width='100'></span>";
                }
                
                echo "</div>";

            }
            
            echo "</td>";

            // Afficher le total des points trouvés pour la dernière ville
            if ($current_ville != "") {
                echo "</td>";
                echo "<td>$found_points / $total_points</td>";
                echo "</tr>";
            }
            ?>

        </tbody> </table>

        </div>
    </div>
</div>


</body>
<?php include_once('footer.php'); ?>


<script>

    // Initialiser le tableau DataTables "Mes flashs"
    var userFlashTable;
    $(document).ready(function() {
        userFlashTable = $('#scorePosition').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Ajouter 'B' pour les boutons dans le DOM de DataTables
        });
    });

</script>


</html>