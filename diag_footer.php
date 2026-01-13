<?php
require_once 'includes/db.php';

echo "--- PAGES ---\n";
$stmt = $pdo->query("SELECT id, title, slug, lang_code FROM pages");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Slug: {$row['slug']} | Lang: {$row['lang_code']}\n";
}

echo "\n--- MENU ITEMS ---\n";
$stmt = $pdo->query("SELECT id, menu_location, label, type, lang_code FROM menu_items");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Location: {$row['menu_location']} | Label: {$row['label']} | Type: {$row['type']} | Lang: {$row['lang_code']}\n";
}
