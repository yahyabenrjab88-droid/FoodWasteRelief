<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0">Mes Produits</h3>
    <a href="<?= BASE_URL ?>/business/products/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i data-lucide="plus" style="width: 18px;" class="me-1"></i> Ajouter un Produit
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3" style="width: 60px;">Image</th>
                        <th class="py-3">Produit</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Prix</th>
                        <th class="py-3">Stock</th>
                        <th class="py-3">Statut</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($produits)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Vous n'avez pas encore ajouté de produits.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($produits as $p): ?>
                            <tr class="<?= !(int)$p['is_active'] ? 'opacity-50' : '' ?>">
                                <td class="ps-4 py-3">
                                    <?php if(!empty($p['image_url'])): ?>
                                        <img src="<?= url($p['image_url']) ?>" alt="Produit" class="rounded object-fit-cover shadow-sm" style="width: 48px; height: 48px;">
                                    <?php else: ?>
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                            <i data-lucide="image" style="width: 20px;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark"><?= e($p['nom']) ?></div>
                                    <div class="text-muted small"><?= e($p['categorie']) ?></div>
                                </td>
                                <td class="py-3">
                                    <span class="badge <?= $p['type'] === 'donation' ? 'bg-success' : 'bg-primary' ?>"><?= ucfirst(e($p['type'])) ?></span>
                                </td>
                                <td class="py-3 fw-medium">
                                    <?= $p['type'] === 'vente' ? number_format((float)$p['prix'], 2) . ' TND' : '<span class="text-success">Gratuit</span>' ?>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fw-bold <?= $p['quantite'] < 5 ? 'text-danger' : 'text-dark' ?> me-2"><?= $p['quantite'] ?></div>
                                        <?php if($p['quantite'] < 5): ?>
                                            <i data-lucide="alert-triangle" class="text-danger" style="width: 14px;"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <?php if((int)$p['is_active']): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <a href="<?= BASE_URL ?>/business/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary rounded-pill me-1" title="Modifier">
                                        <i data-lucide="edit-2" style="width: 14px;"></i>
                                    </a>
                                    <form action="<?= BASE_URL ?>/business/products/<?= $p['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        <?= $this->csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" title="Supprimer">
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
