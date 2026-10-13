<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Entreprises</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Entreprise</th>
                        <th class="py-3">Propriétaire</th>
                        <th class="py-3">Ville</th>
                        <th class="py-3">Patente</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($businesses)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Aucune entreprise trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($businesses as $b): ?>
                            <tr class="<?= $b['status'] === 'en attente' ? 'table-warning' : '' ?>">
                                <td class="ps-4 py-3 fw-bold text-dark"><?= e($b['nom_business']) ?></td>
                                <td class="py-3">
                                    <div class="fw-medium text-dark"><?= e($b['prenom']) ?> <?= e($b['nom']) ?></div>
                                    <div class="text-muted small"><?= e($b['email']) ?></div>
                                </td>
                                <td class="py-3"><?= e($b['ville']) ?></td>
                                <td class="py-3"><span class="badge bg-light text-dark border font-monospace"><?= e($b['patente']) ?></span></td>
                                <td class="py-3">
                                <?php
                                    $sClass = 'bg-secondary';
                                    $sLabel = ucfirst($b['status']);
                                    if ($b['status'] === 'valide')     { $sClass = 'bg-success';           $sLabel = 'Validé'; }
                                    if ($b['status'] === 'en attente') { $sClass = 'bg-warning text-dark'; $sLabel = 'En attente'; }
                                    if (in_array($b['status'], ['rejeté','rejetee'])) { $sClass = 'bg-danger'; $sLabel = 'Rejeté'; }
                                ?>
                                <span class="badge <?= $sClass ?>"><?= $sLabel ?></span>
                            </td>
                            <td class="text-end pe-4 py-3">
                                <?php if ($b['status'] !== 'valide'): ?>
                                    <form action="<?= BASE_URL ?>/admin/businesses/<?= $b['id'] ?>/approve" method="POST" class="d-inline">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm btn-success rounded-pill fw-bold px-3" title="Approuver">
                                            <i data-lucide="check" style="width: 14px;" class="me-1"></i> Approuver
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($b['status'] !== 'rejeté'): ?>
                                    <form action="<?= BASE_URL ?>/admin/businesses/<?= $b['id'] ?>/reject" method="POST" class="d-inline ms-1">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill fw-bold" title="Rejeter">
                                            <i data-lucide="x" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
