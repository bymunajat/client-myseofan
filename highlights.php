<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$pageIdentifier = 'highlights';

// Fetch Editable Content from database if exists
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'highlights-downloader' AND lang_code = 'en'");
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
    'title' => $dbPage ? Translator::translate($dbPage['title'], $lang) : __('Instagram Highlights Downloader', $lang),
    'heading' => $dbPage ? Translator::translate($dbPage['title'], $lang) : __("Save <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Highlights</span> Permanently", $lang),
    'subtitle' => $dbPage ? Translator::translate(strip_tags($dbPage['content']), $lang) : __('The easiest way to archive curated highlights from any public Instagram profile.', $lang),
    'paste' => __('Highlight link here...', $lang),
    'status_fetching' => __('Loading Highlights...', $lang),
    'footer_desc' => __('Premium tool for Instagram media preservation.', $lang)
];

// Re-apply highlight style
if (!$dbPage && $lang !== 'en') {
    $t['heading'] = str_replace("Instagram Highlights", "<span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Highlights</span>", $t['heading']);
} elseif ($dbPage) {
    $t['heading'] = str_replace("Instagram", "<span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram</span>", $t['heading']);
}

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
            background: radial-gradient(circle at top left, #f3f4f6, #e5e7eb);
            min-height: 100vh;
            color: #1f2937;
        }

        .premium-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
            overflow: hidden;
        }

        .blob {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            animation: move 15s infinite alternate;
        }

        @keyframes move {
            from {
                transform: translate(-5%, -5%);
            }

            to {
                transform: translate(15%, 15%);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <?php echo $settings['header_code'] ?? ''; ?>
</head>

<body class="flex flex-col">
    <div class="premium-bg">
        <div class="blob"></div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-white/50 border-b border-gray-200">
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
                    <?php endif; ?>
                    <span
                        class="ml-3 text-xl font-black tracking-tighter text-gray-800"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 font-semibold text-gray-500">
                <?php
                function renderHeaderMenu_hl($items, $pageIdentifier, $currentSlug, $depth = 0)
                {
                    foreach ($items as $item):
                        $isActive = (strpos($item['final_url'], 'highlights.php') !== false);
                        $hasChildren = !empty($item['children']);
                        ?>
                        <div class="relative group">
                            <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                                class="<?php echo $isActive ? 'text-emerald-600 border-b-2 border-emerald-600' : 'hover:text-emerald-600 transition-colors'; ?> py-1 flex items-center gap-1">
                                <?php echo htmlspecialchars($item['label']); ?>
                                <?php if ($hasChildren): ?>
                                    <svg class="w-3 h-3 pt-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
                                <?php endif; ?>
                            </a>
                            <?php if ($hasChildren): ?>
                                <div
                                    class="absolute top-full left-0 mt-2 w-48 bg-white shadow-xl rounded-xl p-2 hidden group-hover:block z-50 border border-gray-100">
                                    <?php renderHeaderMenu_hl($item['children'], $pageIdentifier, $currentSlug, $depth + 1); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach;
                }
                renderHeaderMenu_hl($headerItems, $pageIdentifier, '');
                ?>
                <select onchange="location.href = '?lang=' + this.value"
                    class="appearance-none bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-2xl shadow-sm outline-none cursor-pointer">
                    <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $c => $l): ?>
                        <option value="<?php echo $c; ?>" <?php echo $lang == $c ? 'selected' : ''; ?>><?php echo $l; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-20 flex-1 text-center font-bold">
        <div class="max-w-4xl mx-auto mb-16 fade-in">
            <h1 class="text-5xl md:text-7xl font-black text-gray-900 mb-8 leading-tight tracking-tight">
                <?php echo $t['heading']; ?>
            </h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed"><?php echo $t['subtitle']; ?></p>
        </div>

        <div class="max-w-3xl mx-auto mb-20 fade-in">
            <!-- Tool Tabs -->
            <div class="flex flex-wrap justify-center gap-2 mb-8 p-2 bg-white/20 backdrop-blur-xl rounded-[2rem] border border-white/40 w-max mx-auto shadow-sm">
                <a href="video.php?lang=<?php echo $lang; ?>" class="px-6 py-3 rounded-[1.5rem] hover:bg-white/50 text-gray-700 transition-all">Video</a>
                <a href="reels.php?lang=<?php echo $lang; ?>" class="px-6 py-3 rounded-[1.5rem] hover:bg-white/50 text-gray-700 transition-all">Reels</a>
                <a href="story.php?lang=<?php echo $lang; ?>" class="px-6 py-3 rounded-[1.5rem] hover:bg-white/50 text-gray-700 transition-all">Story</a>
                <a href="highlights.php?lang=<?php echo $lang; ?>" class="px-6 py-3 rounded-[1.5rem] bg-emerald-600 text-white shadow-lg transition-all">Highlights</a>
            </div>

            <div class="glass-card rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-emerald-900/5">
                <form id="dlForm" class="relative group">
                    <input type="text" id="url" placeholder="<?php echo $t['paste']; ?>"
                        class="w-full bg-white/80 border-2 border-gray-100 rounded-3xl py-6 pl-8 pr-32 focus:outline-none focus:border-emerald-500 transition-all text-xl font-bold"
                        required>
                    <button type="submit"
                        class="absolute right-3 top-3 bottom-3 px-10 bg-emerald-600 text-white rounded-2xl font-black shadow-lg hover:bg-emerald-700 transition-all">Go</button>
                </form>
                <div id="res" class="mt-12 transition-all duration-700"></div>
            </div>
        </div>

        <!-- Rich Content Section (Article Style) -->
        <?php if ($dbPage && !empty($dbPage['content'])): ?>
        <div class="max-w-4xl mx-auto mt-24 text-left fade-in">
            <div class="prose prose-emerald prose-xl max-w-none bg-white p-12 md:p-20 rounded-[3rem] shadow-xl border border-gray-100">
                <div class="article-content text-gray-700 leading-relaxed">
                    <?php echo Translator::translate($dbPage['content'], $lang); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-900 text-white mt-auto pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-24 text-left font-bold">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-8">
                        <?php if (!empty($settings['logo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-12 w-auto"
                                alt="<?php echo htmlspecialchars($settings['site_name']); ?>">
                        <?php else: ?>
                            <div
                                class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2.5"
                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <h4 class="text-3xl font-black tracking-tighter">
                            <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>
                        </h4>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-md"><?php echo $t['footer_desc']; ?></p>
                </div>
                <div>
                    <h4 class="font-bold mb-6">Tools</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="video.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Video
                                Downloader</a></li>
                        <li><a href="reels.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Reels
                                Downloader</a></li>
                        <li><a href="story.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Story
                                Downloader</a></li>
                        <li><a href="blog.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Blog</a></li>
                    </ul>
                </div>
                <?php foreach ($footerItems as $item): ?>
                    <div>
                        <h4 class="font-bold mb-6"><?php echo htmlspecialchars($item['label']); ?></h4>
                        <?php if (!empty($item['children'])): ?>
                            <ul class="space-y-4 text-gray-400">
                                <?php foreach ($item['children'] as $child): ?>
                                    <li><a href="<?php echo htmlspecialchars($child['final_url']); ?>"
                                            class="hover:text-emerald-400"><?php echo htmlspecialchars($child['label']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div
                class="border-t border-white/5 pt-12 text-center text-gray-400 text-xs uppercase tracking-widest font-bold">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. All rights
                reserved.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('dlForm').addEventListener('submit', async e => {
            e.preventDefault();
            const res = document.getElementById('res');
            res.innerHTML = `<div class='flex flex-col items-center gap-6 py-10'><div class='w-16 h-16 border-[6px] border-emerald-500 border-t-transparent rounded-full animate-spin'></div><p class='font-black text-gray-400 uppercase tracking-widest text-sm animate-pulse'><?php echo $t['status_fetching']; ?></p></div>`;
            try {
                const r = await fetch('download.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url: document.getElementById('url').value }) });
                const d = await r.json();
                if (d.status === 'single') {
                    const dl = `download.php?action=download&url=${encodeURIComponent(d.url)}`;
                    res.innerHTML = `<div class="flex flex-col gap-8 items-center fade-in"><div class="relative max-w-sm rounded-[2rem] overflow-hidden shadow-2xl border-8 border-white">${d.type === 'video' ? `<video controls class="w-full"><source src="${dl}"></video>` : `<img src="${dl}" class="w-full">`}</div><a href="${dl}" class="w-full max-w-xs bg-emerald-600 text-white text-center py-5 rounded-2xl font-black text-xl shadow-2xl hover:bg-emerald-700 transition-all">Download</a></div>`;
                } else throw new Error();
            } catch (e) { res.innerHTML = `<div class='p-8 bg-red-50 text-red-600 rounded-3xl font-bold border-2 border-red-100 fade-in'>Error fetching content. Check link.</div>`; }
        });
    </script>
    <style>
        .article-content h2 {
            font-size: 2.25rem;
            font-weight: 900;
            color: #111827;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        .article-content ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .article-content li {
            margin-bottom: 0.5rem;
        }
    </style>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>