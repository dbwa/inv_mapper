<?php

//*********
// GÃ©nÃ©ration de chart pie pour voir la proportion de flash de l'utilisateur
//*********

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();

// RÃ©cupÃ©ration des variables POST
$username = $_SESSION['login_name'];


function data_flashs($username)
{
    $pdo = connect();
	$query = "
	        SELECT 
                uf.inv_name AS inv_name,
                etat.points AS points,
                etat.etat AS etat,
                etat.image1 AS image,
                uf.date_flash as dateflash,
                villes.fr_ville as ville
            FROM user_flash uf 
            JOIN etat ON uf.inv_name = etat.inv_name
            join villes ON SUBSTRING_INDEX(uf.inv_name, '_', 1) = villes.short_name
            WHERE uf.user_name = ?
            ORDER BY 5 DESC, 1;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_flashs($username);

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
    </style>
    <script>
        // Fonction pour gérer le chargement des images
        function handleImageLoad(img) {
            img.classList.remove('loading');
        }
        
        // Fonction pour gérer les erreurs de chargement
        function handleImageError(img) {
            img.classList.remove('loading');
            img.src = '/img/placeholder.png'; // Image par défaut en cas d'erreur
        }
    </script>
    ";

    $html_tableau_flash = "
    <table id='userFalshtable' class='striped'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>image</th>
            <th>points</th>
            <th>etat</th>
            <th>date</th>
            <th>ville</th>
            <th>action</th>
        </tr>
    </thead>
    <tbody>
    ";
    
    foreach ($datas as $row) {
        $html_tableau_flash .= "    
        <tr>
            <td>" . htmlspecialchars($row['inv_name']) . "</td>
            <td>
                <div class='inv-thumb-container'>
                    <img class='inv-thumb loading' 
                         loading='lazy' 
                         src='/img_invader/" . htmlspecialchars($row['image']) . "' 
                         alt='Invader " . htmlspecialchars($row['inv_name']) . "'
                         onload='handleImageLoad(this)'
                         onerror='handleImageError(this)'>
                </div>
            </td>
            <td>" . htmlspecialchars($row['points']) . "</td>
            <td>" . htmlspecialchars($row['etat']) . "</td>
            <td>" . htmlspecialchars($row['dateflash']) . "</td>
            <td>" . htmlspecialchars($row['ville']) . "</td>
            <td><input type='button' id='del_flash_" . htmlspecialchars($row['inv_name']) . "' 
                       class='btn-small' value='Supprimer le flash' 
                       onclick='click_to_NON_flash(" . json_encode($row['inv_name']) . ")' /></td>
        </tr>
        ";
    }

    $html_tableau_flash .= "</tbody></table>";

    echo $html_tableau_flash;
}
else {
    echo htmlspecialchars($username);
}

?>
