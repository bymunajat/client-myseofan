<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch page
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$page = $stmt->fetch();

// Try without lang filter if not found
if (!$page) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

if (!$page) {
    header("Location: index.php?lang=$lang");
    exit;
}

$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// 3. Fallback Translations (Expanded for 6 Languages)
$defaults = [
    'en' => [
        'home' => 'Home',
        'blog' => 'Blog'
    ],
    'id' => [
        'home' => 'Beranda',
        'blog' => 'Blog'
    ],
    'es' => [
        'home' => 'Inicio',
        'blog' => 'Blog'
    ],
    'fr' => [
        'home' => 'Accueil',
        'blog' => 'Blog'
    ],
    'de' => [
        'home' => 'Startseite',
        'blog' => 'Blog'
    ],
    'ja' => [
        'home' => 'ホーム',
        'blog' => 'ブログ'
    ]
];

// Merge with defaults (EN as primary fallback)
$t = array_merge($defaults['en'], $defaults[$lang] ?? [], $translations);

// Fetch dynamic navigation links
$headerLinks = $pdo->prepare("SELECT title, slug FROM pages WHERE lang_code = ? AND show_in_header = 1 ORDER BY menu_order ASC");
$headerLinks->execute([$lang]);
$headerLinks = $headerLinks->fetchAll();

$footerLinks = $pdo->prepare("SELECT title, slug FROM pages WHERE lang_code = ? AND show_in_footer = 1 ORDER BY menu_order ASC");
$footerLinks->execute([$lang]);
$footerLinks = $footerLinks->fetchAll();

$seoHelper = new SEO_Helper($pdo, 'page', $lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $page['lang_code']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seoHelper->getTitle(); ?></title>
    <meta name="description" content="<?php echo $seoHelper->getDescription(); ?>">
    <?php echo $seoHelper->getOGTags(); ?>
    <?php echo $seoHelper->getHreflangTags(); ?>
    <?php echo $seoHelper->getSchemaMarkup(); ?>
    <?php if (!empty($settings['favicon_path'])): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($settings['favicon_path']); ?>">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --accent: #3b82f6;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f9fafb;
            color: #1f2937;
            line-height: 1.8;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .page-card {
            background: white;
            border-radius: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.03);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.03);
        }

        .prose h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            color: #111827;
        }

        .prose p {
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
            color: #4b5563;
        }

        .hero-title {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
    <?php echo $settings['header_code'] ?? ''; ?>
</head>

<body class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-50 glass-header">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="flex items-center gap-3 group">
                <div class="flex items-center">
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto"
                            alt="<?php echo htmlspecialchars($settings['site_name']); ?>">
                    <?php else: ?>
                        <div
                            class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2.5"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <span
                            class="ml-3 text-xl font-black tracking-tighter text-gray-800"><?php echo htmlspecialchars($settings['site_name']); ?></span>
                    <?php endif; ?>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 font-semibold text-gray-500">
                <a href="index.php?lang=<?php echo $lang; ?>"
                    class="hover:text-emerald-600 transition-colors py-1"><?php echo $t['home']; ?></a>
                <a href="blog.php?lang=<?php echo $lang; ?>"
                    class="hover:text-emerald-600 transition-colors py-1"><?php echo $t['blog']; ?></a>

                <?php foreach ($headerLinks as $hl): ?>
                    <a href="page.php?slug=<?php echo htmlspecialchars($hl['slug']); ?>&lang=<?php echo $lang; ?>"
                        class="hover:text-emerald-600 transition-colors py-1"><?php echo htmlspecialchars($hl['title']); ?></a>
                <?php endforeach; ?>

                <!-- Language Switcher -->
                <div class="relative group">
                    <select onchange="location.href = this.value"
                        class="appearance-none bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl outline-none focus:border-emerald-500 transition-all cursor-pointer shadow-sm">
                        <?php
                        $langs = ['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'];
                        foreach ($langs as $code => $label):
                            $targetUrl = "page.php?slug=$slug&lang=$code";
                            ?>
                            <option value="<?php echo $targetUrl; ?>" <?php echo $lang === $code ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto px-6 py-20">
        <div class="page-card p-10 md:p-20">
            <header class="mb-16 border-b border-gray-100 pb-12">
                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-tight mb-4">
                    <?php echo htmlspecialchars($page['title']); ?>
                </h1>
                <p class="text-gray-400 font-medium"><?php echo htmlspecialchars($settings['site_name']); ?> Legal &
                    Info</p>
            </header>

            <div class="prose max-w-none text-gray-600">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-auto pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-24">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-gray-900"><svg
                                class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg></div>
                        <h4 class="text-3xl font-black hero-title">MySeoFan</h4>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-md">
                        <?php echo htmlspecialchars($t['footer_desc'] ?? 'The ultimate tool for Instagram media preservation.'); ?>
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Navigation</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="index.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Downloader</a>
                        </li>
                        <li><a href="blog.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Blog & News</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Legal</h4>
                    <ul class="space-y-4 text-gray-400">
                        <?php foreach ($footerLinks as $fl): ?>
                            <li><a href="page.php?slug=<?php echo htmlspecialchars($fl['slug']); ?>&lang=<?php echo $lang; ?>"
                                    class="hover:text-emerald-400"><?php echo htmlspecialchars($fl['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-500 font-medium text-xs">
                &copy; 2026 MySeoFan Studio. All rights reserved.
            </div>
        </div>
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>