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
	$query = "
	        select et.inv_name as inv_name,
	        et.points as points,
	        et.etat as etat,
	        --et.last_maj,
	        et.image1  --,
	        --et.image2,
	        --et.image3
	        from etat et
	        where et.inv_name in (select uf.inv_name from user_flash as uf where uf.status = 'flash' and uf.user_name=$1) order by et.idx;";
	    
	$params = array($username);
	$res = pg_query_params($query, $params);
	$data = pg_fetch_all($res);
    return $data;
}

$datas = data_flashs($username);

if ($datas != null){
    
$html = "<div style='overflow: auto; max-height: 250px'>
    <table id='userFalshtable'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>points</th>
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
            <td>" . addslashes($row['points'])   . "</td>
            <td>" . addslashes($row['etat'])     . "</td>
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












