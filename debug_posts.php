<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT id, title, lang_code FROM blog_posts ORDER BY id DESC LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "[{$r['lang_code']}] {$r['title']}\n";
}
?>