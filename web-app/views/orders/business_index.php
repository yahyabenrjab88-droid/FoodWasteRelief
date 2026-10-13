<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Commandes Reçues</h3>
</div>

<?php
function orderStatusBadge(string $status): string {
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
    return "<span class=\"badge $cls\">$label</span>";
}
?>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Commande #</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Client</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Aucune commande reçue pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                            <tr class="<?= in_array($o['status'], ['en attente', 'pending']) ? 'table-warning bg-opacity-50' : '' ?>">
                                <td class="ps-4 py-3 fw-bold text-dark">#<?= str_pad((string)$o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3 text-muted small"><?= time_ago($o['date_commande']) ?></td>
                                <td class="py-3 text-dark fw-medium">
                                    <?= e(trim(($o['client_prenom'] ?? $o['prenom'] ?? 'Client') . ' ' . ($o['client_nom'] ?? $o['nom'] ?? ''))) ?>
                                </td>
                                <td class="py-3 fw-bold text-primary"><?= number_format((float)$o['total'], 2) ?> TND</td>
                                <td class="py-3"><?= orderStatusBadge($o['status']) ?></td>
                                <td class="text-end pe-4 py-3">
                                    <a href="<?= BASE_URL ?>/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                                        Gérer <i data-lucide="chevron-right" style="width: 14px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
