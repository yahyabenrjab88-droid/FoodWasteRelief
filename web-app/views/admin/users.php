<section class="card">
  <h3>Gestion des utilisateurs</h3>
  <form method="get"><input type="hidden" name="page" value="users"><input type="hidden" name="action" value="index">
    <div class="filters">
      <div><label>Recherche</label><input name="q" value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div><label>Role</label><select name="role"><option value="">Tous</option><option value="admin">Admin</option><option value="business">Business</option><option value="association">Association</option><option value="client">Client</option></select></div>
      <div><button class="btn btn-primary">Filtrer</button></div>
    </div>
  </form>
</section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Nom</th><th>Email</th><th>Role</th><th>Telephone</th><th>Date</th></tr></thead><tbody><?php foreach(($users ?? []) as $u): ?><tr><td><?= htmlspecialchars(($u['prenom'] ?? '').' '.($u['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)($u['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= date('d/m/Y H:i', strtotime((string)$u['created_at'])) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
