<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/storage" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="arrow-left"></i>
    </a>
    <h3 class="fw-bold text-primary m-0">Ajouter au Stock</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius); max-width: 800px;">
    <div class="card-body p-4 p-md-5">
        <form action="<?= BASE_URL ?>/storage/store" method="POST">
            <?= $this->csrf_field() ?>
            
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="form-floating">
                        <input type="text" name="item_name" class="form-control bg-light border-0" id="item_name" required>
                        <label for="item_name">Nom de l'article (ex: Pommes, Farine)</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" step="0.01" name="quantity" class="form-control bg-light border-0" id="quantity" required>
                        <label for="quantity">Quantité</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="unit" class="form-select bg-light border-0" id="unit">
                            <option value="kg">kg</option>
                            <option value="g">g</option>
                            <option value="L">L</option>
                            <option value="pieces">unités</option>
                        </select>
                        <label for="unit">Unité</label>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <div class="form-floating">
                        <input type="date" name="expiry_date" class="form-control bg-light border-0" id="expiry_date" required>
                        <label for="expiry_date">Date d'expiration</label>
                    </div>
                </div>
                
                <div class="col-12 text-end mt-5">
                    <a href="<?= BASE_URL ?>/storage" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Ajouter au Stock <i data-lucide="plus" class="ms-2" style="width: 18px;"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>
