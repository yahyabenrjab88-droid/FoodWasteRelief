<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
    <div class="card border-0 shadow-lg fade-in" style="max-width: 450px; width: 100%; border-radius: var(--radius);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(45,106,79,0.1);">
                    <i data-lucide="lock" style="color: var(--primary); width: 28px; height: 28px;"></i>
                </div>
                <h2 class="fw-bold text-primary mb-2">Mot de passe oublié</h2>
                <p class="text-muted small">Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>

            <form action="<?= BASE_URL ?>/forgot-password" method="POST">
                <?= $this->csrf_field() ?>
                <div class="form-floating mb-4">
                    <input type="email" name="email" class="form-control" id="email" placeholder="nom@exemple.com" required autofocus>
                    <label for="email">Adresse e-mail</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-4 shadow-sm">
                    <i data-lucide="send" style="width: 18px;" class="me-2"></i> Envoyer le lien
                </button>
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/login" class="text-muted text-decoration-none small">
                        <i data-lucide="arrow-left" style="width: 14px;" class="me-1"></i> Retour à la connexion
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
