<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// session_start() doit etre appele AVANT tout include produisant de la sortie :
// fonctions.inc.php se termine par une balise de fermeture suivie d'un saut
// de ligne, ce qui envoie un octet et faisait echouer la session.
session_start();

include_once(__DIR__ . "/fonctions.inc.php");
connect();

// Plus aucun log du corps de requête : il contient le hash du mot de passe,
// qui équivaut au mot de passe effectif (cf. point 12 de besoin_securite.md).
$input = file_get_contents('php://input');

header('Content-Type: application/json');

// Vérifier si on a une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode($input, true);

    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];
        $remember = isset($data['remember']) ? true : false;

        // Limitation des tentatives (anti brute-force)
        $wait = login_attempt_wait($username);
        if ($wait > 0) {
            echo json_encode([
                'success' => false,
                'message' => "Trop de tentatives de connexion. Réessayez dans $wait secondes."
            ]);
            exit;
        }

        error_log("Tentative de connexion pour l'utilisateur: " . $username);
        $auth_result = authentificate($username, $password, $remember);

        if ($auth_result && is_array($auth_result)) {
            list($count, $row) = $auth_result;
            if ($count == 1) {
                login_attempt_reset($username);
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
        login_attempt_failure($username);
        error_log("Échec de connexion pour: " . $username);
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