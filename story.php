<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$pageIdentifier = 'story';

// Fetch Editable Content from database if exists
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'story-downloader' AND lang_code = 'en'");
$stmt->execute();
$dbPage = $stmt->fetch();

// Helper function for auto-translation
if (!function_exists('__')) {
    function __($text, $lang)
    {
        return Translator::translate($text, $lang);
    }
}

$t = [
    'title' => $dbPage ? Translator::translate($dbPage['title'], $lang) : __('Instagram Story Downloader', $lang),
    'heading' => __('Instagram Downloader', $lang),
    'subtitle' => __('Download Instagram Videos, Photos, Reels, IGTV & carousel', $lang),
    'placeholder' => __('Paste Instagram Story URL here...', $lang),
    'btn_download' => __('Download', $lang),
    'btn_paste' => __('Paste', $lang),
    'status_fetching' => __('Processing Story...', $lang),
];

$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);
$seoHelper = new SEO_Helper($pdo, $pageIdentifier, $lang);
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

    <?php echo $settings['header_code'] ?? ''; ?>

    <style>
        :root {
            --hero-gradient: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
            scroll-behavior: smooth;
        }

        .hero-section {
            background: var(--hero-gradient);
            position: relative;
            min-height: 550px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 100px;
            padding-bottom: 120px;
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

        .tool-bar {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            margin-bottom: 2.5rem;
        }

        .tool-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tool-item:last-child {
            border-right: none;
        }

        .tool-item:hover,
        .tool-item.active {
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }

        .input-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
        }

        .input-field {
            flex: 1;
            padding: 14px 20px;
            border: none;
            outline: none;
            font-size: 1.1rem;
            color: #334155;
            background: transparent;
        }

        .btn-paste {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-paste:hover {
            background: #e2e8f0;
        }

        .btn-download {
            padding: 12px 28px;
            background: #3b82f6;
            color: #ffffff;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-download:hover {
            background: #2563eb;
        }

        .section-title {
            font-size: clamp(2.5rem, 8vw, 4rem);
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .section-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 3rem;
        }

        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fade-up 0.6s ease forwards;
        }

        /* Article Content Styles */
        .article-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .article-content {
            background: #ffffff;
            padding: 3rem;
            border-radius: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            line-height: 1.8;
            color: #334155;
        }

        .article-content h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        /* Result Area */
        #result .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #7c3aed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="flex flex-col">
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

    <!-- Hero Section -->
    <section id="downloader" class="hero-section">
        <div class="max-w-5xl mx-auto text-center px-6 w-full">
            <!-- Tool Bar -->
            <div class="tool-bar animate-fade-up">
                <a href="video.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'video' ? 'active' : ''; ?>"><i data-lucide="video"
                        class="w-4 h-4"></i> Video</a>
                <a href="index.php?lang=<?php echo $lang; ?>" class="tool-item"><i data-lucide="image"
                        class="w-4 h-4"></i> Photo</a>
                <a href="reels.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'reels' ? 'active' : ''; ?>"><i
                        data-lucide="clapperboard" class="w-4 h-4"></i> Reels</a>
                <a href="highlights.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'highlights' ? 'active' : ''; ?>"><i data-lucide="tv"
                        class="w-4 h-4"></i> IGTV</a>
                <a href="index.php?lang=<?php echo $lang; ?>" class="tool-item"><i data-lucide="layout"
                        class="w-4 h-4"></i> Carousel</a>
            </div>

            <!-- Title -->
            <h1 class="section-title animate-fade-up"><?php echo $t['heading']; ?></h1>
            <p class="section-subtitle animate-fade-up" style="animation-delay: 0.1s">
                <?php echo $t['subtitle']; ?>
            </p>

            <!-- Input Group -->
            <form id="downloadForm" class="input-container animate-fade-up" style="animation-delay: 0.2s">
                <input type="text" id="instaUrl" placeholder="<?php echo $t['placeholder']; ?>" class="input-field"
                    required>
                <button type="button" id="btnPaste" class="btn-paste">
                    <i data-lucide="clipboard" class="w-4 h-4 text-slate-500"></i>
                    <?php echo $t['btn_paste']; ?>
                </button>
                <button type="submit" class="btn-download">
                    <?php echo $t['btn_download']; ?>
                </button>
            </form>

            <!-- Result Area -->
            <div id="result" class="mt-12 max-w-2xl mx-auto"></div>
        </div>
    </section>

    <main class="py-20 bg-slate-50">
        <!-- Rich Content Section -->
        <?php if ($dbPage && !empty($dbPage['content'])): ?>
            <div class="article-container animate-fade-up">
                <div class="article-content prose prose-slate max-w-none">
                    <?php echo Translator::translate($dbPage['content'], $lang); ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="py-16 bg-white border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-6">
            <div class="footer-brand text-center">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto mx-auto mb-4"
                        alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="footer-logo-icon mx-auto mb-4"></i>
                <?php endif; ?>
                <span
                    class="footer-logo-text block"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></span>
            </div>

            <div class="footer-links-group mt-8">
                <?php foreach ($footerItems as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['final_url']); ?>" class="footer-link">
                        <?php echo htmlspecialchars($item['label']); ?>
                    </a>
                    <span class="text-slate-200">|</span>
                <?php endforeach; ?>
            </div>

            <div class="footer-divider"></div>

            <p class="copyright-text">© <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        document.getElementById('btnPaste').addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                document.getElementById('instaUrl').value = text;
            } catch (err) {
                console.error('Failed to read clipboard', err);
            }
        });

        document.getElementById('downloadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('instaUrl');
            const resDiv = document.getElementById('result');
            const url = input.value.trim();
            if (!url) return;

            resDiv.innerHTML = `<div class='flex flex-col items-center gap-6 py-10'><div class='spinner'></div><p class='font-bold text-white uppercase tracking-widest text-sm animate-pulse'><?php echo $t['status_fetching']; ?></p></div>`;

            try {
                const res = await fetch('download.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url })
                });
                const data = await res.json();
                resDiv.innerHTML = '';
                if (data.status === 'single') {
                    renderSingle(data);
                } else {
                    throw new Error(data.error || 'Error');
                }
            } catch (err) {
                resDiv.innerHTML = `<div class='p-8 bg-red-500/90 text-white rounded-3xl font-bold flex flex-col items-center gap-4 border border-red-400 shadow-xl'><span>${err.message}</span></div>`;
            }
        });

        function renderSingle(data) {
            const dl = `download.php?action=download&url=${encodeURIComponent(data.url)}`;
            document.getElementById('result').innerHTML = `
                <div class="flex flex-col gap-8 items-center bg-white/10 backdrop-blur-lg p-8 rounded-[3rem] border border-white/20 shadow-2xl">
                    <div class="relative group max-w-sm rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white">
                        ${data.type === 'video' ? `<video controls class="w-full h-auto"><source src="${dl}"></video>` : `<img src="${dl}" class="w-full h-auto">`}
                    </div>
                    <a href="${dl}" class="w-full max-w-xs bg-blue-600 text-white text-center py-5 rounded-2xl font-black text-xl shadow-2xl hover:bg-blue-700 transition-all flex items-center justify-center gap-3">
                        <i data-lucide="download" class="w-6 h-6"></i> Download
                    </a>
                </div>`;
            lucide.createIcons();
        }
    </script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>