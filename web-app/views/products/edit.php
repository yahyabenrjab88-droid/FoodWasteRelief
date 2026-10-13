<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/business/products" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="arrow-left"></i>
    </a>
    <h3 class="fw-bold text-primary m-0">Modifier le Produit</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius); max-width: 800px;">
    <div class="card-body p-4 p-md-5">
        <form action="<?= BASE_URL ?>/business/products/<?= $produit['id'] ?>/update" method="POST" enctype="multipart/form-data">
            <?= $this->csrf_field() ?>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="nom" class="form-control bg-light border-0" id="nom" value="<?= e($produit['nom']) ?>" required>
                        <label for="nom">Nom du Produit</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="type" class="form-select bg-light border-0" id="type" required>
                            <option value="vente" <?= $produit['type'] === 'vente' ? 'selected' : '' ?>>Vente</option>
                            <option value="donation" <?= $produit['type'] === 'donation' ? 'selected' : '' ?>>Donation</option>
                        </select>
                        <label for="type">Type d'Offre</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="categorie" class="form-control bg-light border-0" id="categorie" value="<?= e($produit['categorie']) ?>" required>
                        <label for="categorie">Catégorie</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" step="0.01" name="prix" class="form-control bg-light border-0" id="prix" value="<?= $produit['prix'] ?>">
                        <label for="prix">Prix (TND)</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" name="quantite" class="form-control bg-light border-0" id="quantite" value="<?= $produit['quantite'] ?>" required>
                        <label for="quantite">Quantité Disponible</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" name="date_expiration" class="form-control bg-light border-0" id="date_expiration" value="<?= e(explode(' ', $produit['date_expiration'])[0]) ?>" required>
                        <label for="date_expiration">Date d'Expiration</label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-floating">
                        <textarea name="description" class="form-control bg-light border-0" id="description" style="height: 100px"><?= e($produit['description']) ?></textarea>
                        <label for="description">Description (Optionnelle)</label>
                    </div>
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-medium text-muted small text-uppercase">Image du Produit</label>
                    <?php if (!empty($produit['image_url'])): ?>
                    <div class="mb-2 d-flex align-items-center gap-3">
                        <img src="<?= url($produit['image_url']) ?>" alt="Image actuelle" class="rounded shadow-sm" style="height: 72px; width: 72px; object-fit: cover;">
                        <span class="text-muted small">Image actuelle — choisissez un nouveau fichier pour la remplacer</span>
                    </div>
                    <?php endif; ?>
                    <div class="border-2 border-dashed rounded p-3 text-center bg-light position-relative" style="border: 2px dashed #dee2e6; cursor: pointer;" onclick="document.getElementById('image').click()">
                        <i data-lucide="upload" class="text-muted mb-2" style="width: 28px;"></i>
                        <p class="text-muted small mb-1"><?= !empty($produit['image_url']) ? 'Cliquez pour remplacer l\'image' : 'Cliquez pour choisir une image' ?></p>
                        <p class="text-muted" style="font-size: 0.75rem;">JPG, PNG, WebP · Max 5 Mo</p>
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif" class="d-none" onchange="previewImg(this)">
                    </div>
                    <div id="img-preview" class="mt-2 text-center d-none">
                        <img id="preview-src" src="" alt="Aperçu" class="rounded shadow-sm" style="max-height: 120px; max-width: 100%; object-fit: cover;">
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating">
                        <select name="storage_item_id" class="form-select bg-light border-0" id="storage_item_id">
                            <option value="">Aucun lien avec le stock</option>
                            <?php if(!empty($storageItems)): foreach($storageItems as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $produit['storage_item_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['item_name']) ?> (<?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <label for="storage_item_id">Associer à un Article du Stock</label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= (int)$produit['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-medium" for="is_active">Produit visible sur le catalogue</label>
                    </div>
                </div>
                
                <div class="col-12 text-end mt-5">
                    <a href="<?= BASE_URL ?>/business/products" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Mettre à jour <i data-lucide="save" class="ms-2" style="width: 18px;"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-src').src = e.target.result;
            document.getElementById('img-preview').classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
