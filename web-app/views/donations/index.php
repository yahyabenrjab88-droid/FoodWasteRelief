<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Dons</h3>
</div>

<?php if($_SESSION['user']['role'] === 'business'): ?>
    
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i data-lucide="heart" class="text-danger me-2" style="width: 20px;"></i> Faire un Don</h5>
                </div>
                <div class="card-body px-4 pb-4 mt-2">
                    <form action="<?= BASE_URL ?>/donations/send" method="POST">
                        <?= $this->csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Sélectionner une Association</label>
                            <select name="association_id" class="form-select bg-light border-0" required>
                                <?php foreach($associations as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= e($a['nom']) ?><?= !empty($a['ville']) ? ' (' . e($a['ville']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Produit à donner</label>
                            <select name="produit_id" class="form-select bg-light border-0" required>
                                <option value="">-- Choisir un produit --</option>
                                <?php foreach($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= e($p['nom'] ?? '') ?> (Stock: <?= $p['quantite'] ?? 0 ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Quantité</label>
                            <input type="number" step="0.01" name="quantite" class="form-control bg-light border-0" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">Envoyer le Don <i data-lucide="send" class="ms-2" style="width: 16px;"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i data-lucide="inbox" class="text-primary me-2" style="width: 20px;"></i> Demandes Reçues</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php if(empty($donations)): ?>
                                    <tr>
                                        <td class="text-center py-5 text-muted">Aucune demande de don en attente.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($donations as $d): ?>
                                        <?php if($d['status'] === 'demandé'): ?>
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="fw-bold text-dark"><?= e($d['assoc_nom']) ?></div>
                                                    <div class="text-muted small">demande <span class="fw-bold text-primary"><?= $d['quantite'] ?></span> unités du produit #<?= $d['produit_id'] ?></div>
                                                </td>
                                                <td class="text-end pe-4 py-3">
                                                    <form action="<?= BASE_URL ?>/donations/<?= $d['id'] ?>/approve" method="POST" class="d-inline">
                                                        <?= $this->csrf_field() ?>
                                                        <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold me-1">Accepter</button>
                                                    </form>
                                                    <form action="<?= BASE_URL ?>/donations/<?= $d['id'] ?>/reject" method="POST" class="d-inline">
                                                        <?= $this->csrf_field() ?>
                                                        <button class="btn btn-sm btn-danger rounded-pill fw-bold" title="Refuser"><i data-lucide="x" style="width: 14px;"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif($_SESSION['user']['role'] === 'association'): ?>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i data-lucide="hand" class="text-primary me-2" style="width: 20px;"></i> Demander un Don</h5>
                </div>
                <div class="card-body px-4 pb-4 mt-2">
                    <form action="<?= BASE_URL ?>/donations/request" method="POST">
                        <?= $this->csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Produit Disponible</label>
                            <select name="produit_id" class="form-select bg-light border-0" required>
                                <?php foreach($availableProducts as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= e($p['nom']) ?> (par <?= e($p['nom_business']) ?>) - Stock: <?= $p['quantite'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Quantité Souhaitée</label>
                            <input type="number" step="0.01" name="quantite" class="form-control bg-light border-0" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">Envoyer la Demande <i data-lucide="send" class="ms-2" style="width: 16px;"></i></button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i data-lucide="history" class="text-primary me-2" style="width: 20px;"></i> Historique des Dons</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php if(empty($donations)): ?>
                                    <tr>
                                        <td class="text-center py-5 text-muted">Aucun historique de don.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($donations as $d): ?>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="fw-bold text-dark"><?= e($d['produit_nom'] ?? 'Produit') ?></div>
                                                <div class="text-muted small">De: <?= e($d['nom_business'] ?? 'Entreprise') ?></div>
                                            </td>
                                            <td class="py-3 fw-bold text-primary text-center">
                                                <?= $d['quantite'] ?> kg
                                            </td>
                                            <td class="text-end pe-4 py-3">
                                                <?php
                                                $sClass = 'bg-secondary';
                                                if ($d['status'] === 'terminé') $sClass = 'bg-success';
                                                if ($d['status'] === 'demandé') $sClass = 'bg-warning text-dark';
                                                if ($d['status'] === 'rejeté') $sClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $sClass ?>"><?= ucfirst(e($d['status'])) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
