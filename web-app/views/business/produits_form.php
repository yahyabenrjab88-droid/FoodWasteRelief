<div class="card">
  <form method="post" action="index.php?page=business&action=save">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="id" value="<?= (int)($produit['id'] ?? 0) ?>">

    <label>Nom</label><input name="nom" required value="<?= htmlspecialchars($produit['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <label>Description</label><textarea name="description"><?= htmlspecialchars($produit['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    <label>Categorie</label><input name="categorie" value="<?= htmlspecialchars($produit['categorie'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <label>Prix (DT)</label><input type="number" step="0.01" name="prix" required value="<?= htmlspecialchars((string)($produit['prix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <label>Quantite</label><input type="number" name="quantite" required value="<?= htmlspecialchars((string)($produit['quantite'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <label>Date expiration</label><input type="datetime-local" name="date_expiration" required value="<?= !empty($produit['date_expiration']) ? date('Y-m-d\TH:i', strtotime((string)$produit['date_expiration'])) : '' ?>">
    <label>Type</label>
    <select name="type"><option value="vente" <?= (($produit['type'] ?? '') === 'vente') ? 'selected' : '' ?>>Vente</option><option value="don" <?= (($produit['type'] ?? '') === 'don') ? 'selected' : '' ?>>Don</option></select>
    <label>Stock lie (optionnel)</label>
    <select name="storage_item_id"><option value="0">Aucun</option>
      <?php foreach (($storageItems ?? []) as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= ((int)($produit['storage_item_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['item_name'], ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    </select>

    <button class="btn-primary" type="submit">Enregistrer</button>
  </form>
</div>

