<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$lang = $_GET['lang'] ?? 'en';
$settings = getSiteSettings($pdo);
$pageIdentifier = 'video';

function __($text, $lang)
{
    return Translator::translate($text, $lang);
}

$t = [
    'title' => __('Instagram Video Downloader', $lang),
    'heading' => __("Download <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Videos</span> Easily", $lang),
    'subtitle' => __('Get any Instagram video in high resolution directly to your phone or computer.', $lang),
    'paste' => __('Paste Instagram video URL here...', $lang),
    'status_fetching' => __('Processing Video...', $lang),
    'footer_desc' => __('Premium tool for Instagram video preservation.', $lang)
];

if ($lang !== 'en') {
    $t['heading'] = str_replace("Instagram Videos", "<span class='text-emerald-600 border-b-4 border-emerald-400/30'>Instagram Videos</span>", $t['heading']);
}

$headerItems = getMenuTree($pdo, 'header', $lang);
$seoHelper = new SEO_Helper($pdo, $pageIdentifier, $lang);
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f9fafb;
            color: #1f2937;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="h-20 bg-white/80 backdrop-blur shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="text-2xl font-black text-gray-800 tracking-tighter">
                <?php echo htmlspecialchars($settings['site_name']); ?>
            </a>
            <select onchange="location.href='?lang='+this.value"
                class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 font-bold text-sm">
                <?php foreach (['en' => 'EN', 'id' => 'ID', 'es' => 'ES', 'fr' => 'FR', 'de' => 'DE', 'ja' => 'JA'] as $c => $l): ?>
                    <option value="<?php echo $c; ?>" <?php echo $lang == $c ? 'selected' : ''; ?>>
                        <?php echo $l; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </header>

    <main class="flex-1 container mx-auto px-6 py-20 text-center">
        <h1 class="text-5xl md:text-7xl font-black mb-8">
            <?php echo $t['heading']; ?>
        </h1>
        <p class="text-xl text-gray-500 mb-16">
            <?php echo $t['subtitle']; ?>
        </p>

        <div class="max-w-2xl mx-auto glass-card p-10 rounded-[3rem] shadow-xl">
            <form id="dlForm" class="flex gap-4">
                <input type="text" id="url" placeholder="<?php echo $t['paste']; ?>"
                    class="flex-1 bg-white border-2 border-gray-100 rounded-2xl py-5 px-8 focus:outline-none focus:border-emerald-500 text-lg font-bold"
                    required>
                <button type="submit"
                    class="bg-emerald-600 text-white px-10 rounded-2xl font-black hover:bg-emerald-700 transition-all">Go</button>
            </form>
            <div id="res" class="mt-12"></div>
        </div>
    </main>
    <script>
        document.getElementById('dlForm').addEventListener('submit', async e => {
            e.preventDefault();
            const res = document.getElementById('res');
            res.innerHTML = `<div class='animate-pulse font-bold text-gray-400'><?php echo $t['status_fetching']; ?></div>`;
            try {
                const r = await fetch('download.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url: document.getElementById('url').value }) });
                const d = await r.json();
                if (d.status === 'single') {
                    const dl = `download.php?action=download&url=${encodeURIComponent(d.url)}`;
                    res.innerHTML = `<div class='animate-in fade-in zoom-in duration-500'><div class='rounded-3xl overflow-hidden border-8 border-white shadow-2xl mb-8'>${d.type === 'video' ? `<video controls class='w-full'><source src="${dl}"></video>` : `<img src="${dl}" class='w-full'>`}</div><a href="${dl}" class='bg-emerald-600 text-white px-12 py-5 rounded-2xl font-black text-xl shadow-xl hover:bg-emerald-700 uppercase'>Download</a></div>`;
                } else throw new Error();
            } catch (e) { res.innerHTML = `<p class='text-red-500 font-bold'>Error fetching metadata.</p>`; }
        });
    </script>
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
                <?php foreach ($headerItems as $item): ?>
                    <div>
                        <h4 class="text-white font-bold mb-6"><?php echo htmlspecialchars($item['label']); ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-500 font-medium text-xs">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>