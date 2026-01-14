<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
foreach ($tables as $t) {
    if (strpos($t, 'log') !== false) {
        echo "- $t (LOG)\n";
    } else {
        echo "- $t\n";
    }
}
?>