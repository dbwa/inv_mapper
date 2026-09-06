<?php
/**
 * API Invaders Mapper - v1
 *
 * Point d'entree unique. Toutes les routes passent par ce fichier.
 *
 * Routes disponibles :
 *   GET    /api/v1/me
 *   GET    /api/v1/invaders?status=a_flasher|deja_flashe|detruits|all
 *   GET    /api/v1/invaders/{inv_name}
 *   POST   /api/v1/invaders/{inv_name}/flash
 *   DELETE /api/v1/invaders/{inv_name}/flash
 *   PUT    /api/v1/invaders/{inv_name}/status
 *   DELETE /api/v1/invaders/{inv_name}/status
 *   GET    /api/v1/stats
 */

include_once(__DIR__ . '/../api_bootstrap.php');

// ---------------------------------------------------------------------------
// Analyse de la route demandee
// ---------------------------------------------------------------------------

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

// Chemin demande, sans le query string.
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

if ($path === '' && isset($_SERVER['REQUEST_URI'])) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // On retire tout ce qui precede /v1/ (ou /api/v1/) dans l'URL.
    $pos = strpos($path, '/v1/');
    if ($pos !== false) {
        $path = substr($path, $pos + 3);
    } elseif (substr($path, -3) === '/v1') {
        $path = '/';
    }
}

// Nettoyage : on ne garde que les segments non vides.
$segments = array_values(array_filter(explode('/', trim($path, '/')), function ($s) {
    return $s !== '';
}));

$resource = isset($segments[0]) ? $segments[0] : '';
$identifier = isset($segments[1]) ? urldecode($segments[1]) : null;
$action = isset($segments[2]) ? $segments[2] : null;

// ---------------------------------------------------------------------------
// Routes publiques (pas de cle API requise)
// ---------------------------------------------------------------------------

if ($resource === '' || $resource === 'ping') {
    api_require_method('GET');
    api_success(array(
        'api' => 'Invaders Mapper API',
        'version' => 'v1',
        'documentation' => 'https://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . '/settings.php#settings-api',
    ));
}

// ---------------------------------------------------------------------------
// A partir d'ici, une cle API valide est obligatoire
// ---------------------------------------------------------------------------

$login = api_user();

// ---------------------------------------------------------------------------
// GET /v1/me
// ---------------------------------------------------------------------------

if ($resource === 'me') {
    api_require_method('GET');

    $user = api_user_exists($login);
    if (!$user) {
        api_error(401, 'unknown_user', "L'utilisateur associe a cette cle n'existe plus.");
    }

    $key = api_authenticate();

    api_success(array(
        'login' => $user['login'],
        'name' => $user['name'],
        'user_type' => $user['user_type'],
        'api_key_label' => $key['label'],
        'api_key_last_used_at' => $key['last_used_at'],
    ));
}

// ---------------------------------------------------------------------------
// GET /v1/stats
// ---------------------------------------------------------------------------

if ($resource === 'stats') {
    api_require_method('GET');

    $layers = api_all_layers($login);

    api_success(array(
        'a_flasher' => count($layers['a_flasher']['features']),
        'deja_flashe' => count($layers['deja_flashe']['features']),
        'detruits' => count($layers['detruits']['features']),
    ));
}

// ---------------------------------------------------------------------------
// /v1/invaders
// ---------------------------------------------------------------------------

if ($resource === 'invaders') {

    // --- GET /v1/invaders?status=... ---------------------------------------
    if ($identifier === null) {
        api_require_method('GET');

        $status = api_param('status', 'all');
        $allowed = array('a_flasher', 'deja_flashe', 'detruits', 'all');

        if (!in_array($status, $allowed, true)) {
            api_error(
                400,
                'invalid_status',
                "Parametre 'status' invalide.",
                array('allowed' => $allowed)
            );
        }

        if ($status === 'all') {
            api_success(api_all_layers($login));
        }

        $raw = api_geojson_for($status, $login);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            api_error(500, 'geojson_error', 'Impossible de construire la reponse GeoJSON.');
        }

        api_success($decoded);
    }

    // --- /v1/invaders/{inv_name} -------------------------------------------
    if ($action === null) {
        api_require_method('GET');

        $invader = api_invader_state($identifier, $login);
        if (!$invader) {
            api_error(404, 'invader_not_found', "L'invader '" . $identifier . "' n'existe pas.");
        }

        api_success(array(
            'name' => $invader['inv_name'],
            'lat' => $invader['lat'] !== null ? (float) $invader['lat'] : null,
            'lon' => $invader['lon'] !== null ? (float) $invader['lon'] : null,
            'points' => $invader['points'] !== null ? (int) $invader['points'] : null,
            'etat' => $invader['etat'],
            'last_maj' => $invader['last_maj'],
            'flashed' => (bool) $invader['flashed'],
        ));
    }

    // --- /v1/invaders/{inv_name}/flash -------------------------------------
    if ($action === 'flash') {

        if (!api_invader_exists($identifier)) {
            api_error(404, 'invader_not_found', "L'invader '" . $identifier . "' n'existe pas.");
        }

        if ($method === 'POST') {
            // On marque l'invader comme flashe pour CET utilisateur.
            $previous = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : null;
            $_SESSION['login_name'] = $login;
            ajout_flash($identifier);
            if ($previous === null) {
                unset($_SESSION['login_name']);
            } else {
                $_SESSION['login_name'] = $previous;
            }

            $invader = api_invader_state($identifier, $login);
            api_success(array(
                'name' => $identifier,
                'flashed' => true,
                'etat' => $invader ? $invader['etat'] : null,
            ));
        }

        if ($method === 'DELETE') {
            // On retire le flash de CET utilisateur.
            $previous = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : null;
            $_SESSION['login_name'] = $login;
            suppri_flash($identifier);
            if ($previous === null) {
                unset($_SESSION['login_name']);
            } else {
                $_SESSION['login_name'] = $previous;
            }

            $invader = api_invader_state($identifier, $login);
            api_success(array(
                'name' => $identifier,
                'flashed' => false,
                'etat' => $invader ? $invader['etat'] : null,
            ));
        }

        api_require_method(array('POST', 'DELETE'));
    }

    // --- /v1/invaders/{inv_name}/status ------------------------------------
    if ($action === 'status') {

        if (!api_invader_exists($identifier)) {
            api_error(404, 'invader_not_found', "L'invader '" . $identifier . "' n'existe pas.");
        }

        if ($method === 'PUT' || $method === 'POST') {
            $etat = api_param('etat');

            if ($etat === null || trim($etat) === '') {
                api_error(400, 'missing_etat', "Le champ 'etat' est obligatoire.");
            }

            $etat = trim($etat);
            $known = api_known_states();

            if (!in_array($etat, $known, true)) {
                api_error(
                    400,
                    'invalid_etat',
                    "Etat inconnu : '" . $etat . "'.",
                    array('allowed' => $known)
                );
            }

            $previous = isset($_SESSION['login_name']) ? $_SESSION['login_name'] : null;
            $_SESSION['login_name'] = $login;
            update_status($identifier, $etat);
            if ($previous === null) {
                unset($_SESSION['login_name']);
            } else {
                $_SESSION['login_name'] = $previous;
            }

            $invader = api_invader_state($identifier, $login);
            api_success(array(
                'name' => $identifier,
                'etat' => $invader ? $invader['etat'] : $etat,
                'flashed' => $invader ? (bool) $invader['flashed'] : false,
            ));
        }

        if ($method === 'DELETE') {
            // Retour a l'etat global : on supprime la personnalisation.
            $pdo = connect();
            $query = "DELETE FROM modif_state_user WHERE inv_name = ? AND user_name = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$identifier, $login]);

            $invader = api_invader_state($identifier, $login);
            api_success(array(
                'name' => $identifier,
                'etat' => $invader ? $invader['etat'] : null,
                'flashed' => $invader ? (bool) $invader['flashed'] : false,
            ));
        }

        api_require_method(array('PUT', 'POST', 'DELETE'));
    }
}

// ---------------------------------------------------------------------------
// Aucune route ne correspond
// ---------------------------------------------------------------------------

api_error(
    404,
    'route_not_found',
    'Route inconnue : ' . $method . ' /' . implode('/', $segments),
    array(
        'routes' => array(
            'GET /api/v1/me',
            'GET /api/v1/stats',
            'GET /api/v1/invaders?status=a_flasher|deja_flashe|detruits|all',
            'GET /api/v1/invaders/{inv_name}',
            'POST /api/v1/invaders/{inv_name}/flash',
            'DELETE /api/v1/invaders/{inv_name}/flash',
            'PUT /api/v1/invaders/{inv_name}/status',
            'DELETE /api/v1/invaders/{inv_name}/status',
        ),
    )
);
