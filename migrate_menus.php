<?php
require_once 'includes/db.php';

try {
    // 1. Create menu_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        menu_location TEXT CHECK(menu_location IN ('header', 'footer')) NOT NULL,
        lang_code TEXT NOT NULL DEFAULT 'en',
        type TEXT CHECK(type IN ('page', 'custom_link', 'label')) NOT NULL DEFAULT 'page',
        label TEXT,
        url TEXT,
        related_id INTEGER,
        parent_id INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        FOREIGN KEY (related_id) REFERENCES pages(id) ON DELETE SET NULL
    )");

    // Check if migration is needed (count items)
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "Migrating existing pages to new menu system...\n";

        // Migrate Header Pages
        $stmt = $pdo->query("SELECT * FROM pages WHERE show_in_header = 1");
        $headerPages = $stmt->fetchAll();
        foreach ($headerPages as $p) {
            $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, related_id, sort_order) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute(['header', $p['lang_code'], 'page', $p['title'], $p['id'], $p['menu_order']]);
        }
        echo "Migrated " . count($headerPages) . " header items.\n";

        // Migrate Footer Pages (Grouped)
        // Group by lang_code and footer_section
        $stmt = $pdo->query("SELECT * FROM pages WHERE show_in_footer = 1");
        $footerPages = $stmt->fetchAll();

        // Mapping of old section keys to readable labels
        $sectionLabels = [
            'legal' => ['en' => 'Legal', 'id' => 'Legal'],
            'company' => ['en' => 'Company', 'id' => 'Perusahaan'],
            'resources' => ['en' => 'Resources', 'id' => 'Sumber Daya'],
            'tools' => ['en' => 'Tools', 'id' => 'Alat']
        ];

        // First we create parent "Labels" for sections if they don't exist in that language
        $createdSections = []; // key: lang_section -> id

        foreach ($footerPages as $p) {
            $lang = $p['lang_code'];
            $section = $p['footer_section'] ?: 'legal';
            $key = $lang . '_' . $section;

            // Generate Parent Label if new
            if (!isset($createdSections[$key])) {
                $label = $sectionLabels[$section][$lang] ?? ucfirst($section);
                // Insert parent
                $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, sort_order) VALUES (?, ?, ?, ?, ?)")
                    ->execute(['footer', $lang, 'label', $label, 0]); // Order 0 for now
                $createdSections[$key] = $pdo->lastInsertId();
            }

            // Insert Page Item under Parent
            $parentId = $createdSections[$key];
            $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, related_id, parent_id, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute(['footer', $lang, 'page', $p['title'], $p['id'], $parentId, $p['menu_order']]);
        }
        echo "Migrated " . count($footerPages) . " footer items.\n";

    } else {
        echo "Menu items already exist. Skipping migration.\n";
    }

    // Add Indexes for performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_menu_loc_lang ON menu_items(menu_location, lang_code)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_menu_parent ON menu_items(parent_id)");

    echo "Database upgrade complete.";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
