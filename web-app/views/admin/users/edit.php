<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="arrow-left"></i>
    </a>
    <h3 class="fw-bold text-primary m-0">Edit User #<?= $user['id'] ?></h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius); max-width: 600px;">
    <div class="card-body p-4 p-md-5">
        <form action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="prenom" class="form-control bg-light border-0" id="prenom" value="<?= e($user['prenom']) ?>" required>
                        <label for="prenom">First Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="nom" class="form-control bg-light border-0" id="nom" value="<?= e($user['nom']) ?>" required>
                        <label for="nom">Last Name</label>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating">
                        <input type="email" class="form-control bg-light border-0" id="email" value="<?= e($user['email']) ?>" readonly disabled>
                        <label for="email">Email Address (Cannot be changed)</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" name="telephone" class="form-control bg-light border-0" id="telephone" value="<?= e($user['telephone']) ?>">
                        <label for="telephone">Phone Number</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        <select name="status" class="form-select bg-light border-0" id="status">
                            <option value="actif" <?= $user['status'] === 'actif' ? 'selected' : '' ?>>Active</option>
                            <option value="inactif" <?= $user['status'] === 'inactif' ? 'selected' : '' ?>>Inactive / Banned</option>
                        </select>
                        <label for="status">Account Status</label>
                    </div>
                </div>
                
                <div class="col-12 text-end mt-5">
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary rounded-pill px-4 me-2 fw-bold">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Update User <i data-lucide="check" class="ms-2" style="width: 18px;"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>
