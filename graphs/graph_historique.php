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


function data_histo_flash($username){
    $pdo = connect();
	$query = "
	        SELECT 
				COALESCE(uf.date_flash, 
					(SELECT date(min(uf2.date_flash)-1) FROM user_flash uf2 WHERE uf2.user_name = uf.user_name))
					as date_flash,
			    COUNT(uf.date_flash) as flash_count,
			    (SELECT COUNT(*) FROM user_flash uf2 WHERE uf2.user_name = uf.user_name AND uf2.date_flash <= uf.date_flash) as cumulative_flash_count
			FROM
			    user_flash uf
			WHERE
			    uf.user_name = ?
			GROUP BY
			    uf.date_flash
			ORDER BY
			    uf.date_flash ASC;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetchAll();
    return $result;
}



$datas = data_histo_flash($username);


if ($datas != null){
    $data = "";
    foreach ($datas as $d) {
        $data .= "{x: new Date('". addslashes($d['date_flash']) . "'),y:" . $d['cumulative_flash_count']."},";
    }
    $data = substr($data, 0, -1);    //pour enlever derniÃ¨re virgule



	$graph = "
	<script type=\"text/javascript\">
		var chart = new Chartist.Line('#chart_histo', {
		  series: [
		    {
		      name: 'Flash cumulés',
		      data: [
		        ".$data."
		      ]
		    }
		  ]
		}
		, {
		  axisX: {gridLines: {
		        zeroLineColor: '#00ff00'
		    },
		    labelInterpolationFnc: function(value) {
		      return moment(value).format('YYYY MMM D');
		    }
		  }
		}
		);
	</script>
	";


	echo $graph;
}

?>