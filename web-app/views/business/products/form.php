<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/business/products" class="text-decoration-none text-muted fw-bold"><i data-lucide="arrow-left" class="me-1" style="width: 16px;"></i> Back to Products</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4"><?= $produit ? 'Edit Product' : 'Add New Product' ?></h2>
                    
                    <?php $action = $produit ? "<?= BASE_URL ?>/business/products/{$produit['id']}/update" : "<?= BASE_URL ?>/business/products/store"; ?>
                    <form action="<?= $action ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Product Name</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($produit['nom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="vente" <?= ($produit['type'] ?? '') === 'vente' ? 'selected' : '' ?>>Discounted Sale</option>
                                    <option value="don" <?= ($produit['type'] ?? '') === 'don' ? 'selected' : '' ?>>Free Donation</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Category</label>
                                <input type="text" name="categorie" class="form-control" value="<?= htmlspecialchars($produit['categorie'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Price (TND)</label>
                                <input type="number" step="0.01" name="prix" class="form-control" value="<?= htmlspecialchars((string)($produit['prix'] ?? '0')) ?>">
                                <div class="form-text">Leave 0 if donation.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" name="quantite" class="form-control" value="<?= htmlspecialchars((string)($produit['quantite'] ?? '1')) ?>" min="1" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <?php 
                                    $expDate = '';
                                    if ($produit && !empty($produit['date_expiration'])) {
                                        $expDate = (new DateTime($produit['date_expiration']))->format('Y-m-d\TH:i');
                                    }
                                ?>
                                <input type="datetime-local" name="date_expiration" class="form-control" value="<?= $expDate ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Link to Storage Item (Optional)</label>
                                <select name="storage_item_id" class="form-select">
                                    <option value="0">None</option>
                                    <?php foreach ($storageItems as $item): ?>
                                        <option value="<?= $item['id'] ?>" <?= ($produit['storage_item_id'] ?? 0) == $item['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['name']) ?> (<?= $item['quantity'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 px-5 rounded-pill fw-bold">
                            <?= $produit ? 'Save Changes' : 'Create Product' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
