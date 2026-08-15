<?php
// Affichage des erreurs désactivé côté client, mais log côté serveur
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Démarrage du buffer de sortie
ob_start();

include_once(__DIR__ . '/../../fonctions.inc.php');
include_once(__DIR__ . '/../../config.php');
$pdo = connect();
session_start();

// Nettoyage du buffer avant d'envoyer les headers
ob_clean();
header('Content-Type: application/json');

try {
    // Vérification de la connexion utilisateur
    if (!isset($_SESSION['login_user'])) {
        throw new Exception('Utilisateur non connecté');
    }

    // Debug : log des données reçues
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));

    // Vérification des données reçues
    if (!isset($_POST['invader']) || !isset($_FILES['photo'])) {
        throw new Exception('Données manquantes');
    }

    $inv_name = $_POST['invader'];
    $credit = isset($_POST['showCredit']) ? 1 : 0;
    $login = $_SESSION['login_user'];

    // Récupération de l'id_user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    if (!$user) {
        throw new Exception('Utilisateur non trouvé');
    }
    $id_user = $user['id'];

    // Configuration de l'upload
    $upload_dir = __DIR__ . '/../../img/user_img/';
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        throw new Exception('Dossier d\'upload inaccessible');
    }

    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    
    
    if ($file_extension === 'heic') {
        throw new Exception('Le format HEIC n\'est pas supporté. Merci de choisir JPEG ou PNG dans les réglages de votre appareil photo.');
    }
        
    
    
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Type de fichier non autorisé');
    }

    // Création d'un nom de fichier unique
$filename = $inv_name . '_' . time() . '_' . uniqid() . '.webp';
$filepath = $upload_dir . $filename;
    
    
    // Création de l'image source selon le type
$source_image = null;
switch($file_extension) {
    case 'jpg':
    case 'jpeg':
        $source_image = imagecreatefromjpeg($_FILES['photo']['tmp_name']);
        break;
    case 'png':
        $source_image = imagecreatefrompng($_FILES['photo']['tmp_name']);
        break;
    case 'webp':
        $source_image = imagecreatefromwebp($_FILES['photo']['tmp_name']);
        break;
}
 
if (!$source_image) {
    throw new Exception('Erreur lors de la lecture de l\'image');
}

//pour corriger l'orientation si ca vient d'un smartphone
if (in_array($file_extension, ['jpg', 'jpeg'])) {
    $exif = @exif_read_data($_FILES['photo']['tmp_name']);
    if (!empty($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 3:
                $source_image = imagerotate($source_image, 180, 0);
                break;
            case 6:
                $source_image = imagerotate($source_image, -90, 0);
                break;
            case 8:
                $source_image = imagerotate($source_image, 90, 0);
                break;
        }
    }
}


// Conversion et sauvegarde en WebP
if (!imagewebp($source_image, $filepath, 80)) { // 80 = qualité
    imagedestroy($source_image);
    throw new Exception('Erreur lors de la conversion en WebP');
}

// Libération de la mémoire
imagedestroy($source_image);


    // Enregistrement en base
    $stmt = $pdo->prepare("
        INSERT INTO user_photos 
        (id_user, login, inv_name, photo_path, credit) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $photo_path = '/img/user_img/' . $filename;
    $stmt->execute([$id_user, $login, $inv_name, $photo_path, $credit]);

    // Réponse de succès
    $response = [
        'success' => true,
        'message' => utf8_encode('Photo enregistrée avec succès'),
        'filename' => utf8_encode($filename)
    ];
    
} catch (Exception $e) {
    // Nettoyage en cas d'erreur
    if (isset($filepath) && file_exists($filepath)) {
        unlink($filepath);
    }
    // Log l'erreur
    error_log("Erreur upload photo: " . $e->getMessage());
    // Réponse d'erreur
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Envoi de la réponse JSON
echo json_encode($response);
exit();
?>