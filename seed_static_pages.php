<?php
require_once 'includes/db.php';

// Define the pages to seed
$pages_to_seed = [
    [
        'title' => 'About FastDL',
        'slug' => 'about',
        'content' => '<h2>About MySeoFan (FastDL)</h2><p>We provide the fastest Instagram downloader service...</p>',
        'lang_code' => 'en'
    ],
    [
        'title' => 'Contact Us',
        'slug' => 'contact',
        'content' => '<h2>Contact Us</h2><p>Unless you have a specific inquiry, please use the form below...</p>',
        'lang_code' => 'en'
    ],
    [
        'title' => 'Privacy Policy',
        'slug' => 'privacy-policy',
        'content' => '<h2>Privacy Policy</h2><p>Your privacy is important to us...</p>',
        'lang_code' => 'en'
    ],
    [
        'title' => 'Terms & Conditions',
        'slug' => 'terms-conditions',
        'content' => '<h2>Terms of Service</h2><p>By using this website, you agree to...</p>',
        'lang_code' => 'en'
    ]
];

echo "Checking static pages...\n";

foreach ($pages_to_seed as $p) {
    // Check if exists (by slug)
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND lang_code = ?");
    $stmt->execute([$p['slug'], $p['lang_code']]);

    if (!$stmt->fetch()) {
        // Insert
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group, show_in_header, show_in_footer, menu_order, footer_section) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, 0, 'company')");

            $grp = uniqid('page_grp_');
            $stmt->execute([
                $p['title'],
                $p['slug'],
                $p['content'],
                $p['lang_code'],
                $p['title'],
                $p['title'] . ' - MySeoFan',
                $grp
            ]);
            echo "[CREATED] " . $p['title'] . "\n";
        } catch (Exception $e) {
            echo "[ERROR] " . $p['title'] . ": " . $e->getMessage() . "\n";
        }
    } else {
        echo "[EXISTS] " . $p['title'] . "\n";
    }
}
?>