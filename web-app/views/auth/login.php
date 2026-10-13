<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
    <div class="card border-0 shadow-lg fade-in" style="max-width: 450px; width: 100%; border-radius: var(--radius);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i data-lucide="leaf" style="color: var(--primary); width: 48px; height: 48px;" class="mb-3"></i>
                <h2 class="fw-bold text-primary mb-2">Bon retour</h2>
                <p class="text-muted">Connectez-vous à votre compte FoodWasteRelief</p>
            </div>
            
            <form action="<?= BASE_URL ?>/login" method="post">
                <?= $this->csrf_field() ?>
                
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="email" placeholder="nom@exemple.com" required>
                    <label for="email">Adresse e-mail</label>
                </div>
                
                <div class="form-floating mb-4 position-relative">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Mot de passe" required>
                    <label for="password">Mot de passe</label>
                    <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y text-decoration-none" onclick="togglePassword()" style="z-index: 5;">
                        <i data-lucide="eye" id="eye-icon" style="width: 20px;"></i>
                    </button>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" style="accent-color: var(--primary);">
                        <label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="<?= BASE_URL ?>/forgot-password" class="text-primary text-decoration-none small fw-bold">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-4 shadow-sm">Se connecter</button>
                
                <div class="text-center">
                    <p class="text-muted m-0">Vous n'avez pas de compte ? <a href="<?= BASE_URL ?>/register" class="text-primary fw-bold text-decoration-none">Créer un compte</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        pwd.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}
</script>
