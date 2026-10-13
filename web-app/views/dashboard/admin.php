<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="layout-dashboard" class="me-2"></i>Tableau de bord Administrateur</h3>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--primary);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Utilisateurs Total</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['users']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background-color: rgba(45, 106, 79, 0.1); color: var(--primary); padding: 12px; border-radius: 50%;">
                        <i data-lucide="users" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Entreprises en Attente</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['businesses']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background-color: rgba(255, 193, 7, 0.2); color: #ffc107; padding: 12px; border-radius: 50%;">
                        <i data-lucide="store" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--accent);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Commandes Total</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['orders']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background-color: rgba(244, 162, 97, 0.1); color: var(--accent); padding: 12px; border-radius: 50%;">
                        <i data-lucide="shopping-bag" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid #198754;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase small mb-1">Dons Effectués</h6>
                        <h3 class="fw-bold m-0 text-dark"><?= number_format($data['donations']) ?></h3>
                    </div>
                    <div class="kpi-icon" style="background-color: rgba(25, 135, 84, 0.2); color: #198754; padding: 12px; border-radius: 50%;">
                        <i data-lucide="gift" style="width: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold m-0">Activité Récente</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <tbody>
                            <?php if(empty($data['activities'])): ?>
                                <tr><td class="text-center py-4 text-muted">Aucune activité récente.</td></tr>
                            <?php else: ?>
                                <?php foreach($data['activities'] as $act): ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                                    <span class="fw-bold text-primary small"><?= strtoupper(substr($act['prenom'], 0, 1)) ?></span>
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark"><span class="fw-bold"><?= e($act['prenom']) ?> <?= e($act['nom']) ?></span> a <?= e($act['action']) ?></div>
                                                    <div class="text-muted small"><?= time_ago($act['date_action']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 text-center border-top">
                <a href="<?= BASE_URL ?>/admin/activity" class="btn btn-link text-primary text-decoration-none fw-bold small">Voir tout le journal <i data-lucide="arrow-right" class="ms-1" style="width: 14px;"></i></a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); background-color: var(--surface);">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <i data-lucide="zap" style="width: 64px; height: 64px; color: var(--primary);" class="mb-4 mx-auto"></i>
                <h5 class="fw-bold mb-4 text-primary">Actions Rapides</h5>
                <div class="d-grid gap-3 mt-auto">
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-light shadow-sm rounded-pill fw-bold text-start ps-4 py-2"><i data-lucide="users" class="me-2 text-primary" style="width: 18px;"></i> Gérer les Utilisateurs</a>
                    <a href="<?= BASE_URL ?>/admin/businesses" class="btn btn-light shadow-sm rounded-pill fw-bold text-start ps-4 py-2"><i data-lucide="store" class="me-2 text-primary" style="width: 18px;"></i> Approuver Entreprises</a>
                    <a href="<?= BASE_URL ?>/admin/donations" class="btn btn-light shadow-sm rounded-pill fw-bold text-start ps-4 py-2"><i data-lucide="gift" class="me-2 text-primary" style="width: 18px;"></i> Voir les Dons</a>
                    <a href="<?= BASE_URL ?>/admin/activity" class="btn btn-primary shadow-sm rounded-pill fw-bold text-start ps-4 py-2"><i data-lucide="activity" class="me-2" style="width: 18px;"></i> Journal d'Activité</a>
                </div>
            </div>
        </div>
    </div>
</div>
