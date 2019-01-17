<?php
function appelCoordonnee($url){
    $opts = array('http' => array('proxy'=> 'tcp://www-cache.iutnc.univ-lorraine.fr:3128/', 'request_fulluri'=> true));
    $context = stream_context_create($opts);

    $json_raw = file_get_contents($url, false, $context);
    if(http_response_code()== 200){
        return $json_raw;
    } else {
        echo http_response_code();
        return null;
    }
}

// Map de  Nantes
$json_raw = appelCoordonnee("https://geo.api.gouv.fr/communes?nom=Nantes&format=json&fields=centre");
$json_proper = json_decode($json_raw,false);
$long = $json_proper[0]->{'centre'} ->{'coordinates'}[0];
$lat =  $json_proper[0]->{'centre'} ->{'coordinates'}[1];

// Récupérer les infos trafic
$trafic_raw = appelCoordonnee("https://data.nantesmetropole.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&facet=nature&facet=type");
$trafic_proper = json_decode($trafic_raw, false);
$trafic_length = sizeof($trafic_proper->{'records'});

// Stockage dans un tableau
$trafic_points = [];
for($points = 0; $points<$trafic_length; $points++){
  $long_p= $trafic_proper->{'records'}[$points]->{'fields'}->{'localisation'}[0];
  $lat_p = $trafic_proper->{'records'}[$points]->{'fields'}->{'localisation'}[1];
  $event = $trafic_proper->{'records'}[$points]->{'fields'}->{'nature'};

  $tableau = [$long_p, $lat_p, $event];
  array_push($trafic_points, $tableau);

}

// On encode tout en JSON
$final_trafic = json_encode($trafic_points);


// Mars

$curiosity = json_decode(appelCoordonnee("https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?sol=1000&camera=rhaz&api_key=kdlIh4yvdWnDv8ag5AYZpCrlYWU8dfU4V1fACMc0"));
$img = $curiosity->{'photos'}[sizeof($curiosity->{'photos'})-1]->{'img_src'};

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Agglomération de Nantes</title>
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
            <h1 id="header">Agglomération de Nantes</h1>
        </div>
        <div id="mapid"></div>
    </div>
</div>
<script>

trafic_points = {$final_trafic}
var mymap = L.map('mapid').setView([{$lat}, {$long}], 10);
L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
    maxZoom: 18,
    id: 'mapbox.streets'
}).addTo(mymap);

trafic_points.forEach(function(trafic_point){
  L.marker([ trafic_point[0] , trafic_point[1] ]).addTo(mymap).bindPopup("<h3>" + trafic_point[2] + "</h3>");
            });

</script>

<h2> Last picture of Mars by Curiosity rover</h2>
<img src='{$img}' width=200px height=auto />
</body>
</html>
HTML;
echo $html . "</body></html>";
