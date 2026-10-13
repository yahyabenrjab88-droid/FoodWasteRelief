<section class="grid grid-4">
  <article class="card kpi"><i class="fa-solid fa-hand-holding-heart"></i><div><div class="val"><?= (int)($stats['dons_total'] ?? 0) ?></div><div class="lbl">Dons total</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-truck"></i><div><div class="val"><?= (int)($stats['dons_collectes'] ?? 0) ?></div><div class="lbl">Collectes</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-weight-scale"></i><div><div class="val"><?= number_format((float)($stats['kg_collectes'] ?? 0),1,',',' ') ?></div><div class="lbl">Kg collectes</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-people-group"></i><div><div class="val"><?= (int)($stats['partenaires'] ?? 0) ?></div><div class="lbl">Partenaires</div></div></article>
</section>

<section class="grid grid-2" style="margin-top:16px">
  <article class="card"><h3>Actions rapides</h3><div style="display:flex;gap:10px;flex-wrap:wrap"><a class="btn btn-primary" href="index.php?page=association&action=dons">Reserver un don</a><a class="btn btn-secondary" href="index.php?page=association&action=reservations">Mes reservations</a></div></article>
  <article class="card"><h3>Impact social</h3><div class="impact-ticker"><span>🥗 <?= number_format((float)($stats['kg_collectes'] ?? 0),1,',',' ') ?> kg collectes — <?= (int)($stats['repas_estimes'] ?? 0) ?> repas estimes</span></div></article>
</section>

<section class="card" style="margin-top:16px"><h3>Dernieres reservations</h3><div class="table-wrap"><table><thead><tr><th>Produit</th><th>Commerce</th><th>Qt</th><th>Statut</th></tr></thead><tbody><?php foreach(($reservations ?? []) as $r): ?><tr><td><?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($r['nom_business'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$r['quantite'] ?></td><td><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div></section>
