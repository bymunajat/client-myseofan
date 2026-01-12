<?php
/**
 * Database Connection using SQLite
 * Auto-initializes if database is missing
 */
require_once __DIR__ . '/../config.php';

$pdo = null;

// Ensure database directory exists
$dbFolder = dirname(DB_PATH);
if (!is_dir($dbFolder)) {
    mkdir($dbFolder, 0777, true);
}

try {
    // Standard SQLite DSN
    $dsn = "sqlite:" . DB_PATH;

    // SQLite numeric attributes for safety
    $options = [
        3 => 2,     // ATTR_ERR_MODE => ERR_MODE_EXCEPTION
        19 => 2,     // ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC
    ];

    $pdo = new \PDO($dsn, null, null, $options);

    // AUTO-INITIALIZATION: Create tables if site_settings doesn't exist
    $res = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='site_settings'");
    if (!$res->fetch()) {
        $pdo->exec("CREATE TABLE site_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, site_name TEXT, logo_path TEXT, favicon_path TEXT, header_code TEXT, footer_code TEXT)");
        $pdo->exec("CREATE TABLE seo_data (id INTEGER PRIMARY KEY AUTOINCREMENT, page_identifier TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT, og_image TEXT, schema_markup TEXT)");
        $pdo->exec("CREATE TABLE admins (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password_hash TEXT, role TEXT DEFAULT 'super_admin', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE translations (id INTEGER PRIMARY KEY AUTOINCREMENT, lang_code TEXT, t_key TEXT, t_value TEXT, UNIQUE(lang_code, t_key))");
        $pdo->exec("CREATE TABLE blog_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, thumbnail TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
        $pdo->exec("CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT)");

        $pdo->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')");
        $pdo->exec("INSERT INTO seo_data (page_identifier, lang_code, meta_title, meta_description) VALUES ('home', 'id', 'Pengunduh Media Instagram - MySeoFan', 'Alat online gratis terbaik untuk mengunduh media Instagram.')");

        // Seed Static Pages
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Privacy Policy', 'privacy-policy', '<h1>Privacy Policy</h1><p>Your privacy is important to us...</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Terms of Use', 'terms-of-use', '<h1>Terms of Use</h1><p>By using this service, you agree to...</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Support Center', 'support', '<h1>Support</h1><p>Contact us at support@myseofan.com</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('About Us', 'about-us', '<h1>About Us</h1><p>MySeoFan is a premium tool for Instagram enthusiasts.</p>', 'en')");

        // Specialized Pages from PRD
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Video Downloader', 'video-downloader', '<h1>Instagram Video Downloader</h1><p>Save Instagram videos directly to your device.</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Reels Downloader', 'reels-downloader', '<h1>Instagram Reels Downloader</h1><p>Download your favorite Reels in high quality.</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Story Downloader', 'story-downloader', '<h1>Instagram Story Downloader</h1><p>Watch and download stories anonymously.</p>', 'en')");
        $pdo->exec("INSERT INTO pages (title, slug, content, lang_code) VALUES ('Image Downloader', 'image-downloader', '<h1>Instagram Image Downloader</h1><p>Safe and fast Instagram photo saving tool.</p>', 'en')");

        // Seed additional languages
        $langs = ['es', 'fr', 'de', 'ja'];
        foreach ($langs as $l) {
            $pdo->exec("INSERT INTO seo_data (page_identifier, lang_code, meta_title, meta_description) VALUES ('home', '$l', 'Instagram Downloader', 'Free Instagram media downloader.')");
        }

        // SEED SITE SETTINGS (id=1)
        $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'MySeoFan')");
    }

    // Emergency Seed for site_settings (if table exists but row 1 is missing)
    try {
        $checkSettings = $pdo->query("SELECT COUNT(*) FROM site_settings WHERE id = 1")->fetchColumn();
        if ($checkSettings == 0) {
            $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'MySeoFan')");
        }
    } catch (\Exception $e) {
    }

    // Migration Check: Add lang_code to seo_data if it doesn't exist
    $cols = $pdo->query("PRAGMA table_info(seo_data)")->fetchAll();
    $hasLang = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'lang_code')
            $hasLang = true;
    }
    if (!$hasLang)
        $pdo->exec("ALTER TABLE seo_data ADD COLUMN lang_code TEXT DEFAULT 'en'");

    // Migration Check: Create translations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS translations (id INTEGER PRIMARY KEY AUTOINCREMENT, lang_code TEXT, t_key TEXT, t_value TEXT, UNIQUE(lang_code, t_key))");

    // Migration Check: Create blog_posts and pages tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, thumbnail TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT)");

    // Migration Check: Add category to blog_posts
    $cols = $pdo->query("PRAGMA table_info(blog_posts)")->fetchAll();
    $hasCat = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'category')
            $hasCat = true;
    }
    if (!$hasCat)
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN category TEXT DEFAULT 'General'");

    // Migration Check: Add translation_group to blog_posts
    $colsPost = $pdo->query("PRAGMA table_info(blog_posts)")->fetchAll();
    $hasGroupPost = false;
    foreach ($colsPost as $col) {
        if ($col['name'] == 'translation_group')
            $hasGroupPost = true;
    }
    if (!$hasGroupPost)
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN translation_group TEXT");

    // Migration Check: Pages table migrations
    $colsPage = $pdo->query("PRAGMA table_info(pages)")->fetchAll();
    $hasGroupPage = false;
    $hasHeader = false;
    $hasFooter = false;
    $hasOrder = false;
    $hasSection = false;
    foreach ($colsPage as $col) {
        if ($col['name'] == 'translation_group')
            $hasGroupPage = true;
        if ($col['name'] == 'show_in_header')
            $hasHeader = true;
        if ($col['name'] == 'show_in_footer')
            $hasFooter = true;
        if ($col['name'] == 'menu_order')
            $hasOrder = true;
        if ($col['name'] == 'footer_section')
            $hasSection = true;
    }
    if (!$hasGroupPage)
        $pdo->exec("ALTER TABLE pages ADD COLUMN translation_group TEXT");
    if (!$hasHeader)
        $pdo->exec("ALTER TABLE pages ADD COLUMN show_in_header INTEGER DEFAULT 0");
    if (!$hasFooter)
        $pdo->exec("ALTER TABLE pages ADD COLUMN show_in_footer INTEGER DEFAULT 0");
    if (!$hasOrder)
        $pdo->exec("ALTER TABLE pages ADD COLUMN menu_order INTEGER DEFAULT 0");
    if (!$hasSection)
        $pdo->exec("ALTER TABLE pages ADD COLUMN footer_section TEXT DEFAULT 'legal'");

    // Migration Check: Add role to admins
    $colsAdmin = $pdo->query("PRAGMA table_info(admins)")->fetchAll();
    $hasRole = false;
    foreach ($colsAdmin as $col) {
        if ($col['name'] == 'role')
            $hasRole = true;
    }
    if (!$hasRole)
        $pdo->exec("ALTER TABLE admins ADD COLUMN role TEXT DEFAULT 'super_admin'");

    // Migration Check: Add created_at to admins
    $colsAdmin = $pdo->query("PRAGMA table_info(admins)")->fetchAll();
    $hasCreatedAt = false;
    foreach ($colsAdmin as $col) {
        if ($col['name'] == 'created_at')
            $hasCreatedAt = true;
    }
    if (!$hasCreatedAt)
        $pdo->exec("ALTER TABLE admins ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");

    // Emergency Seed
    try {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($adminCount == 0 || $adminCount === false) {
            $pdo->exec("INSERT INTO admins (username, password_hash, role) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin')");
        }
    } catch (\Exception $e) {
    }

    // Auto-heal session Role
    if (isset($_SESSION['admin_id']) && (!isset($_SESSION['role']) || empty($_SESSION['role']))) {
        try {
            $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $fetchedRole = $stmt->fetchColumn();
            $_SESSION['role'] = $fetchedRole ?: 'super_admin';
            if (!$fetchedRole && $_SESSION['admin_id'] == 1)
                $_SESSION['role'] = 'super_admin';
        } catch (\Exception $e) {
        }
    }
} catch (\PDOException $e) {
    error_log("DB Migration Error: " . $e->getMessage());
}

function getSiteSettings($pdo)
{
    if (!$pdo)
        return [];
    try {
        $data = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();
        return $data ?: [
            'site_name' => 'MySeoFan',
            'logo_path' => '',
            'favicon_path' => '',
            'header_code' => '',
            'footer_code' => ''
        ];
    } catch (\Exception $e) {
        return [
            'site_name' => 'MySeoFan',
            'logo_path' => '',
            'favicon_path' => '',
            'header_code' => '',
            'footer_code' => ''
        ];
    }
}

function getSEOData($pdo, $page, $lang = 'en')
{
    if (!$pdo)
        return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM seo_data WHERE page_identifier = ? AND lang_code = ?");
        $stmt->execute([$page, $lang]);
        return $stmt->fetch() ?: [];
    } catch (\Exception $e) {
        return [];
    }
}

function getTranslations($pdo, $lang = 'en')
{
    if (!$pdo)
        return [];
    try {
        $stmt = $pdo->prepare("SELECT t_key, t_value FROM translations WHERE lang_code = ?");
        $stmt->execute([$lang]);
        $rows = $stmt->fetchAll();
        $trans = [];
        foreach ($rows as $row) {
            $trans[$row['t_key']] = $row['t_value'];
        }
        return $trans;
    } catch (\Exception $e) {
        return [];
    }
}
