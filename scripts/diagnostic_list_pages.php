<?php
/**
 * Pages Diagnostic Utility
 * Lists all pages with their metadata in JSON format
 */

require_once '../includes/db.php';

$pages = $pdo->query("
    SELECT 
        id, 
        title, 
        slug, 
        lang_code, 
        show_in_header, 
        show_in_footer,
        translation_group,
        created_at
    FROM pages 
    ORDER BY lang_code, title
")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
