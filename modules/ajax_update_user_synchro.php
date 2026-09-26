<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/../fonctions.inc.php");
connect();

// On récupère les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);

// Action modifiant des donnees : jeton CSRF obligatoire.
csrf_check($data['csrf_token'] ?? '');
if (isset($data['uid_flashinvader']) && isset($data['game_name']) && isset($_SESSION['login_user'])) {
    list($success, $message) = update_user_synchro($_SESSION['login_user'], $data['uid_flashinvader'], $data['game_name']);
    
    if ($success) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
}
?>