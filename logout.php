<?php
include_once(__DIR__ . "/fonctions.inc.php");
session_start();

if (!empty($_SESSION['login_user'])) {
    $username = $_SESSION['login_user'];
    
    // Supprimer uniquement le token de l'appareil actuel
    if (isset($_COOKIE['remember_token'])) {
        $pdo = connect();
        $device_id = generate_device_id();
        $query = "DELETE FROM remember_tokens WHERE username = ? AND device_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username, $device_id]);
        
        // Supprimer le cookie côté client
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    // Vider et détruire la session
    $_SESSION = array();
    session_destroy();
}

// Rediriger vers la page d'accueil
header("Location: index.php");
exit();
?>
