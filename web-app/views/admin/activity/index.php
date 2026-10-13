<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="activity" class="me-2"></i>System Activity Log</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Timestamp</th>
                        <th class="py-3">User</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Entity Type</th>
                        <th class="py-3 pe-4">Entity ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No activity recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="ps-4 text-muted small"><i data-lucide="clock" class="me-1" style="width: 12px;"></i><?= date('Y-m-d H:i:s', strtotime($log['date_action'])) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($log['prenom']) ?> <?= e($log['nom']) ?></div>
                                    <div class="text-muted small">ID: <?= $log['user_id'] ?></div>
                                </td>
                                <td>
                                    <?php
                                    $roleColors = ['admin'=>'bg-danger','business'=>'bg-primary','association'=>'bg-info text-dark','client'=>'bg-secondary'];
                                    $rc = $roleColors[$log['role']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $rc ?> px-2 py-1 text-uppercase" style="font-size: 0.65rem;"><?= e($log['role']) ?></span>
                                </td>
                                <td class="fw-bold"><?= e($log['action']) ?></td>
                                <td class="text-muted text-uppercase small"><?= e($log['entity_type'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-muted font-monospace">#<?= $log['entity_id'] ?? 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
