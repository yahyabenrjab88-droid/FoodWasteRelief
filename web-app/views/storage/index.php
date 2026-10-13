<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="box" class="me-2"></i>Mon Stock</h3>
    <a href="<?= BASE_URL ?>/storage/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i> Ajouter un Article
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Article</th>
                        <th class="py-3">Quantité</th>
                        <th class="py-3">Ajouté le</th>
                        <th class="py-3">Expiration</th>
                        <th class="py-3 pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($items)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Aucun article trouvé dans votre stock. Cliquez sur 'Ajouter un Article' pour commencer.</td></tr>
                    <?php else: ?>
                        <?php foreach($items as $i): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= e($i['item_name']) ?></td>
                                <td>
                                    <?php if($i['quantity'] <= 5): ?>
                                        <span class="badge bg-danger px-2 py-1"><?= $i['quantity'] ?> <?= e($i['unit']) ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold text-dark"><?= $i['quantity'] ?> <?= e($i['unit']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('d/m/Y', strtotime($i['added_at'])) ?></td>
                                <td>
                                    <?php
                                    try {
                                        $expDate = new DateTime($i['expiry_date']);
                                        $now = new DateTime();
                                        $diff = $expDate->diff($now);
                                        $days = $diff->invert ? $diff->days : -$diff->days;
                                        
                                        $col = $days <= 0 ? 'text-danger fw-bold text-decoration-line-through' : ($days <= 3 ? 'text-danger fw-bold' : ($days <= 7 ? 'text-warning text-dark fw-bold' : 'text-success fw-bold'));
                                    } catch (Exception $e) {
                                        $col = 'text-muted';
                                    }
                                    ?>
                                    <span class="<?= $col ?>"><i data-lucide="clock" class="me-1" style="width:14px;"></i><?= date('d/m/Y', strtotime($i['expiry_date'])) ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= BASE_URL ?>/storage/<?= $i['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Modifier"><i data-lucide="edit-2" style="width:16px;"></i></a>
                                        <form action="<?= BASE_URL ?>/storage/<?= $i['id'] ?>/delete" method="POST" onsubmit="return confirm('Supprimer cet article ?');">
                                            <?= $this->csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
