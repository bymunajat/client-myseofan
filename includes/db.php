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
        $pdo->exec("CREATE TABLE admins (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password_hash TEXT)");
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

        // Seed additional languages (Keys will be filled via Admin or Translation seeder)
        // Just ensuring SEO data for home exists for them
        $langs = ['es', 'fr', 'de', 'ja'];
        foreach ($langs as $l) {
            $pdo->exec("INSERT INTO seo_data (page_identifier, lang_code, meta_title, meta_description) VALUES ('home', '$l', 'Instagram Downloader', 'Free Instagram media downloader.')");
        }
    }

    // Migration Check: Add lang_code to seo_data if it doesn't exist
    $cols = $pdo->query("PRAGMA table_info(seo_data)")->fetchAll();
    $hasLang = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'lang_code')
            $hasLang = true;
    }
    if (!$hasLang) {
        $pdo->exec("ALTER TABLE seo_data ADD COLUMN lang_code TEXT DEFAULT 'en'");
    }

    // Migration Check: Create translations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS translations (id INTEGER PRIMARY KEY AUTOINCREMENT, lang_code TEXT, t_key TEXT, t_value TEXT, UNIQUE(lang_code, t_key))");

    // Migration Check: Create blog_posts and pages tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, thumbnail TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT UNIQUE, content TEXT, lang_code TEXT DEFAULT 'en', meta_title TEXT, meta_description TEXT)");

} catch (\Exception $e) {
    // Log error if needed
}

/**
 * Helper to get site settings
 */
function getSiteSettings($pdo)
{
    if (!$pdo)
        return [];
    try {
        return $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
    } catch (\Exception $e) {
        return [];
    }
}

/**
 * Helper to get SEO data
 */
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

/**
 * Helper to get all translations for a language
 */
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
