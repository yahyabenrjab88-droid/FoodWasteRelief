<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Tableau de bord - FoodWasteRelief') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/public/css/app.css" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: var(--surface); }
        .sidebar {
            width: 260px; background-color: white; border-right: 1px solid rgba(0,0,0,0.05);
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            padding: 1.5rem; display: flex; flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.02); transition: transform 0.3s ease;
        }
        .main-content { margin-left: 260px; padding: 2rem; min-height: 100vh; }
        .nav-link {
            color: #495057; border-radius: 8px; padding: 0.75rem 1rem;
            margin-bottom: 0.25rem; display: flex; align-items: center;
            font-weight: 500; transition: all 0.2s;
        }
        .nav-link:hover { background-color: var(--primary-pale); color: var(--primary); }
        .nav-link.active { background-color: var(--primary); color: white; box-shadow: 0 4px 6px rgba(45, 106, 79, 0.2); }
        .nav-link i { margin-right: 12px; width: 20px; height: 20px; }
        .top-navbar {
            background-color: white; border-radius: 12px; padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>

    <?php $role = $_SESSION['user']['role'] ?? ''; ?>

    <div class="sidebar" id="sidebar">
        <div class="d-flex align-items-center mb-4">
            <i data-lucide="leaf" class="text-primary me-2" style="width: 28px; height: 28px;"></i>
            <h4 class="fw-bold text-primary m-0" style="font-family: 'Playfair Display', serif;">FWR</h4>
        </div>
        
        <div class="mb-4 px-3 py-3 bg-light rounded-3 text-center border">
            <div class="rounded-circle bg-white shadow-sm d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                <span class="fw-bold text-primary"><?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div>
                <h6 class="fw-bold m-0 text-dark"><?= e($_SESSION['user']['prenom'] ?? '') ?> <?= e($_SESSION['user']['nom'] ?? '') ?></h6>
                <div class="mt-1">
                    <?php
                    $roleColor = $role === 'admin' ? 'bg-danger' : ($role === 'business' ? 'bg-warning text-dark' : ($role === 'association' ? 'bg-primary' : 'bg-info'));
                    $roleLabel = [
                        'admin' => 'Administrateur',
                        'business' => 'Entreprise',
                        'association' => 'Association',
                        'client' => 'Client'
                    ][$role] ?? ucfirst($role);
                    ?>
                    <span class="badge <?= $roleColor ?> text-white" style="font-size: 0.7rem;"><?= $roleLabel ?></span>
                </div>
            </div>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') && !str_contains($_SERVER['REQUEST_URI'], 'admin/dashboard') ? 'active' : '' ?>">
                    <i data-lucide="layout-dashboard"></i> Tableau de bord
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/users" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'users') ? 'active' : '' ?>">
                        <i data-lucide="users"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/businesses" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'businesses') ? 'active' : '' ?>">
                        <i data-lucide="store"></i> Entreprises
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/associations" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'associations') ? 'active' : '' ?>">
                        <i data-lucide="heart-handshake"></i> Associations
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/products" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'products') ? 'active' : '' ?>">
                        <i data-lucide="package"></i> Tous les Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/orders" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'orders') ? 'active' : '' ?>">
                        <i data-lucide="shopping-bag"></i> Toutes les Commandes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/donations" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'donations') ? 'active' : '' ?>">
                        <i data-lucide="gift"></i> Tous les Dons
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/contact" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'contact') ? 'active' : '' ?>">
                        <i data-lucide="mail"></i> Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/admin/activity" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'activity') ? 'active' : '' ?>">
                        <i data-lucide="activity"></i> Journal d'Activité
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'business'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/business/products" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'products') ? 'active' : '' ?>">
                        <i data-lucide="package"></i> Mes Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/storage" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'storage') ? 'active' : '' ?>">
                        <i data-lucide="box"></i> Mon Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/orders" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'orders') ? 'active' : '' ?>">
                        <i data-lucide="shopping-cart"></i> Commandes Reçues
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/donations" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'donations') ? 'active' : '' ?>">
                        <i data-lucide="heart"></i> Dons
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'association'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/donations" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'donations') ? 'active' : '' ?>">
                        <i data-lucide="gift"></i> Mes Dons
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'client'): ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/orders" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'orders') ? 'active' : '' ?>">
                        <i data-lucide="shopping-bag"></i> Mes Commandes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/cart" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'cart') ? 'active' : '' ?>">
                        <i data-lucide="shopping-cart"></i> Mon Panier
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="<?= BASE_URL ?>/statistics" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'statistics') ? 'active' : '' ?>">
                    <i data-lucide="bar-chart-2"></i> Statistiques
                </a>
            </li>

            <li class="nav-item mt-auto border-top pt-3">
                <a href="<?= BASE_URL ?>/" class="nav-link">
                    <i data-lucide="home"></i> Retour au site
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= BASE_URL ?>/logout" class="nav-link text-danger fw-bold">
                    <i data-lucide="log-out"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark p-0 me-3 d-md-none" onclick="toggleSidebar()">
                    <i data-lucide="menu"></i>
                </button>
                <h5 class="fw-bold m-0 d-none d-md-block text-dark"><?= e($title ?? 'Tableau de bord') ?></h5>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center rounded-pill px-3 py-2 border" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.8rem;">
                        <span class="fw-bold"><?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <span class="fw-medium text-dark me-1 d-none d-sm-inline"><?= e($_SESSION['user']['prenom'] ?? '') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userMenu" style="border-radius: var(--radius);">
                    <li><h6 class="dropdown-header">Connecté en tant que <?= e($_SESSION['user']['role'] ?? '') ?></h6></li>
                    <li><a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/dashboard"><i data-lucide="user" class="me-2" style="width: 16px;"></i> Mon Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold" href="<?= BASE_URL ?>/logout"><i data-lucide="log-out" class="me-2" style="width: 16px;"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>

        <?php if(!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'success' ? 'success' : 'warning') ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: var(--radius); border-left: 5px solid <?= $flash['type'] === 'error' ? '#dc3545' : ($flash['type'] === 'success' ? '#198754' : '#ffc107') ?>;">
                <i data-lucide="<?= $flash['type'] === 'error' ? 'x-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'alert-triangle') ?>" class="me-3"></i>
                <div><?= e($flash['message']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        lucide.createIcons();
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>
