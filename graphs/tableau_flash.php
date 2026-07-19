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
                uf.date_flash as dateflash
            FROM user_flash uf 
            JOIN etat ON uf.inv_name = etat.inv_name
            WHERE uf.user_name = ?
            ORDER BY 5 DESC, 1;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = data_flashs($username);

if ($datas != null){
    
$html = "<div style='overflow: auto; max-height: 250px'>
    <table id='userFalshtable'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>image</th>
            <th>points</th>
            <th>etat</th>
            <th>date</th>
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
            <td>" . addslashes($row['etat'])     . "</td>
            <td>" . addslashes($row['dateflash'])     . "</td>
            <td><input id='del_flash_". addslashes($row['inv_name']) ."' class='btn btn-dark' value='Supprimer le flash' onclick=click_to_NON_flash('". addslashes($row['inv_name']) ."') readonly /></td>
        </tr>
        ";
    }


$html .= "</tbody>  </table> </div>";



$tableau = "<script type=\"text/javascript\">
		$('#tableflash').html('". addslashes($html) ."');
	</script>";

echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $tableau);
}
else
{
	echo $username;
}

?>












