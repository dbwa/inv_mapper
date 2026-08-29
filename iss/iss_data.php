<?php
/**
 * ISS Data API
 * Retourne les 4 segments de 30 min a partir de maintenant
 * Appele par le front JS au chargement et toutes les 25 min
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // adapter si besoin

define('CACHE_FILE',    __DIR__ . '/cache/iss_cache.json');
define('STEP_SECONDS',  60);
define('SEGMENT_MIN',   30);   // minutes par segment
define('NB_SEGMENTS',   4);    // 4 segments de 30 min = 120 min

$now = time();

// --- Verifier que le cache existe ---
if (!file_exists(CACHE_FILE)) {
    http_response_code(503);
    echo json_encode(['error' => 'Cache non disponible']);
    exit;
}

$cache = json_decode(file_get_contents(CACHE_FILE), true);
if (!$cache || empty($cache['points'])) {
    http_response_code(503);
    echo json_encode(['error' => 'Cache invalide']);
    exit;
}

$points      = $cache['points'];
$step        = $cache['step_seconds'];
$first_t     = $points[0]['t'];
$last_t      = $points[count($points) - 1]['t'];

// --- Verifier que le cache couvre bien maintenant ---
if ($now < $first_t || $now > $last_t) {
    http_response_code(503);
    echo json_encode(['error' => 'Cache perime, regeneration en cours']);
    exit;
}

// --- Trouver l'index du point le plus proche de maintenant ---
$start_index = (int)(($now - $first_t) / $step);
$start_index = max(0, min($start_index, count($points) - 1));

// --- Extraire les 4 segments ---
$points_per_segment = (int)((SEGMENT_MIN * 60) / $step); // 180 points pour 30 min a 10s

$segments = [];
for ($s = 0; $s < NB_SEGMENTS; $s++) {
    $seg_start = $start_index + $s * $points_per_segment;
    $seg_end   = $seg_start + $points_per_segment;

    if ($seg_start >= count($points)) break;
    $seg_end = min($seg_end, count($points) - 1);

    $seg_points = array_slice($points, $seg_start, $seg_end - $seg_start + 1);

    // Detecter et couper a l'antimeridien
    $segments[] = splitAtAntimeridian($seg_points);
}

// --- Reponse ---
echo json_encode([
    'now'          => $now,
    'cache_age'    => $now - strtotime($cache['generated_at']),
    'step_seconds' => $step,
    'segments'     => $segments,
]);

// --- Fonction : couper la ligne si saut de longitude > 180 ---
function splitAtAntimeridian(array $points) {
    if (count($points) < 2) return [$points];

    $chunks  = [];
    $current = [$points[0]];

    for ($i = 1; $i < count($points); $i++) {
        $delta_lon = abs($points[$i]['lon'] - $points[$i-1]['lon']);
        if ($delta_lon > 180) {
            // Coupure : on sauvegarde le chunk courant et on repart
            $chunks[]  = $current;
            $current   = [];
        }
        $current[] = $points[$i];
    }
    if (!empty($current)) {
        $chunks[] = $current;
    }
    return $chunks;
}