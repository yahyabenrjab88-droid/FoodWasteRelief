<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="layout-dashboard" class="me-2"></i>Tableau de bord Association</h3>
    <a href="<?= BASE_URL ?>/donations" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i data-lucide="plus" class="me-2" style="width:16px;"></i>Demander un Don</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Demandes en Attente</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['pending_requests']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background-color: rgba(255, 193, 7, 0.2); color: #ffc107; padding: 12px; border-radius: 50%;">
                        <i data-lucide="clock" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--primary);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Dons Approuvés</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['approved_donations']) ?></h3>
                    </div>
                    <div class="kpi-icon primary" style="padding: 12px; border-radius: 50%;">
                        <i data-lucide="check-circle" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #198754;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Total KG Reçus</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['kg_received'], 1) ?> kg</h3>
                    </div>
                    <div class="kpi-icon" style="background: rgba(25, 135, 84, 0.2); color: #198754; padding: 12px; border-radius: 50%;">
                        <i data-lucide="heart" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0">Dons Récents</h5>
        <a href="<?= BASE_URL ?>/donations" class="btn btn-sm btn-outline-primary rounded-pill px-3">Voir Tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Date</th>
                        <th class="py-3">Entreprise</th>
                        <th class="py-3">Produit</th>
                        <th class="py-3">Quantité</th>
                        <th class="py-3 pe-4 text-end">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['recent_donations'])): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Aucun don reçu pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach(array_slice($data['recent_donations'], 0, 5) as $d): ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?= date('d/m/Y', strtotime($d['date_donation'])) ?></td>
                                <td><i data-lucide="store" class="text-muted me-2" style="width:14px;"></i><?= e($d['nom_business']) ?></td>
                                <td class="fw-bold text-dark"><?= e($d['produit_nom']) ?></td>
                                <td><?= $d['quantite'] ?> kg/unités</td>
                                <td class="pe-4 text-end">
                                    <?php
                                    $st = $d['status'];
                                    // Negative matching: if it's not pending/requested, it's green.
                                    if (stripos($st, 'deman') !== false || stripos($st, 'attente') !== false) {
                                        $col = 'bg-warning text-dark';
                                    } elseif (stripos($st, 'rejet') !== false || stripos($st, 'annul') !== false) {
                                        $col = 'bg-danger';
                                    } else {
                                        $col = 'bg-success';
                                    }
                                    ?>
                                    <span class="badge <?= $col ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem;"><?= e($d['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
