<section class="grid grid-3">
  <article class="card kpi"><i class="fa-solid fa-basket-shopping"></i><div><div class="val"><?= (int)($commandesCount ?? 0) ?></div><div class="lbl">Mes commandes</div></div></article>
  <article class="card"><h3>Catalogue</h3><p class="card-sub">Decouvrez les offres disponibles pres de chez vous.</p><a class="btn btn-primary" href="index.php?page=public&action=catalogue">Ouvrir</a></article>
  <article class="card"><h3>Suivi commandes</h3><p class="card-sub">Consultez vos statuts en temps reel.</p><a class="btn btn-secondary" href="index.php?page=client&action=mesCommandes">Voir</a></article>
</section>

<section class="card" style="margin-top:16px">
  <h3>Dernieres commandes</h3>
  <div class="table-wrap"><table><thead><tr><th>ID</th><th>Commerce</th><th>Total</th><th>Date</th><th>Statut</th></tr></thead><tbody>
  <?php foreach(($recentCommandes ?? []) as $c): ?>
    <tr><td>#<?= (int)$c['id'] ?></td><td><?= htmlspecialchars($c['nom_business'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format((float)$c['total'],2,',',' ') ?> DT</td><td><?= date('d/m/Y H:i', strtotime((string)$c['date_commande'])) ?></td><td><?= htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8') ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
