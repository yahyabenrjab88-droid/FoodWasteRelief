<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Mes Commandes</h3>
    <a href="<?= BASE_URL ?>/products" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i data-lucide="shopping-bag" style="width: 18px;" class="me-1"></i> Continuer vos achats
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Commande #</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Entreprise</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Vous n'avez passé aucune commande.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-dark">#<?= str_pad((string)$o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="py-3 text-muted small"><?= date('d/m/Y H:i', strtotime($o['date_commande'])) ?></td>
                                <td class="py-3 text-dark"><?= e($o['nom_business'] ?? 'Entreprise') ?></td>
                                <td class="py-3 fw-bold text-primary"><?= number_format((float)$o['total'], 2) ?> TND</td>
                                <td class="py-3">
                                    <?php
                                    $sClass = 'bg-secondary';
                                    if ($o['status'] === 'terminé') $sClass = 'bg-success';
                                    if ($o['status'] === 'en attente') $sClass = 'bg-warning text-dark';
                                    if ($o['status'] === 'annulé') $sClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $sClass ?>"><?= ucfirst(e($o['status'])) ?></span>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <a href="<?= BASE_URL ?>/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                                        Voir <i data-lucide="chevron-right" style="width: 14px;"></i>
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
