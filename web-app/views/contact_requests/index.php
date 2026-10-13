<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Demandes de Contact</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Date</th>
                        <th class="py-3">Expéditeur</th>
                        <th class="py-3">Sujet / Message</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($requests)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Aucun message trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($requests as $r): ?>
                            <tr class="<?= !(int)$r['is_read'] ? 'fw-bold bg-light' : '' ?>">
                                <td class="ps-4 py-3 text-muted small"><?= time_ago($r['sent_at']) ?></td>
                                <td class="py-3">
                                    <div class="text-dark">ID: <?= e($r['sender_id'] ?? 'Visiteur') ?></div>
                                    <div class="badge bg-secondary font-monospace"><?= e($r['sender_role'] ?? '') ?></div>
                                </td>
                                <td class="py-3" style="max-width: 300px;">
                                    <div class="text-dark text-truncate mb-1"><?= e($r['subject']) ?></div>
                                    <div class="text-muted small text-truncate fw-normal"><?= e($r['body']) ?></div>
                                </td>
                                <td class="py-3">
                                    <?php if((int)$r['is_read']): ?>
                                        <span class="badge bg-success">Lu</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Non Lu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <?php if(!(int)$r['is_read']): ?>
                                        <form action="<?= BASE_URL ?>/admin/contact/<?= $r['id'] ?>/read" method="POST" class="d-inline">
                                            <?= $this->csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill" title="Marquer comme lu"><i data-lucide="check" style="width: 14px;"></i></button>
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
