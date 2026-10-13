<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="package" class="me-2"></i>Platform Products</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Product ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Business</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Price</th>
                        <th class="py-3">Stock</th>
                        <th class="py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($produits)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach($produits as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $p['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($p['nom']) ?></div>
                                    <div class="small text-muted"><?= e($p['categorie']) ?></div>
                                </td>
                                <td><i data-lucide="store" class="text-muted me-1" style="width:14px;"></i><?= e($p['nom_business']) ?></td>
                                <td>
                                    <?php if($p['type'] === 'donation'): ?>
                                        <span class="badge bg-success">Donation</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-pale text-primary border border-primary">Sale</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= $p['type'] === 'donation' ? '-' : number_format((float)$p['prix'], 2) . ' TND' ?></td>
                                <td>
                                    <?php if($p['quantite'] <= 5): ?>
                                        <span class="badge bg-danger px-2 py-1"><?= $p['quantite'] ?> left</span>
                                    <?php else: ?>
                                        <span class="fw-bold"><?= $p['quantite'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($p['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
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
