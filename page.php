<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch page (Try requested language first)
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$page = $stmt->fetch();

// If not found, try English version to auto-translate
if (!$page && $lang !== 'en') {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND lang_code = 'en'");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();

    if ($page) {
        $page['title'] = Translator::translate($page['title'], $lang);
        $page['content'] = Translator::translate($page['content'], $lang);
        $page['lang_code'] = $lang;
    }
}

if (!$page) {
    header("Location: index.php?lang=$lang");
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

// Fetch dynamic navigation links
$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

$seoHelper = new SEO_Helper($pdo, 'page', $lang);
$pageIdentifier = 'page';
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
        <link rel="stylesheet" href="assets/css/responsive.css">body {
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

        .page-header {
            background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
            padding: 120px 0 80px;
            text-align: center;
            color: white;
        }

        .page-card {
            background: white;
            border-radius: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            margin-top: -60px;
            padding: 3rem;
            position: relative;
            z-index: 10;
        }

        .prose h2 {
            font-size: 1.875rem;
            font-weight: 800;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
            color: #1e293b;
        }

        .prose p {
            margin-bottom: 1.5rem;
            color: #475569;
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
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-white ml-2 hover:text-[#ec4899] transition-colors">
                <i data-lucide="menu" class="w-8 h-8"></i>
            </button>
        </div>
        </div>
    </nav>

    <header class="page-header">
        <div class="max-w-4xl mx-auto px-6">
            <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-4">
                <?php echo htmlspecialchars($page['title']); ?>
            </h1>
            <p class="text-white/80 font-medium">
                <?php echo htmlspecialchars($settings['site_name']); ?> Legal & Info
            </p>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto px-6 pb-20 w-full">
        <div class="page-card animate-fade-up">
            <div class="prose max-w-none text-slate-600">
                <?php echo $page['content']; ?>
            </div>
        </div>
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

    <div id="mobile-menu"
        class="fixed inset-0 bg-slate-900/98 z-[60] hidden flex-col transition-all duration-300 backdrop-blur-xl">
        <div class="p-6 flex justify-between items-center border-b border-white/10">
            <span
                class="text-xl font-black bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">Menu</span>
            <button id="close-menu-btn" class="text-white/80 hover:text-white transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        <div class="p-6 flex flex-col gap-6 overflow-y-auto">
            <!-- Tools -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-2">Tools</h4>
                <div class="grid grid-cols-2 gap-3">
                    <a href="video.php"
                        class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all">
                        <i data-lucide="video" class="w-5 h-5 text-purple-400"></i> <span
                            class="font-bold text-sm">Video</span>
                    </a>
                    <a href="photo.php"
                        class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all">
                        <i data-lucide="image" class="w-5 h-5 text-pink-400"></i> <span
                            class="font-bold text-sm">Photo</span>
                    </a>
                    <a href="reels.php"
                        class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all">
                        <i data-lucide="clapperboard" class="w-5 h-5 text-fuchsia-400"></i> <span
                            class="font-bold text-sm">Reels</span>
                    </a>
                    <a href="igtv.php"
                        class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all">
                        <i data-lucide="tv" class="w-5 h-5 text-indigo-400"></i> <span
                            class="font-bold text-sm">IGTV</span>
                    </a>
                    <a href="carousel.php"
                        class="col-span-2 flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white transition-all">
                        <i data-lucide="layout" class="w-5 h-5 text-blue-400"></i> <span
                            class="font-bold text-sm">Carousel</span>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest pl-2">Navigation</h4>
                <div class="flex flex-col gap-2">
                    <?php foreach ($headerItems as $item): ?>
                        <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                            class="p-3 text-lg font-bold text-white hover:text-pink-500 transition-colors border-l-2 border-transparent hover:border-pink-500 pl-4">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Language Selector Mobile -->
            <div class="mt-auto pt-6 border-t border-white/10">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Language</h4>
                <div class="grid grid-cols-3 gap-2">
                    <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $code => $label): ?>
                        <a href="?lang=<?php echo $code; ?>"
                            class="text-center p-2 rounded-lg bg-white/5 text-xs font-bold text-white <?php echo $lang === $code ? 'bg-purple-600' : ''; ?>">
                            <?php echo $label; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js?v=1.1"></script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>
