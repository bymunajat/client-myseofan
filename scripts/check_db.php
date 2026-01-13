<?php
require_once '../includes/db.php';
try {
    $cols = $pdo->query("PRAGMA table_info(admins)")->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in 'admins' table:\n";
    foreach ($cols as $col) {
        echo "- " . $col['name'] . " (" . $col['type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
