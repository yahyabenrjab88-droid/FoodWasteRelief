<section class="hero">
  <div class="hero-panel">
    <h1>Contre le Gaspillage Alimentaire en Tunisie</h1>
    <p>Une plateforme collaborative entre commerces, clients et associations pour transformer les invendus en impact concret.</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a class="btn btn-primary" href="index.php?page=public&action=catalogue"><i class="fa-solid fa-store"></i> Voir les produits</a>
      <a class="btn btn-secondary" href="index.php?page=auth&action=login"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
      <a class="btn btn-outline" href="index.php?page=auth&action=register"><i class="fa-solid fa-user-plus"></i> Inscription</a>
    </div>
  </div>
  <div class="hero-visual"></div>
</section>

<section class="grid grid-3" style="margin-top:16px">
  <article class="card kpi"><i class="fa-solid fa-scale-balanced"></i><div><div class="val" data-counter="<?= max(1,count($produits ?? [])*120) ?>">0</div><div class="lbl">Kg sauves</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-utensils"></i><div><div class="val" data-counter="<?= max(1,count($produits ?? [])*240) ?>">0</div><div class="lbl">Repas estimes</div></div></article>
  <article class="card kpi"><i class="fa-solid fa-shop"></i><div><div class="val" data-counter="<?= max(1,count($produits ?? [])) ?>">0</div><div class="lbl">Commerces partenaires</div></div></article>
</section>

<section class="card" style="margin-top:16px">
  <h3>Derniers produits disponibles</h3>
  <div class="products">
    <?php foreach(($produits ?? []) as $p): $expSoon = strtotime((string)$p['date_expiration']) <= strtotime('+3 days'); ?>
      <article class="product-card">
        <div class="product-media"></div>
        <div class="product-body">
          <h4><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
          <p class="card-sub"><?= htmlspecialchars($p['nom_business'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($p['ville'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><strong><?= number_format((float)$p['prix'],2,',',' ') ?> DT</strong></p>
          <span class="badge <?= $expSoon ? 'badge-warning' : 'badge-sale' ?>">Expire: <?= date('d/m/Y', strtotime((string)$p['date_expiration'])) ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
