<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
include_once(__DIR__ . '/../../fonctions.inc.php');
include_once(__DIR__ . '/../../config.php');

header('Content-Type: application/json');

try {
    // Action modifiant des donnees : jeton CSRF obligatoire.
    csrf_check();

    // Vérification des droits de modération
    if (
        !isset($_SESSION['login_user']) ||
        !isset($_SESSION['user_type']) ||
        $_SESSION['user_type'] !== 'admin'
    ) {
        throw new Exception('Accès non autorisé');
    }

    if (!isset($_POST['photo_id']) || !isset($_POST['status'])) {
        throw new Exception('Données manquantes');
    }

    $photo_id = intval($_POST['photo_id']);
    $status = $_POST['status'];
    
    // Validation du status
    if (!in_array($status, ['accepted', 'rejected', 'pending'])) {
        throw new Exception('Status invalide');
    }

    if (update_photo_status($photo_id, $status, $_SESSION['login_user'])) {
        $response = [
            'success' => true,
            'message' => 'Status mis à jour avec succès'
        ];
    } else {
        throw new Exception('Erreur lors de la mise à jour');
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
exit();
?>