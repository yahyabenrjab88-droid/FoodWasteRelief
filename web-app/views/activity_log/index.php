<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Journal d'Activité Système</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Date</th>
                        <th class="py-3">Utilisateur</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Entité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Aucune activité récente.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="ps-4 py-3 text-muted small" style="white-space: nowrap;">
                                    <i data-lucide="clock" class="me-1" style="width: 12px; height: 12px;"></i>
                                    <?= time_ago($log['timestamp']) ?>
                                </td>
                                <td class="py-3 fw-bold text-dark">
                                    ID: <?= e($log['user_id']) ?>
                                </td>
                                <td class="py-3">
                                    <span class="text-primary"><?= e($log['action']) ?></span>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= e($log['entity_type']) ?> #<?= e($log['entity_id']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
