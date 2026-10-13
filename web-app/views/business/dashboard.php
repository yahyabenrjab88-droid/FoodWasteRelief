<section class="grid grid-4">
  <article class="card kpi"><i class="fa-solid fa-box"></i><div><div class="val"><?= (int)($stats['produits_actifs'] ?? 0) ?></div><div class="lbl">Produits actifs</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-clock"></i><div><div class="val"><?= (int)($stats['produits_expires'] ?? 0) ?></div><div class="lbl">Produits expires</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-file-invoice"></i><div><div class="val"><?= (int)($stats['commandes_total'] ?? 0) ?></div><div class="lbl">Commandes</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-coins"></i><div><div class="val"><?= number_format((float)($stats['revenu_total'] ?? 0),0,',',' ') ?></div><div class="lbl">Revenu DT</div></div></article>
</section>

<section class="grid grid-2" style="margin-top:16px">
  <article class="card"><h3>Actions rapides</h3><p class="card-sub">Ajout, edition, gestion du stock et suivi des commandes.</p><div style="display:flex;gap:10px;flex-wrap:wrap"><a class="btn btn-primary" href="index.php?page=business&action=produits">Produits</a><a class="btn btn-secondary" href="index.php?page=storage&action=stock">Stock</a><a class="btn btn-outline" href="index.php?page=orders&action=businessList">Commandes</a></div></article>
  <article class="card"><h3>Impact</h3><div class="impact-ticker"><span>🌍 <?= number_format((float)($stats['kg_sauves'] ?? 0),1,',',' ') ?> kg sauves — <?= (int)(($stats['kg_sauves'] ?? 0)*2) ?> repas estimes</span></div></article>
</section>

<section class="card" style="margin-top:16px"><h3>Produits recents</h3><div class="table-wrap"><table><thead><tr><th>Nom</th><th>Type</th><th>Prix</th><th>Qt</th></tr></thead><tbody><?php foreach(($produits ?? []) as $p): ?><tr><td><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($p['type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format((float)$p['prix'],2,',',' ') ?> DT</td><td><?= (int)$p['quantite'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
