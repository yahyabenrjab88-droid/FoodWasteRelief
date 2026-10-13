<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-primary m-0"><i data-lucide="layout-dashboard" class="me-2"></i>Tableau de bord Client</h3>
        <p class="text-muted mt-1 mb-0">Bienvenue, voici un aperçu de votre impact et de vos activités.</p>
    </div>
    <a href="<?= BASE_URL ?>/products" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i data-lucide="search" class="me-2" style="width:16px;"></i>Parcourir les produits</a>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--primary);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Total des Commandes</h6>
                        <h2 class="fw-bold m-0 text-dark"><?= number_format($data['total_orders']) ?></h2>
                    </div>
                    <div class="kpi-icon primary" style="width: 56px; height: 56px;">
                        <i data-lucide="shopping-bag" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--accent);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Total Dépensé</h6>
                        <h2 class="fw-bold m-0 text-dark"><?= number_format($data['total_spent'], 2) ?> <span class="fs-5">TND</span></h2>
                    </div>
                    <div class="kpi-icon accent" style="width: 56px; height: 56px;">
                        <i data-lucide="credit-card" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #198754;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Articles Sauvés</h6>
                        <h2 class="fw-bold m-0 text-dark"><?= number_format($data['items_saved']) ?></h2>
                    </div>
                    <div class="kpi-icon" style="background: rgba(25, 135, 84, 0.2); color: #198754; width: 56px; height: 56px;">
                        <i data-lucide="leaf" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0">Commandes Récentes</h5>
                <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-primary rounded-pill px-3">Voir Tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">Entreprise</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Statut</th>
                                <th class="py-3 pe-4 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['recent_orders'])): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">Aucune commande pour l'instant. Commencez à sauver de la nourriture !</td></tr>
                            <?php else: ?>
                                <?php foreach(array_slice($data['recent_orders'], 0, 5) as $o): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <a href="<?= BASE_URL ?>/orders/<?= $o['id'] ?>" class="text-decoration-none text-dark d-flex align-items-center">
                                                <i data-lucide="store" class="text-muted me-2" style="width: 16px;"></i> <?= e($o['nom_business']) ?>
                                            </a>
                                        </td>
                                        <td class="text-muted small"><?= date('d/m/Y', strtotime($o['date_commande'])) ?></td>
                                        <td>
                                            <?php
                                            $col = $o['status'] === 'en attente' ? 'bg-warning text-dark' : (in_array($o['status'], ['terminé', 'livrée']) ? 'bg-success' : 'bg-primary');
                                            ?>
                                            <span class="badge <?= $col ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem;"><?= e($o['status']) ?></span>
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-primary"><?= number_format((float)$o['total'], 2) ?> TND</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); background-color: var(--primary-pale);">
            <div class="card-header bg-transparent p-4 border-0 pb-0">
                <h5 class="fw-bold text-primary m-0"><i data-lucide="sparkles" class="me-2 text-accent"></i>Recommandé pour vous</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">
                    <?php foreach($data['recommended'] as $rec): ?>
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div class="card-body p-3 d-flex align-items-center">
                                <?php
                                $catImages = [
                                    'Boulangerie' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=100&q=80',
                                    'Plat Préparé' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&q=80',
                                    'Epicerie' => 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=100&q=80',
                                    'Fruits & Légumes' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=100&q=80'
                                ];
                                $imgUrl = !empty($rec['image_url']) ? e($rec['image_url']) : ($catImages[$rec['categorie']] ?? 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=100&q=80');
                                ?>
                                <img src="<?= $imgUrl ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" class="me-3">
                                
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold m-0 text-truncate" style="max-width: 180px;"><?= e($rec['nom']) ?></h6>
                                    <small class="text-muted"><i data-lucide="store" style="width:12px; display:inline;" class="me-1"></i><?= e($rec['nom_business']) ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary mb-1"><?= number_format((float)$rec['prix'], 2) ?> TND</div>
                                    <a href="<?= BASE_URL ?>/products/<?= $rec['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 0.75rem;">Voir</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
