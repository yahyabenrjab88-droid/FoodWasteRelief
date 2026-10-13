<section class="grid grid-4">
  <article class="card kpi"><i class="fa-solid fa-box"></i><div><div class="val"><?= (int)($stats['produits_actifs'] ?? 0) ?></div><div class="lbl">Actifs</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-triangle-exclamation"></i><div><div class="val"><?= (int)($stats['produits_expires'] ?? 0) ?></div><div class="lbl">Expires</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-cart-flatbed"></i><div><div class="val"><?= (int)($stats['commandes_livrees'] ?? 0) ?></div><div class="lbl">Livrees</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-coins"></i><div><div class="val"><?= number_format((float)($stats['revenu_total'] ?? 0),0,',',' ') ?></div><div class="lbl">Revenu DT</div></div></article>
</section>
<section class="card" style="margin-top:16px"><canvas id="bizChart"></canvas></section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('bizChart'),{type:'bar',data:{labels:['Produits actifs','Expires','Cmd recues','Cmd livrees','Dons collectes'],datasets:[{label:'Valeurs',data:[<?= (int)($stats['produits_actifs'] ?? 0) ?>,<?= (int)($stats['produits_expires'] ?? 0) ?>,<?= (int)($stats['commandes_total'] ?? 0) ?>,<?= (int)($stats['commandes_livrees'] ?? 0) ?>,<?= (int)($stats['dons_collectes'] ?? 0) ?>],backgroundColor:['#1b4332','#e65100','#2d6a4f','#52b788','#84d4ad']} ]}});
</script>
