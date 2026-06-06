<?php

include_once(__DIR__ . "/postgis_geojson.php");
// utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!-- Swiper CSS et JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!--Plugins pour la carte (dont leaflet.groupedlayercontrol dans index.php-->

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
     integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
     crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
     integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM="
     crossorigin=""></script>

<!--Plugins pour le menu-->
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />


<!--Plugins pour la geolocalisation-->
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.mapbox.css' rel='stylesheet' />
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/css/font-awesome.min.css' rel='stylesheet' />

<!-- Inclure le CSS du plugin Leaflet Search -->
<link rel="stylesheet" href="./map/searchtool/leaflet-search.css">
<script src="./map/searchtool/leaflet-search.src.js"></script>

<!-- CSS pour les fond de carte https://openfreemap.org/ -->
<script src="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js"></script>
<link href="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css" rel="stylesheet" />

<!-- Maplibre GL Leaflet  -->
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet/leaflet-maplibre-gl.js"></script>

<style>
  html, body { 
      height: 100%; /* Important pour que les unit�s vh/svh fonctionnent bien */
      margin: 0;
      padding: 0;
      overflow: hidden; /* Emp�che le scroll de la page enti�re */
  }
  
  #emimapajax {
      width: 100%;
      /* Hauteur de la fen�tre visible (avec barres du navigateur) moins la hauteur de la navbar */
      height: calc(100svh - 91px); 
      /* Fallback pour les navigateurs qui ne supportent pas svh */
      height: calc(100vh - 91px); 
  }

  .leaflet-control-locate a {
      padding: 0 0 0 0;
  }
  .google-maps-icon {
      width: 28px;
      height: 28px;
      vertical-align: text-bottom;
      margin-left: 5px;
  }

  /* Styles modernes pour les popups */
  .leaflet-popup-content-wrapper {
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      backdrop-filter: blur(8px);
      background: rgba(255,255,255,0.95);
  }

  .leaflet-popup-content {
      margin: 16px;
  }

  .leaflet-popup-content h4 {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      margin: 0 0 8px 0;  
      font-size: 18px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: space-between;
  }

  .leaflet-popup-content img:not(.google-maps-icon) {
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
      max-height: 200px;  
      object-fit: cover;  
      width: auto;        
  }

  .popup-images img {
      max-width: 100%;   
      height: auto;      
  }

  /* Boutons modernes */
  .btn {
      border-radius: 8px;
      border: none;
      padding: 8px 16px;  
      font-weight: 500;
      transition: all 0.2s ease;
      margin: 4px;
      cursor: pointer;
      display: inline-flex;  
      align-items: center;   
      justify-content: center; 
      line-height: 1.2;      
      min-height: 36px;      
  }

  .btn-warning {
      background: linear-gradient(45deg, #ff9500, #ffa726);
      box-shadow: 0 2px 8px rgba(255,149,0,0.3);
      color: white;
  }

  .btn-dark {
      background: linear-gradient(45deg, #636363, #484848);
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      color: white;
  }

  .btn-primary {
      background: linear-gradient(45deg, #007AFF, #5856D6);
      box-shadow: 0 2px 8px rgba(0,122,255,0.3);
      color: white;
  }

  .btn:active {
      transform: translateY(1px);
      box-shadow: none;
  }

  /* Contrôles de carte */
  .leaflet-control:not(.leaflet-control-search, .leaflet-control-scale) {
      border-radius: 12px !important;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
      overflow: hidden !important;
  }

  .leaflet-control-zoom a,
  .leaflet-control-locate a {
      border: none !important;
      background: white !important;
      color: #333 !important;
      border-radius: 12px !important;
  }

  .leaflet-control-zoom-in {
      border-radius: 12px 12px 0 0 !important;
  }

  .leaflet-control-zoom-out {
      border-radius: 0 0 12px 12px !important;
  }

  .leaflet-control-locate {
      border-radius: 12px !important;
  }

  .leaflet-control-locate a {
      padding: 0 !important;
      width: 30px !important;
      height: 30px !important;
      line-height: 30px !important;
      text-align: center !important;
  }

  .leaflet-touch .leaflet-control-locate a {
      width: 30px !important;
      height: 30px !important;
  }

  /* Informations dans le popup */
  .info-line {
      display: flex;
      align-items: center;
      margin: 2px 0;  
      color: #666;
      font-size: 13px;  
      padding: 2px 0;   
  }

  .section-divider {
      height: 1px;
      background: linear-gradient(to right, transparent, #e0e0e0, transparent);
      margin: 8px 0;  
  }

  .button-group {
      display: flex;
      gap: 6px;      
      margin-top: 8px;  
  }

  .popup-images {
    position: relative;
    height: 200px;
    margin: 8px 0;
    border-radius: 12px;
    overflow: hidden;
    }

    .swiper {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative; /* Important ! */
    }

    .swiper-slide img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
    }

    .swiper-pagination {
        position: absolute;
        bottom: 8px !important;
    }

    .swiper-pagination-bullet {
        background: white;
        opacity: 0.6;
    }

    .swiper-pagination-bullet-active {
        background: white;
        opacity: 1;
    }

    .swiper-button-next, .swiper-button-prev {
        color: white;
        background: rgba(0, 0, 0, 0.3);
        width: 32px;
        height: 32px;
        border-radius: 50%;
    }

    .swiper-button-next:hover, .swiper-button-prev:hover {
        background: rgba(0, 0, 0, 0.5);
    }

    .swiper-button-next::after, .swiper-button-prev::after {
        font-size: 16px;
    }

    .single-image .swiper-button-next,
    .single-image .swiper-button-prev,
    .single-image .swiper-pagination {
        display: none;
    }

  /* Styles pour la recherche */
  .leaflet-control-search .search-input {
      border-radius: 4px;
      height: 30px;
      padding: 0 8px;
      border: none;
      background: white;
      width: 100%;
      font-size: 14px;
  }

  .leaflet-control-search .search-tooltip {
      max-height: 300px;
      overflow-y: auto;
      background: white;
      border-radius: 4px;
      margin-top: 6px;
      box-shadow: 0 1px 5px rgba(0,0,0,0.65);
      width: 100%;
      z-index: 1000;
  }

  .leaflet-control-search .search-tip {
      background: white;
      padding: 8px;
      border: none;
      margin: 0;
      cursor: pointer;
  }

  .leaflet-control-search .search-tip:hover {
      background: #f4f4f4;
  }

  /* Reset des styles qui pourraient interférer */
  .leaflet-control-search {
      clear: both;
      z-index: 1000;
  }

  .leaflet-control-search .search-cancel {
      z-index: 1001;
  }

  .achievement-toast {
    position: fixed;
    bottom: 20px;
    right: -350px;
    width: 300px;
    background: rgba(211, 230, 233, 0.95);
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: right 0.3s ease-in-out;
    z-index: 9999;
    color: #2c2c2c;
}

.achievement-toast.show {
    right: 20px;
}

.achievement-content {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.achievement-icon {
    font-size: 2em;
}

.achievement-text h4 {
    margin: 0 0 5px 0;
    color: #2c2c2c;
}

.achievement-toast {
    cursor: pointer;
    transition: box-shadow 0.2s;
}
.achievement-toast:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    background: #f8f8f8;
}


.upload-slide {
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.upload-container {
    text-align: center;
    padding: 20px;
}

.upload-container i {
    font-size: 40px;
    color: #666;
    margin-bottom: 10px;
}

.upload-container:hover {
    color: #007bff;
}

.image-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    font-size: 0.8em;
    padding: 5px;
    background-color: rgba(0,0,0,0.5); /* Fond semi-transparent */
    color: white; /* Texte clair */
    text-align: right;
}

.image-date {
    color: #eee;
    display: block;
    padding-right: 10px;
}

.image-credit {
    color: #ddd;
    font-style: italic;
    display: block;
    padding-right: 10px;
}


/* Reviens � ce CSS pour l'�chelle */
.leaflet-control-scale {
    background: none !important;
    border: none !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
    padding: 0 !important;
    border-radius: 0 !important;
}

.leaflet-control-scale-line {
    background: none !important;
    border: 2px solid #333 !important;
    border-top: none !important;
    border-radius: 0 !important;
    color: #333 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    line-height: 1.1 !important;
    padding: 2px 4px 1px !important;
    margin-bottom: 2px !important;
}

</style>

<script type="text/javascript">
    //    Récupération des variables PHP en Javascript

    // Déclaration de la carte avec le fond par défaut
    var map = new L.Map('emimapajax', {
        fullscreenControl: false,
        fullscreenControlOptions: {
            title: 'Plein Ecran',
            position: 'topright',
            forceSeparateButton: false
        },
        zoomControl: false,
        minZoom: 2,
        maxZoom: 20
    }).setView([<?php echo $center_lat;?>, <?php echo $center_lon;?>], 8);
    //	map.setMaxBounds([[41.6, 0], [45.83, 6.2]]);
    //map.addControl(layerControl);	//sinon (sans plugin) :
    
    //cibtrôle du zoom séparé de façon à pouvoir le placer où on veut
    L.control.zoom({
         position:'topright'
    }).addTo(map)

    //fond clair
    var carto_light_tile = L.maplibreGL({
        style: 'https://tiles.openfreemap.org/styles/positron',
    });
    
    //var carto_light_placeholder = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    //    subdomains: 'abcd',
    //    minZoom: 4,
    //    maxZoom: 20,
    //    zIndex: 0, // Cette couche est en dessous
    //    maxNativeZoom: 15,
    //    // Optionnel: tu peux appliquer un filtre CSS pour la flouter
    //    // className: 'blurred-tile'
    //});
    //map.addLayer(carto_light_placeholder);
    
    // fond sombre bleut�
    //var carto_dark_blue_tile = L.tileLayer('/map/tile_proxy.php?layer=carto_dark_blue&z={z}&x={x}&y={y}', {
    //    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTODB</a>',
    //    subdomains: 'abcd',
    //    minZoom: 4,
    //    maxZoom: 20,
    //    zIndex: 0
    //});
    
    // fond "classique"
    var carto_classic_tile = L.maplibreGL({
        style: 'https://tiles.openfreemap.org/styles/bright',
    });

    // Fonction pour sauvegarder le choix dans un cookie
    function saveMapPreference(layerName) {
        document.cookie = "mapBackground=" + layerName + ";path=/;max-age=31536000"; // expire dans 1 an
    }

    // Fonction pour lire le cookie
    function getMapPreference() {
        var name = "mapBackground=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "White tiles"; // valeur par défaut
    }

    var background_tiles = {
        "White tiles": carto_light_tile,
        //"Black tiles": carto_dark_blue_tile,
        "OpenStreetMap": carto_classic_tile
    };

    // Appliquer le fond de carte sauvegardé
    var savedBackground = getMapPreference();
    var defaultLayer = background_tiles[savedBackground] || carto_light_tile;
    defaultLayer.addTo(map);

    // Écouter les changements de fond de carte
    map.on('baselayerchange', function(e) {
        saveMapPreference(e.name);
    });

    // Fonction pour sauvegarder la derniere position vue (centre + zoom) dans un cookie
    function saveMapPosition() {
        var center = map.getCenter();
        var positionData = {
            lat: center.lat,
            lon: center.lng,
            zoom: map.getZoom()
        };
        document.cookie = "mapPosition=" + encodeURIComponent(JSON.stringify(positionData)) + ";path=/;max-age=86400"; // expire dans 24h
    }

    // Fonction pour lire la derniere position vue depuis le cookie
    function getMapPosition() {
        var name = "mapPosition=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                try {
                    var positionData = JSON.parse(c.substring(name.length, c.length));
                    if (positionData &&
                        typeof positionData.lat === 'number' && isFinite(positionData.lat) &&
                        typeof positionData.lon === 'number' && isFinite(positionData.lon) &&
                        typeof positionData.zoom === 'number' && isFinite(positionData.zoom) &&
                        positionData.lat >= -90 && positionData.lat <= 90 &&
                        positionData.lon >= -180 && positionData.lon <= 180 &&
                        positionData.zoom >= map.options.minZoom && positionData.zoom <= map.options.maxZoom) {
                        return positionData;
                    }
                } catch (e) {
                    // cookie corrompu : on ignore
                }
                return null;
            }
        }
        return null; // pas de cookie
    }

    // Restaurer la derniere position vue (si le cookie est encore valide)
    var savedPosition = getMapPosition();
    if (savedPosition !== null) {
        map.setView([savedPosition.lat, savedPosition.lon], savedPosition.zoom);
    }

    // Pendant l'initialisation, on n'ecrit pas le cookie : sinon les mouvements
    // internes de Leaflet (autoPan des popups, ISS...) ecraseraient la position restauree.
    var mapPositionReady = false;
    setTimeout(function() {
        mapPositionReady = true;
    }, 1000);

    // Sauvegarde de la position apres un deplacement ou un zoom (avec temporisation
    // pour ne pas ecrire le cookie en continu pendant le glisser-deposer)
    var mapPositionTimer = null;
    map.on('moveend', function() {
        if (!mapPositionReady) {
            return;
        }
        if (mapPositionTimer !== null) {
            clearTimeout(mapPositionTimer);
        }
        mapPositionTimer = setTimeout(function() {
            saveMapPosition();
            mapPositionTimer = null;
        }, 1000);
    });





    // --- ISS / SPACE_2 ---
    var ISS_INVADER_NAME = 'SPACE_2';
    var ISS_DATA_URL = '/iss/iss_data.php';
    var issLatestData = null;
    var issForecastGroup = L.layerGroup().addTo(map);
    var issForecastLabelGroup = L.layerGroup().addTo(map);
    var issForecastVisible = false;
    var issCurrentPoint = null;
    var issNextPoint = null;
    var issCurrentTsMs = 0;
    var issNextTsMs = 0;
    var issAnimRaf = null;

    function findSpace2Layer(layerGroup) {
        var found = null;
        layerGroup.eachLayer(function (layer) {
            if (layer.feature && layer.feature.properties && layer.feature.properties.name === ISS_INVADER_NAME) {
                found = layer;
            }
        });
        return found;
    }

    function updateSpace2PositionFromPoint(point) {
        if (!point) return;
        var lat = Number(point.lat);
        var lon = Number(point.lon);
        if (!isFinite(lat) || !isFinite(lon)) return;

        var layer = findSpace2Layer(L_a_fla);
        if (!layer) return;

        layer.setLatLng([lat, lon]);
        if (layer.feature && layer.feature.geometry && Array.isArray(layer.feature.geometry.coordinates)) {
            layer.feature.geometry.coordinates = [lon, lat];
        }
    }

    function normalizeSegmentsForLeaflet(segments) {
        if (!Array.isArray(segments)) return [];

        // On veut un format: [ segment1Chunks, segment2Chunks, ... ]
        // avec segmentXChunks = [ [ [lat, lon], [lat, lon]... ], [chunk2...], ... ]
        return segments.map(function (segment) {
            if (!Array.isArray(segment)) return [];

            // Cas 1: segment = [{lat,lon...}, ...]
            if (segment.length > 0 && segment[0] && typeof segment[0].lat !== 'undefined') {
                return [segment.map(function (p) { return [Number(p.lat), Number(p.lon)]; })];
            }

            // Cas 2: segment = [ [{lat,lon...}, ...], [{...}] ] (chunks)
            return segment.map(function (chunk) {
                if (!Array.isArray(chunk)) return [];
                return chunk.map(function (p) { return [Number(p.lat), Number(p.lon)]; });
            });
        });
    }

    function drawIssForecastLines() {
        issForecastGroup.clearLayers();
        issForecastLabelGroup.clearLayers();
        if (!issLatestData || !issLatestData.segments) return;

        var normalized = normalizeSegmentsForLeaflet(issLatestData.segments);
        var colors = ['#00B0FF', '#FFB300', '#E53935']; // +30, +60, +90
        var labels = ['+30 min', '+60 min', '+90 min'];

        for (var i = 0; i < 3; i++) {
            if (!normalized[i]) continue;

            var lastPoint = null;

            normalized[i].forEach(function (chunk) {
                if (chunk.length >= 2) {
                    L.polyline(chunk, {
                        color: colors[i],
                        weight: 3,
                        opacity: 0.8,
                        dashArray: i === 0 ? null : '6 6'
                    }).addTo(issForecastGroup);
                }
                if (chunk.length > 0) {
                    lastPoint = chunk[chunk.length - 1];
                }
            });

            if (lastPoint) {
                L.marker(lastPoint, {
                    interactive: false,
                    keyboard: false,
                    icon: L.divIcon({
                        className: 'iss-forecast-label',
                        html: '<span style="display:inline-block;padding:1px 4px;font-size:10px;color:#2c3e50;background:rgba(255,255,255,0.65);border-radius:8px;">' + labels[i] + '</span>',
                        iconSize: [60, 16],
                        iconAnchor: [30, 8]
                    })
                }).addTo(issForecastLabelGroup);
            }
        }
    }

    function getCurrentPointFromIssData(data) {
        if (!data) return null;
        if (data.current && typeof data.current.lat !== 'undefined' && typeof data.current.lon !== 'undefined') {
            return data.current;
        }

        // Fallback: premier point du 1er segment
        if (Array.isArray(data.segments) && data.segments.length > 0) {
            var s0 = data.segments[0];
            if (Array.isArray(s0) && s0.length > 0) {
                if (s0[0] && typeof s0[0].lat !== 'undefined') {
                    return s0[0];
                }
                if (Array.isArray(s0[0]) && s0[0][0] && typeof s0[0][0].lat !== 'undefined') {
                    return s0[0][0];
                }
            }
        }

        return null;
    }

    function loadIssDataAndApply() {
        fetch(ISS_DATA_URL, { cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) throw new Error('ISS data HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                issLatestData = data;
    
                var picked = pickCurrentAndNextPoints(data);
                if (!picked.current) return;
    
                var now = Date.now();
    
                // ancrage pour interpolation "live" sur ~60s
                issCurrentPoint = { lat: Number(picked.current.lat), lon: Number(picked.current.lon) };
                issNextPoint = { lat: Number(picked.next.lat), lon: Number(picked.next.lon) };
                issCurrentTsMs = now;
                issNextTsMs = now + 60000;
    
                // pose immédiate (évite latence visuelle au refresh)
                updateSpace2PositionFromPoint(issCurrentPoint);
    
                if (issForecastVisible) {
                    drawIssForecastLines();
                }
            })
            .catch(function (err) {
                console.error('Erreur chargement ISS:', err);
            });
    }


    function lerp(a, b, t) {
        return a + (b - a) * t;
    }
    
    // interpolation de longitude en passant par le chemin le plus court (antiméridien)
    function lerpLongitudeShortest(lon1, lon2, t) {
        var d = lon2 - lon1;
        if (d > 180) d -= 360;
        if (d < -180) d += 360;
        var lon = lon1 + d * t;
        if (lon > 180) lon -= 360;
        if (lon < -180) lon += 360;
        return lon;
    }
    
    function flattenSegmentPoints(segment) {
        // Retourne [{lat, lon}, ...] qu'importe le format reçu
        var out = [];
        if (!Array.isArray(segment)) return out;
    
        // format direct: [{lat,lon}, ...]
        if (segment.length && segment[0] && typeof segment[0].lat !== 'undefined') {
            return segment;
        }
    
        // format chunks: [ [{lat,lon},...], [{lat,lon},...] ]
        segment.forEach(function (chunk) {
            if (Array.isArray(chunk)) {
                chunk.forEach(function (p) {
                    if (p && typeof p.lat !== 'undefined' && typeof p.lon !== 'undefined') {
                        out.push(p);
                    }
                });
            }
        });
    
        return out;
    }
    
    function pickCurrentAndNextPoints(data) {
        var current = null;
        var next = null;
    
        if (data && data.current && typeof data.current.lat !== 'undefined' && typeof data.current.lon !== 'undefined') {
            current = data.current;
        }
    
        if (data && Array.isArray(data.segments) && data.segments.length > 0) {
            var s0 = flattenSegmentPoints(data.segments[0]); // 0 -> +30 min
            if (!current && s0.length > 0) current = s0[0];
            // next = 1er point suivant, sinon fallback
            if (s0.length > 1) next = s0[1];
        }
    
        if (!next && current) next = { lat: current.lat, lon: current.lon };
        return { current: current, next: next };
    }
    
    function animateIssPosition() {
        if (!issCurrentPoint || !issNextPoint || !issCurrentTsMs || !issNextTsMs) {
            issAnimRaf = requestAnimationFrame(animateIssPosition);
            return;
        }
    
        var now = Date.now();
        var duration = Math.max(1, issNextTsMs - issCurrentTsMs);
        var t = (now - issCurrentTsMs) / duration;
        if (t < 0) t = 0;
        if (t > 1) t = 1;
    
        var lat = lerp(Number(issCurrentPoint.lat), Number(issNextPoint.lat), t);
        var lon = lerpLongitudeShortest(Number(issCurrentPoint.lon), Number(issNextPoint.lon), t);
    
        updateSpace2PositionFromPoint({ lat: lat, lon: lon });
    
        issAnimRaf = requestAnimationFrame(animateIssPosition);
    }
    
    function startIssAnimationLoop() {
        if (issAnimRaf) cancelAnimationFrame(issAnimRaf);
        issAnimRaf = requestAnimationFrame(animateIssPosition);
    }



    function getImageCount(feature) {
        return ['image1', 'image2', 'image3'].filter(img => 
            feature.properties[img] && !feature.properties[img].includes('None')
        ).length;
    }

    function onEachFeature_a_flasher(feature, layer) {
            //creation du pop up pour quand oon click sur un invader
	        layer.bindPopup(`
	            <h4>
	                ${feature.properties.name}
	                <a href="https://www.google.com/maps?q=${feature.geometry.coordinates[1]},${feature.geometry.coordinates[0]}" 
	                   target="_blank" 
	                   title="Voir sur Google Maps">
	                    <img src="map/google-map.png" class="google-maps-icon" alt="Google Maps">
	                </a>
	            </h4>
                <div class="popup-images${getImageCount(feature) === 1 ? ' single-image' : ''}">
            <div class="swiper">
                <div class="swiper-wrapper">
                    ${!feature.properties.image1.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image1}" alt="Photo 1">
                            <div class="image-caption">
                                ${feature.properties.image1_d ? `<span class="image-date">${feature.properties.image1_d}</span>` : ''}
                                ${feature.properties.image1_c ? `<span class="image-credit">© ${feature.properties.image1_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image2.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image2}" alt="Photo 2">
                            <div class="image-caption">
                                ${feature.properties.image2_d ? `<span class="image-date">${feature.properties.image2_d}</span>` : ''}
                                ${feature.properties.image2_c ? `<span class="image-credit">© ${feature.properties.image2_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image3.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image3}" alt="Photo 3">
                            <div class="image-caption">
                                ${feature.properties.image3_d ? `<span class="image-date">${feature.properties.image3_d}</span>` : ''}
                                ${feature.properties.image3_c ? `<span class="image-credit">© ${feature.properties.image3_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${feature.properties.more_p === true ? 
                        `<div class="swiper-slide upload-slide">
                            <div class="upload-container" onclick="openUploadModal('${feature.properties.name}')">
                                <p style='font-size: 3em; margin-bottom: 0px; margin-top: 0px;'>📸</p>
                                <p>Alimenter la galerie en partageant votre photo !</p>
                            </div>
                        </div>` : ''}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
            <div class="section-divider"></div>
            <div class="info-section">
                <div class="info-line">📍 Points : ${feature.properties.points}</div>
                <div class="info-line">📊 État : ${feature.properties.etat}</div>
                <div class="info-line">🕒 Dernière maj : ${feature.properties.last_maj}</div>
            </div>
            <div class="button-group">
                <button class="btn btn-success" onclick="click_to_flash('${feature.properties.name}')">Je l'ai !</button>
                <button class="btn btn-warning" onclick="click_to_detruit('${feature.properties.name}')">Déclarer détruit</button>
            </div>
	        `, {maxHeight: 400, maxWidth: 300});

            if (feature.properties && feature.properties.name === ISS_INVADER_NAME) {
                layer.on('popupopen', function () {
                    issForecastVisible = true;
                    drawIssForecastLines();
                });

                layer.on('popupclose', function () {
                    issForecastVisible = false;
                    issForecastGroup.clearLayers();
                    issForecastLabelGroup.clearLayers();
                });
            }
	    }

    function onEachFeature_deja_flasher(feature, layer) {
    //creation du pop up pour quand on click sur un invader
    layer.bindPopup(`
        <h4>
            ${feature.properties.name}
            <a href="https://www.google.com/maps?q=${feature.geometry.coordinates[1]},${feature.geometry.coordinates[0]}" 
               target="_blank" 
               title="Voir sur Google Maps">
                <img src="map/google-map.png" class="google-maps-icon" alt="Google Maps">
            </a>
        </h4>
        <div class="popup-images${getImageCount(feature) === 1 ? ' single-image' : ''}">
            <div class="swiper">
                <div class="swiper-wrapper">
                    ${!feature.properties.image1.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image1}" alt="Photo 1">
                            <div class="image-caption">
                                ${feature.properties.image1_d ? `<span class="image-date">${feature.properties.image1_d}</span>` : ''}
                                ${feature.properties.image1_c ? `<span class="image-credit">© ${feature.properties.image1_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image2.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image2}" alt="Photo 2">
                            <div class="image-caption">
                                ${feature.properties.image2_d ? `<span class="image-date">${feature.properties.image2_d}</span>` : ''}
                                ${feature.properties.image2_c ? `<span class="image-credit">© ${feature.properties.image2_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image3.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image3}" alt="Photo 3">
                            <div class="image-caption">
                                ${feature.properties.image3_d ? `<span class="image-date">${feature.properties.image3_d}</span>` : ''}
                                ${feature.properties.image3_c ? `<span class="image-credit">© ${feature.properties.image3_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${feature.properties.more_p === true ? 
                        `<div class="swiper-slide upload-slide">
                            <div class="upload-container" onclick="openUploadModal('${feature.properties.name}')">
                                <p style='font-size: 3em; margin-bottom: 0px; margin-top: 0px;'>📸</p>
                                <p>Alimenter la galerie en partageant votre photo !</p>
                            </div>
                        </div>` : ''}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
        <div class="section-divider"></div>
        <div class="info-section">
            <div class="info-line">📍 Points : ${feature.properties.points}</div>
            <div class="info-line">📊 État : ${feature.properties.etat}</div>
            <div class="info-line">🕒 Dernière maj : ${feature.properties.last_maj}</div>
        </div>
        <div class="button-group">
            <button class="btn btn-dark" onclick="click_to_NON_flash('${feature.properties.name}')">Je l'ai pas</button>
            <button class="btn btn-warning" onclick="click_to_detruit('${feature.properties.name}')">Déclarer détruit</button>
        </div>
    `, {maxHeight: 400, maxWidth: 300});

            if (feature.properties && feature.properties.name === ISS_INVADER_NAME) {
                layer.on('popupopen', function () {
                    issForecastVisible = true;
                    drawIssForecastLines();
                });

                layer.on('popupclose', function () {
                    issForecastVisible = false;
                    issForecastGroup.clearLayers();
                    issForecastLabelGroup.clearLayers();
                });
            }
	    }

    function onEachFeature_detruits(feature, layer) {
            //creation du pop up pour quand oon click sur un invader
            layer.bindPopup(`
	            <h4>
	                ${feature.properties.name}
	                <a href="https://www.google.com/maps?q=${feature.geometry.coordinates[1]},${feature.geometry.coordinates[0]}" 
	                   target="_blank" 
	                   title="Voir sur Google Maps">
	                    <img src="map/google-map.png" class="google-maps-icon" alt="Google Maps">
	                </a>
	            </h4>
	            <div class="popup-images${getImageCount(feature) === 1 ? ' single-image' : ''}">
            <div class="swiper">
                <div class="swiper-wrapper">
                    ${!feature.properties.image1.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image1}" alt="Photo 1">
                            <div class="image-caption">
                                ${feature.properties.image1_d ? `<span class="image-date">${feature.properties.image1_d}</span>` : ''}
                                ${feature.properties.image1_c ? `<span class="image-credit">© ${feature.properties.image1_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image2.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image2}" alt="Photo 2">
                            <div class="image-caption">
                                ${feature.properties.image2_d ? `<span class="image-date">${feature.properties.image2_d}</span>` : ''}
                                ${feature.properties.image2_c ? `<span class="image-credit">© ${feature.properties.image2_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${!feature.properties.image3.includes('None') ? 
                        `<div class="swiper-slide">
                            <img src="${feature.properties.image3}" alt="Photo 3">
                            <div class="image-caption">
                                ${feature.properties.image3_d ? `<span class="image-date">${feature.properties.image3_d}</span>` : ''}
                                ${feature.properties.image3_c ? `<span class="image-credit">© ${feature.properties.image3_c}</span>` : ''}
                            </div>
                        </div>` : ''}
                    ${feature.properties.more_p === true ? 
                        `<div class="swiper-slide upload-slide">
                            <div class="upload-container" onclick="openUploadModal('${feature.properties.name}')">
                                <p style='font-size: 3em; margin-bottom: 0px; margin-top: 0px;'>📸</p>
                                <p>Alimenter la galerie en partageant votre photo !</p>
                            </div>
                        </div>` : ''}
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
	            <div class="section-divider"></div>
	            <div class="info-section">
	                <div class="info-line">📍 Points : ${feature.properties.points}</div>
	                <div class="info-line">📊 État : ${feature.properties.etat}</div>
	                <div class="info-line">🕒 Dernière maj : ${feature.properties.last_maj}</div>
	            </div>
	            <div class="button-group">
	                <button class="btn btn-primary" onclick="click_to_reactive('${feature.properties.name}')">Déclarer non détruit</button>
	            </div>
	        `, {maxHeight: 400, maxWidth: 300});
        }



// Ouverture du modal
function openUploadModal(invaderName) {
    const modal = document.getElementById('uploadModal');
    modal.setAttribute('data-invader', invaderName);
    modal.querySelector('.invader-name').textContent = invaderName;
    
    // Reset du formulaire
    document.getElementById('uploadForm').reset();
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('submitBtn').setAttribute('disabled', '');
    
    var instance = M.Modal.getInstance(modal);
    instance.open();
}

// Mise à jour du bouton Submit
function updateSubmitButton() {
    const hasImage = document.getElementById('photoInput').files.length > 0;
    const termsAccepted = document.getElementById('termsCheck').checked;
    const submitBtn = document.getElementById('submitBtn');
    
    if (hasImage && termsAccepted) {
        submitBtn.classList.remove('disabled');
        submitBtn.removeAttribute('disabled');
    } else {
        submitBtn.classList.add('disabled');
        submitBtn.setAttribute('disabled', '');
    }
}

// Suppression de l'image
function removeImage() {
    document.getElementById('photoInput').value = '';
    document.querySelector('.file-path').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    updateSubmitButton();
}



// Fonction pour redimensionner l'image
async function compressImage(file) {
    // Création d'un canvas pour le redimensionnement
    const maxWidth = 1200; // Largeur max souhaitée
    const maxHeight = 1200; // Hauteur max souhaitée

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                // Calcul des nouvelles dimensions
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Conversion en WebP si possible, sinon JPEG
                if (canvas.toBlob) {
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, {
                            type: 'image/webp',
                            lastModified: Date.now()
                        }));
                    }, 'image/webp', 0.85); // Qualité 0.85
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// Soumission du formulaire
async function submitPhoto() {
    const invaderName = document.getElementById('uploadModal').getAttribute('data-invader');
    const formData = new FormData();
    
    formData.append('invader', invaderName);
    formData.append('photo', document.getElementById('photoInput').files[0]);
    formData.append('showCredit', document.getElementById('creditCheck').checked ? 1 : 0);

    // Afficher un toast "en cours"
    M.toast({html: 'Envoi en cours...', classes: 'blue'});

    fetch('/modules/user_images/ajax_save_user_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Status:', response.status); // Voir le status HTTP
        return response.text();
    })
    .then(text => {
        console.log('Réponse brute reçue:', text); // Voir la réponse exacte
        if (!text) {
            throw new Error('Réponse vide du serveur');
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Erreur parsing JSON:', e);
            console.error('Texte reçu:', text);
            throw e;
        }
    })
    .then(data => {
        console.log('Données parsées:', data); // Voir les données après parsing
        if (data.success) {
            M.toast({html: 'Photo envoyée avec succès !', classes: 'green'});
            var instance = M.Modal.getInstance(document.getElementById('uploadModal'));
            instance.close();
        } else {
            M.toast({html: 'Erreur : ' + data.message, classes: 'red'});
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        /*M.toast({html: 'Erreur lors de l\'envoi', classes: 'red'});*/
        M.toast({html: error, classes: 'red'});
    });
}



    function click_to_detruit(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=detruit';
        $.ajax({
            type: "GET",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
               loadIssDataAndApply();
           }
        });
    }

    function click_to_reactive(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=OK';
        $.ajax({
            type: "GET",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
               loadIssDataAndApply();
           }
        });
    }


    function click_to_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=vrai';
        $.ajax({
            type: "GET",
            url: "maj_click/maj_flash.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
               loadIssDataAndApply();
           }
        });

        // Vérification des badges
        $.ajax({
            type: "GET",
            url: "/modules/badges/ajax_check_badges.php",
            dataType: 'json', // On précise que c'est du JSON
            success: function(badgeData) {
                // Plus besoin de JSON.parse ici
                if (badgeData.badges && badgeData.badges.length > 0) {
                    badgeData.badges.forEach(function(badge, index) {
                        setTimeout(function() {
                            showToast(badge);
                        }, index * 3000);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log("Erreur lors de la vérification des badges:", error);
            }
        });
    }

    function click_to_NON_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=faux';
        console.log(dataString);
        $.ajax({
            type: "GET",
            url: "maj_click/maj_flash.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
               loadIssDataAndApply();
           }
        });
    }

    function showToast(badge) {
        const toast = document.createElement('div');
        toast.className = 'achievement-toast';
        toast.innerHTML = `
            <h5>Badge débloqué !</h5>
            <div class="achievement-content">
                <div class="achievement-icon">${badge.icone}</div>
                <div class="achievement-text">
                    <h4>${badge.nom_badge}</h4>
                    ${badge.description}
                </div>
            </div>`;
        document.body.appendChild(toast);

        // Quand on clique sur le toast, on ouvre la page des badges
        toast.addEventListener('click', function() {
            window.location.href = '/modules/badges/badges.php';
        });

        // Animation d'entrée
        setTimeout(() => toast.classList.add('show'), 100);

        // Suppression après 5 secondes
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    //icones dispo pour carte :
    var LeafIcon = L.Icon.extend({
        options: {
          //iconUrl: "img/icon-active_green_64.png",
          //shadowUrl: "https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png",
          iconSize:     [40, 40], // size of the icon
          shadowSize:   [40, 60], // size of the shadow
          iconAnchor:   [20, 40], // point of the icon which will correspond to marker's location
          shadowAnchor: [10, 60],  // the same for the shadow
          popupAnchor:  [0, -25] // point from which the popup should open relative to the iconAnchor
        }
    });

    var greenIcon = new LeafIcon({iconUrl: 'img/icon-active_green_64.png'}),
        redIcon = new LeafIcon({iconUrl: 'img/icon-active_red_64.png'}),
        orangeIcon = new LeafIcon({iconUrl: 'img/icon-active_orange_64.png'}),
        blueIcon = new LeafIcon({iconUrl: 'img/icon-active_blue_64.png'}),
        greyIcon = new LeafIcon({iconUrl: 'img/icon-active_grey_64.png'});

    //les a_flasher avec des images venant d'utilisateurs
    var geojsonMarkerOptions_flash = {
	    radius: 5,
	    fillColor: "#01be01",
	    color: "#000",
	    weight: 0.5,
	    opacity: 0.6,
	    fillOpacity: 0.8
	};
    
    //les a_flasher avec des images venant de invader spotter
    var geojsonMarkerOptions_flash_officiel = {
	    radius: 5,
	    fillColor: "#00ff00",
	    color: "#000",
	    weight: 0.4,
	    opacity: 0.4,
	    fillOpacity: 0.8
    };
    
    var geojsonMarkerOptions_destr = {
	    radius: 4,
	    fillColor: "#ff7800",
	    color: "#000",
	    weight: 0.5,
	    opacity: 0.6,
	    fillOpacity: 0.8
	};


/*    var a_flasher =  <?php echo get_geojson_a_flasher();?> ;  //JSON.parse();
    var L_a_fla = L.geoJSON(a_flasher, {onEachFeature: onEachFeature_a_flasher}).addTo(map);
*/
    var a_flasher =  <?php echo get_geojson_a_flasher();?> ;  //JSON.parse();
    var L_a_fla = L.geoJSON(a_flasher, {onEachFeature: onEachFeature_a_flasher, pointToLayer: function (feature, latlng) {
        return L.marker(latlng, {icon : blueIcon }) ;
    }});


    var deja_flashé = <?php echo get_geojson_deja_flashe();?> ;
    var L_deja_fla = L.geoJSON(deja_flashé, {
        onEachFeature: onEachFeature_deja_flasher,
        pointToLayer: function (feature, latlng) {
            var isOfficiel = feature.properties.image2_c && 
                             feature.properties.image2_c.toLowerCase() === 'invader spotter';
            return L.circleMarker(latlng, isOfficiel ? geojsonMarkerOptions_flash_officiel : geojsonMarkerOptions_flash);
        }
    });

    var detruits = <?php echo get_geojson_detruits();?> ;
    var L_detruits = L.geoJSON(detruits, {onEachFeature: onEachFeature_detruits, pointToLayer: function (feature, latlng) {
        return L.circleMarker(latlng, geojsonMarkerOptions_destr);
    }});

    /*var baseMaps = {
        "OpenStreetMap": regular_tile,
        "White tiles": white_tile
    };
    */
    
    var baseMaps = {
        "White tiles": carto_light_tile,
        //"Black tiles": carto_dark_blue_tile,
        "OpenStreetMap": carto_classic_tile
    };

    var overlayMaps = {
        "À flasher": L_a_fla,
        "Déjà flashés": L_deja_fla,
        "Détruits": L_detruits
    };

    var Lcontrol = L.control.layers(baseMaps, overlayMaps, {
        collapsed: true
    }).addTo(map);

    var couches = L.layerGroup([L_a_fla, L_deja_fla, L_detruits]);
    var controlSearch = new L.Control.Search({
        position: 'topright',        
        layer: couches,
        propertyName: 'name',
        initial: false,
        zoom: 19,
        marker: false,
        textPlaceholder: 'Rechercher',
        textErr: 'Aucun résultat',
        minLength: 3,
        autoCollapse: true,
        autoType: false,
        delayType: 1,
        sortResults: function(results) {
            return results.sort(function(a, b) {
                return a.layer.feature.properties.name.localeCompare(b.layer.feature.properties.name);
            });
        }
    });

    // Gestion des événements de recherche
    controlSearch.on('search:locationfound', function(e) {
        var isISS = e.layer.feature && e.layer.feature.properties && e.layer.feature.properties.name === 'SPACE_2';
    
        // Zoom adapté selon l'invader
        if (isISS) {
            map.setView(e.latlng, 4);
        } else {
            map.setView(e.latlng, 19);
        }
    
        // Tentative d'ouverture du popup (logique existante)
        e.layer.openPopup();
        if (!e.layer.isPopupOpen()) {
            for (var layerName in baseMaps) {
                var layer = baseMaps[layerName];
                if (map.hasLayer(layer)) {
                    // rien
                } else {
                    map.addLayer(layer);
                    e.layer.openPopup();
                    if (e.layer.isPopupOpen()) {
                        break;
                    } else {
                        map.removeLayer(layer);
                    }
                }
            }
        }
    });

    // Ajout du contrôle à la carte
    map.addControl(controlSearch);

    //je veux juste enlever les couches inutiles au début
    map.removeLayer(L_deja_fla);
    map.removeLayer(L_detruits);


    /*geolocalisation*/
    /*Attention : necessite une connexion https pour que les navigateurs acceptent le partage*/
    var geoloc = L.control.locate({position: 'topright',
      flyTo:'true',
      showCompass : 'true',
       locateOptions: {
               enableHighAccuracy: true
      }}).addTo(map);



    var attrib = map.attributionControl.addAttribution('Carte d\'invasion; v0.4.1 2026');
    
    map.on('popupopen', function(e) {
        const popup = e.popup;
        const container = popup.getElement();
        
        if (container.querySelector('.swiper')) {
            new Swiper(container.querySelector('.swiper'), {
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                }
            });
        }
    
        // AutoPan désactivé après centrage initial → navigation libre ensuite
        setTimeout(function() {
            popup.options.autoPan = false;
        }, 300);
    });
    
    map.on('popupclose', function(e) {
        e.popup.options.autoPan = true;
    });
    
    L.control.scale({
        position: 'bottomright',
        metric: true,
        imperial: false,
        maxWidth: 150
    }).addTo(map);
    
    
    //ISS
    // Chargement initial de la position ISS (SPACE_2), puis rafraichissement regulier
    loadIssDataAndApply();
    setInterval(loadIssDataAndApply, 60000);
    startIssAnimationLoop();
    

</script>
