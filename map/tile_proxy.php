<?php
// tile_proxy.php

// --- 1. Configuration ---
// Chemin où les tuiles seront mises en cache sur ton serveur
// Assure-toi que ce dossier est accessible en écriture par le serveur web (chmod 0775 ou 0777 si nécessaire)
define('CACHE_DIR', __DIR__ . '/cache/tiles');

// Durée de vie maximale des tuiles en cache (en secondes)
// 7 jours = 604800 secondes
define('CACHE_LIFETIME', 604800);

// Sources des tuiles CARTO
$tileSources = [
    'carto_light'     => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    'carto_dark_blue' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    'carto_classic'   => 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png',
];

// Subdomains utilisés par CARTO
$cartoSubdomains = ['a', 'b', 'c', 'd'];

// --- 2. Récupération et validation des paramètres ---
$layer = $_GET['layer'] ?? null;
$z = $_GET['z'] ?? null;
$x = $_GET['x'] ?? null;
$y = $_GET['y'] ?? null;
$r = $_GET['r'] ?? ''; // Pour les tuiles retina (ex: @2x)

// Vérification des paramètres essentiels
if (!isset($tileSources[$layer]) || !is_numeric($z) || !is_numeric($x) || !is_numeric($y)) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid tile parameters.');
}

$z = (int)$z;
$x = (int)$x;
$y = (int)$y;

// --- 3. Construction du chemin du fichier de cache ---
$cachePath = CACHE_DIR . '/' . $layer . '/' . $z . '/' . $x . '/';
$cacheFile = $cachePath . $y . '.png'; // Les tuiles CARTO sont des PNG

// --- 4. Vérification et service depuis le cache ---
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < CACHE_LIFETIME)) {
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($cacheFile));
    readfile($cacheFile);
    exit;
}

// --- 5. La tuile n'est pas en cache ou est expirée, on la télécharge ---
$sourceUrl = $tileSources[$layer];

// Préparer les remplacements pour l'URL source
$replacements = [
    '{z}' => $z,
    '{x}' => $x,
    '{y}' => $y,
    '{r}' => $r,
];

// Gérer le subdomain {s} si présent dans l'URL source
if (strpos($sourceUrl, '{s}') !== false) {
    $subdomain = $cartoSubdomains[($x + $y) % count($cartoSubdomains)];
    $replacements['{s}'] = $subdomain;
}

// Appliquer tous les remplacements à l'URL source
$sourceUrl = str_replace(array_keys($replacements), array_values($replacements), $sourceUrl);

// --- Utilisation de cURL pour le téléchargement ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sourceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Retourne le transfert sous forme de chaîne
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Suivre les redirections
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 secondes
// Définir un User-Agent pour se faire passer pour un navigateur
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36');

$tileData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

// Vérifier si le téléchargement a réussi (code HTTP 200)
if ($tileData === false || $httpCode !== 200) {
    // Enregistre l'erreur dans les logs du serveur pour le débogage
    error_log("Failed to fetch tile from: " . $sourceUrl . " HTTP Code: " . $httpCode . " cURL Error: " . $curlError);
    header('HTTP/1.1 404 Not Found');
    die('Tile not found or source unreachable.');
}

// --- 6. Sauvegarde de la tuile dans le cache ---
// Créer les répertoires si ils n'existent pas
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0775, true); // true pour créer les répertoires parents récursivement
}

// Sauvegarder le fichier
file_put_contents($cacheFile, $tileData);

// --- 7. Servir la tuile téléchargée ---
header('Content-Type: image/png');
header('Content-Length: ' . strlen($tileData));
echo $tileData;
exit;

?>