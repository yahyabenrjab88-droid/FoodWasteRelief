<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'FoodWasteRelief') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/app.css" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="<?= BASE_URL ?>/">
                <i data-lucide="leaf" class="me-2" style="color: var(--primary);"></i>
                FoodWasteRelief
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i data-lucide="menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASE_URL ?>/">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASE_URL ?>/about">À propos</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASE_URL ?>/products">Catalogue</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="<?= BASE_URL ?>/contact">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <?php if(isset($_SESSION['user'])): ?>
                        <?php if($_SESSION['user']['role'] === 'client'): ?>
                            <a href="<?= BASE_URL ?>/cart" class="text-dark position-relative me-3">
                                <i data-lucide="shopping-cart"></i>
                                <!-- Cart count could be added here dynamically -->
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-primary rounded-pill px-4">Tableau de bord</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="text-dark fw-medium text-decoration-none">Connexion</a>
                        <a href="<?= BASE_URL ?>/register" class="btn btn-primary rounded-pill px-4 shadow-sm">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if(!empty($flash)): ?>
        <div class="container mt-4">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'success' ? 'success' : 'warning') ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: var(--radius); border-left: 5px solid <?= $flash['type'] === 'error' ? '#dc3545' : ($flash['type'] === 'success' ? '#198754' : '#ffc107') ?>;">
                <i data-lucide="<?= $flash['type'] === 'error' ? 'x-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'alert-triangle') ?>" class="me-3"></i>
                <div><?= e($flash['message']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-4 text-white d-flex align-items-center">
                        <i data-lucide="leaf" class="me-2" style="color: var(--light-green);"></i> FoodWasteRelief
                    </h5>
                    <p class="text-white-50 pe-lg-4">Connecter les entreprises ayant des surplus alimentaires à ceux qui en ont besoin. Ensemble, nous pouvons construire un avenir durable et sans gaspillage.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-white-50 text-white-hover"><i data-lucide="facebook"></i></a>
                        <a href="#" class="text-white-50 text-white-hover"><i data-lucide="twitter"></i></a>
                        <a href="#" class="text-white-50 text-white-hover"><i data-lucide="instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-4 text-white">Liens Rapides</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/" class="text-white-50 text-decoration-none">Accueil</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/about" class="text-white-50 text-decoration-none">À propos</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/products" class="text-white-50 text-decoration-none">Catalogue</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-4 text-white">S'impliquer</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/register" class="text-white-50 text-decoration-none">Devenir partenaire (Entreprise)</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/register" class="text-white-50 text-decoration-none">Rejoindre comme Association</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/register" class="text-white-50 text-decoration-none">Acheter des produits sauvés</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-4 text-white">Contact Info</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-3 d-flex align-items-center"><i data-lucide="map-pin" class="me-3" style="width: 18px;"></i> Tunis, Tunisie</li>
                        <li class="mb-3 d-flex align-items-center"><i data-lucide="mail" class="me-3" style="width: 18px;"></i> hello@foodwasterelief.com</li>
                        <li class="mb-3 d-flex align-items-center"><i data-lucide="phone" class="me-3" style="width: 18px;"></i> +216 71 000 000</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary mt-5 mb-4">
            <div class="text-center text-white-50 small">
                &copy; <?= date('Y') ?> FoodWasteRelief. Tous droits réservés. Construit pour une planète plus verte.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Initialize Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
