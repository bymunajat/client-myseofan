<?php
require_once __DIR__ . '/../includes/db.php';

// 1. Define the Standard Tool List
$tools = [
    ['label' => 'Video', 'url' => 'video-downloader'],
    ['label' => 'Photo', 'url' => 'photo-downloader'],
    ['label' => 'Reels', 'url' => 'reels-downloader'],
    ['label' => 'IGTV', 'url' => 'igtv-downloader'],
    ['label' => 'Carousel', 'url' => 'carousel-downloader']
];

// Helper to reset and seed
$pdo->exec("DELETE FROM menu_items WHERE lang_code = 'en'"); // Clear all English menus to be safe/clean

// 2. Update Header Menu
// Insert Home + Blog + Tools
$headerItems = array_merge(
    [
        ['label' => 'Home', 'url' => 'index.php'],
        ['label' => 'Blog', 'url' => 'blog']
    ],
    $tools
);

foreach ($headerItems as $i => $item) {
    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, sort_order) VALUES ('header', 'en', 'custom_link', ?, ?, ?)");
    $stmt->execute([$item['label'], $item['url'], $i]);
}

// 3. Update Footer Menu with Columns
// We need columns: Tools, Links, External (maybe?)
// MySeoFan footer usually has groups.
// Let's create groups as 'label' type parents.

// Group 1: Tools
$stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, sort_order) VALUES ('footer', 'en', 'label', 'Tools', 0)");
$stmt->execute();
$toolsGroupId = $pdo->lastInsertId();

foreach ($tools as $i => $item) {
    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, sort_order, parent_id) VALUES ('footer', 'en', 'custom_link', ?, ?, ?, ?)");
    $stmt->execute([$item['label'], $item['url'], $i, $toolsGroupId]);
}

// Group 2: Pages
$stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, sort_order) VALUES ('footer', 'en', 'label', 'Pages', 1)");
$stmt->execute();
$pagesGroupId = $pdo->lastInsertId();

$footerLinks = [
    ['label' => 'Blog', 'slug' => 'blog'],
    ['label' => 'About MySeoFan', 'slug' => 'about'],
    ['label' => 'Contact', 'slug' => 'contact'],
    ['label' => 'Privacy Policy', 'slug' => 'privacy-policy'],
    ['label' => 'Terms & Conditions', 'slug' => 'terms']
];

// Ensure pages exist
$pages = [
    ['title' => 'Blog', 'slug' => 'blog', 'content' => '[blog_index]'],
    ['title' => 'About MySeoFan', 'slug' => 'about', 'content' => '<h1>About MySeoFan</h1><p>MySeoFan is the best tool for Instagram media downloading.</p>'],
    ['title' => 'Contact', 'slug' => 'contact', 'content' => '<h1>Contact Us</h1><p>Email: support@myseofan.link</p>'],
    ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us.</p>'],
    ['title' => 'Terms & Conditions', 'slug' => 'terms', 'content' => '<h1>Terms & Conditions</h1><p>By using this site, you agree to our terms.</p>']
];

foreach ($pages as $p) {
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
    $stmt->execute([$p['slug']]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code) VALUES (?, ?, ?, 'en')");
        $stmt->execute([$p['title'], $p['slug'], $p['content']]);
    }
}

foreach ($footerLinks as $i => $l) {
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
    $stmt->execute([$l['slug']]);
    $pageId = $stmt->fetchColumn();

    if ($pageId) {
        $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, related_id, sort_order, parent_id) VALUES ('footer', 'en', 'page', ?, ?, ?, ?)");
        $stmt->execute([$l['label'], $pageId, $i, $pagesGroupId]);
    }
}

// Group 3: External (if needed) - Keeping it to match prior logic
$stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, sort_order) VALUES ('footer', 'en', 'label', 'External', 2)");
$stmt->execute();
$externalGroupId = $pdo->lastInsertId();

$external = [
    ['label' => '123Tik', 'url' => 'https://123tik.com']
];
foreach ($external as $i => $e) {
    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, sort_order, parent_id) VALUES ('footer', 'en', 'custom_link', ?, ?, ?, ?)");
    $stmt->execute([$e['label'], $e['url'], $i, $externalGroupId]);
}

echo "Menus updated successfully with hierarchy.\n";
?>