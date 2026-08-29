<?php
/**
 * ISS Cache Builder
 * Lance par cron toutes les 12h
 * Genere 36h de positions ISS toutes les 60 secondes
 */
set_include_path(
    __DIR__ . '/lib' . PATH_SEPARATOR . get_include_path()
);

require_once __DIR__ . '/lib/Predict/TLE.php';
require_once __DIR__ . '/lib/Predict/Sat.php';
require_once __DIR__ . '/lib/Predict.php';
require_once __DIR__ . '/lib/Predict/Time.php';
require_once __DIR__ . '/lib/Predict/Math.php';
require_once __DIR__ . '/lib/Predict/SGPObs.php';
require_once __DIR__ . '/lib/Predict/SGPSDP.php';
require_once __DIR__ . '/lib/Predict/SGSDPStatic.php';
require_once __DIR__ . '/lib/Predict/DeepArg.php';
require_once __DIR__ . '/lib/Predict/DeepStatic.php';
require_once __DIR__ . '/lib/Predict/Geodetic.php';
require_once __DIR__ . '/lib/Predict/ObsSet.php';
require_once __DIR__ . '/lib/Predict/Vector.php';
require_once __DIR__ . '/lib/Predict/Solar.php';

define('CACHE_FILE',    __DIR__ . '/cache/iss_cache.json');
define('TLE_URL',       'https://celestrak.org/NORAD/elements/gp.php?GROUP=stations&FORMAT=tle');
define('STEP_SECONDS',  60);
define('HOURS_AHEAD',   36);

// --- 1. Recuperer le TLE ---
function fetchIssTle() {
    $raw = @file_get_contents(TLE_URL);
    if (!$raw) {
        throw new Exception("Impossible de telecharger le TLE");
    }
    $lines = array_values(array_filter(
        array_map('trim', explode("\n", $raw))
    ));
    // Chercher la ligne "ISS (ZARYA)"
    for ($i = 0; $i < count($lines) - 2; $i++) {
        if (stripos($lines[$i], 'ISS (ZARYA)') !== false) {
            return [
                'header' => $lines[$i],
                'line1'  => $lines[$i + 1],
                'line2'  => $lines[$i + 2],
            ];
        }
    }
    throw new Exception("TLE ISS introuvable dans le flux");
}

// --- 2. Calculer une position a un instant donne ---
function calcIssPosition(Predict_Sat $sat, $timestamp) {
    $jul_utc = Predict_Time::unix2daynum($timestamp);
    $tsince  = ($jul_utc - $sat->jul_epoch) * Predict::xmnpda; // minutes depuis epoch

    $sdpsgp = Predict_SGPSDP::getInstance($sat);
    if ($sat->flags & Predict_SGPSDP::DEEP_SPACE_EPHEM_FLAG) {
        $sdpsgp->SDP4($sat, $tsince);
    } else {
        $sdpsgp->SGP4($sat, $tsince);
    }

    Predict_Math::Convert_Sat_State($sat->pos, $sat->vel);

    $sat_geodetic = new Predict_Geodetic();
    Predict_SGPObs::Calculate_LatLonAlt($jul_utc, $sat->pos, $sat_geodetic);

    // Normaliser la longitude entre -180 et +180
    while ($sat_geodetic->lon < -Predict::pi) $sat_geodetic->lon += Predict::twopi;
    while ($sat_geodetic->lon >  Predict::pi) $sat_geodetic->lon -= Predict::twopi;

    return [
        't'   => $timestamp,
        'lat' => round(Predict_Math::Degrees($sat_geodetic->lat), 5),
        'lon' => round(Predict_Math::Degrees($sat_geodetic->lon), 5),
        'alt' => round($sat_geodetic->alt, 2),
    ];
}

// --- MAIN ---
try {
    $tleData = fetchIssTle();

    $tle = new Predict_TLE(
        $tleData['header'],
        $tleData['line1'],
        $tleData['line2']
    );
    $sat = new Predict_Sat($tle);

    $now        = time();
    $end        = $now + HOURS_AHEAD * 3600;
    $totalSteps = (int)(($end - $now) / STEP_SECONDS);

    $points = [];
    for ($i = 0; $i <= $totalSteps; $i++) {
        $t        = $now + $i * STEP_SECONDS;
        $points[] = calcIssPosition($sat, $t);
    }

    $cache = [
        'generated_at' => date('c', $now),
        'tle_line1'    => $tleData['line1'],
        'tle_line2'    => $tleData['line2'],
        'step_seconds' => STEP_SECONDS,
        'count'        => count($points),
        'points'       => $points,
    ];

    if (!is_dir(dirname(CACHE_FILE))) {
        mkdir(dirname(CACHE_FILE), 0755, true);
    }

    file_put_contents(CACHE_FILE, json_encode($cache));
    echo "OK : " . count($points) . " points generes jusqu'au " . date('Y-m-d H:i', $end) . "\n";

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}