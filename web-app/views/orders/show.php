<?php

function statusBadge(string $status): string {
    $st = strtolower(trim($status));
    $cls = 'bg-secondary';
    $label = ucfirst($status);

    if (strpos($st, 'attente') !== false || strpos($st, 'pending') !== false) {
        $cls = 'bg-warning text-dark';
        $label = 'En attente';
    } elseif (strpos($st, 'termin') !== false || strpos($st, 'complet') !== false || strpos($st, 'livr') !== false) {
        $cls = 'bg-success';
        $label = 'Terminé';
    } elseif (strpos($st, 'annul') !== false || strpos($st, 'cancel') !== false) {
        $cls = 'bg-danger';
        $label = 'Annulé';
    }
    return "<span class=\"badge $cls px-2 py-1\">$label</span>";
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/orders" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="arrow-left"></i>
    </a>
    <h3 class="fw-bold text-primary m-0">Commande #<?= str_pad((string)$order['id'], 5, '0', STR_PAD_LEFT) ?></h3>
    <span class="ms-3"><?= statusBadge($order['status']) ?></span>
</div>

<div class="row g-4">
    <!-- Left: Items Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold text-dark m-0"><i data-lucide="list" class="me-2 text-primary" style="width: 20px;"></i> Détails des Articles</h5>
            </div>
            <div class="card-body p-0 mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 60px;">Image</th>
                                <th class="py-3">Produit</th>
                                <th class="py-3">Prix Unitaire</th>
                                <th class="py-3">Quantité</th>
                                <th class="text-end pe-4 py-3">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($order['details'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Aucun article dans cette commande.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($order['details'] as $item): ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['nom_produit']) ?>" class="rounded object-fit-cover shadow-sm" style="width: 44px; height: 44px;">
                                            <?php else: ?>
                                                <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 44px; height: 44px;">
                                                    <i data-lucide="image" style="width: 18px;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-dark"><?= e($item['nom_produit']) ?></div>
                                            <?php if (!empty($item['categorie'])): ?>
                                                <div class="text-muted small"><?= e($item['categorie']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3"><?= number_format((float)$item['prix_unitaire'], 2) ?> TND</td>
                                        <td class="py-3"><span class="badge bg-light text-dark border">x<?= $item['quantite'] ?></span></td>
                                        <td class="text-end pe-4 py-3 fw-bold text-primary">
                                            <?= number_format((float)$item['prix_unitaire'] * (float)$item['quantite'], 2) ?> TND
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end py-3 fw-bold text-dark">Total de la Commande :</td>
                                <td class="text-end pe-4 py-3 fw-bold text-primary fs-5"><?= number_format((float)$order['total'], 2) ?> TND</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Info & Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.85rem; letter-spacing: 0.5px;">Informations</h6>

                <div class="mb-3">
                    <div class="text-muted small mb-1">Date de Commande</div>
                    <div class="fw-bold text-dark"><?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small mb-1">Statut</div>
                    <div><?= statusBadge($order['status']) ?></div>
                </div>

                <?php if($_SESSION['user']['role'] === 'business'): ?>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Client</div>
                        <div class="fw-bold text-dark">
                            <?= e(trim(($order['client_prenom'] ?? '') . ' ' . ($order['client_nom'] ?? ''))) ?: 'Client inconnu' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($_SESSION['user']['role'] === 'client'): ?>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Entreprise Fournisseur</div>
                        <div class="fw-bold text-dark"><?= e($order['nom_business'] ?? 'Entreprise') ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($_SESSION['user']['role'] === 'business' && in_array($order['status'], ['en attente', 'pending'])): ?>
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius); border: 1px solid rgba(45,106,79,0.2);">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-primary mb-3">
                        <i data-lucide="settings" class="me-2" style="width: 16px;"></i> Gérer cette commande
                    </h6>
                    <form action="<?= BASE_URL ?>/business/orders/<?= $order['id'] ?>/status" method="POST" class="mb-2">
                        <?= $this->csrf_field() ?>
                        <input type="hidden" name="status" value="terminé">
                        <button class="btn btn-success w-100 fw-bold text-white shadow-sm rounded-pill">
                            <i data-lucide="check" style="width: 16px;" class="me-1"></i> Marquer comme Terminé
                        </button>
                    </form>
                    <form action="<?= BASE_URL ?>/business/orders/<?= $order['id'] ?>/status" method="POST">
                        <?= $this->csrf_field() ?>
                        <input type="hidden" name="status" value="annulé">
                        <button class="btn btn-outline-danger w-100 fw-bold rounded-pill">
                            <i data-lucide="x" style="width: 16px;" class="me-1"></i> Annuler la Commande
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
