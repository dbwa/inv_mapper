<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/fonctions.inc.php");
connect();
session_start();

// Log des données reçues
error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . $_SERVER['CONTENT_TYPE']);
$input = file_get_contents('php://input');
error_log("Données reçues: " . $input);

header('Content-Type: application/json');

// Vérifier si on a une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode($input, true);
    error_log("Données décodées: " . print_r($data, true));
    
    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];
        $remember = isset($data['remember']) ? true : false;
        
        error_log("Tentative de connexion pour l'utilisateur: " . $username);
        $auth_result = authentificate($username, $password, $remember);
        error_log("Résultat auth: " . print_r($auth_result, true));
        
        if ($auth_result && is_array($auth_result)) {
            list($count, $row) = $auth_result;
            if ($count == 1) {
                $_SESSION['login_user'] = $row['login'];
                $_SESSION['login_name'] = $row['name'];
                $_SESSION['user_type'] = $row['user_type'];
                
                echo json_encode([
                    'success' => true,
                    'user' => $row['login']
                ]);
                exit;
            }
        }
        
        // En cas d'échec
        $_SESSION['login_user'] = '';
        $_SESSION['login_name'] = '';
        $_SESSION['user_type'] = '';
        
        echo json_encode([
            'success' => false,
            'message' => "Nom d'utilisateur ou mot de passe incorrect"
        ]);
        exit;
    }
}

// Si on arrive ici, c'est qu'il y a eu une erreur
echo json_encode([
    'success' => false,
    'message' => 'Requête invalide'
]);