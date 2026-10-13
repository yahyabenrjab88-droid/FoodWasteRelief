<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="heart" class="me-2"></i>Platform Donations</h3>
</div>

<div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4">Donation ID</th>
                        <th class="py-3">Business</th>
                        <th class="py-3">NGO</th>
                        <th class="py-3">Product</th>
                        <th class="py-3">Quantity</th>
                        <th class="py-3">Date</th>
                        <th class="py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($donations)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No donations found.</td></tr>
                    <?php else: ?>
                        <?php foreach($donations as $d): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $d['id'] ?></td>
                                <td><i data-lucide="store" class="text-muted me-1" style="width:14px;"></i><?= e($d['nom_business']) ?></td>
                                <td><i data-lucide="heart-handshake" class="text-muted me-1" style="width:14px;"></i><?= e($d['association_nom']) ?></td>
                                <td><?= e($d['produit_nom']) ?></td>
                                <td class="fw-bold text-primary"><?= $d['quantite'] ?> kg</td>
                                <td class="text-muted small"><?= date('M d, Y H:i', strtotime($d['date_donation'])) ?></td>
                                <td>
                                    <?php
                                    $col = $d['status'] === 'requested' ? 'bg-warning text-dark' : ($d['status'] === 'completed' ? 'bg-success' : 'bg-danger');
                                    ?>
                                    <span class="badge <?= $col ?> px-2 py-1 text-uppercase" style="font-size: 0.7rem;"><?= e($d['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
