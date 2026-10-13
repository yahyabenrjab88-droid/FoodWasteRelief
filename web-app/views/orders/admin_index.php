<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Toutes les Commandes</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Commande #</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Client (ID)</th>
                        <th class="py-3">Entreprise (ID)</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Aucune commande trouvée sur la plateforme.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-dark">
                                    <a href="<?= BASE_URL ?>/orders/<?= $o['id'] ?>" class="text-decoration-none">#<?= str_pad((string)$o['id'], 5, '0', STR_PAD_LEFT) ?></a>
                                </td>
                                <td class="py-3 text-muted small"><?= date('d/m/Y H:i', strtotime($o['date_commande'])) ?></td>
                                <td class="py-3 text-dark"><?= e($o['client_id']) ?></td>
                                <td class="py-3 text-dark"><?= e($o['business_id']) ?></td>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
