<div class="card table-responsive">
  <table>
    <thead><tr><th>#</th><th>Client</th><th>Total</th><th>Date</th><th>Statut</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach (($commandes ?? []) as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= htmlspecialchars(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format((float)$c['total'], 2, ',', ' ') ?> DT</td>
        <td><?= date('d/m/Y H:i', strtotime((string)$c['date_commande'])) ?></td>
        <td><?= htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>
          <form method="post" action="index.php?page=orders&action=updateStatus">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <select name="status"><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select>
            <button class="btn-success" type="submit">Mettre a jour</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

