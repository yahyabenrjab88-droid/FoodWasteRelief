<div class="card table-responsive">
  <table>
    <thead><tr><th>Don</th><th>Commerce</th><th>Quantite</th><th>Date</th><th>Statut</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach (($reservations ?? []) as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($r['nom_business'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= (int)$r['quantite'] ?></td>
        <td><?= date('d/m/Y H:i', strtotime((string)$r['date_donation'])) ?></td>
        <td><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>
          <?php if (in_array(strtolower((string)$r['status']), ['reserved','accepted'], true)): ?>
          <form method="post" action="index.php?page=association&action=collect">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn-success" type="submit">Confirmer collecte</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

