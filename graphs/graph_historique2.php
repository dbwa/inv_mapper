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
			WITH liste_ville AS (
			    SELECT DISTINCT
			        SUBSTRING_INDEX(uf.inv_name, '_', 1) AS short_name,
			        villes.fr_ville AS ville
			    FROM
			        user_flash uf
			    LEFT JOIN
			        villes ON SUBSTRING_INDEX(uf.inv_name, '_', 1) = villes.short_name
			    WHERE
			        user_name = ?
			),

			liste_date AS (
			    SELECT DISTINCT
			        date_flash
			    FROM
			        user_flash
			    WHERE
			        user_name = ?
			),

			ville_date AS (
			    SELECT *
			    FROM liste_ville
			    CROSS JOIN liste_date
			),

			user_flash_count AS (
			    SELECT
			        villes.fr_ville AS ville,
			        uf.date_flash,
			        COUNT(uf.user_name) AS ctt
			    FROM
			        user_flash uf
			    LEFT JOIN
			        villes ON SUBSTRING_INDEX(uf.inv_name, '_', 1) = villes.short_name
			    WHERE
			        user_name = ?
			    GROUP BY
			        1, 2
			),

			compte AS (
			    SELECT
			        ville_date.date_flash,
			        ville_date.ville,
			        COALESCE(uf.ctt, 0) AS flash_count
			    FROM
			        ville_date
			    LEFT JOIN
			        user_flash_count uf ON uf.ville = ville_date.ville AND uf.date_flash = ville_date.date_flash
			    ORDER BY
			        1, 2
			)

			SELECT
			    c.date_flash,
			    c.ville,
			    SUM(c.flash_count) OVER (PARTITION BY c.ville ORDER BY c.date_flash ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS flash_count_cumul
			FROM
			    compte c
			ORDER BY
    2,1

	        ;";
	    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $username, $username]);
    $result = $stmt->fetchAll();
    return $result;
}



$datas = data_histo_flash($username);

if ($datas != null) {
    $result = array(); 
    $colors = array(
        'rgba(54, 162, 235, 0.7)',   // Bleu
        'rgba(255, 99, 132, 0.7)',   // Rose
        'rgba(75, 192, 192, 0.7)',   // Turquoise
        'rgba(255, 159, 64, 0.7)',   // Orange
        'rgba(153, 102, 255, 0.7)',  // Violet
        'rgba(255, 205, 86, 0.7)',   // Jaune
        'rgba(201, 203, 207, 0.7)',  // Gris
        'rgba(100, 255, 218, 0.7)',  // Menthe
        'rgba(255, 127, 80, 0.7)',   // Corail
        'rgba(147, 112, 219, 0.7)',  // Violet moyen
        'rgba(64, 224, 208, 0.7)',   // Turquoise vif
        'rgba(255, 182, 193, 0.7)',  // Rose clair
        'rgba(135, 206, 235, 0.7)',  // Bleu ciel
        'rgba(152, 251, 152, 0.7)',  // Vert pâle
        'rgba(238, 130, 238, 0.7)'   // Violet clair
    );
    $colorIndex = 0; 
    $groupedData = array();

    foreach ($datas as $d) {
        $ville = $d['ville'];
        
        if (!isset($groupedData[$ville])) {
            $baseColor = $colors[$colorIndex];
            $groupedData[$ville] = array(
                'label' => $ville,
                'data' => array(),
                'backgroundColor' => $baseColor,
                'borderColor' => str_replace(', 0.7)', ', 1)', $baseColor),
                'borderWidth' => 2,
                'pointRadius' => 0, 
                'pointHoverRadius' => 0, 
                'tension' => 0.3, 
                'fill' => 'stack'
            );
            $colorIndex = ($colorIndex + 1) % count($colors);
        }

        $groupedData[$ville]['data'][] = array(
            'x' => $d['date_flash'],
            'y' => (int)$d['flash_count_cumul']
        );
    }

    foreach ($groupedData as $villeData) {
        $result[] = $villeData;
    }

    $jsonResult = json_encode($result, JSON_NUMERIC_CHECK);
}

$graph = "
<script type=\"text/javascript\">
    var ctx = document.getElementById('historyChart').getContext('2d');
    var historyChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: $jsonResult
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'nearest'
            },
            scales: {
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#e0e0e0',
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return value + ' flash' + (value > 1 ? 's' : '');
                        }
                    }
                },
                x: {
                    type: 'time',
                    time: {
                        unit: 'month',
                        tooltipFormat: 'DD MMM YYYY',
                        displayFormats: {
                            day: 'DD MMM',
                            month: 'MMM YYYY'
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#e0e0e0',
                        font: {
                            size: 12
                        },
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 8
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#e0e0e0',
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 13
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' flashes';
                        }
                    }
                },
                filler: {
                    propagate: true
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
</script>";
echo $graph;

?>