<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="users" class="me-2"></i>Manage Users</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Joined</th>
                        <th class="py-3 pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?= $u['id'] ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($u['prenom']) ?> <?= e($u['nom']) ?></div>
                                <div class="small text-muted"><?= e($u['telephone']) ?></div>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <?php
                                $roleColors = ['admin'=>'bg-danger','business'=>'bg-primary','association'=>'bg-info text-dark','client'=>'bg-secondary'];
                                $rc = $roleColors[$u['role']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $rc ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem;"><?= e($u['role']) ?></span>
                            </td>
                            <td>
                                <?php if($u['status'] === 'actif'): ?>
                                    <span class="badge bg-success px-2 py-1"><i data-lucide="check" style="width: 12px;" class="me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-2 py-1"><i data-lucide="x" style="width: 12px;" class="me-1"></i><?= e($u['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= date('Y-m-d', strtotime($u['date_inscription'])) ?></td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <form action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/toggle-active" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <button type="submit" class="btn btn-sm <?= $u['status'] === 'actif' ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="Toggle Active">
                                            <i data-lucide="power" style="width: 16px;"></i>
                                        </button>
                                    </form>
                                    <a href="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i data-lucide="edit-2" style="width:16px;"></i></a>
                                    <?php if((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
                                        <form action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this user permanently?');">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i data-lucide="trash-2" style="width:16px;"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
