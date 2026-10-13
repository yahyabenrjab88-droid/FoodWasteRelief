<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="store" class="me-2"></i>Verify Businesses</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Business ID</th>
                        <th class="py-3">Name / User</th>
                        <th class="py-3">Location</th>
                        <th class="py-3">Patente / Tax ID</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($businesses)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No businesses found.</td></tr>
                    <?php else: ?>
                        <?php foreach($businesses as $b): ?>
                            <tr class="<?= $b['status'] === 'en_attente' ? 'table-warning' : '' ?>">
                                <td class="ps-4 fw-bold">#<?= $b['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($b['nom_business']) ?></div>
                                    <div class="small text-muted"><i data-lucide="user" style="width: 12px; display:inline;" class="me-1"></i><?= e($b['email']) ?></div>
                                </td>
                                <td>
                                    <div><?= e($b['ville']) ?></div>
                                    <div class="small text-muted"><?= e($b['localisation']) ?></div>
                                </td>
                                <td class="font-monospace text-muted"><?= e($b['patente'] ?: 'N/A') ?></td>
                                <td>
                                    <?php
                                    $col = $b['status'] === 'en_attente' ? 'bg-warning text-dark' : ($b['status'] === 'valide' ? 'bg-success' : 'bg-danger');
                                    ?>
                                    <span class="badge <?= $col ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem;"><?= e($b['status']) ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if($b['status'] === 'en_attente'): ?>
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="<?= BASE_URL ?>/admin/businesses/<?= $b['id'] ?>/approve" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold px-3">Approve</button>
                                            </form>
                                            <form action="<?= BASE_URL ?>/admin/businesses/<?= $b['id'] ?>/reject" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">Reject</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Reviewed</span>
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
