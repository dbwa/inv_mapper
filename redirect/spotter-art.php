<?php
$ville = $_GET['ville'] ?? '';
$numero = $_GET['numero'] ?? '';

// 1. Créer un fichier temporaire pour stocker les cookies
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');

// 2. Simuler la visite initiale pour obtenir le cookie de session
$ch = curl_init('https://www.invader-spotter.art/news.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_exec($ch);

// 3. Préparer les données POST avec le nom dynamique
$postData = $ville . '=&numero=' . $numero . '&mode=si';

// 4. Envoyer la requête POST avec le cookie et les bons en-têtes
$ch = curl_init('https://www.invader-spotter.art/listing.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.invader-spotter.art/news.php');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: https://www.invader-spotter.art',
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.7',
    'Cache-Control: no-cache',
    'DNT: 1',
    'Pragma: no-cache',
    'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: same-origin',
    'Upgrade-Insecure-Requests: 1'
]);

$response = curl_exec($ch);

// 5. Nettoyer le fichier de cookies
unlink($cookieFile);

// 6. Corriger les URLs relatives dans la réponse
$base = '<base href="https://www.invader-spotter.art/">';
$response = str_replace('<head>', '<head>' . $base, $response);

// 7. Afficher la page
echo $response;
?>