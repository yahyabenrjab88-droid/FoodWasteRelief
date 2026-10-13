<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Utilisateurs</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Utilisateur</th>
                        <th class="py-3">Rôle</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Statut</th>
                        <th class="py-3">Mot de passe</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Aucun utilisateur trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark"><?= e($u['prenom']) ?> <?= e($u['nom']) ?></div>
                                </td>
                                <td class="py-3">
                                    <?php
                                    $roleColor = $u['role'] === 'admin' ? 'bg-danger' : ($u['role'] === 'business' ? 'bg-warning text-dark' : ($u['role'] === 'association' ? 'bg-primary' : 'bg-info'));
                                    ?>
                                    <span class="badge <?= $roleColor ?> text-white font-monospace"><?= ucfirst(e($u['role'])) ?></span>
                                </td>
                                <td class="py-3">
                                    <div class="text-muted small"><i data-lucide="mail" style="width: 12px; height: 12px;" class="me-1"></i> <?= e($u['email']) ?></div>
                                    <?php if(!empty($u['telephone'])): ?>
                                    <div class="text-dark small"><i data-lucide="phone" style="width: 12px; height: 12px;" class="me-1"></i> <?= e($u['telephone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <span class="badge <?= $u['status'] === 'actif' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst(e($u['status'])) ?></span>
                                </td>
                                <td class="py-3">
                                    <code class="text-primary small" style="word-break: break-all;"><?= e($u['password']) ?></code>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <form action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/toggle-active" method="POST" class="d-inline">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm <?= $u['status'] === 'actif' ? 'btn-outline-warning' : 'btn-outline-success' ?> rounded-pill fw-bold" title="<?= $u['status'] === 'actif' ? 'Désactiver' : 'Activer' ?>">
                                            <i data-lucide="<?= $u['status'] === 'actif' ? 'power-off' : 'power' ?>" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill fw-bold" title="Supprimer">
                                            <i data-lucide="trash-2" style="width: 14px;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
