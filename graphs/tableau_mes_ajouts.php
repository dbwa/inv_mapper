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
                etat.image1 AS image
            FROM positions uf 
            left JOIN etat ON uf.inv_name = etat.inv_name
            WHERE uf.photo = ?
            ORDER BY 1;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_positions($username);

if ($datas != null){
    
$html = "<div style='overflow: auto; max-height: 250px'>
    <table id='tablepositionuser'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>image</th>
            <th>points</th>
            <th>lon</th>
            <th>lat</th>
            <th>etat</th>
            <th>action</th>
        </tr>
    </thead>
    <tbody>
    ";

    
foreach ($datas as $row) {

   $html .= "    
        <tr>
            <td>" . addslashes($row['inv_name']) . "</td>
            <td><img src ='/img_invader/". addslashes($row['image'])  ."' height=25px></td>
            <td>" . addslashes($row['points'])   . "</td>
            <td>" . addslashes($row['lon'])   . "</td>
            <td>" . addslashes($row['lat'])   . "</td>
            <td>" . addslashes($row['etat'])     . "</td>
            <td><input id='del_position_". addslashes($row['inv_name']) ."' class='btn btn-dark' value='Supprimer' onclick=delete_position_invader_base('". addslashes($row['inv_name']) ."') readonly data-toggle='tooltip' title='Pour des raisons de sécurité, ne sera pas supprimé des bases, mais seulement de la carte'/></td>
        </tr>
        ";
    }


$html .= "</tbody>  </table> </div>";



$tableau = "<script type=\"text/javascript\">
		$('#tablepositionuser').html('". addslashes($html) ."');
	</script>";

echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $tableau);
}
else
{
	echo 'Les utilisateurs de confiance peuvent utiliser la carte pour ajouter des invaders manquants';
}

?>
