<div class="container py-5" style="min-height: calc(100vh - 200px);">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center mb-5">
            <h1 class="fw-bold text-primary mb-3">Contactez-nous</h1>
            <p class="lead text-muted">Vous avez une question ou souhaitez devenir partenaire ? Écrivez-nous, nous serions ravis d'échanger avec vous.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg" style="border-radius: var(--radius);">
                <div class="row g-0">
                    <div class="col-md-5 bg-primary text-white p-5 d-flex flex-column justify-content-center" style="border-top-left-radius: var(--radius); border-bottom-left-radius: var(--radius);">
                        <h4 class="fw-bold mb-4">Informations</h4>
                        <div class="d-flex align-items-center mb-4">
                            <i data-lucide="map-pin" class="me-3" style="width: 24px; color: var(--accent);"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Adresse</h6>
                                <p class="small text-white-50 m-0">123 Rue de la Durabilité<br>Casablanca, 20000</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <i data-lucide="phone" class="me-3" style="width: 24px; color: var(--accent);"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Téléphone</h6>
                                <p class="small text-white-50 m-0">+212 522 123 456</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i data-lucide="mail" class="me-3" style="width: 24px; color: var(--accent);"></i>
                            <div>
                                <h6 class="fw-bold mb-0">E-mail</h6>
                                <p class="small text-white-50 m-0">contact@foodwasterelief.ma</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7 p-5">
                        <form action="<?= BASE_URL ?>/contact" method="POST">
                            <?= $this->csrf_field() ?>
                            
                            <div class="form-floating mb-3">
                                <input type="text" name="name" class="form-control bg-light border-0" id="name" placeholder="Votre nom" required>
                                <label for="name">Votre Nom</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control bg-light border-0" id="email" placeholder="name@example.com" required>
                                <label for="email">Adresse E-mail</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="text" name="subject" class="form-control bg-light border-0" id="subject" placeholder="Sujet" required>
                                <label for="subject">Sujet</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <textarea name="message" class="form-control bg-light border-0" id="message" placeholder="Votre message" style="height: 150px" required></textarea>
                                <label for="message">Votre Message</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                Envoyer le message <i data-lucide="send" class="ms-2" style="width: 18px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
