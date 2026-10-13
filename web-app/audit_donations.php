<?php
require_once 'config/Database.php';
$db = Database::getConnection();
echo "=== DONATIONS SUMMARY ===\n";
$rows = $db->query("SELECT association_id, status, COUNT(*) as c FROM donations GROUP BY association_id, status")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

echo "\n=== ASSOCIATIONS ===\n";
$assocs = $db->query("SELECT id, nom FROM associations")->fetchAll(PDO::FETCH_ASSOC);
print_r($assocs);
