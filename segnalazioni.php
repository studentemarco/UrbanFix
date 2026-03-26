<?php
    session_start();
    require_once 'config.php';
    
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    $email = urlencode($_SESSION['email']);

    // $context = stream_context_create([
    //     'http' => [
    //         'method' => 'GET',
    //         'header' =>
    //             "Content-Type: application/json\r\n" .
    //             "Authorization: Bearer " . ($_SESSION['token'] ?? '') . "\r\n"
    //     ]
    // ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer " . ($_SESSION['token'] ?? '')
            ],
            'ignore_errors' => true
        ]
    ]);


    //var_dump(API_URL . "ruolo/".$email);

    $response = file_get_contents(
        API_URL . "privilegi/".$email,
        false,
        $context
    );

    $status_code = 0;
    if (isset($http_response_header)) {
        // La prima riga è sempre "HTTP/1.1 200 OK" o simile
        preg_match('{HTTP\/\S*\s(\d+)}', $http_response_header[0], $matches);
        $status_code = intval($matches[1]);
    }

    // CONTROLLO DIRETTO SUL CODICE 401
    if ($status_code === 401) {
        // Token scaduto o non valido: vai al logout
        header("Location: account/logout.php");
        exit;
    }

    // Se il codice non è 200, qualcosa è andato storto (opzionale)
    if ($status_code !== 200) {
        // Gestisci altri errori (es. 404, 500) se vuoi
        die("Errore API: Codice di stato " . $status_code);
    }

    // $ruolo = json_decode($response, true);
    // var_dump($response);
    // var_dump($ruolo);
    
    $data_api = json_decode($response, true);
    
    //se l'api risponde che il token non è valido, reindirizzo al logout in modo da eliminare la sessione
    // if (!$response || !isset($response["success"]) || !$response["success"]) {
    //     header("Location: account/logout.php");
    //     exit;
    // }



    // 2. Trasformiamo l'array dei privilegi in un formato facile da controllare
    // Risultato atteso: ['VISUALIZZA_SEGNALAZIONI', 'CREA_SEGNALAZIONE', ...]
    $privilegi = [];
    if (isset($data_api['data']) && is_array($data_api['data'])) {
        $privilegi = array_column($data_api['data'], 'nome');
    }

    // Funzione di utilità per il controllo nella UI
    function può($nome_privilegio, $lista_privilegi) {
        return in_array($nome_privilegio, $lista_privilegi);
    }
?>

<!doctype html>
<html lang="it">
    <head>
        <title>UrbanFix - Segnalazioni</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="style.css">
        <!-- Leaflet CSS per la mappa -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #mapSegnalazione {
                height: 400px;
                width: 100%;
                border-radius: 8px;
                margin-top: 10px;
                position: relative;
            }
            
            .map-container {
                position: relative;
            }
            
            .map-loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.9);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                border-radius: 8px;
            }
            
            .map-loading-overlay.active {
                display: flex;
            }
            
            .map-loading-content {
                text-align: center;
            }
            
            .map-disabled {
                opacity: 0.5;
                pointer-events: none;
            }
            
            .coordinate-alert {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                min-width: 300px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                animation: slideInRight 0.3s ease-out;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
    </head>

    <body>
        <header>
            <!-- place navbar here -->
            <?php $current_page = 'segnalazioni'; include 'navbar.php'; ?>
        </header>
        <main>
            <div class="container my-5">
                <?php if (può('GESTISCI_UTENTI', $privilegi) || può('GESTISCI_AMMINISTRAZIONI', $privilegi) || può('GESTISCI_COMUNE', $privilegi)): ?>
                <div class="admin-section d-flex justify-content-center gap-2">
                    <h4 class="text-danger">Pannello Amministrazione</h4>
                    <div class="d-flex gap-2">
                        <?php if (può('GESTISCI_UTENTI', $privilegi)): ?>
                            <a href="admin_utenti.php" class="btn btn-danger">👤 Gestione Utenti</a>
                        <?php endif; ?>
        
                        <?php if (può('GESTISCI_COMUNE', $privilegi)): ?>
                            <a href="admin_comune.php" class="btn btn-danger">🏛️ Gestione Comune</a>
                        <?php endif; ?>
        
                        <?php if (può('GESTISCI_AMMINISTRAZIONI', $privilegi)): ?>
                            <a href="admin_enti.php" class="btn btn-danger">🏢 Gestione Enti</a>
                        <?php endif; ?>
                    </div>
                    <hr>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1>Segnalazioni</h1>
                        <p class="text-muted">Gestisci le problematiche del tuo territorio.</p>
                    </div>
                    
                    <?php if (può('CREA_SEGNALAZIONE', $privilegi)): ?>
                        <button id="btnNuovaSegnalazione" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuovaSegnalazione">+ Nuova Segnalazione</button>
                    <?php endif; ?>
                </div>

                <div class="action-bar d-flex flex-wrap gap-2">
                    <?php if (può('VISUALIZZA_SEGNALAZIONI_AREA', $privilegi)): ?>
                        <button class="btn btn-outline-secondary">Filtra per il mio Comune</button>
                    <?php endif; ?>

                    <?php if (può('GESTISCI_STATO_SEGNALAZIONE', $privilegi)): ?>
                        <button class="btn btn-outline-warning">Aggiorna Stati</button>
                    <?php endif; ?>
                </div>
                


                <?php 
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => [
                                "Content-Type: application/json",
                                "Authorization: Bearer " . ($_SESSION['token'] ?? '')
                                // "Origin: " . SERVER_URL 
                            ],
                            'ignore_errors' => true // Permette di leggere il body anche se l'HTTP status è 4xx o 5xx
                        ]
                    ]);

                    $response = file_get_contents(
                        API_URL . "problemi", 
                        false, 
                        $context
                    );

                    $data = json_decode($response, true);

                    // if (!$data || !isset($data["success"]) || !$data["success"]) {
                    //     $messaggio = $data["description"] ?? "Errore durante il recupero dei problemi";
                    //     echo "<div class='alert alert-danger'>$messaggio</div>";
                    // }

                    foreach ($data["data"] as $problema) {
                        //var_dump($problema);
                        include 'segnalazioneTemplate.php';
                        //va a capo
                        echo "<hr/>";
                    }
                ?>

            </div>
        </main>

        <!-- Modal Nuova Segnalazione -->
        <div class="modal fade" id="modalNuovaSegnalazione" tabindex="-1" aria-labelledby="modalNuovaSegnalazioneLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalNuovaSegnalazioneLabel">Nuova Segnalazione</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNuovaSegnalazione">
                            <div class="mb-3">
                                <label for="inputTitolo" class="form-label">Titolo *</label>
                                <input type="text" class="form-control" id="inputTitolo" placeholder="Es: Buca nella strada" required>
                                <small class="text-muted">Minimo 5 caratteri</small>
                            </div>

                            <div class="mb-3">
                                <label for="inputDescrizione" class="form-label">Descrizione *</label>
                                <textarea class="form-control" id="inputDescrizione" rows="4" placeholder="Descrivi il problema in dettaglio..." required></textarea>
                                <small class="text-muted">Minimo 10 caratteri</small>
                            </div>

                            <div class="mb-3">
                                <label for="inputComune" class="form-label">Comune *</label>
                                <select class="form-select" id="inputComune" required>
                                    <option value="">-- Seleziona un comune --</option>
                                </select>
                                <small class="text-muted">Caricamento dei comuni...</small>
                            </div>

                            <div class="mb-3">
                                <label for="inputCoordinate" class="form-label">Coordinate (lat, lon) *</label>
                                <input type="text" class="form-control" id="inputCoordinate" placeholder="Es: 40.7128,-74.0060" required readonly>
                                <small class="text-muted">Clicca sulla mappa per selezionare la posizione</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Seleziona la posizione sulla mappa</label>
                                <div class="map-container">
                                    <div id="mapSegnalazione"></div>
                                    <div id="mapLoadingOverlay" class="map-loading-overlay">
                                        <div class="map-loading-content">
                                            <div class="spinner-border text-primary mb-3" role="status">
                                                <span class="visually-hidden">Caricamento...</span>
                                            </div>
                                            <h5>Caricamento confini del comune...</h5>
                                            <p class="text-muted">Attendere prego</p>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">Clicca sulla mappa per impostare le coordinate della segnalazione</small>
                            </div>

                            <div id="alertContainer"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-primary" id="btnInviaForm">Invia Segnalazione</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>

        <!-- Leaflet JavaScript per la mappa -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        
        <!-- Turf.js per operazioni geometriche -->
        <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

        <script>
            // NO TOKEN esposto! Usiamo il proxy server-side
            const modal = document.getElementById('modalNuovaSegnalazione');
            let map = null;
            let marker = null;
            let comuniBounds = {}; // Cache dei confini dei comuni
            let comunePolygon = null; // Poligono del comune corrente
            let overlayLayer = null; // Layer grigio che copre tutto tranne il comune
            let currentComuneGeometry = null; // Geometria GeoJSON del comune corrente
            let mapClickEnabled = true; // Flag per abilitare/disabilitare i click sulla mappa
            
            // Funzione per mostrare un alert moderno
            function showAlert(message, type = 'warning') {
                // Rimuovi eventuali alert esistenti
                const existingAlert = document.querySelector('.coordinate-alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Mappa delle icone per tipo
                const icons = {
                    'warning': '⚠️',
                    'danger': '❌',
                    'success': '✅',
                    'info': 'ℹ️'
                };
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show coordinate-alert`;
                alertDiv.innerHTML = `
                    <strong>${icons[type] || 'ℹ️'}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                // Rimuovi automaticamente dopo 4 secondi
                setTimeout(() => {
                    if (alertDiv && alertDiv.parentNode) {
                        alertDiv.classList.remove('show');
                        setTimeout(() => alertDiv.remove(), 300);
                    }
                }, 4000);
            }
            
            // Funzione per mostrare/nascondere l'overlay di caricamento
            function setMapLoading(loading) {
                const overlay = document.getElementById('mapLoadingOverlay');
                const mapDiv = document.getElementById('mapSegnalazione');
                
                if (loading) {
                    overlay.classList.add('active');
                    mapDiv.classList.add('map-disabled');
                    mapClickEnabled = false;
                } else {
                    overlay.classList.remove('active');
                    mapDiv.classList.remove('map-disabled');
                    mapClickEnabled = true;
                }
            }

            // Inizializza la mappa quando il modal viene mostrato
            modal.addEventListener('shown.bs.modal', async function() {
                const selectComune = document.getElementById('inputComune');
                const smallText = document.querySelector('#inputComune + small');
                
                // Inizializza la mappa se non è stata già creata
                if (!map) {
                    map = L.map('mapSegnalazione').setView([45.4642, 9.19], 8);
                    
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                        maxZoom: 19
                    }).addTo(map);

                    // Gestisci il click sulla mappa
                    map.on('click', function(e) {
                        // Verifica se i click sono abilitati
                        if (!mapClickEnabled) {
                            return;
                        }
                        
                        const lat = e.latlng.lat;
                        const lon = e.latlng.lng;

                        // Se c'è un comune selezionato, verifica che il punto sia dentro i confini
                        if (currentComuneGeometry) {
                            // Crea un punto in formato GeoJSON [lon, lat]
                            const point = turf.point([lon, lat]);
                            
                            try {
                                let isInside = false;
                                
                                // Verifica per ogni poligono nella geometria
                                for (let i = 0; i < currentComuneGeometry.coordinates.length; i++) {
                                    // currentComuneGeometry.coordinates[i] contiene array di [lat, lon]
                                    // Convertiamo a [lon, lat] per GeoJSON/Turf
                                    const coords = currentComuneGeometry.coordinates[i].map(c => [c[1], c[0]]);
                                    const polygon = turf.polygon([coords]);
                                    
                                    // Se il punto è dentro questo poligono, è valido
                                    if (turf.booleanPointInPolygon(point, polygon)) {
                                        isInside = true;
                                        break;
                                    }
                                }
                                
                                console.log('Click: lat=', lat, 'lon=', lon, 'isInside=', isInside, 'Poligoni verificati:', currentComuneGeometry.coordinates.length);
                                
                                if (!isInside) {
                                    showAlert('Seleziona un punto all\'interno del comune scelto', 'warning');
                                    return;
                                }
                            } catch (error) {
                                console.error('Errore nella verifica del punto:', error);
                                // In caso di errore, mostra avviso ma permetti il click
                                showAlert('Attenzione: verifica confini non riuscita. Controlla la posizione selezionata.', 'warning');
                            }
                        } else {
                            // Se non c'è un comune selezionato, non permettere il click
                            showAlert('Seleziona prima un comune dall\'elenco a discesa', 'info');
                            return;
                        }

                        // Aggiorna o crea il marker
                        if (!marker) {
                            marker = L.marker([lat, lon]).addTo(map);
                        } else {
                            marker.setLatLng([lat, lon]);
                        }

                        // Aggiorna il campo coordinate
                        document.getElementById('inputCoordinate').value = `${lat.toFixed(6)},${lon.toFixed(6)}`;
                    });
                }

                // Ridimensiona la mappa per evitare problemi di visualizzazione
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);

                // Se già caricati, non ricaricare
                if (selectComune.options.length > 1) {
                    smallText.textContent = 'Seleziona il tuo comune';
                    return;
                }

                smallText.textContent = 'Caricamento dei comuni...';

                try {
                    // Chiama il proxy PHP invece dell'API diretta
                    const response = await fetch('api_proxy.php?action=comuni', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    console.log('Comuni ricevuti:', data);

                    if (response.ok && data.data) {
                        // Popola il select
                        data.data.forEach(comune => {
                            const option = document.createElement('option');
                            option.value = comune.QID;
                            option.textContent = comune.nome + ' (' + comune.QID + ')';
                            option.dataset.nome = comune.nome;
                            selectComune.appendChild(option);
                        });
                        smallText.textContent = 'Seleziona il tuo comune';
                    } else {
                        console.error('Errore nel caricamento dei comuni');
                        selectComune.innerHTML = '<option value="">Errore nel caricamento</option>';
                        smallText.textContent = 'Errore nel caricamento dei comuni';
                    }
                } catch (error) {
                    console.error('Errore:', error);
                    selectComune.innerHTML = '<option value="">Errore di comunicazione</option>';
                    smallText.textContent = 'Errore di comunicazione con il server';
                }
            });

            // Quando viene selezionato un comune, centra la mappa su di esso
            document.getElementById('inputComune').addEventListener('change', async function(e) {
                const qid = e.target.value;
                const nomeComune = e.target.options[e.target.selectedIndex].dataset.nome;
                
                if (!qid || !map) return;

                // Mostra l'overlay di caricamento
                setMapLoading(true);

                // Rimuovi il poligono e l'overlay precedenti
                if (comunePolygon) {
                    map.removeLayer(comunePolygon);
                    comunePolygon = null;
                }
                if (overlayLayer) {
                    map.removeLayer(overlayLayer);
                    overlayLayer = null;
                }
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                currentComuneGeometry = null;
                document.getElementById('inputCoordinate').value = '';

                try {
                    await searchComuneNominatim(nomeComune);
                } catch (error) {
                    console.error('Errore nel recupero dei confini:', error);
                } finally {
                    // Nascondi l'overlay di caricamento
                    setMapLoading(false);
                }
            });

            // Funzione di fallback per cercare un comune su Nominatim
            async function searchComuneNominatim(nomeComune) {
                console.log('Cercando su Nominatim:', nomeComune);
                
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(nomeComune + ', Italia')}&format=json&polygon_geojson=1&polygon_threshold=0.0&limit=1`,
                        {
                            headers: {
                                'User-Agent': 'UrbanFix/1.0'
                            }
                        }
                    );
                    const data = await response.json();
                    console.log('Dati Nominatim ricevuti:', data);
                    
                    if (data && data.length > 0) {
                        const result = data[0];
                        const bounds = result.boundingbox;
                        const geojson = result.geojson;
                        
                        console.log('GeoJSON type:', geojson?.type, 'Coordinates:', geojson?.coordinates?.length);
                        
                        if (geojson && geojson.coordinates && geojson.coordinates.length > 0) {
                            if (geojson.type === 'Polygon') {
                                let coordsForStorage = geojson.coordinates[0].map(coord => [coord[1], coord[0]]);
                                currentComuneGeometry = {
                                    type: 'MultiPolygon',
                                    coordinates: [coordsForStorage]
                                };
                            } else if (geojson.type === 'MultiPolygon') {
                                let allPolygons = [];
                                geojson.coordinates.forEach(polygon => {
                                    if (polygon[0] && polygon[0].length > 0) {
                                        let coordsForStorage = polygon[0].map(coord => [coord[1], coord[0]]);
                                        allPolygons.push(coordsForStorage);
                                    }
                                });
                                currentComuneGeometry = {
                                    type: 'MultiPolygon',
                                    coordinates: allPolygons
                                };
                            }

                            // Use Native Leaflet GeoJSON to render perfectly without auto-intersections
                            comunePolygon = L.geoJSON(geojson, {
                                style: {
                                    color: '#3388ff',
                                    weight: 3,
                                    fillOpacity: 0
                                }
                            }).addTo(map);

                            // Grey world overlay with holes for our polygons
                            const worldBounds = [
                                [-90, -180],
                                [-90, 180],
                                [90, 180],
                                [90, -180],
                                [-90, -180]
                            ];
                            
                            let overlayCoords = [worldBounds];
                            if (currentComuneGeometry && currentComuneGeometry.coordinates) {
                                currentComuneGeometry.coordinates.forEach(polyCoords => {
                                    overlayCoords.push(polyCoords);
                                });
                            }

                            overlayLayer = L.polygon(overlayCoords, {
                                color: 'transparent',
                                fillColor: '#000000',
                                fillOpacity: 0.3,
                                interactive: false
                            }).addTo(map);
                            
                            showAlert(`Confini di ${nomeComune} caricati`, 'success');
                        } else {
                            console.error('Nessuna geometria GeoJSON da Nominatim o coordinates vuoto');
                            showAlert(`Confini non disponibili per ${nomeComune}. Seleziona un altro comune.`, 'danger');
                        }
                        
                        if (bounds) {
                            // bounds format: [minLat, maxLat, minLon, maxLon]
                            const leafletBounds = [
                                [parseFloat(bounds[0]), parseFloat(bounds[2])],
                                [parseFloat(bounds[1]), parseFloat(bounds[3])]
                            ];
                            map.fitBounds(leafletBounds);
                        }
                    } else {
                        console.error('Nessun risultato da Nominatim');
                        showAlert(`Comune ${nomeComune} non trovato. Seleziona un altro comune.`, 'danger');
                    }
                } catch (error) {
                    console.error('Errore nella ricerca su Nominatim:', error);
                    showAlert(`Errore nel caricamento dei confini. Riprova.`, 'danger');
                }
            }

            // Gestione del click sul bottone Invia
            document.getElementById('btnInviaForm').addEventListener('click', async function() {
                const form = document.getElementById('formNuovaSegnalazione');
                const alertContainer = document.getElementById('alertContainer');

                // Validate form
                if (!form.checkValidity()) {
                    alertContainer.innerHTML = '<div class="alert alert-danger" role="alert">Perfavore compila tutti i campi obbligatori</div>';
                    return;
                }

                // Raccogli i dati
                const titolo = document.getElementById('inputTitolo').value.trim();
                const descrizione = document.getElementById('inputDescrizione').value.trim();
                const comune_qid = document.getElementById('inputComune').value.trim();
                const coordinate = document.getElementById('inputCoordinate').value.trim();

                // Logga i dati raccolti
                console.log('[DEBUG] Dati raccolti per invio:', {
                    titolo,
                    descrizione,
                    comune_qid,
                    coordinate
                });

                // Disabilita il bottone durante l'invio
                const btnInvia = document.getElementById('btnInviaForm');
                btnInvia.disabled = true;
                btnInvia.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Invio in corso...';

                try {

                    // Prepara il payload
                    const payload = {
                        titolo: titolo,
                        descrizione: descrizione,
                        comune_qid: comune_qid,
                        coordinate: coordinate
                    };
                    console.log('[DEBUG] Payload inviato:', JSON.stringify(payload));

                    // Chiama il proxy PHP server-side (sicuro, token non esposto)
                    const response = await fetch('api_proxy.php?action=crea_segnalazione', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    // Leggi il response come testo prima
                    const responseText = await response.text();
                    console.log('[DEBUG] Response Status:', response.status);
                    console.log('[DEBUG] Response Text:', responseText);

                    // Prova a parsare il JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        alertContainer.innerHTML = '<div class="alert alert-danger" role="alert">✗ Errore di parsing JSON. Status: ' + response.status + '. Risposta: ' + responseText + '</div>';
                        throw e;
                    }

                    if (response.ok && data.method) {
                        // Successo
                        alertContainer.innerHTML = '<div class="alert alert-success" role="alert">✓ Segnalazione creata con successo! La pagina si ricaricherà tra breve...</div>';
                        
                        // Resetta il form
                        form.reset();
                        
                        // Ricarica la pagina dopo 2 secondi
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Errore dall'API
                        const errorMsg = data.description || data.error || 'Errore sconosciuto durante la creazione della segnalazione';
                        alertContainer.innerHTML = '<div class="alert alert-danger" role="alert">✗ Errore: ' + errorMsg + '</div>';
                    }
                } catch (error) {
                    // Errore di rete o parsing
                    console.error('Errore:', error);
                    alertContainer.innerHTML = '<div class="alert alert-danger" role="alert">✗ Errore di comunicazione con il server: ' + error.message + '</div>';
                } finally {
                    // Riabilita il bottone
                    btnInvia.disabled = false;
                    btnInvia.innerHTML = 'Invia Segnalazione';
                }
            });

            // Pulisci gli alert quando chiudi il modal
            modal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('formNuovaSegnalazione').reset();
                document.getElementById('alertContainer').innerHTML = '';
                
                // Rimuovi eventuali alert di coordinate
                const coordinateAlert = document.querySelector('.coordinate-alert');
                if (coordinateAlert) {
                    coordinateAlert.remove();
                }
                
                // Rimuovi il marker dalla mappa quando chiudi il modal
                if (marker && map) {
                    map.removeLayer(marker);
                    marker = null;
                }
                
                // Rimuovi il poligono del comune
                if (comunePolygon && map) {
                    map.removeLayer(comunePolygon);
                    comunePolygon = null;
                }
                
                // Rimuovi l'overlay grigio
                if (overlayLayer && map) {
                    map.removeLayer(overlayLayer);
                    overlayLayer = null;
                }
                
                // Reset geometria corrente
                currentComuneGeometry = null;
                
                // Nascondi l'overlay di caricamento se attivo
                setMapLoading(false);
                
                // Resetta la vista della mappa
                if (map) {
                    map.setView([45.4642, 9.19], 8);
                }
            });
        </script>
    </body>
</html>
