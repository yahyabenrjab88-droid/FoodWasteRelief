<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold m-0">My Products</h1>
        <a href="<?= BASE_URL ?>/business/products/create" class="btn btn-primary fw-bold rounded-pill px-4"><i data-lucide="plus" style="width:18px;"></i> Add Product</a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Expiry Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produits)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No products found. Add your first product!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produits as $p): ?>
                                <?php 
                                    $isExpired = new DateTime($p['date_expiration']) < new DateTime();
                                    $daysLeft = (new DateTime($p['date_expiration']))->diff(new DateTime())->days;
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($p['nom']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['categorie'] ?? 'N/A') ?></span></td>
                                    <td>
                                        <?php if ($p['type'] === 'vente'): ?>
                                            <span class="badge bg-primary">Sale</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Donation</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['type'] === 'vente'): ?>
                                            <?= number_format((float)$p['prix'], 2) ?> TND
                                        <?php else: ?>
                                            0.00 TND
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold <?= $p['quantite'] <= 5 ? 'text-danger' : '' ?>"><?= (int)$p['quantite'] ?></span>
                                    </td>
                                    <td>
                                        <span class="<?= $isExpired ? 'text-danger fw-bold' : ($daysLeft <= 3 ? 'text-warning fw-bold' : '') ?>">
                                            <?= htmlspecialchars((new DateTime($p['date_expiration']))->format('Y-m-d')) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= BASE_URL ?>/business/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary rounded-circle p-2 me-1" title="Edit">
                                            <i data-lucide="edit-2" style="width:16px; height:16px;"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/business/products/<?= $p['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Delete">
                                                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
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
</div>
