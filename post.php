<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch post (Try requested language first)
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$post = $stmt->fetch();

// If not found, try English version to auto-translate
if (!$post && $lang !== 'en') {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND lang_code = 'en'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if ($post) {
        $post['title'] = Translator::translate($post['title'], $lang);
        $post['content'] = Translator::translate($post['content'], $lang);
        $post['lang_code'] = $lang;
    }
}

if (!$post) {
    header("Location: blog.php?lang=$lang");
    exit;
}

$settings = getSiteSettings($pdo);

// Helper function for auto-translation
if (!function_exists('__')) {
    function __($text, $lang)
    {
        return Translator::translate($text, $lang);
    }
}

$t = [
    'back' => __('Back to Insights', $lang),
];

$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

$seoHelper = new SEO_Helper($pdo, 'post', $lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $post['lang_code']; ?>">

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
            background-color: #ffffff;
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

        .article-hero {
            padding: 140px 0 80px;
            background: #f8fafc;
        }

        .article-content h2 {
            font-size: 2.25rem;
            font-weight: 900;
            margin-top: 4rem;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
            color: #1e293b;
        }

        .article-content p {
            margin-bottom: 2rem;
            font-size: 1.125rem;
            color: #475569;
        }

        .article-content img {
            border-radius: 2rem;
            margin: 3rem 0;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
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

        .back-link {
            color: #64748b;
            font-weight: 700;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #7c3aed;
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
                    <div
                        class="absolute right-0 mt-2 w-32 bg-white shadow-xl rounded-xl p-2 hidden group-hover:block border border-gray-100">
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
    </nav>

    <article class="flex-1">
        <header class="article-hero">
            <div class="max-w-4xl mx-auto px-6">
                <a href="blog.php?lang=<?php echo $lang; ?>" class="back-link">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <?php echo $t['back']; ?>
                </a>

                <div
                    class="flex items-center gap-4 mb-6 text-xs font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-4 py-2 rounded-xl w-fit">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                </div>

                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-[1.1] mb-12">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>

                <?php if ($post['thumbnail']): ?>
                    <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>"
                        class="w-full h-auto rounded-[3rem] shadow-2xl"
                        alt="<?php echo htmlspecialchars($post['title']); ?>">
                <?php endif; ?>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-6 py-20">
            <div class="article-content prose max-w-none">
                <?php echo $post['content']; ?>
            </div>
        </div>
    </article>

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