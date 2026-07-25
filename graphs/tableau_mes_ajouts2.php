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



function data_positions($username)
{
    $pdo = connect();
	$query = "
	        SELECT 
                uf.inv_name AS inv_name,
                uf.lon,
                uf.lat,
                etat.points AS points,
                etat.etat AS etat,
                etat.image1 AS image,
                villes.fr_ville as ville
            FROM positions uf 
            left JOIN etat ON uf.inv_name = etat.inv_name
            join villes ON SUBSTRING_INDEX(etat.inv_name, '_', 1) = villes.short_name
            WHERE uf.photo = ?
            ORDER BY 1;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_positions($username);

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
        function handleImageLoad(img) {
            img.classList.remove('loading');
        }
        
        function handleImageError(img) {
            img.classList.remove('loading');
            img.src = '/img/placeholder.png';
        }
    </script>
    ";

    $html_contrib = "
    <table id='tablepositionuser' class='striped'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>image</th>
            <th>points</th>
            <th>lon</th>
            <th>lat</th>
            <th>etat</th>
            <th>ville</th>
            <th>action</th>
        </tr>
    </thead>
    <tbody>
    ";
    
    foreach ($datas as $row) {
        $html_contrib .= "    
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
            <td>" . htmlspecialchars($row['lon']) . "</td>
            <td>" . htmlspecialchars($row['lat']) . "</td>
            <td>" . htmlspecialchars($row['etat']) . "</td>
            <td>" . htmlspecialchars($row['ville']) . "</td>
            <td>
                <input type='button' 
                       class='btn btn-dark' 
                       value='Supprimer' 
                       onclick='delete_position_invader_base(" . json_encode($row['inv_name']) . ")'
                       data-toggle='tooltip' 
                       title='Pour des raisons de sécurité, ne sera pas supprimé des bases, mais seulement de la carte'>
            </td>
        </tr>
        ";
    }

    $html_contrib .= "</tbody></table>";

    echo $html_contrib;
}
else
{
	echo 'Les utilisateurs de confiance peuvent utiliser la carte pour ajouter des invaders manquants';
}

?>