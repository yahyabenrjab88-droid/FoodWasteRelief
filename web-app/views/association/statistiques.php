<section class="grid grid-4">
  <article class="card kpi"><i class="fa-solid fa-hand-holding-heart"></i><div><div class="val"><?= (int)($stats['dons_total'] ?? 0) ?></div><div class="lbl">Dons</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-truck"></i><div><div class="val"><?= (int)($stats['dons_collectes'] ?? 0) ?></div><div class="lbl">Collectes</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-scale-balanced"></i><div><div class="val"><?= number_format((float)($stats['kg_collectes'] ?? 0),1,',',' ') ?></div><div class="lbl">Kg</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-people-group"></i><div><div class="val"><?= (int)($stats['partenaires'] ?? 0) ?></div><div class="lbl">Partenaires</div></div></article>
</section>
<section class="card" style="margin-top:16px"><canvas id="assocChart"></canvas></section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('assocChart'),{type:'doughnut',data:{labels:['Reserves','Collectes'],datasets:[{data:[<?= (int)($stats['dons_reserves'] ?? 0) ?>,<?= (int)($stats['dons_collectes'] ?? 0) ?>],backgroundColor:['#bfdafe','#52b788']}]}});
</script>
