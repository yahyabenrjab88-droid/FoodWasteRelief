<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 200px);">
    <div class="card border-0 shadow-lg fade-in" style="max-width: 450px; width: 100%; border-radius: var(--radius);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(45,106,79,0.1);">
                    <i data-lucide="key" style="color: var(--primary); width: 28px; height: 28px;"></i>
                </div>
                <h2 class="fw-bold text-primary mb-2">Nouveau mot de passe</h2>
                <p class="text-muted small">Choisissez un mot de passe fort d'au moins 8 caractères.</p>
            </div>

            <form action="<?= BASE_URL ?>/reset-password/<?= e($token) ?>" method="POST">
                <?= $this->csrf_field() ?>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" name="password" class="form-control" id="password"
                           placeholder="Nouveau mot de passe" minlength="8" required>
                    <label for="password">Nouveau mot de passe</label>
                    <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y text-decoration-none"
                            onclick="togglePwd('password','eye1')" style="z-index: 5;" tabindex="-1">
                        <i data-lucide="eye" id="eye1" style="width: 20px;"></i>
                    </button>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" name="password_confirm" class="form-control" id="password_confirm"
                           placeholder="Confirmer le mot de passe" minlength="8" required>
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y text-decoration-none"
                            onclick="togglePwd('password_confirm','eye2')" style="z-index: 5;" tabindex="-1">
                        <i data-lucide="eye" id="eye2" style="width: 20px;"></i>
                    </button>
                </div>

                <!-- Password strength indicator -->
                <div class="mb-4">
                    <div class="progress" style="height: 4px; border-radius: 2px;">
                        <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%; transition: all 0.3s;"></div>
                    </div>
                    <div id="strength-text" class="text-muted small mt-1"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-4 shadow-sm">
                    <i data-lucide="check" style="width: 18px;" class="me-2"></i> Réinitialiser le mot de passe
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

<script>
function togglePwd(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.setAttribute('data-lucide', f.type === 'password' ? 'eye' : 'eye-off');
    lucide.createIcons();
}

document.getElementById('password').addEventListener('input', function() {
    const v = this.value;
    const bar = document.getElementById('strength-bar');
    const txt = document.getElementById('strength-text');
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const levels = [
        [0,   'bg-secondary', ''],
        [25,  'bg-danger',    'Très faible'],
        [50,  'bg-warning',   'Faible'],
        [75,  'bg-info',      'Moyen'],
        [100, 'bg-success',   'Fort']
    ];
    const [pct, cls, label] = levels[score] || levels[0];
    bar.style.width = pct + '%';
    bar.className = 'progress-bar ' + cls;
    txt.textContent = label;
});
</script>
