<?php
// utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//session_start();
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
connect();
session_start();
// Fonction pour extraire les métadonnées EXIF d'une image
function extractExifData($image_path) {
    if (function_exists('exif_read_data') && file_exists($image_path)) {
        $exif = @exif_read_data($image_path, 'ANY_TAG', true);
        if ($exif) {
            return json_encode($exif);
        }
    }
    return null;
}

// Récupération des données POST
$uid = isset($_POST['uid']) ? $_POST['uid'] : '';
$urls_datas = json_encode($_POST);
$entityBody = json_encode($_SERVER); // Détails de la requête HTTP (comme les headers)
$cookies = json_encode($_COOKIE); // Cookies envoyés

// En-têtes HTTP complets
$headers = json_encode(getallheaders()); // Récupère tous les en-têtes HTTP reçus

// Initialisation de la variable pour stocker les données d'image
$image_data = null;
$image_exif = null;
$image_info = null;

// Vérification si un fichier image a été uploadé
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];
    $file_type = $_FILES['image']['type'];
    
    // Conversion de l'image en hexadécimal
    $image_data = bin2hex(file_get_contents($tmp_name));
    
    // Extraction des métadonnées EXIF (si disponible)
    $image_exif = extractExifData($tmp_name);

    // Informations sur l'image (type, taille)
    $image_info = json_encode([
        'name' => $file_name,
        'type' => $file_type,
        'size' => $file_size
    ]);
}

// Fonction d'insertion des données dans la base de données
function insert_uid($uid, $urls_datas, $entityBody, $image_data, $image_exif, $image_info, $headers, $cookies) {
    $pdo = connect();
    $query = "INSERT INTO uid_table (uid, url_datas, body_datas, image_data, image_exif, image_info, headers, cookies) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$uid, $urls_datas, $entityBody, $image_data, $image_exif, $image_info, $headers, $cookies]);
}

// Insertion des données capturées dans la base de données
insert_uid($uid, $urls_datas, $entityBody, $image_data, $image_exif, $image_info, $headers, $cookies);

?>
{"player": {"name": "ELEDBO", "email": "eledbo@yopmail.com", "hide_pseudo": 0, "score": 0, "invader_count": 0}, "cities": {}, "code": 0, "message": "HOO NOOON\nFLUTE!"}