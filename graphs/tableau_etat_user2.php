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



function data_flashs_for_tab($username)
{
    $pdo = connect();
    $query = "
            SELECT msu.inv_name AS inv_name,
                   msu.etat AS etat,
                   etat.etat AS etat_officiel,
                   etat.image1 AS image,
                   villes.fr_ville as ville
            FROM modif_state_user msu
            JOIN etat ON msu.inv_name = etat.inv_name
            join villes ON SUBSTRING_INDEX(msu.inv_name, '_', 1) = villes.short_name
            WHERE msu.user_name = ?
            ORDER BY 1;";
        
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_flashs_for_tab($username);

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
        .action-buttons {
            display: flex;
            gap: 5px;
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

    $html_tableau_etat = "
    <table id='tableetat' class='striped'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>image</th>
            <th>etat</th>
            <th>etat officiel</th>
            <th>ville</th>
            <th>action</th>
        </tr>
    </thead>
    <tbody>
    ";
    
    foreach ($datas as $row) {
        $html_tableau_etat .= "    
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
            <td>" . htmlspecialchars($row['etat']) . "</td>
            <td>" . htmlspecialchars($row['etat_officiel']) . "</td>
            <td>" . htmlspecialchars($row['ville']) . "</td>
            <td>
                <div class='action-buttons'>
                    <input type='button' class='btn-small' value='Passer en OKAY' 
                           onclick='click_to_reactive(" . json_encode($row['inv_name']) . ")'>
                    <input type='button' class='btn-small' value='Passer en détruit' 
                           onclick='click_to_detruit(" . json_encode($row['inv_name']) . ")'>
                </div>
            </td>
        </tr>
        ";
    }

    $html_tableau_etat .= "</tbody></table>";

    echo $html_tableau_etat;
}
else {
    echo htmlspecialchars($username);
}

?>
