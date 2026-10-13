<div class="card"><h3>Gestion des produits</h3><p class="card-sub">Ajoutez, modifiez et supprimez les offres de vente ou don.</p>
  <a class="btn-primary" href="index.php?page=business&action=businessForm">Ajouter produit</a>
</div>
<div class="card table-responsive">
  <table>
    <thead><tr><th>Nom</th><th>Type</th><th>Prix</th><th>Quantite</th><th>Expiration</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach (($produits ?? []) as $p): ?>
      <?php $expSoon = strtotime((string)$p['date_expiration']) <= strtotime('+3 days'); ?>
      <tr>
        <td><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($p['type'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format((float)$p['prix'], 2, ',', ' ') ?> DT</td>
        <td><span class="badge <?= (int)$p['quantite'] < 5 ? 'badge-expiring' : 'badge-delivered' ?>"><?= (int)$p['quantite'] ?></span></td>
        <td><span class="badge <?= $expSoon ? 'badge-expired' : 'badge-confirmed' ?>"><?= date('d/m/Y', strtotime((string)$p['date_expiration'])) ?></span></td>
        <td>
          <a class="btn-success" href="index.php?page=business&action=businessForm&id=<?= (int)$p['id'] ?>">Modifier</a>
          <form method="post" action="index.php?page=business&action=delete" style="display:inline-block">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn-danger" type="submit">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

