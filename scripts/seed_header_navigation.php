<?php
require_once '../includes/db.php';

$langs = ['en', 'id', 'es', 'fr', 'de', 'ja'];
$menuItems = [
    'home' => [
        'en' => 'Home',
        'id' => 'Beranda',
        'es' => 'Inicio',
        'fr' => 'Accueil',
        'de' => 'Startseite',
        'ja' => 'ホーム'
    ],
    'blog' => [
        'en' => 'Blog',
        'id' => 'Blog',
        'es' => 'Blog',
        'fr' => 'Blog',
        'de' => 'Blog',
        'ja' => 'ブログ'
    ]
];

try {
    $pdo->beginTransaction();

    foreach ($langs as $lang) {
        $homeSlug = ($lang === 'en') ? 'home' : "home-$lang";
        $blogSlug = ($lang === 'en') ? 'blog' : "blog-$lang";

        // Seed Home
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND lang_code = ?");
        $stmt->execute([$homeSlug, $lang]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, show_in_header, show_in_footer, menu_order) VALUES (?, ?, '', ?, 1, 0, 0)");
            $stmt->execute([$menuItems['home'][$lang], $homeSlug, $lang]);
        }

        // Seed Blog
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND lang_code = ?");
        $stmt->execute([$blogSlug, $lang]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, show_in_header, show_in_footer, menu_order) VALUES (?, ?, '', ?, 1, 0, 1)");
            $stmt->execute([$menuItems['blog'][$lang], $blogSlug, $lang]);
        }
    }

    // Ensure "About Us" is visible in header for English
    $pdo->exec("UPDATE pages SET show_in_header = 1 WHERE slug = 'about-us' AND lang_code = 'en'");

    $pdo->commit();
    echo "Header navigation seeded successfully!";
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
