<div class="card mb-4 shadow-sm" id="problema-<?php echo $problema['ID']; ?>">
    <?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

        $exists = false;
        $imgPath = null;
        if (!empty($problema['immagine_problema'])) {
            $localPath = FRONTEND_URL . '/img/problemi/' . basename($problema['immagine_problema']);
            if (file_exists($localPath)) {
                $imgPath = htmlspecialchars(FRONTEND_URL . '/img/problemi/' . basename($problema['immagine_problema']));
                $exists = true;
            } else {
                $imgPath = htmlspecialchars(FRONTEND_URL . '/img/problemi/default.png');
            }
        } else {
            $imgPath = htmlspecialchars(FRONTEND_URL . '/img/problemi/default.png');
        }

        if($exists):
    ?>
    <img src="<?php echo $imgPath; ?>"
         class="card-img-top" alt="Immagine segnalazione"
         style="height: 200px; object-fit: cover;">
        <?php endif; ?>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-title mb-1"><?php echo $problema['titolo']; ?></h5>
                
                <div class="mb-2">
                    <?php 
                    if (!empty($problema['categorie'])): 
                        $lista_cat = explode(', ', $problema['categorie']);
                        foreach ($lista_cat as $cat): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($cat); ?></span>
                        <?php endforeach; 
                    else: ?>
                        <span class="badge bg-light text-dark border">Generale</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
                $stato_class = match ($problema['stato']) {
                    'aperto' => 'bg-info text-dark',
                    'in lavorazione' => 'bg-warning text-dark',
                    'risolta' => 'bg-success',
                    default => 'bg-secondary',
                };
            ?>
            <span class="badge <?php echo $stato_class; ?>"><?php echo htmlspecialchars($problema['stato']); ?></span>
        </div>
        
        <h6 class="card-subtitle mb-3 text-muted">
            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($problema['nome_comune'] ?? 'Comune non specificato'); ?>
        </h6>

        <p class="card-text text-dark"><?php echo htmlspecialchars($problema['descrizione']); ?></p>
        
        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
            <?php
                $avatarPath = null;
                if (!empty($problema['user_avatar'])) {
                    $localAvatarPath = __DIR__ . '/img/avatar/' . basename($problema['user_avatar']);
                    if (file_exists($localAvatarPath)) {
                        $avatarPath = htmlspecialchars(FRONTEND_URL . '/img/avatar/' . basename($problema['user_avatar']));
                    } else {
                        $avatarPath = htmlspecialchars(FRONTEND_URL . '/img/avatar/default.png');
                    }
                } else {
                    $avatarPath = htmlspecialchars(FRONTEND_URL . '/img/avatar/default.png');
                }
            ?>
            <img src="<?php echo $avatarPath; ?>"
                 alt="Avatar" class="rounded-circle me-2"
                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">

            <small class="text-muted">
                <strong>Inviata da:</strong> <?php echo htmlspecialchars(($problema['nome_utente'] ?? '') . ' ' . ($problema['cognome_utente'] ?? '') ?: 'Cittadino'); ?><br>
                <strong>Il:</strong> <?php echo date('d/m/Y H:i', strtotime($problema['timestampSegnalazione'])); ?>
            </small>

            <div class="btn-group">
                <?php if (può('VOTA_SEGNALAZIONE', $privilegi)): ?>
                    <?php $num_voti_favore = $problema['num_voti_favore'] ?? 0; ?>
                    <button class="btn btn-sm btn-outline-primary">👍 Vota<?php if($num_voti_favore > 0) echo ' (' . $num_voti_favore . ')'; ?></button>
                <?php endif; ?>

                <?php if (può('COMMENTA_SEGNALAZIONE', $privilegi)): ?>
                    <?php $num_commenti = $problema['num_commenti'] ?? 0; ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="apriCommentiPHP(<?php echo $problema['ID']; ?>)">💬 Commenta<?php if($num_commenti > 0) echo ' (' . $num_commenti . ')'; ?></button>
                <?php endif; ?>
            <!-- Banner commenti modale PHP -->
            <div id="commenti-banner-<?php echo $problema['ID']; ?>" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999;">
                <div style="background:#fff; margin:40px auto; max-width:600px; border-radius:8px; box-shadow:0 2px 16px #0003; padding:24px; position:relative;">
                    <button onclick="chiudiCommentiPHP(<?php echo $problema['ID']; ?>)" style="position:absolute; top:8px; right:8px; border:none; background:none; font-size:1.5em;">&times;</button>
                    <h4>Commenti segnalazione #<?php echo $problema['ID']; ?></h4>
                    <div id="elenco-commenti-<?php echo $problema['ID']; ?>">
                        <!-- Qui verrà caricato il contenuto PHP -->
                    </div>
                </div>
            </div>
                        <script>
                        function apriCommentiPHP(id) {
                                const banner = document.getElementById('commenti-banner-' + id);
                                banner.style.display = 'block';
                                // Carica il contenuto commentiModal.php via AJAX
                                fetch('/progetto/UrbanFix/commentiModal.php?problema_id=' + id)
                                    .then(r => r.text())
                                    .then(html => {
                                        document.getElementById('elenco-commenti-' + id).innerHTML = html;
                                    });
                                // Gestione click fuori dalla modale
                                setTimeout(() => {
                                    banner.addEventListener('click', function handler(e) {
                                        if (e.target === banner) {
                                            chiudiCommentiPHP(id);
                                            banner.removeEventListener('click', handler);
                                        }
                                    });
                                }, 100);
                        }
                        function chiudiCommentiPHP(id) {
                                document.getElementById('commenti-banner-' + id).style.display = 'none';
                        }
                        </script>
            </div>
        </div>

        <?php if (può('SEGNALA_RISOLTA', $privilegi)): ?>
            <div class="mt-3 text-end">
                <hr>
                <button class="btn btn-sm btn-success">
                    <i class="bi bi-check-circle"></i> Segna come Risolta
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>