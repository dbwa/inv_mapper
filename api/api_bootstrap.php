<?php
/**
 * Bootstrap de l'API Invaders Mapper.
 *
 * Ce fichier est inclus par tous les endpoints de l'API. Il s'occupe de :
 *   - charger la configuration et les fonctions du site
 *   - envoyer les entetes HTTP (JSON, CORS)
 *   - authentifier l'appel via une cle API (header Authorization: Bearer ...)
 *   - fournir des helpers de reponse JSON et de lecture de la requete
 *
 * Aucune session PHP n'est utilisee : l'API est stateless.
 */

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

// Mettre a false en production pour ne pas exposer les erreurs SQL.
define('API_DEBUG', false);

// Nombre maximum de requetes par heure et par cle API (0 = pas de limite).
define('API_RATE_LIMIT', 600);

// Domaines autorises a appeler l'API depuis un navigateur.
// '*' = tout le monde. Mettre l'URL du site pour restreindre.
define('API_CORS_ORIGIN', '*');

// ---------------------------------------------------------------------------
// Chargement du socle du site
// ---------------------------------------------------------------------------

if (API_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');

// ---------------------------------------------------------------------------
// Entetes HTTP
// ---------------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . API_CORS_ORIGIN);
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('X-Content-Type-Options: nosniff');

// Requete preflight CORS : on repond et on s'arrete.
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------------------------------------------------------------------
// Helpers de reponse
// ---------------------------------------------------------------------------

/**
 * Envoie une reponse JSON et termine le script.
 */
function api_respond($data, $http_code = 200)
{
    http_response_code($http_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Envoie une erreur JSON normalisee et termine le script.
 */
function api_error($http_code, $code, $message, $details = null)
{
    $body = array(
        'error' => array(
            'code' => $code,
            'message' => $message,
        ),
    );
    if ($details !== null) {
        $body['error']['details'] = $details;
    }
    api_respond($body, $http_code);
}

/**
 * Reponse de succes normalisee.
 */
function api_success($data, $http_code = 200)
{
    api_respond(array('data' => $data), $http_code);
}

// ---------------------------------------------------------------------------
// Helpers de requete
// ---------------------------------------------------------------------------

/**
 * Retourne le corps de la requete decode (JSON, ou formulaire en secours).
 */
function api_input()
{
    static $input = null;
    if ($input !== null) {
        return $input;
    }

    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        $decoded = $_POST;
    }

    $input = is_array($decoded) ? $decoded : array();
    return $input;
}

/**
 * Retourne une valeur du corps de la requete, ou du query string en secours.
 */
function api_param($key, $default = null)
{
    $input = api_input();
    if (isset($input[$key]) && $input[$key] !== '') {
        return $input[$key];
    }
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        return $_GET[$key];
    }
    return $default;
}

/**
 * Verifie que la methode HTTP fait partie de celles autorisees.
 */
function api_require_method($methods)
{
    $methods = (array) $methods;
    $current = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

    if (!in_array($current, $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        api_error(405, 'method_not_allowed', 'Methode ' . $current . ' non autorisee sur cette ressource.');
    }
}

// ---------------------------------------------------------------------------
// Authentification par cle API
// ---------------------------------------------------------------------------

/**
 * Extrait la cle API presentee dans la requete.
 *
 * Formats acceptes :
 *   - Authorization: Bearer inv_xxxxxxxx
 *   - Authorization: inv_xxxxxxxx
 *   - ?api_key=inv_xxxxxxxx  (pratique pour un navigateur ou un outil simple)
 */
function api_extract_key()
{
    $header = null;

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $header = $value;
                break;
            }
        }
    }

    if (!empty($header)) {
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return trim($header);
    }

    if (!empty($_GET['api_key'])) {
        return trim($_GET['api_key']);
    }

    return null;
}

/**
 * Authentifie la requete et retourne la ligne de la cle API.
 * Termine le script avec une erreur 401 si l'authentification echoue.
 */
function api_authenticate()
{
    static $key_row = null;
    if ($key_row !== null) {
        return $key_row;
    }

    $key = api_extract_key();

    if (empty($key)) {
        header('WWW-Authenticate: Bearer realm="Invaders Mapper API"');
        api_error(
            401,
            'missing_api_key',
            "Cle API manquante. Envoyez le header 'Authorization: Bearer <votre_cle>'."
        );
    }

    $pdo = connect();
    $query = "SELECT id, user_name, label, last_used_at, request_count, window_started_at
              FROM api_keys
              WHERE key_hash = ? AND revoked_at IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->execute([hash('sha256', $key)]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        header('WWW-Authenticate: Bearer realm="Invaders Mapper API"');
        api_error(401, 'invalid_api_key', 'Cle API invalide ou revoquee.');
    }

    // Limitation du nombre de requetes (fenetre glissante d'une heure).
    if (API_RATE_LIMIT > 0) {
        $window_start = $row['window_started_at'];
        $count = (int) $row['request_count'];

        if (empty($window_start) || strtotime($window_start) < time() - 3600) {
            $count = 0;
            $window_start = date('Y-m-d H:i:s');
        }

        if ($count >= API_RATE_LIMIT) {
            header('Retry-After: 3600');
            api_error(
                429,
                'rate_limit_exceeded',
                'Trop de requetes. Limite de ' . API_RATE_LIMIT . ' requetes par heure atteinte.'
            );
        }

        $query = "UPDATE api_keys
                  SET request_count = ?, window_started_at = ?, last_used_at = CURRENT_TIMESTAMP
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$count + 1, $window_start, $row['id']]);
    } else {
        $query = "UPDATE api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$row['id']]);
    }

    $key_row = $row;
    return $key_row;
}

/**
 * Retourne le login de l'utilisateur proprietaire de la cle API.
 * C'est ce login qui est utilise partout a la place de $_SESSION['login_name'].
 */
function api_user()
{
    $row = api_authenticate();
    return $row['user_name'];
}

/**
 * Verifie que l'utilisateur authentifie existe toujours et est actif.
 */
function api_user_exists($login)
{
    $pdo = connect();
    $query = "SELECT login, name, user_type FROM users WHERE login = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$login]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---------------------------------------------------------------------------
// Helpers metier
// ---------------------------------------------------------------------------

/**
 * Verifie qu'un invader existe dans la table etat.
 */
function api_invader_exists($inv_name)
{
    $pdo = connect();
    $query = "SELECT inv_name FROM etat WHERE inv_name = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$inv_name]);
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retourne l'etat effectif d'un invader pour un utilisateur donne
 * (etat personnalise s'il existe, sinon etat global).
 */
function api_invader_state($inv_name, $login)
{
    $pdo = connect();
    $query = "SELECT pos.inv_name, pos.lat, pos.lon, et.points,
                     COALESCE(uf2.etat, et.etat) AS etat,
                     et.last_maj,
                     CASE WHEN uf3.inv_name IS NULL THEN 0 ELSE 1 END AS flashed
              FROM positions AS pos
              LEFT JOIN etat AS et ON et.inv_name = pos.inv_name
              LEFT JOIN modif_state_user AS uf2
                     ON uf2.inv_name = pos.inv_name AND uf2.user_name = ?
              LEFT JOIN user_flash AS uf3
                     ON uf3.inv_name = pos.inv_name AND uf3.user_name = ? AND uf3.status = 'flash'
              WHERE pos.inv_name = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$login, $login, $inv_name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retourne la liste des etats connus (pour la validation des entrees).
 */
function api_known_states()
{
    $pdo = connect();
    $query = "SELECT DISTINCT etat FROM etat WHERE etat IS NOT NULL AND etat <> ''";
    $stmt = $pdo->query($query);
    $states = array();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $state) {
        $states[] = $state;
    }
    return $states;
}

/**
 * Construit la reponse GeoJSON d'une categorie pour un utilisateur donne.
 *
 * Les fonctions get_geojson_*() du site s'appuient sur $_SESSION['login_name'].
 * On positionne donc cette variable le temps de l'appel, puis on la restaure.
 */
function api_geojson_for($category, $login)
{
    $previous = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : null;
    $_SESSION['login_name'] = $login;

    switch ($category) {
        case 'a_flasher':
            $geojson = get_geojson_a_flasher();
            break;
        case 'deja_flashe':
            $geojson = get_geojson_deja_flashe();
            break;
        case 'detruits':
            $geojson = get_geojson_detruits();
            break;
        default:
            $geojson = null;
    }

    if ($previous === null) {
        unset($_SESSION['login_name']);
    } else {
        $_SESSION['login_name'] = $previous;
    }

    return $geojson;
}

/**
 * Retourne les trois categories d'un coup, decodees en tableaux PHP.
 */
function api_all_layers($login)
{
    $layers = array();
    foreach (array('a_flasher', 'deja_flashe', 'detruits') as $category) {
        $raw = api_geojson_for($category, $login);
        $decoded = json_decode($raw, true);
        $layers[$category] = is_array($decoded)
            ? $decoded
            : array('type' => 'FeatureCollection', 'features' => array());
    }
    return $layers;
}

/**
 * Genere une nouvelle cle API au format inv_<64 caracteres hexa>.
 */
function api_generate_key()
{
    return 'inv_' . bin2hex(random_bytes(32));
}
