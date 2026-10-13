<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="bar-chart-2" class="me-2"></i>Statistiques & Rapports</h3>
</div>

<div class="row g-4 mb-4" id="stats-container">
    <!-- Chart 1 -->
    <div class="col-lg-6" id="chart1_container">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold m-0" id="chart1_title">Activité dans le temps</h5>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <canvas id="chart1" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Chart 2 -->
    <div class="col-lg-6" id="chart2_container">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius);">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold m-0" id="chart2_title">Répartition</h5>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <canvas id="chart2" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div id="assoc_kpis" class="row g-4 mb-4" style="display: none;">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: var(--radius); border-left: 5px solid var(--primary);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-bold text-uppercase small mb-2" style="letter-spacing: 1px;">Total Reçu (KG)</h6>
                    <h2 class="display-5 fw-bold m-0 text-primary" id="assoc_total_kg">0</h2>
                </div>
                <div class="rounded-circle bg-primary-pale d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i data-lucide="award" class="text-primary" style="width: 30px; height: 30px;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--radius);">
            <h6 class="fw-bold mb-3">Top 5 Donateurs</h6>
            <ul class="list-group list-group-flush" id="assoc_top_donors">
                <!-- Injected via JS -->
            </ul>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('<?= BASE_URL ?>/statistics/data')
        .then(response => response.json())
        .then(data => {
            const role = '<?= $_SESSION['user']['role'] ?? '' ?>';

            const primary = '#2D6A4F';
            const accent = '#F4A261';
            const lightGreen = '#95D5B2';
            const danger = '#dc3545';
            
            if(role === 'admin') {
                document.getElementById('chart1_title').innerText = 'Commandes Récentes (7 jours)';
                const ctx1 = document.getElementById('chart1').getContext('2d');
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: data.ordersByDay.map(d => d.date),
                        datasets: [{
                            label: 'Commandes',
                            data: data.ordersByDay.map(d => d.count),
                            borderColor: primary,
                            backgroundColor: lightGreen,
                            tension: 0.3,
                            fill: true
                        }]
                    }
                });
                
                document.getElementById('chart2_title').innerText = 'Utilisateurs par Rôle';
                const ctx2 = document.getElementById('chart2').getContext('2d');
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: data.usersByRole.map(d => d.role.charAt(0).toUpperCase() + d.role.slice(1)),
                        datasets: [{
                            data: data.usersByRole.map(d => d.count),
                            backgroundColor: [primary, accent, danger, '#0d6efd']
                        }]
                    }
                });
            } else if (role === 'business') {
                document.getElementById('chart1_title').innerText = 'Commandes vs Dons (7 jours)';
                const ctx1 = document.getElementById('chart1').getContext('2d');
                
                const dates1 = data.ordersByDay.map(d => d.date);
                const dates2 = data.donationsByDay.map(d => d.date);
                const allDates = [...new Set([...dates1, ...dates2])].sort();
                
                const oData = allDates.map(d => {
                    const f = data.ordersByDay.find(x => x.date === d); return f ? f.count : 0;
                });
                const dData = allDates.map(d => {
                    const f = data.donationsByDay.find(x => x.date === d); return f ? f.qty : 0;
                });
                
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: allDates,
                        datasets: [
                            { label: 'Commandes', data: oData, backgroundColor: primary },
                            { label: 'Dons (kg)', data: dData, backgroundColor: accent }
                        ]
                    }
                });
                
                document.getElementById('chart2_container').style.display = 'none';
                document.getElementById('chart1_container').classList.remove('col-lg-6');
                document.getElementById('chart1_container').classList.add('col-12');
                
            } else if (role === 'association') {
                document.getElementById('chart1_title').innerText = 'Dons Reçus par Mois';
                const ctx1 = document.getElementById('chart1').getContext('2d');
                
                const monthNames = ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Déc"];
                
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: data.donationsPerMonth.map(d => monthNames[d.mois - 1] + ' ' + d.annee),
                        datasets: [{
                            label: 'Dons',
                            data: data.donationsPerMonth.map(d => d.total),
                            backgroundColor: lightGreen
                        }]
                    }
                });
                
                document.getElementById('chart2_title').innerText = 'Dons par Catégorie';
                const ctx2 = document.getElementById('chart2').getContext('2d');
                new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: data.donationsByCategory.map(d => d.categorie || 'Autre'),
                        datasets: [{
                            data: data.donationsByCategory.map(d => d.total),
                            backgroundColor: [primary, accent, danger, '#0d6efd', '#6f42c1', '#17a2b8']
                        }]
                    }
                });
                
                document.getElementById('assoc_kpis').style.display = 'flex';
                document.getElementById('assoc_total_kg').innerText = data.totalKgReceived || '0';
                
                const donorsList = document.getElementById('assoc_top_donors');
                if (data.topDonors.length === 0) {
                    donorsList.innerHTML = '<li class="list-group-item text-muted text-center py-3">Aucune donnée disponible.</li>';
                } else {
                    data.topDonors.forEach(d => {
                        donorsList.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-medium">${d.nom_business}</span>
                            <span class="badge bg-primary rounded-pill">${d.kg} kg</span>
                        </li>`;
                    });
                }
                
            } else if (role === 'client') {
                document.getElementById('chart1_title').innerText = 'Évolution de vos dépenses (7 derniers jours)';
                const ctx1 = document.getElementById('chart1').getContext('2d');
                
                // Create a beautiful gradient
                const gradient = ctx1.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(45, 106, 79, 0.4)');
                gradient.addColorStop(1, 'rgba(45, 106, 79, 0)');

                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: data.spentByDay.map(d => {
                            const date = new Date(d.date);
                            return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' });
                        }),
                        datasets: [{
                            label: 'Dépenses (TND)',
                            data: data.spentByDay.map(d => d.amount),
                            borderColor: '#2D6A4F',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4, // Smoother curves
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#2D6A4F',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1a1a1a',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 },
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y.toFixed(2) + ' TND';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    callback: function(value) { return value + ' TND'; }
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
                document.getElementById('chart2_container').style.display = 'none';
                document.getElementById('chart1_container').classList.remove('col-lg-6');
                document.getElementById('chart1_container').classList.add('col-12');
            }
        })
        .catch(error => console.error("Error fetching stats:", error));
});
</script>
