<?php
require_once '../includes/db.php';

// Set all current pages to show in footer by default
$pdo->exec("UPDATE pages SET show_in_footer = 1");

// Specific ordering/naming for essential pages if they exist
$essential = [
    'about-us' => ['order' => 1, 'header' => 0],
    'privacy-policy' => ['order' => 2, 'header' => 0],
    'terms-of-service' => ['order' => 3, 'header' => 0],
    'contact-us' => ['order' => 4, 'header' => 0],
    'faq' => ['order' => 5, 'header' => 1], // Example: FAQ in header
];

foreach ($essential as $slug => $meta) {
    $stmt = $pdo->prepare("UPDATE pages SET menu_order = ?, show_in_header = ? WHERE slug = ?");
    $stmt->execute([$meta['order'], $meta['header'], $slug]);
}

echo "Navigation defaults seeded! All pages are now in the footer, and FAQ is marked for Header (if it exists).";
unlink(__FILE__);
