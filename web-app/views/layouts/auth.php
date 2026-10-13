<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Auth', ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
</head>
<body>
  <main class="content" style="max-width:560px;margin:30px auto;">
    <?php if (!empty($flash)): ?>
      <div class="card"><?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?= $content ?>
  </main>
</body>
</html>
