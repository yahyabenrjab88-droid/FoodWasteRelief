<div class="card">
  <form method="post" action="index.php?page=contact&action=send">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <label>Sujet</label><input name="subject" required>
    <label>Message</label><textarea name="body" required></textarea>
    <button class="btn-primary">Envoyer</button>
  </form>
</div>
<div class="card table-responsive"><table><thead><tr><th>Sujet</th><th>Date</th><th>Lu</th></tr></thead><tbody><?php foreach(($messages ?? []) as $m): ?><tr><td><?= htmlspecialchars($m['subject'], ENT_QUOTES, 'UTF-8') ?></td><td><?= date('d/m/Y H:i', strtotime((string)$m['sent_at'])) ?></td><td><?= (int)$m['is_read'] ? 'Oui':'Non' ?></td></tr><?php endforeach; ?></tbody></table></div>

