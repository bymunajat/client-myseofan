<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS translation_cache (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hash TEXT NOT NULL UNIQUE,
        source_text TEXT NOT NULL,
        target_lang TEXT NOT NULL,
        translated_text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'translation_cache' created or already exists successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>