<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS redirects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_url VARCHAR(255) NOT NULL UNIQUE,
        target_url VARCHAR(255) NOT NULL,
        redirect_type INT DEFAULT 301,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed a sample if empty
    $count = $pdo->query("SELECT COUNT(*) FROM redirects")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO redirects (source_url, target_url, redirect_type) VALUES ('/old-blog', '/blog.php', 301)");
    }

    echo "Migration Success: redirects table created.";
} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage();
}
