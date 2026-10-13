<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 100px);">
    <div class="card border-0 shadow-lg fade-in" style="max-width: 600px; width: 100%; border-radius: var(--radius);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i data-lucide="user-plus" style="color: var(--primary); width: 48px; height: 48px;" class="mb-3"></i>
                <h2 class="fw-bold text-primary mb-2">Rejoignez le Mouvement</h2>
                <p class="text-muted">Créez votre compte FoodWasteRelief</p>
            </div>
            
            <div class="progress mb-4" style="height: 6px; border-radius: 3px;">
                <div id="progressBar" class="progress-bar bg-primary" role="progressbar" style="width: 33%; transition: width 0.3s ease;"></div>
            </div>

            <form action="<?= BASE_URL ?>/register" method="post" id="registerForm">
                <?= $this->csrf_field() ?>
                
                <!-- STEP 1: Role Selection -->
                <div id="step1" class="step-container">
                    <h5 class="fw-bold mb-3">Sélectionnez votre rôle</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="role" id="role_client" value="client" checked onclick="updateRoleUI()">
                            <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3 d-flex align-items-center" for="role_client">
                                <i data-lucide="user" class="me-3" style="width: 24px;"></i>
                                <div>
                                    <div class="fw-bold">Client</div>
                                    <small class="text-muted">Acheter des invendus à prix réduit</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="role" id="role_business" value="business" onclick="updateRoleUI()">
                            <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3 d-flex align-items-center" for="role_business">
                                <i data-lucide="store" class="me-3" style="width: 24px;"></i>
                                <div>
                                    <div class="fw-bold">Entreprise</div>
                                    <small class="text-muted">Vendre et faire don de surplus (Restaurants, Boulangeries...)</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="role" id="role_association" value="association" onclick="updateRoleUI()">
                            <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3 d-flex align-items-center" for="role_association">
                                <i data-lucide="heart-handshake" class="me-3" style="width: 24px;"></i>
                                <div>
                                    <div class="fw-bold">Association (ONG)</div>
                                    <small class="text-muted">Recevoir et distribuer des dons alimentaires</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="nextStep(2)">Étape Suivante <i data-lucide="arrow-right" class="ms-2" style="width: 16px;"></i></button>
                    </div>
                </div>

                <!-- STEP 2: Basic Info -->
                <div id="step2" class="step-container" style="display: none;">
                    <h5 class="fw-bold mb-3">Informations Personnelles</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="prenom" class="form-control" id="prenom" placeholder="Prénom" required>
                                <label for="prenom">Prénom</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="nom" class="form-control" id="nom" placeholder="Nom" required>
                                <label for="nom">Nom de famille</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control" id="email" placeholder="Email" pattern=".*@gmail\.com" title="Veuillez utiliser une adresse @gmail.com" required>
                                <label for="email">Adresse E-mail</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" name="password" class="form-control" id="password" placeholder="Mot de passe" minlength="8" required>
                                <label for="password">Mot de passe</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" name="password_confirm" class="form-control" id="password_confirm" placeholder="Confirmer" minlength="8" required>
                                <label for="password_confirm">Confirmer</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="nextStep(1)"><i data-lucide="arrow-left" class="me-2" style="width: 16px;"></i> Retour</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="nextStep(3)">Étape Suivante <i data-lucide="arrow-right" class="ms-2" style="width: 16px;"></i></button>
                    </div>
                </div>

                <!-- STEP 3: Specific Info -->
                <div id="step3" class="step-container" style="display: none;">
                    <h5 class="fw-bold mb-3" id="step3_title">Informations Complémentaires</h5>
                    
                    <!-- Shared / Client -->
                    <div class="form-floating mb-3">
                        <input type="text" name="telephone" class="form-control" id="telephone" placeholder="Téléphone">
                        <label for="telephone">Numéro de Téléphone</label>
                    </div>

                    <!-- Business Specific -->
                    <div id="business_fields" style="display: none;">
                        <div class="form-floating mb-3">
                            <input type="text" name="nom_business" class="form-control" id="nom_business" placeholder="Nom de l'entreprise">
                            <label for="nom_business">Nom de l'entreprise</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="patente" class="form-control" id="patente" placeholder="Patente">
                            <label for="patente">Identifiant Fiscal (Patente)</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="ville" class="form-control" id="ville" placeholder="Ville">
                            <label for="ville">Ville</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="localisation" class="form-control" id="localisation" placeholder="Adresse">
                            <label for="localisation">Adresse complète</label>
                        </div>
                    </div>

                    <!-- Association Specific -->
                    <div id="association_fields" style="display: none;">
                        <div class="form-floating mb-3">
                            <input type="text" name="nom_association" class="form-control" id="nom_association" placeholder="Nom de l'association">
                            <label for="nom_association">Nom de l'ONG / Association</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="ville_association" class="form-control" id="ville_association" placeholder="Ville">
                            <label for="ville_association">Ville</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="nextStep(2)"><i data-lucide="arrow-left" class="me-2" style="width: 16px;"></i> Retour</button>
                        <button type="submit" class="btn btn-accent rounded-pill px-4 fw-bold shadow-sm">Terminer l'inscription <i data-lucide="check" class="ms-2" style="width: 16px;"></i></button>
                    </div>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted m-0">Vous avez déjà un compte ? <a href="<?= BASE_URL ?>/login" class="text-primary fw-bold text-decoration-none">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<script>
function nextStep(step) {
    
    if(step === 3) {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('password_confirm').value;
        if(p1.length < 8) { alert('Le mot de passe doit contenir au moins 8 caractères'); return; }
        if(p1 !== p2) { alert('Les mots de passe ne correspondent pas'); return; }
        
        const email = document.getElementById('email').value;
        if(!email.toLowerCase().endsWith('@gmail.com')) {
            alert('Désolé, seule les adresses @gmail.com sont acceptées.');
            return;
        }

        const reqFields = ['prenom', 'nom', 'email'];
        for(let id of reqFields) {
            if(!document.getElementById(id).value) {
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }
        }
    }

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    
    document.getElementById('step' + step).style.display = 'block';

    const progress = step === 1 ? 33 : (step === 2 ? 66 : 100);
    document.getElementById('progressBar').style.width = progress + '%';
    
    updateRoleUI();
}

function updateRoleUI() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const bFields = document.getElementById('business_fields');
    const aFields = document.getElementById('association_fields');
    const tLabel = document.getElementById('step3_title');
    
    bFields.style.display = 'none';
    aFields.style.display = 'none';

    ['nom_business', 'ville', 'nom_association', 'ville_association'].forEach(id => {
        document.getElementById(id).required = false;
    });

    if (role === 'business') {
        bFields.style.display = 'block';
        tLabel.innerText = 'Informations de l\'Entreprise';
        document.getElementById('nom_business').required = true;
        document.getElementById('ville').required = true;
    } else if (role === 'association') {
        aFields.style.display = 'block';
        tLabel.innerText = 'Informations de l\'Association';
        document.getElementById('nom_association').required = true;
        document.getElementById('ville_association').required = true;
    } else {
        tLabel.innerText = 'Coordonnées';
    }
}
</script>
