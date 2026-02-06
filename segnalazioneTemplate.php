<div class="card mb-4 shadow-sm" id="problema-<?php echo $problema['ID']; ?>">
    <?php if (!empty($problema['immagine_problema'])): ?>
        <img src="<?php echo htmlspecialchars($problema['immagine_problema']); ?>" 
             class="card-img-top" alt="Immagine segnalazione" 
             style="height: 200px; object-fit: cover;">
    <?php endif; ?>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-title mb-1">Segnalazione #<?php echo $problema['ID']; ?></h5>
                
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
            <img src="<?php echo !empty($problema['user_avatar']) ? htmlspecialchars($problema['user_avatar']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" 
                 alt="Avatar" class="rounded-circle me-2" 
                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">

            <small class="text-muted">
                <strong>Inviata da:</strong> <?php echo htmlspecialchars($problema['autore_nome'] ?? 'Cittadino'); ?><br>
                <strong>Il:</strong> <?php echo date('d/m/Y H:i', strtotime($problema['timestampSegnalazione'])); ?>
            </small>

            <div class="btn-group">
                <?php if (può('VOTA_SEGNALAZIONE', $privilegi)): ?>
                    <button class="btn btn-sm btn-outline-primary">👍 Vota</button>
                <?php endif; ?>

                <?php if (può('COMMENTA_SEGNALAZIONE', $privilegi)): ?>
                    <button class="btn btn-sm btn-outline-secondary">💬 Commenta</button>
                <?php endif; ?>
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