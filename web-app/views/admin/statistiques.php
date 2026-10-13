<div class="kpi-grid">
  <div class="kpi-card"><div class="kpi-value"><?= (int)($kpis['roles']['admin'] ?? 0) ?></div><div>Admins</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= (int)($kpis['roles']['business'] ?? 0) ?></div><div>Commerces</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= (int)($kpis['roles']['association'] ?? 0) ?></div><div>Associations</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= (int)($kpis['roles']['client'] ?? 0) ?></div><div>Clients</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= (int)($kpis['orders_delivered'] ?? 0) ?> / <?= (int)($kpis['orders_total'] ?? 0) ?></div><div>Commandes livrees/total</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= number_format((float)($kpis['kg_total'] ?? 0),1,',',' ') ?></div><div>Kg sauves</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= number_format((float)($kpis['meals'] ?? 0),0,',',' ') ?></div><div>Repas estimes</div></div>
  <div class="kpi-card"><div class="kpi-value"><?= number_format((float)($kpis['co2'] ?? 0),1,',',' ') ?></div><div>CO2 evite (kg)</div></div>
</div>

<div class="impact-ticker"><span>🌍 <?= number_format((float)($kpis['kg_total'] ?? 0),1,',',' ') ?> kg sauves — <?= number_format((float)($kpis['meals'] ?? 0),0,',',' ') ?> repas aides — <?= number_format((float)($kpis['co2'] ?? 0),1,',',' ') ?> kg CO2 evites</span></div>

<div class="card"><canvas id="adminEvolution"></canvas></div>
<div class="card"><canvas id="adminStatus"></canvas></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('adminEvolution'),{
  type:'bar',
  data:{
    labels: <?= json_encode($evolution['labels'] ?? []) ?>,
    datasets:[
      {label:'Kg donnes', data: <?= json_encode($evolution['donated'] ?? []) ?>, backgroundColor:'#1B4332'},
      {label:'Kg vendus', data: <?= json_encode($evolution['sold'] ?? []) ?>, backgroundColor:'#52B788'}
    ]
  }
});
new Chart(document.getElementById('adminStatus'),{
  type:'doughnut',
  data:{
    labels:['pending','confirmed','delivered','cancelled'],
    datasets:[{data:[<?= (int)($status['pending'] ?? 0) ?>,<?= (int)($status['confirmed'] ?? 0) ?>,<?= (int)($status['delivered'] ?? 0) ?>,<?= (int)($status['cancelled'] ?? 0) ?>],backgroundColor:['#FDE68A','#BFDBFE','#BBF7D0','#FCA5A5']}]
  }
});
</script>
