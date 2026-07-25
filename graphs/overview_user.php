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



function stats_user($username)
{
    $pdo = connect();
    $query = "
        select 1 as order_output , 'Nombre de flash' as key_output, count(*) as value_output from user_flash 
        WHERE user_name = ? 
        union ALL 
        SELECT 2 AS order_output, 'Nombre de villes visitées' AS key_output, COUNT(DISTINCT SUBSTRING_INDEX(msu.inv_name, '_', 1)) AS value_output FROM user_flash msu
        WHERE msu.user_name = ?
        union all 
        SELECT 3 AS order_output, 'Nombre de pays visités' AS key_output, COUNT(DISTINCT fr_pays) AS value_output FROM user_flash msu join villes on (SUBSTRING_INDEX(msu.inv_name, '_', 1) = villes.short_name )
        WHERE msu.user_name = ?
        order by 1
;";
        
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $username, $username]);
    $result = $stmt->fetchAll();
    return $result;
}


$datas = stats_user($username);

if ($datas != null){
    
    // Ajouter le style CSS personnalisé
    echo "
    <style>
        .stat-card {
            background: rgba(30, 30, 30, 0.6);
            border-radius: 12px;
            padding: 20px;
            margin: 10px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background: rgba(40, 40, 40, 0.8);
        }
        
        .stat-card .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .stat-card .stat-value {
            font-size: 2.2rem;
            font-weight: 600;
            margin: 0;
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 600px) {
            .stat-card {
                margin: 5px 0;
            }
            .stat-card .stat-value {
                font-size: 1.8rem;
            }
        }
    </style>";

    echo "<div class='row' style='margin-top: 20px'>";
    
    foreach ($datas as $row) {
        echo "    
            <div class='col s12 m4' data-aos='fade-up'>
                <div class='stat-card'>
                    <span class='stat-label'>". htmlspecialchars($row['key_output'])."</span>
                    <h5 class='stat-value'>". htmlspecialchars($row['value_output'])."</h5>
                </div>
            </div>
        ";
    }

    echo "</div>";
}
else
{
    echo htmlspecialchars($username);
}

?>