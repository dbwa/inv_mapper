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



function data_pie_tot($username)
{
    $query = "
    select 
		case 
		when et.etat in ('Détruit !', 'Très dégradé', 'Non visible') then '1-Flashés Détruits'
		else '2-Flashés OKAY' end as src,

		count(uf.inv_name) as nombre from user_flash uf
		join etat et on (uf.inv_name = et.inv_name)
		where user_name = $1 
		group by 1

	union 

	select 
		case 
		when et.etat in ('Détruit !', 'Très dégradé', 'Non visible') then '4-Non flashés Détruits'
		else '3-Non flashés OKAY' end as src,
		count(et.inv_name) as nombre from etat et 
		where inv_name not in (select inv_name from user_flash where user_name = $1)
		group by 1 order by 1
    ";

    $params = array($username);

    $res = pg_query_params($query, $params);
    $data = pg_fetch_all($res);
    return $data;
}

$datas = data_pie_tot($username);


if ($datas != null){
    $data = "";
    $labels = "";
    foreach ($datas as $d) {
        $labels .= "'" . addslashes(  substr($d['src'], 2)) . "',";
        $data .= addslashes($d['nombre']) . ",";

    }
    $data = substr($data, 0, -1);    //pour enlever derniÃ¨re virgule
    $labels = substr($labels, 0, -1);    //pour enlever derniÃ¨re virgule



	$graph = "
	<script type=\"text/javascript\">
		var datatot = {
		  labels: [". $labels ."],
		  series: [". $data ."]
		};

		var charpie = Chartist.Pie('#pie_chart_total', datatot);
	</script>
	";



	echo $graph;
}

?>