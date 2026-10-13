<div class="bg-primary-pale py-4 mb-5 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/products" class="text-decoration-none text-primary fw-bold">Catalogue</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($produit['nom']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5" style="min-height: calc(100vh - 300px);">
    <div class="row g-5">
        <!-- Image Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: var(--radius);">
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <?php if(!empty($produit['image_url'])): ?>
                        <img src="<?= url($produit['image_url']) ?>" alt="Produit" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <i data-lucide="image" class="text-muted" style="width: 80px; height: 80px; opacity: 0.3;"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="col-md-6">
            <div class="mb-3 d-flex gap-2">
                <?php if($produit['type'] === 'donation'): ?>
                    <span class="badge bg-success shadow-sm fs-6 px-3 py-2">Donation</span>
                <?php endif; ?>
                <span class="badge bg-light text-dark border shadow-sm fs-6 px-3 py-2"><?= e($produit['categorie']) ?></span>
            </div>
            
            <h1 class="fw-bold text-dark mb-2"><?= e($produit['nom']) ?></h1>
            <p class="text-muted mb-4"><i data-lucide="store" class="me-2" style="width: 18px;"></i>Proposé par <span class="fw-bold text-dark"><?= e($produit['nom_business']) ?></span></p>
            
            <div class="card border-0 bg-light mb-4" style="border-radius: var(--radius);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1 text-uppercase fw-bold">Prix</div>
                        <?php if($produit['type'] === 'donation'): ?>
                            <h2 class="fw-bold text-success m-0">Gratuit</h2>
                        <?php else: ?>
                            <h2 class="fw-bold text-primary m-0"><?= number_format((float)$produit['prix'], 2) ?> TND</h2>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small mb-1 text-uppercase fw-bold">Disponibilité</div>
                        <h4 class="fw-bold m-0 <?= $produit['quantite'] > 0 ? 'text-dark' : 'text-danger' ?>">
                            <?= $produit['quantite'] > 0 ? $produit['quantite'] . ' en stock' : 'Rupture de stock' ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="fw-bold mb-3">Description</h5>
                <p class="text-muted" style="line-height: 1.6;"><?= e($produit['description'] ?: 'Aucune description fournie par le commerçant.') ?></p>
            </div>

            <div class="d-flex align-items-center mb-5 gap-4 text-muted small fw-medium">
                <div class="d-flex align-items-center"><i data-lucide="map-pin" class="me-2 text-primary"></i> <?= e($produit['ville']) ?></div>
                <div class="d-flex align-items-center"><i data-lucide="calendar" class="me-2 text-primary"></i> Expire le <?= date('d/m/Y', strtotime($produit['date_expiration'])) ?></div>
            </div>

            <?php if($produit['quantite'] > 0): ?>
                <?php if($_SESSION['user']['role'] === 'client'): ?>
                    <form action="<?= BASE_URL ?>/cart/add" method="POST" class="d-flex gap-3">
                        <?= $this->csrf_field() ?>
                        <input type="hidden" name="produit_id" value="<?= $produit['id'] ?>">
                        <div class="input-group flex-nowrap shadow-sm" style="width: 150px; border-radius: 50px; overflow: hidden;">
                            <span class="input-group-text bg-white border-0 fw-bold px-3">Qté</span>
                            <input type="number" name="quantite" class="form-control border-0 text-center fw-bold" value="1" min="1" max="<?= $produit['quantite'] ?>" required style="background-color: #f8f9fa;">
                        </div>
                        <button type="submit" class="btn btn-primary flex-grow-1 rounded-pill fw-bold shadow-sm py-3 fs-5">
                            <i data-lucide="shopping-cart" class="me-2"></i> Ajouter au Panier
                        </button>
                    </form>
                <?php elseif($_SESSION['user']['role'] === 'association' && $produit['type'] === 'donation'): ?>
                    <a href="<?= BASE_URL ?>/donations" class="btn btn-success w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm">
                        <i data-lucide="hand" class="me-2"></i> Demander ce Don
                    </a>
                <?php else: ?>
                    <div class="alert alert-info rounded-3 text-center mb-0">
                        Connectez-vous en tant que client pour acheter ce produit.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn btn-secondary w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm" disabled>Produit Épuisé</button>
            <?php endif; ?>
        </div>
    </div>
</div>
