<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/../fonctions.inc.php");
connect();

header('Content-Type: application/json');

if (!isset($_SESSION['login_user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Non connecté']);
    exit;
}

// Récupération des données JSON
$data = json_decode(file_get_contents('php://input'), true);

// Action modifiant des donnees : jeton CSRF obligatoire.
csrf_check($data['csrf_token'] ?? '');

if (!$data || !isset($data['invaders'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données invalides']);
    exit;
}

try {
    $result = update_user_flashes($_SESSION['login_user'], $data['invaders']);
    
    if ($result['success']) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Synchronisation réussie',
            'stats' => $result['stats']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $result['message']
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
    ]);
}