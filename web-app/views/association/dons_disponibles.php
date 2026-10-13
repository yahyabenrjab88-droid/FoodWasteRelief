<div class="card table-responsive">
  <table>
    <thead><tr><th>Produit</th><th>Commerce</th><th>Ville</th><th>Quantite</th><th>Expiration</th><th>Reserver</th></tr></thead>
    <tbody>
    <?php foreach (($dons ?? []) as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($d['nom_business'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($d['ville'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= (int)$d['quantite'] ?></td>
        <td><?= date('d/m/Y', strtotime((string)$d['date_expiration'])) ?></td>
        <td>
          <form method="post" action="index.php?page=association&action=reserve">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="produit_id" value="<?= (int)$d['id'] ?>">
            <input type="number" name="quantite" min="1" max="<?= (int)$d['quantite'] ?>" required>
            <button class="btn-primary" type="submit">Reserver</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

