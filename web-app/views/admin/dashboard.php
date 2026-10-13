<section class="grid grid-4">
  <article class="card kpi"><i class="fa-solid fa-users"></i><div><div class="val"><?= (int)($usersCount ?? 0) ?></div><div class="lbl">Utilisateurs</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-shop"></i><div><div class="val"><?= (int)($businessCount ?? 0) ?></div><div class="lbl">Commerces</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-building-ngo"></i><div><div class="val"><?= (int)($associationCount ?? 0) ?></div><div class="lbl">Associations</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-chart-line"></i><div><div class="val" data-counter="12000">0</div><div class="lbl">Impact total</div></div></article>
</section>

<section class="card" style="margin-top:16px">
  <h3>Dernieres activites</h3>
  <div class="table-wrap"><table><thead><tr><th>Date</th><th>Action</th><th>Details</th></tr></thead><tbody>
  <?php foreach(($logs ?? []) as $l): ?>
    <tr><td><?= date('d/m/Y H:i', strtotime((string)$l['created_at'])) ?></td><td><?= htmlspecialchars((string)$l['action'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)($l['details'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
