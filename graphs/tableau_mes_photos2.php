<?php

//*********
// Tableau des photos mises en ligne par l'utilisateur
//*********

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();

// Récupération des variables POST
$username = $_SESSION['login_name'];


function data_mes_photos($username)
{
    $pdo = connect();
    $query = "
        SELECT
            up.inv_name AS inv_name,
            up.photo_path AS photo_path,
            up.credit AS credit,
            up.status AS status,
            up.upload_date AS upload_date,
            up.validation_date AS validation_date,
            etat.points AS points,
            etat.etat AS etat,
            villes.fr_ville as ville
        FROM user_photos up
        LEFT JOIN etat ON up.inv_name = etat.inv_name
        LEFT JOIN villes ON SUBSTRING_INDEX(up.inv_name, '_', 1) = villes.short_name
        WHERE up.login = ?
        AND up.status <> 'rejected'
        ORDER BY up.upload_date DESC, up.inv_name;";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_mes_photos($username);

if ($datas != null){

    // Ajouter le style pour les images et le loading spinner
    echo "
    <style>
        .inv-thumb {
            height: 25px;
            width: 25px;
            object-fit: cover;
            background: #2a2a2a;
        }
        .inv-thumb.loading {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 0.7; }
            100% { opacity: 0.3; }
        }
        .inv-thumb-container {
            display: inline-block;
            height: 25px;
            width: 25px;
            vertical-align: middle;
        }
        .photo-thumb {
            height: 60px;
            width: 60px;
            object-fit: cover;
            border-radius: 3px;
            background: #2a2a2a;
            cursor: pointer;
        }
        .photo-thumb.loading {
            animation: pulse 1.5s infinite;
        }
        .photo-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            white-space: nowrap;
        }
        .photo-status.accepted {
            background-color: #1b5e20;
            color: #fff;
        }
        .photo-status.pending {
            background-color: #e65100;
            color: #fff;
        }
    </style>
    <script>
        function handleImageLoad(img) {
            img.classList.remove('loading');
        }

        function handleImageError(img) {
            img.classList.remove('loading');
            img.src = '/img/placeholder.png';
        }
    </script>
    ";

    $html_mes_photos = "
    <table id='tablemesphotos' class='striped'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>ma photo</th>
            <th>statut</th>
            <th>crédit</th>
            <th>envoyée le</th>
            <th>validée le</th>
            <th>points</th>
            <th>ville</th>
        </tr>
    </thead>
    <tbody>
    ";

    foreach ($datas as $row) {
        if ($row['status'] === 'accepted') {
            $statut = "<span class='photo-status accepted'>En ligne</span>";
        } else {
            $statut = "<span class='photo-status pending'>En attente de modération</span>";
        }

        $credit = ($row['credit'] == 1) ? 'oui' : 'non';

        $html_mes_photos .= "
        <tr>
            <td>" . htmlspecialchars($row['inv_name']) . "</td>
            <td>
                <a href='" . htmlspecialchars($row['photo_path']) . "' target='_blank'>
                    <img class='photo-thumb loading'
                         loading='lazy'
                         src='" . htmlspecialchars($row['photo_path']) . "'
                         alt='Photo de " . htmlspecialchars($row['inv_name']) . "'
                         onload='handleImageLoad(this)'
                         onerror='handleImageError(this)'>
                </a>
            </td>
            <td>" . $statut . "</td>
            <td>" . $credit . "</td>
            <td>" . htmlspecialchars($row['upload_date']) . "</td>
            <td>" . htmlspecialchars($row['validation_date']) . "</td>
            <td>" . htmlspecialchars($row['points']) . "</td>
            <td>" . htmlspecialchars($row['ville']) . "</td>
        </tr>
        ";
    }

    $html_mes_photos .= "</tbody></table>";

    echo $html_mes_photos;
}
else
{
	echo 'Vous n\'avez pas encore mis de photo en ligne.';
}

?>
