<!-- Header -->
<div class="bg-primary-pale py-5 mb-5 border-bottom">
    <div class="container text-center">
        <h1 class="display-5 fw-bold text-primary mb-3">Catalogue des Produits</h1>
        <p class="lead text-muted mb-0 mx-auto" style="max-width: 600px;">Découvrez des repas et produits de qualité à prix réduits, et contribuez à la lutte contre le gaspillage alimentaire.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: var(--radius); position: sticky; top: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-4 d-flex align-items-center"><i data-lucide="filter" class="me-2" style="width: 18px;"></i> Filtres</h5>
                    <form action="<?= BASE_URL ?>/products" method="GET">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">Recherche</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i data-lucide="search" style="width: 16px;"></i></span>
                                <input type="text" name="q" class="form-control bg-light border-0" placeholder="Mot-clé..." value="<?= e($filters['q']) ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">Catégorie</label>
                            <select name="categorie" class="form-select bg-light border-0">
                                <option value="">Toutes les catégories</option>
                                <option value="Boulangerie" <?= $filters['cat'] === 'Boulangerie' ? 'selected' : '' ?>>Boulangerie & Pâtisserie</option>
                                <option value="Fruits" <?= $filters['cat'] === 'Fruits' ? 'selected' : '' ?>>Fruits & Légumes</option>
                                <option value="Plats" <?= $filters['cat'] === 'Plats' ? 'selected' : '' ?>>Plats Cuisinés</option>
                                <option value="Épicerie" <?= $filters['cat'] === 'Épicerie' ? 'selected' : '' ?>>Épicerie & Autres</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">Budget Maximum</label>
                            <select name="max_price" class="form-select bg-light border-0">
                                <option value="">Tous les prix</option>
                                <option value="5" <?= ($filters['max_price'] ?? '') == '5' ? 'selected' : '' ?>>Moins de 5 TND</option>
                                <option value="10" <?= ($filters['max_price'] ?? '') == '10' ? 'selected' : '' ?>>Moins de 10 TND</option>
                                <option value="20" <?= ($filters['max_price'] ?? '') == '20' ? 'selected' : '' ?>>Moins de 20 TND</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">Ville</label>
                            <select name="ville" class="form-select bg-light border-0">
                                <option value="">Toutes les villes</option>
                                <option value="Tunis" <?= $filters['ville'] === 'Tunis' ? 'selected' : '' ?>>Tunis</option>
                                <option value="Sfax" <?= $filters['ville'] === 'Sfax' ? 'selected' : '' ?>>Sfax</option>
                                <option value="Sousse" <?= $filters['ville'] === 'Sousse' ? 'selected' : '' ?>>Sousse</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">Appliquer les filtres</button>
                        <a href="<?= BASE_URL ?>/products" class="btn btn-link text-muted w-100 mt-2 text-decoration-none small">Réinitialiser</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="text-muted"><span class="fw-bold text-dark"><?= count($produits) ?></span> produits trouvés</div>
            </div>

            <?php if(empty($produits)): ?>
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i data-lucide="search-x" class="text-muted" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Aucun produit ne correspond à votre recherche</h4>
                    <p class="text-muted">Essayez de modifier vos filtres ou de revenir plus tard.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($produits as $p): ?>
                        <div class="col-md-6 col-xl-4 d-flex">
                            <div class="card border-0 shadow-sm w-100 product-card overflow-hidden" style="border-radius: var(--radius); transition: transform 0.2s, box-shadow 0.2s;">
                                <!-- Image Container -->
                                <div class="position-relative bg-light" style="height: 180px;">
                                    <?php if(!empty($p['image_url'])): ?>
                                        <img src="<?= url($p['image_url']) ?>" alt="Produit" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                            <i data-lucide="image" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Badges -->
                                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                                        <?php if($p['type'] === 'donation'): ?>
                                            <span class="badge bg-success shadow-sm">Donation</span>
                                        <?php endif; ?>
                                        <span class="badge bg-white text-dark shadow-sm"><?= e($p['categorie'] ?? 'Divers') ?></span>
                                    </div>
                                    
                                    <!-- Stock warning -->
                                    <?php if($p['quantite'] <= 3 && $p['quantite'] > 0): ?>
                                        <div class="position-absolute bottom-0 start-0 w-100 bg-danger text-white text-center py-1 small fw-bold" style="opacity: 0.9;">
                                            Plus que <?= $p['quantite'] ?> disponible(s) !
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body p-4 d-flex flex-column">
                                    <h5 class="fw-bold text-dark text-truncate mb-1"><?= e($p['nom']) ?></h5>
                                    <p class="text-muted small mb-3"><i data-lucide="store" style="width: 14px;" class="me-1"></i><?= e($p['nom_business']) ?></p>
                                    
                                    <div class="d-flex align-items-center text-muted small mb-3">
                                        <i data-lucide="map-pin" style="width: 14px;" class="me-1"></i> <span class="text-truncate"><?= e($p['ville']) ?></span>
                                    </div>
                                    
                                    <div class="mt-auto d-flex justify-content-between align-items-end pt-3 border-top">
                                        <div>
                                            <div class="text-muted small mb-1">Prix</div>
                                            <?php if($p['type'] === 'donation'): ?>
                                                <h5 class="fw-bold text-success m-0">Gratuit</h5>
                                            <?php else: ?>
                                                <h5 class="fw-bold text-primary m-0"><?= number_format((float)$p['prix'], 2) ?> TND</h5>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= BASE_URL ?>/products/<?= $p['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Détails</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($page['total_pages'] > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php for($i = 1; $i <= $page['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $page['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?p=<?= $i ?>&q=<?= urlencode($filters['q']) ?>&ville=<?= urlencode($filters['ville']) ?>&categorie=<?= urlencode($filters['cat']) ?>&max_price=<?= urlencode((string)($filters['max_price'] ?? '')) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
</style>
