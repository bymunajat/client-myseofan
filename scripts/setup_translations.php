<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS translations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hash VARCHAR(32) NOT NULL,
        source_text TEXT NOT NULL,
        target_lang VARCHAR(10) NOT NULL,
        translated_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_translation (hash)
    )";

    $pdo->exec($sql);
    echo "Table 'translations' created or already exists successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>