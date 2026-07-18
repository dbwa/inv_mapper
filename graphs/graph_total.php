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


function data_pie_tot($username){
    $pdo = connect();
	$query = "
	        SELECT 
			    CASE 
			        WHEN et.etat IN ('Détruit !', 'Très dégradé', 'Non visible') THEN '1-Flashés Détruits'
			        ELSE '2-Flashés OKAY'
			    END AS src,
			    COUNT(uf.inv_name) AS nombre 
			FROM 
			    user_flash uf 
			    JOIN etat et ON uf.inv_name = et.inv_name 
			WHERE 
			    uf.user_name = ?
			GROUP BY 
			    src
			UNION 
			SELECT 
			    CASE 
			        WHEN et.etat IN ('Détruit !', 'Très dégradé', 'Non visible') THEN '4-Non flashés Détruits'
			        ELSE '3-Non flashés OKAY'
			    END AS src,
			    COUNT(et.inv_name) AS nombre 
			FROM 
			    etat et 
			WHERE 
			    et.inv_name NOT IN (
			        SELECT 
			            inv_name 
			        FROM 
			            user_flash 
			        WHERE 
			            user_name = ?
			    )
			GROUP BY 
			    src 
			ORDER BY 
			    src;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $username]);
    $result = $stmt->fetchAll();
    return $result;
}



$datas = data_pie_tot($username);


if ($datas != null){
    $data = "";
    $labels = "";
    foreach ($datas as $d) {
        $labels .= "'" . addslashes(  
        		substr($d['src'], 2)
        	) . "',";
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