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
	$query = "select 
            msu.inv_name as inv_name,
	        msu.etat as etat,
	        --et.last_maj,
	        et.etat as etat_officiel  --,
	        --et.image2,
	        --et.image3
	        from modif_state_user msu
            join etat as et on ( msu.inv_name = et.inv_name)
	        where msu.user_name = $1
             order by 1;
             ";
	    
	$params = array($username);
	$res = pg_query_params($query, $params);
	$data = pg_fetch_all($res);
    return $data;
}

$datas = data_flashs_for_tab($username);

if ($datas != null){
    
$html = "<div style='overflow: auto; max-height: 250px'>
    <table id='tableetat'>
    <thead>
        <tr>
            <th>inv name</th>
            <th>etat</th>
            <th>etat officiel</th>
            <th>action</th>

        </tr>
    </thead>
    <tbody>
    ";

    
foreach ($datas as $row) {

   $html .= "    
        <tr>
            <td>" . addslashes($row['inv_name']) . "</td>
            <td>" . addslashes($row['etat'])   . "</td>
            <td>" . addslashes($row['etat_officiel'])     . "</td>
            <td>

            <input name='to_reactive' class='btn btn-primary' value='Passer en OKAY' onclick=click_to_reactive('" . addslashes($row['inv_name']). "') readonly />
            <input name='to_detruit' class='btn btn-warning' value='Passer en détruit' onclick=click_to_detruit('" . addslashes($row['inv_name']). "') readonly />

            </td>
        </tr>
        ";
    }

$html .= "</tbody>  </table> </div>";


$tableau = "<script type=\"text/javascript\">
		$('#tableetatuser').html('". addslashes($html) ."');
	</script>";

echo preg_replace('/^\s+|\n|\r|\s+$/m', '', $tableau);
}
else
{
	echo $username;
}

?>












