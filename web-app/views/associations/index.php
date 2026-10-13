<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Associations</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Association</th>
                        <th class="py-3">Représentant</th>
                        <th class="py-3">Ville</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($associations)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Aucune association trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($associations as $a): ?>
                            <tr class="<?= $a['status'] === 'en attente' ? 'table-warning' : '' ?>">
                                <td class="ps-4 py-3 fw-bold text-dark"><?= e($a['nom']) ?></td>
                                <td class="py-3">
                                    <div class="fw-medium text-dark"><?= e($a['user_prenom'] ?? 'N/A') ?> <?= e($a['user_nom'] ?? 'N/A') ?></div>
                                    <div class="text-muted small"><?= e($a['email']) ?></div>
                                </td>
                                <td class="py-3"><?= e($a['ville']) ?></td>
                                <td class="py-3">
                                    <div class="text-dark small"><i data-lucide="phone" style="width: 12px; height: 12px;" class="me-1"></i> <?= e($a['telephone']) ?></div>
                                </td>
                                <td class="py-3">
                                <?php
                                    $sClass = 'bg-secondary';
                                    $sLabel = ucfirst($a['status']);
                                    if ($a['status'] === 'valide')     { $sClass = 'bg-success';           $sLabel = 'Validé'; }
                                    if ($a['status'] === 'en attente') { $sClass = 'bg-warning text-dark'; $sLabel = 'En attente'; }
                                    if (in_array($a['status'], ['rejeté','rejetee'])) { $sClass = 'bg-danger'; $sLabel = 'Rejeté'; }
                                ?>
                                <span class="badge <?= $sClass ?>"><?= $sLabel ?></span>
                            </td>
                            <td class="text-end pe-4 py-3">
                                <?php if ($a['status'] !== 'valide'): ?>
                                    <form action="<?= BASE_URL ?>/admin/associations/<?= $a['id'] ?>/approve" method="POST" class="d-inline">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm btn-success rounded-pill fw-bold px-3" title="Approuver">
                                            <i data-lucide="check" style="width: 14px;" class="me-1"></i> Approuver
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($a['status'] !== 'rejeté'): ?>
                                    <form action="<?= BASE_URL ?>/admin/associations/<?= $a['id'] ?>/reject" method="POST" class="d-inline ms-1">
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
