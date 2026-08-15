<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once(__DIR__ . '/../../fonctions.inc.php');
include_once(__DIR__ . '/../../config.php');
session_start();

header('Content-Type: application/json');

// Vérification des droits
if (!isset($_SESSION['login_user'])) {  // || !user_can_moderate()) {
    echo json_encode([]);
    exit();
}

// Récupère les photos validées ou rejetées
$photos = get_user_photos_by_invader(null); // On récupère tout
$filtered = [];
foreach ($photos as $inv_name => $data) {
    $filtered_photos = array_filter($data['photos'], function($p) {
        return in_array($p['status'], ['accepted', 'rejected']);
    });
    if (!empty($filtered_photos)) {
        $filtered[$inv_name] = [
            'image_ref' => $data['image_ref'],
            'photos' => array_values($filtered_photos)
        ];
    }
}
echo json_encode($filtered);
exit();
?>