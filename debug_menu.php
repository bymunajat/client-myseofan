<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT lang_code, COUNT(*) as count FROM menu_items GROUP BY lang_code");
$res = $stmt->fetchAll();
echo "Counts per language:\n";
print_r($res);

$stmt2 = $pdo->query("SELECT * FROM menu_items ORDER BY lang_code, menu_location, sort_order");
$res2 = $stmt2->fetchAll();
echo "\nAll items:\n";
foreach ($res2 as $row) {
    echo "ID: {$row['id']} | Lang: {$row['lang_code']} | Loc: {$row['menu_location']} | Label: {$row['label']} | Parent: {$row['parent_id']}\n";
}
