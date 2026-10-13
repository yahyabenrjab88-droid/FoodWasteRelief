<div class="container py-5" style="min-height: calc(100vh - 200px);">
    <h2 class="fw-bold mb-4 text-primary"><i data-lucide="shopping-cart" class="me-3"></i>Mon Panier</h2>

    <?php if (empty($cartItems)): ?>
        <div class="card border-0 shadow-sm text-center p-5" style="border-radius: var(--radius);">
            <div class="card-body py-5">
                <i data-lucide="shopping-bag" style="width: 80px; height: 80px; color: #ccc;" class="mb-4"></i>
                <h4 class="fw-bold text-muted mb-3">Votre panier est vide</h4>
                <p class="text-muted mb-4">Il semble que vous n'avez pas encore ajouté de nourriture sauvée du gaspillage.</p>
                <a href="<?= BASE_URL ?>/products" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Parcourir le Catalogue</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius);">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Produit</th>
                                        <th class="py-3">Prix</th>
                                        <th class="py-3">Quantité</th>
                                        <th class="py-3">Sous-total</th>
                                        <th class="py-3 pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark"><?= e($item['nom']) ?></div>
                                                <div class="text-muted small"><?= e($item['nom_business'] ?? 'Entreprise') ?></div>
                                            </td>
                                            <td><?= number_format((float)$item['prix'], 2) ?> TND</td>
                                            <td>
                                                <form action="<?= BASE_URL ?>/cart/update" method="POST" class="d-flex align-items-center" style="width: 120px;">
                                                    <?= $this->csrf_field() ?>
                                                    <input type="hidden" name="produit_id" value="<?= $item['id'] ?>">
                                                    <input type="number" name="quantite" class="form-control form-control-sm text-center border-0 bg-light me-2" value="<?= $item['cart_qty'] ?>" min="1" max="<?= $item['quantite'] ?>" onchange="this.form.submit()">
                                                    <span class="text-muted small">/ <?= $item['quantite'] ?></span>
                                                </form>
                                            </td>
                                            <td class="fw-bold"><?= number_format((float)$item['subtotal'], 2) ?> TND</td>
                                            <td class="pe-4 text-end">
                                                <form action="<?= BASE_URL ?>/cart/remove" method="POST">
                                                    <?= $this->csrf_field() ?>
                                                    <input type="hidden" name="produit_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-link text-danger p-0" title="Retirer">
                                                        <i data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/products" class="btn btn-outline-primary fw-bold rounded-pill"><i data-lucide="arrow-left" class="me-2" style="width: 16px;"></i> Continuer mes achats</a>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius); background-color: var(--primary-pale);">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-primary">Résumé de la Commande</h5>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Sous-total</span>
                            <span class="fw-bold"><?= number_format($total, 2) ?> TND</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Frais de Plateforme</span>
                            <span class="fw-bold text-success">Gratuit</span>
                        </div>
                        
                        <hr class="my-4 border-secondary">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5 text-dark">Total</span>
                            <span class="fw-bold fs-5 text-primary"><?= number_format($total, 2) ?> TND</span>
                        </div>

                        <form action="<?= BASE_URL ?>/cart/checkout" method="POST">
                            <?= $this->csrf_field() ?>
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                Valider la Commande <i data-lucide="check-circle" class="ms-2" style="width: 18px;"></i>
                            </button>
                        </form>
                        
                        <div class="mt-4 text-center text-muted small">
                            <i data-lucide="shield-check" style="width: 16px;" class="me-1"></i> Paiement lors du retrait en magasin
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
