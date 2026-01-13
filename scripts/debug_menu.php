<?php
/**
 * Menu Items Debug Utility
 * Displays menu structure and statistics
 */

require_once '../includes/db.php';

echo "=== MENU ITEMS STATISTICS ===\n\n";

// Language statistics
$stmt = $pdo->query("SELECT lang_code, COUNT(*) as count FROM menu_items GROUP BY lang_code");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 Menu Items per Language:\n";
foreach ($res as $row) {
    echo "  • {$row['lang_code']}: {$row['count']} items\n";
}

// Location statistics
echo "\n📍 Menu Items per Location:\n";
$stmt = $pdo->query("SELECT menu_location, COUNT(*) as count FROM menu_items GROUP BY menu_location");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row) {
    echo "  • {$row['menu_location']}: {$row['count']} items\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "=== ALL MENU ITEMS ===\n\n";

$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY lang_code, menu_location, sort_order");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentLang = '';
$currentLocation = '';

foreach ($res as $row) {
    if ($currentLang !== $row['lang_code']) {
        $currentLang = $row['lang_code'];
        echo "\n🌐 Language: " . strtoupper($currentLang) . "\n";
        echo str_repeat("-", 70) . "\n";
    }

    if ($currentLocation !== $row['menu_location']) {
        $currentLocation = $row['menu_location'];
        echo "\n  📂 Location: {$currentLocation}\n";
    }

    $parent = $row['parent_id'] ? " [Parent: {$row['parent_id']}]" : "";
    $type = $row['type'] ? " [{$row['type']}]" : "";

    echo "    • ID:{$row['id']} | {$row['label']}{$type}{$parent}\n";
}

echo "\n✅ Menu debug completed!\n";
