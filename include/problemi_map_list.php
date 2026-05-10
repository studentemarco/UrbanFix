<?php
// Richiede $problemi (array di segnalazioni), $is_admin (bool)
// $nome_comune_fallback = stringa per centrare la mappa in assenza di segnalini.
?>

<div class="row">
    <!-- Colonna di Sinistra: Lista Problemi -->
    <div class="col-lg-5 col-xl-4 mb-4">
        <div class="problems-list" style="height: 70vh; overflow-y: auto; padding-right: 10px;">
            <?php if (count($problemi) > 0): ?>
                <?php foreach($problemi as $p): ?>
                    <?php 
                        // Estrai lat e lng dalla stringa "lat,lng" se presente
                        $lat = ''; $lng = '';
                        if (!empty($p['coordinate'])) {
                            $coords = explode(',', $p['coordinate']);
                            if (count($coords) >= 2) {
                                $lat = trim($coords[0]);
                                $lng = trim($coords[1]);
                            }
                        }
                    ?>
                    <div class="card shadow-sm mb-3 problem-card" data-id="<?php echo $p['ID']; ?>" data-lat="<?php echo htmlspecialchars($lat); ?>" data-lng="<?php echo htmlspecialchars($lng); ?>" style="cursor: pointer; border-left: 4px solid transparent; transition: all 0.2s ease;">
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
                                <strong>Descrizione:</strong> <span class="d-inline-block text-truncate" style="max-width: 100%; vertical-align: top;"><?php echo htmlspecialchars($p['descrizione']); ?></span><br>
                                <i class="bi bi-clock"></i> Segnalato: <?php echo date('d/m/Y H:i', strtotime($p['timestampSegnalazione'])); ?><br>
                                <?php if(!empty($p['timestampStato'])): ?>
                                    <i class="bi bi-arrow-repeat"></i> Aggiornato: <?php echo date('d/m/Y H:i', strtotime($p['timestampStato'])); ?><br>
                                <?php endif; ?>
                                <i class="bi bi-geo-alt"></i> Vicino a: <span class="addr-nominatim" data-lat="<?php echo htmlspecialchars($lat); ?>" data-lng="<?php echo htmlspecialchars($lng); ?>">Caricamento posizione...</span><br>
                                <i class="bi bi-person"></i> Utente: <?php echo htmlspecialchars($p['nome_utente'] . ' ' . $p['cognome_utente']); ?>
                            </p>
                            
                            <div class="mt-2 text-start">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="apriCommentiPHP(<?php echo $p['ID']; ?>)">
                                    <i class="bi bi-chat"></i> Visualizza e commenta
                                </button>
                            </div>

                            <!-- Banner commenti modale PHP per dipendenti -->
                            <div id="commenti-banner-<?php echo $p['ID']; ?>" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999;">
                                <div style="background:#fff; margin:40px auto; max-width:600px; border-radius:8px; box-shadow:0 2px 16px #0003; padding:24px; position:relative;" class="commenti-modal-content">
                                    <button type="button" onclick="chiudiCommentiPHP(<?php echo $p['ID']; ?>)" style="position:absolute; top:8px; right:8px; border:none; background:none; font-size:1.5em; z-index:10000;">&times;</button>
                                    <h4>Commenti segnalazione #<?php echo $p['ID']; ?></h4>
                                    <div id="elenco-commenti-<?php echo $p['ID']; ?>" style="max-height: 60vh; overflow-y: auto;">
                                        <div class="text-center my-3"><span class="spinner-border spinner-border-sm"></span> Caricamento...</div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($is_admin): ?>
                            <form method="POST" class="form-aggiorna-stato mt-3" data-confirm="Vuoi davvero aggiornare questo stato?">
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
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-4 text-muted border rounded bg-white">
                    Nessuna segnalazione ricevuta o attiva.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Colonna di Destra: Mappa -->
    <div class="col-lg-7 col-xl-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-0">
                <div id="sharedMap" style="height: 70vh; min-height: 500px; border-radius: 8px; z-index: 1;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Logica condivisa JavaScript della Mappa e Reverse Geocoding -->
<script>
    let sharedMap;
    let sharedMarkers = {};

    function initSharedMap() {
        const mapEl = document.getElementById('sharedMap');
        if (!mapEl || mapEl.innerHTML !== "") return; // Previene doppia init
        
        sharedMap = L.map('sharedMap').setView([45.4642, 9.19], 8);
        
        const tileBase = (window.AppConfig && window.AppConfig.mapTileBase) 
            ? window.AppConfig.mapTileBase 
            : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            
        L.tileLayer(tileBase, {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(sharedMap);

        if (window.AppConfig && window.AppConfig.mapTileOverlays) {
            window.AppConfig.mapTileOverlays.forEach(url => {
                L.tileLayer(url, { maxZoom: 19, opacity: 0.7 }).addTo(sharedMap);
            });
        }

        const boundsArr = [];
        const problems = document.querySelectorAll('.problem-card');
        
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
                    
                    const m = L.marker([lat, lng], {icon: defaultIcon}).addTo(sharedMap);
                    m.bindPopup("<b>Segnalazione #" + id + "</b>");
                    sharedMarkers[id] = m;
                    
                    m.on('mouseover', function() {
                        this.openPopup();
                        document.querySelectorAll('.problem-card').forEach(c => {
                            c.style.borderLeftColor = 'transparent';
                            c.style.backgroundColor = '';
                        });
                        card.style.borderLeftColor = '#0d6efd';
                        card.style.backgroundColor = '#f8f9fa';
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                    m.on('mouseout', function() {
                        card.style.borderLeftColor = 'transparent';
                        card.style.backgroundColor = '';
                        this.closePopup();
                    });

                    card.addEventListener('mouseenter', () => {
                        card.style.borderLeftColor = '#0d6efd';
                        card.style.backgroundColor = '#f8f9fa';
                        m.setIcon(hoverIcon);
                        m.setZIndexOffset(1000);
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.borderLeftColor = 'transparent';
                        card.style.backgroundColor = '';
                        m.setIcon(defaultIcon);
                        m.setZIndexOffset(0);
                    });
                }
            }
        });

        // Fit Bounds o Cerca Comune
        if(boundsArr.length > 0) {
            sharedMap.fitBounds(boundsArr, { padding: [20, 20], maxZoom: 16 });
        } else {
            // Se nessun marker per il comune, cerca le coordinate del comune tramite query Nominatim
            const fallbackCity = "<?php echo isset($nome_comune_per_mappa) ? addslashes($nome_comune_per_mappa) : 'Bergamo'; ?>";
            if(fallbackCity) {
                fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(fallbackCity)}&format=json&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if(data && data.length > 0) {
                        sharedMap.setView([data[0].lat, data[0].lon], 12);
                    }
                }).catch(e => console.error("Geocoding failed", e));
            }
        }
    }

    // Reverse Geocoder per indirizzi
    async function fetchIndirizziShared() {
        const addrSpans = Array.from(document.querySelectorAll('.addr-nominatim'));
        for(let span of addrSpans) {
            if (span.innerText !== "Caricamento posizione...") continue; // Already processed
            
            const lat = span.getAttribute('data-lat');
            const lng = span.getAttribute('data-lng');
            if(!lat || !lng || isNaN(lat) || isNaN(lng)) {
                span.innerText = "Posizione non salvata correttamente";
                continue;
            }
            
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: { 'Accept-Language': 'it' }
                });
                const data = await res.json();
                if(data && data.display_name) {
                    let parts = data.display_name.split(',');
                    span.innerText = parts.slice(0, 2).join(', ');
                } else {
                    span.innerText = "Nessun indirizzo trovato";
                }
            } catch(e) {
                span.innerText = "Errore geocoding";
            }
            // Piccolo delay per nominatim
            await new Promise(r => setTimeout(r, 600));
        }
    }

    function apriCommentiPHP(id) {
        // Usa requestAnimationFrame per non interferire on l'event propagate
        setTimeout(() => {
            const banner = document.getElementById('commenti-banner-' + id);
            if (!banner) return;
            banner.style.display = 'block';
            
            // Carica dal server (usiamo il file commentiModal.php della root che è shared)
            fetch('commentiModal.php?problema_id=' + id)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('elenco-commenti-' + id).innerHTML = html;
                }).catch(e => {
                    document.getElementById('elenco-commenti-' + id).innerHTML = '<div class="alert alert-danger">Errore nel caricamento dei commenti</div>';
                });
                
            banner.onclick = function(e) {
                if (e.target === banner) {
                    chiudiCommentiPHP(id);
                }
            };
        }, 10);
    }
    
    function chiudiCommentiPHP(id) {
        document.getElementById('commenti-banner-' + id).style.display = 'none';
        // Non blocchiamo il propagate così Leaflet riprende il controllo.
    }
    //             });
    //             if(res.ok) {
    //                 const data = await res.json();
    //                 let via = data.address?.road || data.address?.pedestrian || data.address?.cycleway || data.name || "Indirizzo sconosciuto";
    //                 span.innerText = via;
    //             } else {
    //                 span.innerText = "Non disponibile";
    //             }
    //         } catch(e) {
    //             span.innerText = "Errore di rete";
    //         }
            
    //         await new Promise(r => setTimeout(r, 1500));
    //     }
    // }

    document.addEventListener('DOMContentLoaded', () => {
        // Instanzia Mappa per le tab aperte, o di default se non in tab
        if(document.getElementById('sharedMap').closest('.tab-pane') && document.getElementById('sharedMap').closest('.tab-pane').classList.contains('active') || !document.getElementById('sharedMap').closest('.tab-pane')) {
            initSharedMap();
        }
        fetchIndirizziShared();
        
        // Modal Confirmation for local SweetAlert inputs
        if(typeof Swal !== 'undefined') {
            document.querySelectorAll('.btn-salva-stato').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('form');
                    const sel = form.querySelector('.status-select');
                    const textStato = sel.options[sel.selectedIndex].text;
                    Swal.fire({
                        title: 'Cambio stato',
                        text: "Aggiornare in '" + textStato + "' questa segnalazione?",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Sì, aggiorna',
                        cancelButtonText: 'Annulla'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        }
    });
</script>
