<?php
require_once '../includes/db.php';
$pages = $pdo->query("SELECT id, title, slug, lang_code, show_in_header FROM pages")->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($pages, JSON_PRETTY_PRINT);
