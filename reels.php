<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$pageIdentifier = 'reels'; // For SEO

// Helper function for auto-translation
function __($text, $lang)
{
    return Translator::translate($text, $lang);
}

// Translations Logic
$t = [
    'title' => __('Instagram Reels Downloader', $lang),
    'heading' => __("Download <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Reels</span> in HD", $lang),
    'subtitle' => __('Save your favorite Instagram Reels videos instantly to your device for free.', $lang),
    'paste' => __('Paste Reels link here...', $lang),
    'status_fetching' => __('Fetching Reels...', $lang),
    'footer_desc' => __('The ultimate tool for Instagram media preservation.', $lang)
];

// Re-apply HTML in heading for non-english if auto-translate stripped it
if ($lang !== 'en') {
    $t['heading'] = str_replace("Instagram Reels", "<span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Reels</span>", $t['heading']);
} else {
    $t['heading'] = "Download <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Reels</span> in HD";
}

// Fetch dynamic navigation links
$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

$seoHelper = new SEO_Helper($pdo, $pageIdentifier, $lang);

// Include the master layout (we use index.php as a partial or just copy-paste for simplicity in this specific architecture)
// For this project, we'll keep it as a standalone for better individual SEO control
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $seoHelper->getTitle(); ?>
    </title>
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
            <a href="index.php?lang=<?php echo $lang; ?>" class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <span class="ml-3 text-xl font-black tracking-tighter text-gray-800">
                    <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>
                </span>
            </a>
            <nav class="hidden md:flex items-center gap-8 font-semibold text-gray-500">
                <?php
                foreach ($headerItems as $item):
                    $isActive = (strpos($item['final_url'], 'reels.php') !== false);
                    ?>
                    <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                        class="<?php echo $isActive ? 'text-emerald-600 border-b-2 border-emerald-600' : 'hover:text-emerald-600 transition-colors'; ?> py-1">
                        <?php echo htmlspecialchars($item['label']); ?>
                    </a>
                <?php endforeach; ?>
                <select onchange="location.href = this.value"
                    class="appearance-none bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl outline-none focus:border-emerald-500 transition-all cursor-pointer shadow-sm ml-4">
                    <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $code => $label): ?>
                        <option value="?lang=<?php echo $code; ?>" <?php echo $lang === $code ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-20 flex-1">
        <div class="max-w-4xl mx-auto text-center mb-16 fade-in">
            <h1 class="text-4xl md:text-7xl font-bold text-gray-900 mb-8 leading-[1.1] tracking-tight">
                <?php echo $t['heading']; ?>
            </h1>
            <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto">
                <?php echo $t['subtitle']; ?>
            </p>
        </div>

        <div class="max-w-3xl mx-auto mb-20 fade-in">
            <div class="glass-card rounded-[2.5rem] p-6 md:p-12">
                <form id="downloadForm" class="relative">
                    <input type="text" id="instaUrl" placeholder="<?php echo $t['paste']; ?>"
                        class="w-full bg-white/80 border-2 border-gray-100 rounded-3xl py-6 pl-8 pr-32 focus:outline-none focus:border-emerald-500 transition-all text-xl"
                        required>
                    <button type="submit"
                        class="absolute right-3 top-3 bottom-3 px-8 bg-emerald-600 text-white rounded-2xl font-black shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">Go</button>
                </form>
                <div id="result" class="mt-12"></div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-auto pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-24">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </div>
                        <h4 class="text-3xl font-black text-white tracking-tighter"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></h4>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-md"><?php echo $t['footer_desc'] ?? 'The ultimate tool for Instagram media preservation.'; ?></p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Downloader</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="video.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Video Downloader</a></li>
                        <li><a href="reels.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Reels Downloader</a></li>
                        <li><a href="story.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Story Downloader</a></li>
                        <li><a href="blog.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Blog & News</a></li>
                    </ul>
                </div>
                <?php foreach ($footerItems as $item): ?>
                    <div>
                        <h4 class="text-white font-bold mb-6"><?php echo htmlspecialchars($item['label']); ?></h4>
                        <?php if(!empty($item['children'])): ?>
                        <ul class="space-y-4 text-gray-400">
                            <?php foreach ($item['children'] as $child): ?>
                                <li><a href="<?php echo htmlspecialchars($child['final_url']); ?>" class="hover:text-emerald-400"><?php echo htmlspecialchars($child['label']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-500 font-medium text-xs">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('downloadForm').addEventListener('submit', async e => {
            e.preventDefault();
            const resDiv = document.getElementById('result');
            resDiv.innerHTML = `<div class='flex flex-col items-center gap-4 py-10'><div class='w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin'></div><p class='font-bold text-gray-400'><?php echo $t['status_fetching']; ?></p></div>`;
            try {
                const res = await fetch('download.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url: document.getElementById('instaUrl').value }) });
                const data = await res.json();
                if (data.status === 'single') {
                    const dl = `download.php?action=download&url=${encodeURIComponent(data.url)}`;
                    resDiv.innerHTML = `<div class="flex flex-col gap-6 items-center fade-in"><div class="rounded-3xl overflow-hidden border-8 border-white shadow-xl">${data.type === 'video' ? `<video controls class="max-w-full h-auto"><source src="${dl}"></video>` : `<img src="${dl}" class="max-w-full h-auto">`}</div><a href="${dl}" class="bg-emerald-600 text-white px-12 py-4 rounded-2xl font-black text-xl hover:bg-emerald-700 transition-all">Download Now</a></div>`;
                } else throw new Error('Error');
            } catch (e) { resDiv.innerHTML = `<div class='p-6 bg-red-50 text-red-500 rounded-2xl text-center font-bold'>Error fetching video. Please check the link.</div>`; }
        });
    </script>
</body>

</html>