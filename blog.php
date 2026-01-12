<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// 3. Fallback Translations (Expanded for 6 Languages)
$defaults = [
    'en' => [
        'home' => 'Home',
        'blog' => 'Blog',
        'blog_title' => 'Instagram <span class="text-emerald-600">Insights</span>',
        'blog_subtitle' => 'Expert tips, tricks, and updates on Instagram media preservation and creative archiving.',
        'read_more' => 'Read Story',
        'coming_soon' => 'New articles are being prepared. Stay tuned!',
        'back' => 'Back to Home'
    ],
    'id' => [
        'home' => 'Beranda',
        'blog' => 'Blog',
        'blog_title' => 'Wawasan <span class="text-emerald-600">Instagram</span>',
        'blog_subtitle' => 'Tips ahli, trik, dan pembaruan tentang pengarsipan media Instagram dan kreativitas.',
        'read_more' => 'Baca Artikel',
        'coming_soon' => 'Artikel baru sedang disiapkan. Tunggu saja!',
        'back' => 'Kembali ke Beranda'
    ],
    'es' => [
        'home' => 'Inicio',
        'blog' => 'Blog',
        'blog_title' => 'Ideas de <span class="text-emerald-600">Instagram</span>',
        'blog_subtitle' => 'Consejos de expertos, trucos y actualizaciones sobre la preservación de medios de Instagram.',
        'read_more' => 'Leer Más',
        'coming_soon' => 'Nuevos artículos próximamente.',
        'back' => 'Volver'
    ],
    'fr' => [
        'home' => 'Accueil',
        'blog' => 'Blog',
        'blog_title' => 'Infos <span class="text-emerald-600">Instagram</span>',
        'blog_subtitle' => 'Conseils d\'experts, astuces et mises à jour sur la préservation des médias Instagram.',
        'read_more' => 'Lire la Suite',
        'coming_soon' => 'De nouveaux articles arrivent bientôt.',
        'back' => 'Retour'
    ],
    'de' => [
        'home' => 'Startseite',
        'blog' => 'Blog',
        'blog_title' => 'Instagram <span class="text-emerald-600">Insights</span>',
        'blog_subtitle' => 'Expertentipps, Tricks und Updates zur Archivierung von Instagram-Medien.',
        'read_more' => 'Weiterlesen',
        'coming_soon' => 'Neue Artikel sind in Vorbereitung.',
        'back' => 'Zurück'
    ],
    'ja' => [
        'home' => 'ホーム',
        'blog' => 'ブログ',
        'blog_title' => 'Instagram <span class="text-emerald-600">インサイト</span>',
        'blog_subtitle' => 'Instagramメディアの保存とクリエイティブなアーカイブに関するエキスパートのヒントと更新情報。',
        'read_more' => '詳しく読む',
        'coming_soon' => '新しい記事を準備中です。',
        'back' => '戻る'
    ]
];

// Merge with defaults (EN as primary fallback)
$t = array_merge($defaults['en'], $defaults[$lang] ?? [], $translations);

// Fetch dynamic navigation links
$headerLinks = $pdo->prepare("SELECT title, slug FROM pages WHERE lang_code = ? AND show_in_header = 1 ORDER BY menu_order ASC");
$headerLinks->execute([$lang]);
$headerLinks = $headerLinks->fetchAll();

$rawFooterLinks = $pdo->prepare("SELECT title, slug, footer_section FROM pages WHERE lang_code = ? AND show_in_footer = 1 ORDER BY menu_order ASC");
$rawFooterLinks->execute([$lang]);
$rawFooterLinks = $rawFooterLinks->fetchAll();

$footerGroups = [];
foreach ($rawFooterLinks as $fl) {
    $section = $fl['footer_section'] ?: 'legal';
    $footerGroups[$section][] = $fl;
}

$seoHelper = new SEO_Helper($pdo, 'blog', $lang);

// Fetch posts for current language
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE lang_code = ? ORDER BY created_at DESC");
$stmt->execute([$lang]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seoHelper->getTitle(); ?></title>
    <meta name="description" content="<?php echo $seoHelper->getDescription(); ?>">
    <?php echo $seoHelper->getOGTags(); ?>
    <?php echo $seoHelper->getHreflangTags(); ?>
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
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .blog-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .blog-card:hover {
            transform: translateY(-8px);
            shadow-2xl shadow-emerald-900/10;
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
                            class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200 group-hover:rotate-12 transition-transform">
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
                    class="text-emerald-600 border-b-2 border-emerald-600 py-1"><?php echo $t['blog']; ?></a>

                <?php foreach ($headerLinks as $hl): ?>
                    <a href="page.php?slug=<?php echo htmlspecialchars($hl['slug']); ?>&lang=<?php echo $lang; ?>"
                        class="hover:text-emerald-600 transition-colors py-1"><?php echo htmlspecialchars($hl['title']); ?></a>
                <?php endforeach; ?>

                <!-- Language Switcher -->
                <div class="relative group">
                    <select onchange="location.href = this.value"
                        class="appearance-none bg-white border border-gray-100 text-gray-700 font-bold py-2 px-4 rounded-xl outline-none focus:border-emerald-500 transition-all cursor-pointer shadow-sm">
                        <?php
                        $langs = ['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'];
                        foreach ($langs as $code => $label):
                            $targetUrl = "blog.php?lang=$code";
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

    <main class="flex-1 max-w-7xl mx-auto px-6 py-20">
        <header class="mb-20 text-center">
            <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-8"><?php echo $t['blog_title']; ?></h1>
            <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed">
                <?php echo $t['blog_subtitle']; ?>
            </p>
        </header>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($posts as $p): ?>
                <article
                    class="blog-card bg-white rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm flex flex-col h-full group">
                    <div class="aspect-[16/10] bg-gray-100 relative overflow-hidden">
                        <?php if ($p['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($p['thumbnail']); ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-50 text-emerald-100">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-8 flex flex-col flex-1">
                        <div
                            class="flex items-center gap-4 mb-4 text-[10px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-50 px-3 py-1.5 rounded-lg w-fit">
                            <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                        </div>
                        <h2 class="text-2xl font-black mb-6 group-hover:text-emerald-600 transition-colors leading-tight">
                            <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>">
                                <?php echo htmlspecialchars($p['title']); ?>
                            </a>
                        </h2>
                        <div class="mt-auto pt-6 border-t border-gray-50">
                            <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>"
                                class="text-sm font-black text-gray-900 flex items-center gap-2 group/btn">
                                <span><?php echo $t['read_more']; ?></span>
                                <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (empty($posts)): ?>
            <div class="text-center py-32 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h3" />
                    </svg>
                </div>
                <p class="text-gray-400 font-bold uppercase tracking-widest text-sm">
                    <?php echo $t['coming_soon']; ?>
                </p>
                <a href="index.php?lang=<?php echo $lang; ?>"
                    class="mt-8 inline-block px-8 py-3 bg-gray-900 text-white rounded-2xl font-bold text-sm hover:bg-gray-800 transition-all"><?php echo $t['back']; ?></a>
            </div>
        <?php endif; ?>
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
                    <h4 class="text-white font-bold mb-6">Downloader</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="index.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Video
                                Downloader</a></li>
                        <li><a href="index.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Reels
                                Downloader</a></li>
                        <li><a href="index.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Story
                                Downloader</a></li>
                        <li><a href="blog.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Blog & News</a>
                        </li>
                    </ul>
                </div>

                <?php foreach ($footerGroups as $section => $links): ?>
                    <div>
                        <h4 class="text-white font-bold mb-6">
                            <?php echo $t['footer_section_' . $section] ?? ucfirst($section); ?>
                        </h4>
                        <ul class="space-y-4 text-gray-400">
                            <?php foreach ($links as $fl): ?>
                                <li><a href="page.php?slug=<?php echo htmlspecialchars($fl['slug']); ?>&lang=<?php echo $lang; ?>"
                                        class="hover:text-emerald-400"><?php echo htmlspecialchars($fl['title']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-500 font-medium text-xs">
                &copy; 2026 MySeoFan Studio. All rights reserved.
            </div>
        </div>
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>