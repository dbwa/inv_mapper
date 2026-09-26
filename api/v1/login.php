<?php
/**
 * API Invaders Mapper - v1 - Authentification
 *
 * POST /api/v1/login
 *
 * Permet a un outil externe d'echanger un couple login / mot de passe
 * contre une cle API, sans passer par l'interface web.
 *
 * Corps de la requete (JSON) :
 *   {
 *     "login": "mon_login",
 *     "password": "mon_mot_de_passe",
 *     "label": "Mon script python"     // optionnel
 *   }
 *
 * Reponse :
 *   {
 *     "data": {
 *       "api_key": "inv_....",
 *       "label": "Mon script python",
 *       "created_at": "2026-09-12 10:00:00"
 *     }
 *   }
 *
 * ATTENTION : la cle n'est affichee qu'une seule fois. Elle est stockee
 * hachee (SHA-256) en base, il est donc impossible de la retrouver ensuite.
 */

include_once(__DIR__ . '/../api_bootstrap.php');

api_require_method('POST');

$login = api_param('login');
$password = api_param('password');
$label = api_param('label', 'Cle generee via /api/v1/login');

if (empty($login) || empty($password)) {
    api_error(400, 'missing_credentials', "Les champs 'login' et 'password' sont obligatoires.");
}

// Limitation des tentatives (anti brute-force)
$wait = login_attempt_wait($login);
if ($wait > 0) {
    api_error(429, 'too_many_attempts', "Trop de tentatives. Reessayez dans $wait secondes.");
}

$pdo = connect();

// Le site stocke les mots de passe en SHA1 (voir authentificate() dans fonctions.inc.php).
$query = "SELECT login, name, user_type FROM users WHERE login = ? AND pwd = SHA1(?)";
$stmt = $pdo->prepare($query);
$stmt->execute([$login, $password]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    login_attempt_failure($login);
    // Message volontairement generique : on n'indique pas quel champ est faux.
    api_error(401, 'invalid_credentials', 'Login ou mot de passe incorrect.');
}

login_attempt_reset($login);

// On limite le nombre de cles actives par utilisateur pour eviter les abus.
$query = "SELECT COUNT(*) FROM api_keys WHERE user_name = ? AND revoked_at IS NULL";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['login']]);
$active_keys = (int) $stmt->fetchColumn();

if ($active_keys >= 10) {
    api_error(
        429,
        'too_many_keys',
        'Trop de cles API actives pour ce compte. Revoquez-en une depuis la page Parametres.'
    );
}

$api_key = api_generate_key();
$label = mb_substr(trim($label), 0, 100);

$query = "INSERT INTO api_keys (user_name, key_hash, label, created_at)
          VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['login'], hash('sha256', $api_key), $label]);

api_success(array(
    'api_key' => $api_key,
    'label' => $label,
    'login' => $user['login'],
    'created_at' => date('Y-m-d H:i:s'),
    'warning' => "Conservez cette cle : elle ne sera plus jamais affichee.",
), 201);
