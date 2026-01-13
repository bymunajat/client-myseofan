<?php
require_once 'includes/db.php';

// 1. Ensure Pages exist
$pages = [
    ['title' => 'About MySeoFan', 'slug' => 'about', 'content' => '<h1>About MySeoFan</h1><p>MySeoFan is the best tool for Instagram media downloading.</p>'],
    ['title' => 'Contact', 'slug' => 'contact', 'content' => '<h1>Contact Us</h1><p>Email: support@myseofan.link</p>'],
    ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us.</p>'],
    ['title' => 'Terms & Conditions', 'slug' => 'terms', 'content' => '<h1>Terms & Conditions</h1><p>By using this site, you agree to our terms.</p>']
];

foreach ($pages as $p) {
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
    $stmt->execute([$p['slug']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE pages SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$p['title'], $p['content'], $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code) VALUES (?, ?, ?, 'en')");
        $stmt->execute([$p['title'], $p['slug'], $p['content']]);
    }
}

// 2. Clear existing footer menus to rebuild
$pdo->exec("DELETE FROM menu_items WHERE menu_location LIKE 'footer%' AND lang_code = 'en'");

// 3. Seed footer_tools
$tools = [
    ['label' => 'Video', 'url' => 'video.php'],
    ['label' => 'Photo', 'url' => 'index.php'],
    ['label' => 'Reels', 'url' => 'reels.php'],
    ['label' => 'Story', 'url' => 'story.php'],
    ['label' => 'Viewer', 'url' => 'index.php'],
    ['label' => 'Igtv', 'url' => 'highlights.php'],
    ['label' => 'Carousel', 'url' => 'index.php']
];

foreach ($tools as $i => $t) {
    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, sort_order) VALUES ('footer_tools', 'en', 'custom_link', ?, ?, ?)");
    $stmt->execute([$t['label'], $t['url'], $i]);
}

// 4. Seed footer_links
$links = [
    ['label' => 'About MySeoFan', 'slug' => 'about'],
    ['label' => 'Contact', 'slug' => 'contact'],
    ['label' => 'Privacy Policy', 'slug' => 'privacy-policy'],
    ['label' => 'Terms & Conditions', 'slug' => 'terms']
];

foreach ($links as $i => $l) {
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
    $stmt->execute([$l['slug']]);
    $pageId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, related_id, sort_order) VALUES ('footer_links', 'en', 'page', ?, ?, ?)");
    $stmt->execute([$l['label'], $pageId, $i]);
}

// 5. Seed footer_external
$external = [
    ['label' => '123Tik', 'url' => 'https://123tik.com']
];

foreach ($external as $i => $e) {
    $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, sort_order) VALUES ('footer_external', 'en', 'custom_link', ?, ?, ?)");
    $stmt->execute([$e['label'], $e['url'], $i]);
}

echo "Footer pages and menu groups seeded successfully!\n";
unlink(__FILE__);
