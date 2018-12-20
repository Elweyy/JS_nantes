<?php

$opts = array('http' => array('proxy'=> 'tcp://www-cache.iutnc.univ-lorraine.fr:3128/', 'request_fulluri'=> true));

$context = stream_context_create($opts); 

// Passer ça en fonction pour la recherche d'erreur 

function appelCoordonnee($url){
    $json_raw = file_get_contents($url, false, $context);

    if(http_response_code()== 200){

        return $json_raw;

    } else {
        echo http_response_code();
        return null;
    }
}

$json_raw = file_get_contents("https://geo.api.gouv.fr/communes?nom=Nantes&fields=centre", false, $context);

$json_proper = json_decode(appelCoordonnee("https://geo.api.gouv.fr/communes?nom=Nantes&fields=centre"),false);

$long = $json_proper[0]->{centre} ->{coordinates}[0];
$lat =  $json_proper[0]->{centre} ->{coordinates}[1];

var_dump($lat);
var_dump($long);

// Fin de la fonction

//$trafic_raw = file_get_contents("https://data.nantesmetropole.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&facet=nature&facet=type", false, $context);

//  $trafic_proper = json_decode($trafic_raw, false);




$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
   integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
   integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
   crossorigin=""></script>
   <style>
    #mapid { height: 400px; };
   </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="Titre">
            <h1 id="header">Test map</h1>
        </div>
        <div id="mapid"></div>
    </div>
</div> 
<script> 		

var mymap = L.map('mapid').setView([{$lat}, {$long}], 10);

L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
    maxZoom: 18,
    id: 'mapbox.streets'
}).addTo(mymap);

</script>
</body>
</html>
HTML;

echo $html;