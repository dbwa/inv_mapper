<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
session_start();
include_once(__DIR__ . "/../../fonctions.inc.php");
connect();

header('Content-Type: application/json');

// Vérification de la connexion
if (!isset($_SESSION['login_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Non connecté']);
    exit;
}

try {
    // Récupération des badges à vérifier
    $stmt = $pdo->prepare("SELECT get_badges_to_check(:username) as badges");
    $stmt->execute(['username' => $_SESSION['login_user']]);
    $badges = json_decode($stmt->fetchColumn(), true);

    if (!$badges) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Aucun nouveau badge disponible',
            'badges' => []
        ]);
        exit;
    }

    $badgesGagnes = [];
    
    // Vérification de chaque badge
    foreach ($badges as $badge) {
        // Construction et exécution de la requête de vérification
        $sql = "SELECT " . $badge['fonction'] . "(:username" . 
               ($badge['arguments'] ? ", " . $badge['arguments'] : "") . 
               ") as resultat";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $_SESSION['login_user']]);
        
        if ($stmt->fetchColumn()) {
            // Récupère d'abord l'id_user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login = :username");
            $stmt->execute(['username' => $_SESSION['login_user']]);
            $userId = $stmt->fetchColumn();

            // Débloque le badge avec l'id_user
            $stmt = $pdo->prepare(
                "INSERT INTO user_badges (id_user, id_badge, date_obtention) 
                 VALUES (:id_user, :id_badge, NOW())"
            );
            $stmt->execute([
                'id_user' => $userId,
                'id_badge' => $badge['id_badge']
            ]);
            
            // Ajoute aux badges gagnés pour notification
            $badgesGagnes[] = [
                'id_badge' => $badge['id_badge'],
                'nom_badge' => $badge['nom_badge'],
                'description' => $badge['description'],
                'icone' => $badge['icone']
            ];
        }
    }

    // Retourne le résultat
    echo json_encode([
        'status' => 'success',
        'message' => count($badgesGagnes) > 0 ? 
            'Nouveaux badges débloqués !' : 
            'Aucun nouveau badge débloqué',
        'badges' => $badgesGagnes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la vérification des badges: ' . $e->getMessage()
    ]);
}