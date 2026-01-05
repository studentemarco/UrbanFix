<?php
// =======================
// API AJAX (stesso file)
// =======================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
    $lon = filter_input(INPUT_GET, 'lon', FILTER_VALIDATE_FLOAT);

    if ($lat === false || $lon === false) {
        echo json_encode(['error' => 'Coordinate non valide']);
        exit;
    }

    $query = <<<OVERPASS
[out:json];
is_in($lat,$lon)->.a;
rel(pivot.a)[boundary=administrative][admin_level=8];
out tags;
OVERPASS;

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query(['data' => $query]),
            'timeout' => 10
        ]
    ];

    $response = @file_get_contents(
        'https://overpass-api.de/api/interpreter',
        false,
        stream_context_create($options)
    );

    if (!$response) {
        echo json_encode(['error' => 'Overpass non raggiungibile']);
        exit;
    }

    $data = json_decode($response, true);

    foreach ($data['elements'] ?? [] as $el) {
        if (isset($el['tags']['wikidata'])) {
            echo json_encode([
                'name' => $el['tags']['name'] ?? 'Sconosciuto',
                'qid'  => $el['tags']['wikidata']
            ]);
            exit;
        }
    }

    echo json_encode(['error' => 'Comune non trovato']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Mappa OSM con Wikidata</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
body { font-family: sans-serif; margin: 0; }
#map { height: 500px; }
#info {
    padding: 10px;
    background: #fff;
    border-top: 1px solid #ccc;
}
</style>
</head>
<body>

<h2 style="padding:10px">Clicca sulla mappa</h2>
<div id="map"></div>
<div id="info">Nessuna selezione</div>

<script>
const map = L.map('map').setView([45.4642, 9.19], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

let marker = null;

map.on('click', e => {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    if (!marker) {
        marker = L.marker([lat, lon]).addTo(map);
    } else {
        marker.setLatLng([lat, lon]);
    }

    document.getElementById('info').innerHTML = `
        <p><b>Lat:</b> ${lat.toFixed(6)}<br>
        <b>Lon:</b> ${lon.toFixed(6)}</p>
        <p>Caricamento...</p>
    `;

    fetch(`?ajax=1&lat=${lat}&lon=${lon}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('info').innerHTML +=
                    `<p style="color:red">${data.error}</p>`;
                return;
            }

            document.getElementById('info').innerHTML = `
                <h3>${data.name}</h3>
                <p><b>Wikidata:</b>
                <a href="https://www.wikidata.org/wiki/${data.qid}" target="_blank">
                ${data.qid}
                </a></p>
                <pre style="color: lightgray">${JSON.stringify(data, null, 2)}</pre>
            `;
        });
});
</script>

</body>
</html>