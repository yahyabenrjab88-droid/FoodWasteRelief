<!-- Hero Section -->
<section class="hero-gradient text-white text-center py-5 d-flex align-items-center" style="min-height: 80vh;">
    <div class="container fade-in">
        <i data-lucide="leaf" style="width: 80px; height: 80px; color: var(--accent);" class="mb-4"></i>
        <h1 class="display-3 fw-bold mb-4 text-white">Sauvons la Nourriture, Sauvons la Planète</h1>
        <p class="lead mb-5 mx-auto" style="max-width: 700px; color: rgba(255,255,255,0.9);">
            Rejoignez la révolution contre le gaspillage alimentaire. Connectez votre entreprise, soutenez votre association, ou sauvez de délicieux repas à une fraction de leur prix.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/products" class="btn btn-accent btn-lg rounded-pill px-5 py-3 shadow-lg fw-bold">Découvrir le Catalogue</a>
            <a href="<?= BASE_URL ?>/register" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">Devenir Partenaire</a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5" style="background-color: var(--primary-pale);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="display-5 fw-bold text-primary mb-2"><?= number_format($stats['kg_saved'] ?? 0, 1) ?></div>
                <div class="text-muted fw-medium text-uppercase small">KG CO2 Évités</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-5 fw-bold text-primary mb-2"><?= number_format($stats['businesses'] ?? 0) ?></div>
                <div class="text-muted fw-medium text-uppercase small">Entreprises Partenaires</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-5 fw-bold text-primary mb-2"><?= number_format($stats['associations'] ?? 0) ?></div>
                <div class="text-muted fw-medium text-uppercase small">Associations Soutenues</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-5 fw-bold text-primary mb-2"><?= number_format($stats['orders'] ?? 0) ?></div>
                <div class="text-muted fw-medium text-uppercase small">Repas Sauvés</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 my-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5 text-primary">Comment ça Marche ?</h2>
        <div class="row g-5">
            <div class="col-md-4">
                <div class="rounded-circle bg-primary-pale mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i data-lucide="store" style="color: var(--primary); width: 40px; height: 40px;"></i>
                </div>
                <h4 class="fw-bold mb-3">1. Les entreprises proposent</h4>
                <p class="text-muted">Boulangeries, restaurants et supermarchés publient leurs invendus en parfait état avec des réductions ou sous forme de dons.</p>
            </div>
            <div class="col-md-4">
                <div class="rounded-circle bg-primary-pale mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i data-lucide="smartphone" style="color: var(--primary); width: 40px; height: 40px;"></i>
                </div>
                <h4 class="fw-bold mb-3">2. Vous découvrez & réservez</h4>
                <p class="text-muted">Parcourez le catalogue, trouvez de superbes offres près de chez vous, et réservez depuis votre appareil.</p>
            </div>
            <div class="col-md-4">
                <div class="rounded-circle bg-primary-pale mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                    <i data-lucide="heart-handshake" style="color: var(--primary); width: 40px; height: 40px;"></i>
                </div>
                <h4 class="fw-bold mb-3">3. Vous récupérez & profitez</h4>
                <p class="text-muted">Rendez-vous en magasin, présentez votre commande et régalez-vous tout en sachant que vous aidez la planète.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="fw-bold mb-5 text-center text-primary">Témoignages</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                    <div class="card-body p-4 text-center">
                        <div class="text-warning mb-3">
                            <i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i>
                        </div>
                        <p class="fst-italic text-muted mb-4">"Nous avons réduit nos déchets quotidiens à zéro. Ce qui n'est pas vendu part aux associations ou aux clients qui adorent nos pâtisseries !"</p>
                        <h6 class="fw-bold m-0">La Bonne Baguette</h6>
                        <small class="text-muted">Boulangerie Partenaire</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                    <div class="card-body p-4 text-center">
                        <div class="text-warning mb-3">
                            <i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i>
                        </div>
                        <p class="fst-italic text-muted mb-4">"FoodWasteRelief nous fournit des dons réguliers pour nourrir plus de 50 familles chaque semaine. Une véritable bouée de sauvetage."</p>
                        <h6 class="fw-bold m-0">Association Espoir</h6>
                        <small class="text-muted">ONG</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
                    <div class="card-body p-4 text-center">
                        <div class="text-warning mb-3">
                            <i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i><i data-lucide="star" style="fill: currentColor;"></i>
                        </div>
                        <p class="fst-italic text-muted mb-4">"Je mange de superbes plats de mes restaurants préférés avec 70% de réduction tout en faisant un geste pour l'environnement. Gagnant-gagnant !"</p>
                        <h6 class="fw-bold m-0">Sarah K.</h6>
                        <small class="text-muted">Étudiante</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5" style="background-color: var(--primary);">
    <div class="container text-center py-4">
        <h2 class="fw-bold text-white mb-4">Prêt à avoir un impact ?</h2>
        <a href="<?= BASE_URL ?>/register" class="btn btn-accent btn-lg rounded-pill px-5 py-3 fw-bold shadow">Créer votre compte</a>
    </div>
</section>
