<section class="card">
  <h3>Catalogue produits</h3>
  <form method="get">
    <input type="hidden" name="page" value="public"><input type="hidden" name="action" value="catalogue">
    <div class="filters">
      <div><label>Nom</label><input name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div><label>Type</label><select name="type"><option value="">Tous</option><option value="vente" <?= (($filters['type'] ?? '')==='vente')?'selected':'' ?>>Vente</option><option value="don" <?= (($filters['type'] ?? '')==='don')?'selected':'' ?>>Don</option></select></div>
      <div><label>Ville</label><input name="ville" value="<?= htmlspecialchars($filters['ville'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div><label>Prix min</label><input type="number" step="0.01" name="min" value="<?= htmlspecialchars($filters['min'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div><label>Prix max</label><input type="number" step="0.01" name="max" value="<?= htmlspecialchars($filters['max'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div><label><input type="checkbox" name="exp" value="1" <?= !empty($filters['exp'])?'checked':'' ?>> Expire bientot</label><br><button class="btn btn-primary">Filtrer</button></div>
    </div>
  </form>
</section>

<section class="products" style="margin-top:16px">
<?php foreach(($produits ?? []) as $p): $expSoon=strtotime((string)$p['date_expiration'])<=strtotime('+3 days'); ?>
  <article class="product-card">
    <div class="product-media"></div>
    <div class="product-body">
      <h4><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
      <p class="card-sub"><?= htmlspecialchars($p['nom_business'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($p['ville'], ENT_QUOTES, 'UTF-8') ?></p>
      <div style="display:flex;justify-content:space-between;align-items:center"><strong><?= number_format((float)$p['prix'],2,',',' ') ?> DT</strong><span class="badge <?= (($p['type']??'')==='don')?'badge-don':'badge-sale' ?>"><?= htmlspecialchars($p['type'], ENT_QUOTES, 'UTF-8') ?></span></div>
      <p class="card-sub">Quantite: <?= (int)$p['quantite'] ?></p>
      <span class="badge <?= $expSoon?'badge-warning':'badge-sale' ?>">Expiration <?= date('d/m/Y', strtotime((string)$p['date_expiration'])) ?></span>
    </div>
  </article>
<?php endforeach; ?>
</section>
