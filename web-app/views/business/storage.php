<div class="card">
  <h3>Ajouter / modifier une ligne de stock</h3>
  <form method="post" action="index.php?page=storage&action=save">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="id" value="0">
    <label>Nom item</label><input name="item_name" required>
    <label>Quantite</label><input type="number" step="0.01" name="quantity" required>
    <label>Unite</label><input name="unit" value="kg" required>
    <label>Expiration</label><input type="date" name="expiry_date" required>
    <button class="btn-primary" type="submit">Ajouter</button>
  </form>
</div>

<div class="card table-responsive">
  <table>
    <thead><tr><th>Item</th><th>Quantite</th><th>Unite</th><th>Expiration</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach (($items ?? []) as $i): ?>
      <tr>
        <td><?= htmlspecialchars($i['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format((float)$i['quantity'], 2, ',', ' ') ?></td>
        <td><?= htmlspecialchars($i['unit'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= date('d/m/Y', strtotime((string)$i['expiry_date'])) ?></td>
        <td>
          <form method="post" action="index.php?page=storage&action=delete">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
            <button class="btn-danger" type="submit">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

