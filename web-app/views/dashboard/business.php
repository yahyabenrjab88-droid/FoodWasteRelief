<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="layout-dashboard" class="me-2"></i>Tableau de bord Entreprise</h3>
    <a href="<?= BASE_URL ?>/business/products/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i data-lucide="plus" class="me-2" style="width:16px;"></i>Ajouter un Produit</a>
</div>

<!-- Low Stock Banner -->
<?php if(!empty($data['low_stock'])): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm" style="border-radius: var(--radius); border-left: 5px solid #ffc107;" role="alert">
        <i data-lucide="alert-triangle" class="me-3 text-warning"></i>
        <div>
            <strong>Alerte de stock faible :</strong> Vous avez <?= count($data['low_stock']) ?> produits actifs avec moins de 5 articles en stock.
            <a href="<?= BASE_URL ?>/business/products" class="alert-link ms-2">Gérer l'inventaire</a>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--primary);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Produits Actifs</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['active_products']) ?></h3>
                    </div>
                    <div class="kpi-icon primary">
                        <i data-lucide="package" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Expire Bientôt (3j)</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['expiring_soon']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
                        <i data-lucide="clock" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--accent);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Commandes Total</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['total_orders']) ?></h3>
                    </div>
                    <div class="kpi-icon accent">
                        <i data-lucide="shopping-cart" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #198754;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">KG Donnés</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['kg_donated'], 1) ?> kg</h3>
                    </div>
                    <div class="kpi-icon" style="background: rgba(25, 135, 84, 0.2); color: #198754;">
                        <i data-lucide="heart" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0">Commandes Récentes</h5>
        <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-primary rounded-pill px-3">Voir Tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">ID Commande</th>
                        <th class="py-3">Client</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Statut</th>
                        <th class="py-3 pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['recent_orders'])): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Aucune commande reçue pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach(array_slice($data['recent_orders'], 0, 5) as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $o['id'] ?></td>
                                <td><?= e($o['client_prenom'] ?? $o['prenom'] ?? 'Client') ?> <?= e($o['client_nom'] ?? $o['nom'] ?? '') ?></td>
                                <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($o['date_commande'])) ?></td>
                                <td>
                                    <?php
                                    $st = strtolower(trim($o['status']));
                                    $col = 'bg-secondary';
                                    $label = ucfirst($st);

                                    if (strpos($st, 'attente') !== false || strpos($st, 'pending') !== false) {
                                        $col = 'bg-warning text-dark';
                                        $label = 'En attente';
                                    } elseif (strpos($st, 'termin') !== false || strpos($st, 'complet') !== false || strpos($st, 'livr') !== false) {
                                        $col = 'bg-success';
                                        $label = 'Terminé';
                                    } elseif (strpos($st, 'annul') !== false || strpos($st, 'cancel') !== false) {
                                        $col = 'bg-danger';
                                        $label = 'Annulé';
                                    }
                                    ?>
                                    <span class="badge <?= $col ?> px-2 py-1" style="font-size: 0.7rem;"><?= $label ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="<?= BASE_URL ?>/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">Gérer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
