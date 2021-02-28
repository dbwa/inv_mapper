<?php
// Attribution pour le pied de carte

include_once(__DIR__ . "/postgis_geojson.php");

?>

<!--Plugins pour la carte (dont leaflet.groupedlayercontrol dans index.php-->

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

<!--Plugins pour le menu-->
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />


<!--Plugins pour la geolocalisation-->
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/L.Control.Locate.mapbox.css' rel='stylesheet' />
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.43.0/css/font-awesome.min.css' rel='stylesheet' />

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
        /*addbuttonControl : true,
         addbuttonControlOptions: {
         position: 'topleft'
         },*/
        zoomControl: true,
        minZoom: 4,
        maxZoom: 17,
        layers: []
    }).setView([<?php echo $center_lat;?>, <?php echo $center_lon;?>], 8);
    //	map.setMaxBounds([[41.6, 0], [45.83, 6.2]]);
    //map.addControl(layerControl);	//sinon (sans plugin) :
    



    // Fond de carte
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 4,
        maxZoom: 17,
        attribution: '',
        zIndex: 0
        }).addTo(map);


	function onEachFeature_a_flasher(feature, layer) {
            //creation du pop up pour quand oon click sur un invader
	        layer.bindPopup(
	        	//le html du pop up :
                "<h4>" +
	        	feature.properties.name + "</h3><br>" +
	        	( (feature.properties.image1 != 'None') ? '<a href="'+feature.properties.image1+'" target="_blank"> <img src="'+feature.properties.image1+'" width=95%></a>' : '') +
                ( (feature.properties.image2 != 'None') ? '<a href="'+feature.properties.image2+'" target="_blank"> <img src="'+feature.properties.image2+'" width=45%></a>' : '') + 
                ( (feature.properties.image3 != 'None') ? '<a href="'+feature.properties.image3+'" target="_blank"> <img src="'+feature.properties.image3+'" width=45%></a>' : '') +  "<br>"
	        	+'nbr de pts : ' + feature.properties.points + "<br>"
	        	+'etat : ' + feature.properties.etat + "<br>" 
	        	+'last maj : ' + feature.properties.last_maj + "<br>" 
                +'<input name="to_flash" class="btn btn-success" value="Je l\'ai !" onclick=click_to_flash("' + feature.properties.name +'") readonly /> <input name="to_detruit" class="btn btn-warning" value="Déclarer detruit" onclick=click_to_detruit("' + feature.properties.name +'") readonly />' ,
	        	{maxHeight: 300, maxWidth:200});  //taille du pop up
	    }

    function onEachFeature_deja_flasher(feature, layer) {
            //creation du pop up pour quand oon click sur un invader
            layer.bindPopup(
                //le html du pop up :
                "<h4>" +
                feature.properties.name + "</h3><br>" +
                ( (feature.properties.image1 != 'None') ? '<a href="'+feature.properties.image1+'" target="_blank"> <img src="'+feature.properties.image1+'" width=95%></a>' : '') +
                ( (feature.properties.image2 != 'None') ? '<a href="'+feature.properties.image2+'" target="_blank"> <img src="'+feature.properties.image2+'" width=45%></a>' : '') + 
                ( (feature.properties.image3 != 'None') ? '<a href="'+feature.properties.image3+'" target="_blank"> <img src="'+feature.properties.image3+'" width=45%></a>' : '') +  "<br>"
                +'nbr de pts : ' + feature.properties.points + "<br>"
                +'etat : ' + feature.properties.etat + "<br>" 
                +'last maj : ' + feature.properties.last_maj + "<br>" 
                +'<input name="to_flash" class="btn btn-dark" value="Je l\'ai pas" onclick=click_to_NON_flash("' + feature.properties.name +'") readonly /> <input name="to_detruit" class="btn btn-warning" value="Déclarer detruit" onclick=click_to_detruit("' + feature.properties.name +'") readonly />' ,
                {maxHeight: 400, maxWidth:250});  //taille du pop up
        }

    function onEachFeature_detruits(feature, layer) {
            //creation du pop up pour quand oon click sur un invader
            layer.bindPopup(
                //le html du pop up :
                "<h4>" +
                feature.properties.name + "</h3><br>" +
                ( (feature.properties.image1 != 'None') ? '<a href="'+feature.properties.image1+'" target="_blank"> <img src="'+feature.properties.image1+'" width=95%></a>' : '') +
                ( (feature.properties.image2 != 'None') ? '<a href="'+feature.properties.image2+'" target="_blank"> <img src="'+feature.properties.image2+'" width=45%></a>' : '') + 
                ( (feature.properties.image3 != 'None') ? '<a href="'+feature.properties.image3+'" target="_blank"> <img src="'+feature.properties.image3+'" width=45%></a>' : '') +  "<br>"
                +'nbr de pts : ' + feature.properties.points + "<br>"
                +'etat : ' + feature.properties.etat + "<br>" 
                +'last maj : ' + feature.properties.last_maj + "<br>" 
                +'<input name="to_detruit" class="btn btn-primary" value="Déclarer non detruit" onclick=click_to_reactive("' + feature.properties.name +'") readonly />' ,
                {maxHeight: 400, maxWidth:250});  //taille du pop up
        }


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
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
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
               L_a_fla.clearLayers();
               L_a_fla.addData(JSON.parse(geojson.a_flasher).features);
               L_deja_fla.clearLayers();
               L_deja_fla.addData(JSON.parse(geojson.deja_flashe).features);
               L_detruits.clearLayers();
               L_detruits.addData(JSON.parse(geojson.detruits).features);
           }
        });
    }


    function click_to_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=vrai';
        $.ajax({
            type: "POST",
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
           }
        });
    }

    function click_to_NON_flash(inv_name) {
        var dataString = 'inv_name=' + inv_name + '&flash=faux';
        console.log(dataString);
        $.ajax({
            type: "POST",
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
           }
        });
    }

    var geojsonMarkerOptions_flash = {
	    radius: 4,
	    fillColor: "#00ff00",
	    color: "#000",
	    weight: 1,
	    opacity: 1,
	    fillOpacity: 0.8
	};

    var geojsonMarkerOptions_destr = {
	    radius: 4,
	    fillColor: "#ff7800",
	    color: "#000",
	    weight: 1,
	    opacity: 1,
	    fillOpacity: 0.8
	};


    var a_flasher =  <?php echo get_geojson_a_flasher();?> ;  //JSON.parse();
    var L_a_fla = L.geoJSON(a_flasher, {onEachFeature: onEachFeature_a_flasher}).addTo(map);

    var deja_flashé = <?php echo get_geojson_deja_flashe();?> ;
    var L_deja_fla = L.geoJSON(deja_flashé, {onEachFeature: onEachFeature_deja_flasher, pointToLayer: function (feature, latlng) {
        return L.circleMarker(latlng, geojsonMarkerOptions_flash);
    }});

    var detruits = <?php echo get_geojson_detruits();?> ;
    var L_detruits = L.geoJSON(detruits, {onEachFeature: onEachFeature_detruits, pointToLayer: function (feature, latlng) {
        return L.circleMarker(latlng, geojsonMarkerOptions_destr);
    }});

    var couches = L.layerGroup([L_a_fla, L_deja_fla, L_detruits]);


    var baseMaps = {
        "A flasher": L_a_fla,
        "Déjà flashés": L_deja_fla,
        "Détruits et inacessibles": L_detruits
    };

    var Lcontrol = L.control.layers(null,baseMaps, {collapsed:true}).addTo(map);

    /*geolocalisation*/
    /*Attention : necessite une connexion https pour que les navigateurs acceptent le partage*/
    var geoloc = L.control.locate({position: 'topleft',
      flyTo:'true',
      /*showCompass : 'true',*/
       locateOptions: {
               enableHighAccuracy: true
      }}).addTo(map);


</script>
