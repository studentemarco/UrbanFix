<?php
session_start();
require_once 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email']) || !isset($_SESSION['token'])) {
    header("Location: account/login.php");
    exit;
}

$email = $_SESSION['email'];
$token = $_SESSION['token'];

function call_api($endpoint, $method = 'GET', $data = null) {
    global $token;
    $options = [
        'http' => [
            'method' => $method,
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $token
            ],
            'ignore_errors' => true
        ]
    ];
    if ($data !== null) {
        $options['http']['content'] = json_encode($data);
    }
    $context = stream_context_create($options);
    $response = file_get_contents(API_URL . $endpoint, false, $context);
    return json_decode($response, true);
}

$me_res = call_api("dipendenti/" . urlencode($email));
if (!$me_res || isset($me_res['error']) || empty($me_res['data'])) {
    die("Accesso negato: Non risulti essere un dipendente comunale e non hai i permessi per accedere a questa pagina. Debug info: " . json_encode($me_res));
}

$me = $me_res['data'];
$comuni_qid = $me['Comuni_QID'];
$is_admin = (bool)$me['isAdminComunale'];

// Azioni POST (Pattern PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'aggiorna_stato') {
        $problema_id = (int)$_POST['problema_id'];
        $nuovo_stato = $_POST['stato'];
        $res = call_api("problemi/" . $problema_id . "/stato", "POST", [
            "stato" => $nuovo_stato,
            "comuni_qid" => $comuni_qid
        ]);
        if ($res && !isset($res['error'])) {
            $_SESSION['flash_success'] = "Stato della segnalazione n°$problema_id aggiornato con successo.";
        } else {
            $_SESSION['flash_error'] = "Nessuna modifica effettuata o errore API: " . ($res['description'] ?? 'Sconosciuto');
        }
        $_SESSION['active_tab'] = 'segnalazioni';
    }
    else if ($_POST['action'] === 'aggiungi_dipendente' && $is_admin) {
        $res = call_api("dipendenti", "POST", [
            "nome" => trim($_POST['nome']),
            "cognome" => trim($_POST['cognome']),
            "email" => trim($_POST['email']),
            "password" => trim($_POST['password']),
            "comuni_qid" => $comuni_qid
        ]);
        if ($res && !isset($res['error'])) {
            $_SESSION['flash_success'] = "Dipendente registrato o aggiornato con successo.";
        } else {
            $_SESSION['flash_error'] = "Errore durante l'aggiunta del dipendente: " . ($res['description'] ?? 'Sconosciuto');
        }
        $_SESSION['active_tab'] = 'dipendenti';
    }
    else if ($_POST['action'] === 'rimuovi_dipendente' && $is_admin) {
        $dip_email = trim($_POST['email_rimuovi']);
        if ($dip_email === $email) {
            $_SESSION['flash_error'] = "Non puoi rimuovere te stesso dall'amministrazione.";
        } else {
            $res = call_api("dipendenti/" . urlencode($dip_email), "DELETE");
            if ($res && !isset($res['error'])) {
                $_SESSION['flash_success'] = "Dipendente rimosso con successo dal comune.";
            } else {
                $_SESSION['flash_error'] = "Errore durante la rimozione del dipendente: " . ($res['description'] ?? 'Sconosciuto');
            }
        }
        $_SESSION['active_tab'] = 'dipendenti';
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Recupera i messaggi flash
$success = $_SESSION['flash_success'] ?? "";
$error = $_SESSION['flash_error'] ?? "";
$active_tab = $_SESSION['active_tab'] ?? "segnalazioni";
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['active_tab']);

// Recupera dati per la view
$comune_res = call_api("comuni/" . urlencode($comuni_qid));
$nome_comune = ($comune_res && isset($comune_res['data']['nome'])) ? $comune_res['data']['nome'] : "Sconosciuto";

$problemi_res = call_api("problemi/comune/" . urlencode($comuni_qid));
$problemi = ($problemi_res && isset($problemi_res['data'])) ? $problemi_res['data'] : [];

$dipendenti = [];
if ($is_admin) {
    $dip_res = call_api("dipendenti/comune/" . urlencode($comuni_qid));
    if ($dip_res && isset($dip_res['data'])) {
        $dipendenti = $dip_res['data'];
    }
}
?>
<!doctype html>
<html lang="it">
<head>
    <title>Pannello Comune - <?php echo htmlspecialchars($nome_comune); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="style.css">
    <style>
        .problem-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .problem-card.active-hover, .problem-card:hover {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        #adminMap {
            height: 70vh;
            min-height: 500px;
            border-radius: 8px;
            z-index: 1;
        }
        .problems-list {
            height: 70vh;
            overflow-y: auto;
            padding-right: 10px;
        }
    </style>
</head>

<body style="background-color: #f4f6f9; padding-top: 80px;">
    <header>
        <?php $current_page = 'admin_comune'; include 'navbar.php'; ?>
    </header>
    
    <main class="container-fluid px-4 my-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">
                    <i class="bi bi-building"></i> Amministrazione: <?php echo htmlspecialchars($nome_comune); ?>
                </h2>
                <p class="text-muted">
                    <?php if ($is_admin): ?>
                        Sei un Amministratore Comunale e hai accesso completo.
                    <?php else: ?>
                        Sei un Dipendente Comunale. Gestisci le segnalazioni relative al tuo territorio.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Sistema di schede (Tabs) -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab === 'segnalazioni' ? 'active' : ''; ?>" id="segnalazioni-tab" data-bs-toggle="tab" data-bs-target="#segnalazioni" type="button" role="tab">Gestione Segnalazioni</button>
            </li>
            <?php if ($is_admin): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab === 'dipendenti' ? 'active' : ''; ?>" id="dipendenti-tab" data-bs-toggle="tab" data-bs-target="#dipendenti" type="button" role="tab">Gestione Dipendenti</button>
            </li>
            <?php endif; ?>
        </ul>
        
        <div class="tab-content" id="adminTabsContent">
            
            <!-- SCHEDA SEGNALAZIONI (Split View) -->
            <div class="tab-pane fade <?php echo $active_tab === 'segnalazioni' ? 'show active' : ''; ?>" id="segnalazioni" role="tabpanel">
                <div class="row">
                    <!-- Colonna di Sinistra: Lista Problemi -->
                    <div class="col-lg-5 col-xl-4 mb-4">
                        <div class="problems-list">
                            <?php if (count($problemi) > 0): ?>
                                <?php foreach($problemi as $p): ?>
                                    <div class="card shadow-sm mb-3 problem-card" data-id="<?php echo $p['ID']; ?>" data-lat="<?php echo htmlspecialchars($p['latitudine'] ?? ''); ?>" data-lng="<?php echo htmlspecialchars($p['longitudine'] ?? ''); ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title fw-bold mb-0">#<?php echo $p['ID']; ?> - <?php echo htmlspecialchars($p['titolo']); ?></h6>
                                                <span class="badge border <?php 
                                                    echo $p['stato'] == 'aperto' ? 'bg-danger' : 
                                                        ($p['stato'] == 'in lavorazione' ? 'bg-warning text-dark' : 
                                                        ($p['stato'] == 'risolto' ? 'bg-success' : 'bg-info text-dark')); ?>">
                                                    <?php echo strtoupper($p['stato']); ?>
                                                </span>
                                            </div>
                                            <p class="card-text small text-muted mb-2">
                                                <i class="bi bi-clock"></i> Segnalato: <?php echo date('d/m/Y H:i', strtotime($p['timestampSegnalazione'])); ?><br>
                                                <?php if(!empty($p['timestampStato'])): ?>
                                                    <i class="bi bi-arrow-repeat"></i> Aggiornato: <?php echo date('d/m/Y H:i', strtotime($p['timestampStato'])); ?><br>
                                                <?php endif; ?>
                                                <i class="bi bi-geo-alt"></i> Vicino a: <span class="addr-nominatim" data-lat="<?php echo htmlspecialchars($p['latitudine'] ?? ''); ?>" data-lng="<?php echo htmlspecialchars($p['longitudine'] ?? ''); ?>">Caricamento...</span><br>
                                                <i class="bi bi-person"></i> Utente: <?php echo htmlspecialchars($p['nome_utente'] . ' ' . $p['cognome_utente']); ?>
                                            </p>
                                            
                                            <form method="POST" class="form-aggiorna-stato mt-3">
                                                <input type="hidden" name="action" value="aggiorna_stato">
                                                <input type="hidden" name="problema_id" value="<?php echo $p['ID']; ?>">
                                                <div class="input-group input-group-sm">
                                                    <select name="stato" class="form-select status-select">
                                                        <option value="aperto" <?php if($p['stato']=='aperto') echo 'selected';?>>Aperto</option>
                                                        <option value="in lavorazione" <?php if($p['stato']=='in lavorazione') echo 'selected';?>>In Lavorazione</option>
                                                        <option value="monitoraggio" <?php if($p['stato']=='monitoraggio') echo 'selected';?>>In Monitoraggio</option>
                                                        <option value="risolto" <?php if($p['stato']=='risolto') echo 'selected';?>>Risolto</option>
                                                    </select>
                                                    <button type="button" class="btn btn-outline-primary btn-salva-stato">Aggiorna</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center p-4 text-muted border rounded bg-white">
                                    Nessuna segnalazione ricevuta.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Colonna di Destra: Mappa -->
                    <div class="col-lg-7 col-xl-8">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-0">
                                <div id="adminMap"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SCHEDA DIPENDENTI (SOLO SUPERADMIN) -->
            <?php if ($is_admin): ?>
            <div class="tab-pane fade <?php echo $active_tab === 'dipendenti' ? 'show active' : ''; ?>" id="dipendenti" role="tabpanel">
                <div class="row">
                    <!-- Lista Dipendenti -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 pt-2"><i class="bi bi-people"></i> Elenco Dipendenti</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                <?php foreach($dipendenti as $dip): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($dip['nome'] . ' ' . $dip['cognome']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($dip['email']); ?></small>
                                            <?php if ($dip['isAdminComunale']): ?>
                                                <span class="badge bg-dark ms-2">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($dip['email'] !== $email): ?>
                                            <form method="POST" class="form-rimuovi-dipendente">
                                                <input type="hidden" name="action" value="rimuovi_dipendente">
                                                <input type="hidden" name="email_rimuovi" value="<?php echo htmlspecialchars($dip['email']); ?>">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-rimuovi" title="Rimuovi" data-nome="<?php echo htmlspecialchars($dip['nome'] . ' ' . $dip['cognome']); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aggiungi nuovo -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 pt-2"><i class="bi bi-person-plus"></i> Aggiungi Dipendente</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formAggiungiDipendente">
                                    <input type="hidden" name="action" value="aggiungi_dipendente">
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="nome" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cognome</label>
                                        <input type="text" name="cognome" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required placeholder="es. dipendente@comune.it">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required minlength="6">
                                        <div class="form-text">Usata per l'accesso, modificabile in seguito.</div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="button" id="btnAggiungiDip" class="btn btn-primary">Registra Dipendente</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </main>

    <!-- Bootstrap & SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // --- 1. Flash Messages SweetAlert2 ---
        const flashSuccess = <?php echo json_encode($success); ?>;
        const flashError = <?php echo json_encode($error); ?>;
        if (flashSuccess) {
            Swal.fire('Ottimo!', flashSuccess, 'success');
        }
        if (flashError) {
            Swal.fire('Errore', flashError, 'error');
        }

        // --- 2. Form Confirmations (Modals non invasivi) ---
        // Aggiungi dipendente
        const btnAggiungi = document.getElementById('btnAggiungiDip');
        if (btnAggiungi) {
            btnAggiungi.addEventListener('click', function(e) {
                const form = document.getElementById('formAggiungiDipendente');
                if(!form.checkValidity()) { form.reportValidity(); return; }
                const data = new FormData(form);
                Swal.fire({
                    title: 'Sei sicuro?',
                    text: "Vuoi aggiungere o aggiornare " + data.get('nome') + " " + data.get('cognome') + " come dipendente?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sì, procedi',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        }

        // Rimuovi dipendente
        document.querySelectorAll('.btn-rimuovi').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const nome = this.getAttribute('data-nome') || "questo dipendente";
                Swal.fire({
                    title: 'Attenzione!',
                    text: "Vuoi espellere " + nome + " dai dipendenti comunali?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Espelli',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Aggiorna stato problema
        document.querySelectorAll('.btn-salva-stato').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const sel = form.querySelector('.status-select');
                const textStato = sel.options[sel.selectedIndex].text;
                Swal.fire({
                    title: 'Cambio stato',
                    text: "Impostare lo stato in '" + textStato + "' per questa segnalazione?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sì, aggiorna',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // --- 3. Mappa Leaflet & Hover Interattivo ---
        let map;
        let markers = {};

        function initAdminMap() {
            if (!document.getElementById('adminMap')) return;
            
            // Base layer setup as in map.js
            map = L.map('adminMap').setView([45.4642, 9.19], 8);
            
            // Fallback for mapTileBase
            const tileBase = (window.AppConfig && window.AppConfig.mapTileBase) 
                ? window.AppConfig.mapTileBase 
                : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                
            L.tileLayer(tileBase, {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            if (window.AppConfig && window.AppConfig.mapTileOverlays) {
                window.AppConfig.mapTileOverlays.forEach(url => {
                    L.tileLayer(url, { maxZoom: 19, opacity: 0.7 }).addTo(map);
                });
            }

            const boundsArr = [];
            const problems = document.querySelectorAll('.problem-card');
            
            // Icone Standard/Hover
            const defaultIcon = new L.Icon.Default();
            const hoverIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            problems.forEach(card => {
                const id = card.getAttribute('data-id');
                const latStr = card.getAttribute('data-lat');
                const lngStr = card.getAttribute('data-lng');
                if(latStr && lngStr) {
                    const lat = parseFloat(latStr);
                    const lng = parseFloat(lngStr);
                    if(!isNaN(lat) && !isNaN(lng)) {
                        boundsArr.push([lat, lng]);
                        
                        const m = L.marker([lat, lng], {icon: defaultIcon}).addTo(map);
                        m.bindPopup("<b>Segnalazione #" + id + "</b><br>Trovata qui.");
                        markers[id] = m;
                        
                        // Event: Click on marker -> Scroll array and visual
                        m.on('mouseover', function() {
                            this.openPopup();
                            document.querySelectorAll('.problem-card').forEach(c => c.classList.remove('active-hover'));
                            card.classList.add('active-hover');
                            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                        m.on('mouseout', function() {
                            card.classList.remove('active-hover');
                            this.closePopup();
                        });

                        // Event: Hover on card -> Highlight marker
                        card.addEventListener('mouseenter', () => {
                            m.setIcon(hoverIcon);
                            m.setZIndexOffset(1000);
                        });
                        card.addEventListener('mouseleave', () => {
                            m.setIcon(defaultIcon);
                            m.setZIndexOffset(0);
                        });
                    }
                }
            });

            if(boundsArr.length > 0) {
                map.fitBounds(boundsArr, { padding: [20, 20] });
            }
        }

        // --- 4. Reverse Geocoding Lento con Nominatim ---
        // Funzione per richiedere via asincrona un array di posizioni (con delay)
        async function fetchIndirizzi() {
            const addrSpans = Array.from(document.querySelectorAll('.addr-nominatim'));
            for(let span of addrSpans) {
                const lat = span.getAttribute('data-lat');
                const lng = span.getAttribute('data-lng');
                if(!lat || !lng || isNaN(lat) || isNaN(lng)) {
                    span.innerText = "Posizione non valida";
                    continue;
                }
                
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                        headers: {
                            'Accept-Language': 'it'
                        }
                    });
                    if(res.ok) {
                        const data = await res.json();
                        let via = data.address?.road || data.address?.pedestrian || data.address?.cycleway || data.address?.neighbourhood || data.name || "Indirizzo sconosciuto";
                        span.innerText = via;
                    } else {
                        span.innerText = "Non disponibile";
                    }
                } catch(e) {
                    span.innerText = "Errore di rete";
                }
                
                // Sleep 1200ms per rispettare il limite di Nominatim (1 request/second)
                await new Promise(r => setTimeout(r, 1200));
            }
        }

        // Inizializza tutto
        if (document.getElementById('segnalazioni').classList.contains('active')) {
            initAdminMap();
        } else {
            // Se la mappa è in un tab non attivo, inizializzala al click sul tab
            const tabBtn = document.getElementById('segnalazioni-tab');
            if (tabBtn) {
                tabBtn.addEventListener('shown.bs.tab', function () {
                    if (!map) {
                        initAdminMap();
                    } else {
                        map.invalidateSize();
                    }
                });
            }
        }
        
        // Fai partire il geocoding (se ci sono spans da riempire)
        document.addEventListener('DOMContentLoaded', fetchIndirizzi);
        // Assicurati che se non l'ha istanziato in automatico perché attivo lo fa per sicurezza
        if (document.getElementById('segnalazioni-tab') && document.getElementById('segnalazioni-tab').classList.contains('active')) {
           initAdminMap();
        }

    </script>
</body>
</html>
