<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

// Helper function for auto-translation
if (!function_exists('__')) {
    function __($text, $lang)
    {
        return Translator::translate($text, $lang);
    }
}

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);

// 3. Translations Logic
$t = [
    'home' => __('Home', $lang),
    'blog' => __('Blog', $lang),
    'blog_title' => __('Instagram <span class="text-purple-600">Insights</span>', $lang),
    'blog_subtitle' => __('Expert tips, tricks, and updates on Instagram media preservation and creative archiving.', $lang),
    'read_more' => __('Read Story', $lang),
    'coming_soon' => __('New articles are being prepared. Stay tuned!', $lang),
    'back' => __('Back to Home', $lang)
];

// Fetch dynamic navigation links
$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

$seoHelper = new SEO_Helper($pdo, 'blog', $lang);

// Fetch posts for current language (Published only)
// 4. Fetch Posts Logic (Smart Fallback)
// Step A: Fetch native posts for the current language
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE lang_code = ? AND status = 'published' ORDER BY created_at DESC");
$stmt->execute([$lang]);
$nativePosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$posts = $nativePosts;

// Step B: If not English, fetch English posts to fill gaps (Partial Translation Support)
if ($lang !== 'en') {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE lang_code = 'en' AND status = 'published' ORDER BY created_at DESC");
    $stmt->execute();
    $englishPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Collect translation groups present in native posts
    $existingGroups = [];
    foreach ($nativePosts as $np) {
        if (!empty($np['translation_group'])) {
            $existingGroups[] = $np['translation_group'];
        }
    }

    // Merge missing English posts
    foreach ($englishPosts as $enPost) {
        // If this post's group is NOT in the native list, add it as a fallback
        if (empty($enPost['translation_group']) || !in_array($enPost['translation_group'], $existingGroups)) {
            // Translate Metadata On-the-Fly
            $enPost['title'] = Translator::translate($enPost['title'], $lang);
            $enPost['excerpt'] = Translator::translate($enPost['excerpt'], $lang);
            // Mark as fallback (optional, for UI styling if needed)
            $enPost['is_fallback'] = true;

            $posts[] = $enPost;
        }
    }

    // Step C: Re-sort merged list by Date
    usort($posts, function ($a, $b) {
        return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    });
}

$pageIdentifier = 'blog';
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1a1a1a;
            line-height: 1.8;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo-text {
            color: #7c3aed;
            font-weight: 800;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .blog-card {
            background: white;
            border-radius: 2rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .footer-logo-text {
            font-size: 2.25rem;
            font-weight: 800;
            color: #3b82f6;
        }

        .footer-logo-icon {
            color: #a855f7;
            width: 36px;
            height: 36px;
        }

        .footer-links-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 12px;
        }

        .footer-link {
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: color 0.2s;
            text-decoration: none;
        }

        .footer-link:hover {
            color: #3b82f6;
        }

        .footer-divider {
            width: 100%;
            height: 1px;
            background: #e2e8f0;
            margin: 40px 0;
        }

        .copyright-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.6875rem;
            font-weight: 600;
        }
    </style>
    <?php echo $settings['header_code'] ?? ''; ?>
</head>

<body class="flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 py-4 glass-header">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="logo-text">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-8 w-auto" alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="w-8 h-8 text-purple-600"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>
            </a>

            <div class="flex items-center gap-6 text-slate-700 font-bold text-sm uppercase tracking-wider">
                <nav class="hidden md:flex items-center gap-6">
                    <?php foreach ($headerItems as $item): ?>
                        <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                            class="hover:text-purple-600 transition-colors">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="relative group cursor-pointer">
                    <div class="flex items-center gap-1 hover:text-purple-600 transition-colors uppercase">
                        <?php echo $lang; ?> <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                    <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-50">
                        <div class="w-32 bg-white shadow-xl rounded-xl p-2 border border-gray-100">
                            <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $code => $label): ?>
                                <a href="?lang=<?php echo $code; ?>"
                                    class="block px-4 py-2 text-xs hover:bg-purple-50 rounded-lg <?php echo $lang === $code ? 'text-purple-600 font-bold' : ''; ?>">
                                    <?php echo $label; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl mx-auto px-6 py-32 w-full">
        <header class="mb-20 text-center animate-fade-up">
            <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-8"><?php echo $t['blog_title']; ?></h1>
            <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed">
                <?php echo $t['blog_subtitle']; ?>
            </p>
        </header>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($posts as $p): ?>
                <article class="blog-card group flex flex-col h-full animate-fade-up">
                    <div class="aspect-[16/10] bg-slate-100 relative overflow-hidden">
                        <?php if ($p['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($p['thumbnail']); ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                alt="<?php echo htmlspecialchars($p['title']); ?>">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-slate-50 text-purple-200">
                                <i data-lucide="image" class="w-16 h-16"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-8 flex flex-col flex-1">
                        <div
                            class="flex items-center gap-4 mb-4 text-[10px] font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-3 py-1.5 rounded-lg w-fit">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                        </div>
                        <h2 class="text-2xl font-black mb-6 group-hover:text-purple-600 transition-colors leading-tight">
                            <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>">
                                <?php echo htmlspecialchars($p['title']); ?>
                            </a>
                        </h2>
                        <div class="mt-auto pt-6 border-t border-slate-50">
                            <a href="post.php?slug=<?php echo $p['slug']; ?>&lang=<?php echo $lang; ?>"
                                class="text-sm font-black text-slate-900 flex items-center gap-2 group/btn">
                                <span><?php echo $t['read_more']; ?></span>
                                <i data-lucide="arrow-right"
                                    class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (empty($posts)): ?>
            <div class="text-center py-32 bg-white rounded-[3rem] border-2 border-dashed border-slate-100 animate-fade-up">
                <div
                    class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i data-lucide="newspaper" class="w-10 h-10"></i>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">
                    <?php echo $t['coming_soon']; ?>
                </p>
                <a href="index.php?lang=<?php echo $lang; ?>"
                    class="mt-8 inline-block px-8 py-3 bg-purple-600 text-white rounded-2xl font-bold text-sm hover:bg-purple-700 transition-all shadow-lg shadow-purple-200"><?php echo $t['back']; ?></a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="py-16 bg-white border-t border-slate-100 mt-auto">
        <div class="max-w-4xl mx-auto px-6">
            <div class="footer-brand">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto" alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="footer-logo-icon"></i>
                <?php endif; ?>
                <span
                    class="footer-logo-text"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></span>
            </div>

            <?php foreach ($footerItems as $group): ?>
                <div class="footer-links-group">
                    <?php if (isset($group['children']) && !empty($group['children'])): ?>
                        <?php foreach ($group['children'] as $index => $item): ?>
                            <a href="<?php echo htmlspecialchars($item['final_url']); ?>" class="footer-link">
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                            <?php if ($index < count($group['children']) - 1): ?>
                                <span class="text-slate-200 px-1">|</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-400 uppercase tracking-widest">follow us:</span>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="youtube" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="twitter" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="music-2" class="w-5 h-5"></i></a>
                    </div>
                </div>
                <p class="copyright-text">© 2020-2026
                    <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>