<?php
$role = $_SESSION['user']['role'] ?? '';

if ($role === 'admin') {
    require __DIR__ . '/admin.php';
} elseif ($role === 'business') {
    require __DIR__ . '/business.php';
} elseif ($role === 'association') {
    require __DIR__ . '/association.php';
} elseif ($role === 'client') {
?>
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i data-lucide="shopping-bag"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?= (int)($commandesCount ?? 0) ?></span>
                    <span class="kpi-label">Total Orders</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-icon" style="color: var(--primary-light); background: var(--primary-pale);"><i data-lucide="check-circle"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value">Active</span>
                    <span class="kpi-label">Account Status</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">Recent Orders</h5>
                        <a href="<?= BASE_URL ?>/orders" class="btn btn-sm btn-outline-primary">View All Orders</a>
                    </div>
                    <?php if (empty($recentCommandes)): ?>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentCommandes as $c): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#<?= htmlspecialchars($c['id']) ?></strong> - <?= htmlspecialchars($c['nom_business']) ?>
                                    </div>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars(ucfirst($c['status'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Quick Actions</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= BASE_URL ?>/products" class="btn btn-primary text-start"><i data-lucide="search" class="me-2" style="width:16px;"></i> Browse Products</a>
                        <a href="<?= BASE_URL ?>/cart" class="btn btn-outline-primary text-start"><i data-lucide="shopping-cart" class="me-2" style="width:16px;"></i> View Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    echo "<p>Role not recognized.</p>";
}
?>
