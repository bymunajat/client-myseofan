<?php
require_once 'includes/db.php';

try {
    // 1. Add 'status' column
    $cols = $pdo->query("PRAGMA table_info(blog_posts)")->fetchAll();
    $hasStatus = false;
    $hasTags = false;

    foreach ($cols as $col) {
        if ($col['name'] === 'status')
            $hasStatus = true;
        if ($col['name'] === 'tags')
            $hasTags = true;
    }

    if (!$hasStatus) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN status TEXT DEFAULT 'published'");
        echo "Column 'status' added.<br>";
    } else {
        echo "Column 'status' already exists.<br>";
    }

    if (!$hasTags) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN tags TEXT DEFAULT ''");
        echo "Column 'tags' added.<br>";
    } else {
        echo "Column 'tags' already exists.<br>";
    }

    echo "Migration completed successfully!";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage();
}
