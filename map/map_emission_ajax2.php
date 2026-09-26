<?php

include_once(__DIR__ . "/postgis_geojson.php");
// utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

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

<!-- pour mettre les tile dans le cache du navigateur -->
<script src="https://unpkg.com/pouchdb@^5.2.0/dist/pouchdb.js"></script>
<script src="https://unpkg.com/leaflet.tilelayer.pouchdbcached@latest/L.TileLayer.PouchDBCached.js"></script>


<!--Plugins pour la geolocalisation-->
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.mapbox.css' rel='stylesheet' />
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/css/font-awesome.min.css' rel='stylesheet' />


<!-- Inclure le CSS du plugin Leaflet Search -->
<link rel="stylesheet" href="./map/searchtool/leaflet-search.css">
<script src="./map/searchtool/leaflet-search.src.js"></script>


<style>
  .leaflet-control-locate a {
      padding: 0 0 0 0;
  }
</style>
<script type="text/javascript">
    //    Récupération des variables PHP en Javascript

    // Déclaration de la carte
    var map = new L.Map('emimapajax', {
        fullscreenControl: true,
        fullscreenControlOptions: {
            title: 'Plein Ecran',
            position: 'topleft',
            forceSeparateButton: false //false pour être dans la barre de zoom (valeur par defaut)
        },

        zoomControl: true,
        minZoom: 4,
        maxZoom: 20,
        layers: []
    }).setView([<?php echo $center_lat;?>, <?php echo $center_lon;?>], 8);




    // Fond de carte
/*    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 4,
        maxZoom: 17,
        attribution: '',
        zIndex: 0
        }).addTo(map);*/

 var white_tile =  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTODB</a>',
            subdomains: 'abcd',
            minZoom: 4,
            maxZoom: 20,
            zIndex: 0
    }).addTo(map);;


var regular_tile = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTODB</a>',
            subdomains: 'abcd',
            minZoom: 4,
            maxZoom: 20,
            zIndex: 0
    }).addTo(map);

var background_tiles ={ "regular_tile" : regular_tile, "white_tile" : white_tile};


    function click_to_detruit(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=detruit';
        $.ajax({
            type: "POST",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               <?php  
                  $categories =$categories_default;
                  foreach ($categories as $key => &$val) 
          {
            echo("L_".$key.".clearLayers();");
            echo("L_".$key.".addData(JSON.parse(geojson.L_".$key.").features);");
          }
               ?>
           }
        });
    }

    function click_to_reactive(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&statusout=OK';
        $.ajax({
            type: "POST",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               <?php  
               $categories =$categories_default;
                  foreach ($categories as $key => &$val) 
          {
            echo("L_".$key.".clearLayers();");
            echo("L_".$key.".addData(JSON.parse(geojson.L_".$key.").features);");
          }
               ?>
           }
        });
    }

    function click_to_change_etat(inv_name, etat) {
        var dataString = 'inv_name=' + inv_name + '&statusout='+etat;
        $.ajax({
            type: "POST",
            url: "maj_click/maj_status.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               <?php  
               $categories =$categories_default;
                  foreach ($categories as $key => &$val) 
                    {
            echo("L_".$key.".clearLayers();");
            echo("L_".$key.".addData(JSON.parse(geojson.L_".$key.").features);");
                    }
               ?>
           }
        });
    }

    function click_to_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=vrai';
        $.ajax({
            type: "POST",
            url: "maj_click/maj_flash2.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               //console.log(JSON.parse(geojson['L_a_flasher']).features);
                console.log(geojson);
              <?php  
              $categories =$categories_default ;
              foreach ($categories as $key => &$val) 
              {
                echo("var Layer_".$key." = JSON.parse(geojson.Layer_".$key.");"); 
                echo("L_".$key.".clearLayers();");
                echo("L_".$key.".addData(Layer_".$key.".features);");
              }
               ?>
           },
           error: function (reponse){
            console.log(reponse);
           }
        });
    }

    function click_to_NON_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=faux';
        $.ajax({
            type: "POST",
            url: "maj_click/maj_flash2.php",
            data: dataString,
            cache: false,
            success: function (reponse) {
                //netoyage puis reapplication
               var geojson = JSON.parse(reponse);
               //console.log(JSON.parse(geojson['L_a_flasher']).features);
                console.log(geojson);
              <?php  
              $categories =$categories_default ;
              foreach ($categories as $key => &$val) 
              {
                echo("var Layer_".$key." = JSON.parse(geojson.Layer_".$key.");"); 
                echo("L_".$key.".clearLayers();");
                echo("L_".$key.".addData(Layer_".$key.".features);");
              }
               ?>
           },
           error: function (reponse){
            console.log(reponse);
           }
        });
    }

//icones dispo pour carte :
var LeafIcon = L.Icon.extend({
    options: {
      //iconUrl: "img/icon-active_green_64.png",
      // shadowUrl: "https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png",
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


  <?php  
    
    $nom_couches = "";
    $basemap = "";

    $liste_boutons_modif_etat = '<div class=\"dropdown\"><button class=\"btn btn-secondary dropdown-toggle\" type=\"button\" id=\"dropdownMenu2\" data-bs-toggle=\"dropdown\"> Changer l\\\'état <span class="caret"></span></button><ul class=\"dropdown-menu\">';

    //$categories =eval(get_categories($categories_default));
    $categories =$categories_default;
    foreach ($transfert_categories_possible as $cat_pos)
      {
      $liste_boutons_modif_etat .= "<li><input name=\"to_". $cat_pos ."\" class=\"dropdown-item btn btn-warning\" value=\"". $cat_pos ."\" onclick=click_to_change_etat(\"' + feature.properties.name +'\",\"". str_replace(' ', ':s:', $cat_pos) ."\") readonly /></li>";
      }    
      $liste_boutons_modif_etat .= '</ul></div>';

    foreach ($categories as $key => &$val) 
    {
      $nom_couches .= 'L_'. $key . ',';
      $basemap .=  '"'.$val["properties"]["name"] . '" : L_'. $key . ',';


      echo("
        var Layer_". $key ." = ". get_geojson_invaders_from_config($categories, $key) .";
        " );

      echo("
        function onEachFeature_". $key ."(feature, layer) {
              //creation du pop up pour quand oon click sur un invader
            layer.bindPopup(
              //le html du pop up :
                  \"<h4>\" +
              feature.properties.name + \"&nbsp;&nbsp;<a class='btn btn-secondary' target='_blank' href='https://www.google.com/maps/dir//\"+feature.geometry.coordinates[1]+\",\"+feature.geometry.coordinates[0]+\"' title='Itinéraire sur google'><i class='fa fa-map-marker'></i></a></h4><br>\" +
              ( (!feature.properties.image1.includes('None')) ? '<a href=\"'+feature.properties.image1+'\" target=\"_blank\"> <img src=\"'+feature.properties.image1+'\" width=95%></a>' : '') +
                  ( (!feature.properties.image2.includes('None')) ? '<a href=\"'+feature.properties.image2+'\" target=\"_blank\"> <img src=\"'+feature.properties.image2+'\" width=45%></a>' : '') + 
                  ( (!feature.properties.image3.includes('None')) ? '<a href=\"'+feature.properties.image3+'\" target=\"_blank\"> <img src=\"'+feature.properties.image3+'\" width=45%></a>' : '') +  \"<br>\"
              +'nbr de pts : ' + feature.properties.points + \"<br>\"
              +'etat : ' + feature.properties.etat + \"<br>\" 
              +'last maj : ' + feature.properties.last_maj + \"<br>\" + '"
            . ( in_array('je_lai', $val["properties"]["boutons"]) ? "<input name=\"to_flash\" class=\"btn btn-success\" value=\"Je l\'ai !\" onclick=click_to_flash(\"' + feature.properties.name +'\") readonly />" : '') 
            . ( in_array('je_lai_pas', $val["properties"]["boutons"]) ? "<input name=\"to_flash\" class=\"btn btn-dark\" value=\"Je l\'ai pas\" onclick=click_to_NON_flash(\"' + feature.properties.name +'\") readonly />" : '')
            . ( in_array('changer_etat', $val["properties"]["boutons"]) ? $liste_boutons_modif_etat : '') 
            . "',{maxHeight: 300, maxWidth:200});}"
          );


        if ($val["properties"]["default_on"] == true)
        {
        $addmap = ".addTo(map)";
        }
        else {
          $addmap = "";
        }

      if ($val["properties"]["marker"] == "default")
      {
          echo("
            var L_". $key ." = L.geoJSON(Layer_". $key .", {onEachFeature: onEachFeature_". $key ."})". $addmap .";
            ");
      }

        else  //si on donne nous meme les param de marker
        {
          echo("
            var L_". $key ." = L.geoJSON(Layer_". $key .", {onEachFeature: onEachFeature_". $key .", pointToLayer: function (feature, latlng) {
              return ". $val["properties"]["marker"] . ";
            }})". $addmap ."
            ;
          ");
        }
    }


    $nom_couches = substr($nom_couches, 0, -1);
    echo( "
      var couches = L.layerGroup([".$nom_couches."]);
      " );

    $basemap = substr($basemap, 0, -1);
    echo("
      var baseMaps = {". $basemap ." };
      " );
  ?>





    var Lcontrol = L.control.layers(background_tiles,baseMaps, {collapsed:true}).addTo(map);
    
    
    /*geolocalisation*/
    /*Attention : necessite une connexion https pour que les navigateurs acceptent le partage*/
    var geoloc = L.control.locate({position: 'topleft',
      flyTo:'true',
      showCompass : 'true',
       locateOptions: {
               enableHighAccuracy: true
      }}).addTo(map);

    var attrib = map.attributionControl.addAttribution('Carte d\'invasion, v0.4.1 2025');


var controlSearch = new L.Control.Search({
        position:'topright',        
        layer: couches,
        propertyName: 'name',
        initial: false,
        zoom: 19,
        marker: false
    });



controlSearch.on('search:locationfound', function(e) {
        console.log(e);
        e.layer.openPopup();
        if(e.layer.isPopupOpen())
            {
                //rien
            }
        else
            {
                for (var layerName in baseMaps) {
                    var layer = baseMaps[layerName];
                    if(map.hasLayer(layer)) {
                        //rien
                    }
                    else
                    {
                        map.addLayer(layer);
                        e.layer.openPopup();  //on essaye d'ouvrir le popup

                        if(e.layer.isPopupOpen())  //si on n'y arrive pas, c'est qu'il n'est pas sur cette couche
                        {
                           break;
                        }
                        else
                        {
                            map.removeLayer(layer);  // et donc on enlève la couche si elle n'est pas utrilisée
                        }
                    }    
                }
            }
    });




map.addControl( controlSearch );



    // out des options d'ajout de position 
    function ajout_position_invader_base() {
	    var inv_name = document.getElementById('inv_name_ajout').value;
	    var lat = document.getElementById('lat_ajout').value;
	    var lon = document.getElementById('lon_ajout').value;

	    var dataString = 'inv_name=' + inv_name + '&lat=' + lat + '&lon=' + lon;

	        $.ajax({
	            type: "POST",
	            url: "maj_click/ajout_position.php",
	            data: dataString,
	            cache: false,
	            success: function (reponse) {
	                //netoyage puis reapplication
	               var geojson = JSON.parse(reponse);
	               //console.log(JSON.parse(geojson['L_a_flasher']).features);
	                console.log(geojson);
	              <?php  
	              $categories =$categories_default ;
	              foreach ($categories as $key => &$val) 
	              {
	                echo("var Layer_".$key." = JSON.parse(geojson.Layer_".$key.");"); 
	                echo("L_".$key.".clearLayers();");
	                echo("L_".$key.".addData(Layer_".$key.".features);");
	              }
	               ?>
	              map.closePopup();
	           },
	           error: function (reponse){
	            console.log(reponse);
	           }
	        });
    }
    map.on('contextmenu', function(e) {        
        var popLocation = e.latlng;
        var popup = L.popup()
            .setLatLng(popLocation)
            .setContent(
                '<h4>Ajouter un invader ?</h4>' +
                '<form id="addForm">' +
                '   <label for="lon">Longitude:</label><br>' +
                '   <input type="number" id="lon_ajout" name="longitude" value="' + popLocation.lng + '"><br>' +
                '   <label for="lat">Latitude:</label><br>' +
                '   <input type="number" id="lat_ajout" name="latitude" value="' + popLocation.lat + '"><br>' +
                '   <label for="inv_name">Nom de l\'invader:</label><br>' +
                '   <input type="text" id="inv_name_ajout" name="Nom"><br><br>' +
                '   <input type="button" value="Ajouter" onclick="ajout_position_invader_base()">' +
                '   <input type="button" value="Annuler" onclick="map.closePopup()">' +
                '</form>'
            )
            .openOn(map);        
    });




</script>
