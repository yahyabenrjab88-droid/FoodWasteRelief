<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold m-0">Platform Statistics</h1>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i data-lucide="users"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?= array_sum($kpis['roles']) ?></span>
                    <span class="kpi-label">Total Users</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i data-lucide="store"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?= $kpis['business_active'] ?> / <?= $kpis['business_total'] ?></span>
                    <span class="kpi-label">Active Businesses</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i data-lucide="shopping-bag"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?= $kpis['orders_total'] ?></span>
                    <span class="kpi-label">Total Orders</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="color: var(--accent); background: rgba(244,162,97,0.1);"><i data-lucide="globe"></i></div>
                <div class="kpi-body">
                    <span class="kpi-value"><?= number_format((float)$kpis['co2'], 1) ?> kg</span>
                    <span class="kpi-label">CO₂ Avoided</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Monthly Evolution (6 Months)</h5>
                    <canvas id="evolutionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Order Status Distribution</h5>
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= BASE_URL ?>/statistics/data')
        .then(response => response.json())
        .then(data => {
            
            new Chart(document.getElementById('evolutionChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.evolution.labels,
                    datasets: [
                        {
                            label: 'Food Sold (kg)',
                            data: data.evolution.sold,
                            backgroundColor: '#2D6A4F'
                        },
                        {
                            label: 'Food Donated (kg)',
                            data: data.evolution.donated,
                            backgroundColor: '#F4A261'
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            new Chart(document.getElementById('statusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Delivered', 'Cancelled'],
                    datasets: [{
                        data: [
                            data.status.pending, 
                            data.status.confirmed, 
                            data.status.delivered, 
                            data.status.cancelled
                        ],
                        backgroundColor: ['#ffc107', '#17a2b8', '#198754', '#dc3545']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
});
</script>
