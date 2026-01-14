<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

$available_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'DE', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

// Determine Active Language
if (isset($_GET['filter_lang'])) {
    $_curr_lang = $_GET['filter_lang'];
} elseif (isset($_SESSION['last_seo_lang'])) {
    $_curr_lang = $_SESSION['last_seo_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';
$_SESSION['last_seo_lang'] = $_curr_lang;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_id = $_POST['page_identifier'] ?? 'home';
    $lang = $_POST['lang_code'] ?? 'en';
    $title = $_POST['meta_title'] ?? '';
    $desc = $_POST['meta_description'] ?? '';
    $og_image = $_POST['og_image'] ?? '';
    $schema = $_POST['schema_markup'] ?? '';

    if ($pdo) {
        try {
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM seo_data WHERE page_identifier = ? AND lang_code = ?");
            $stmt->execute([$page_id, $lang]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE seo_data SET meta_title = ?, meta_description = ?, og_image = ?, schema_markup = ? WHERE page_identifier = ? AND lang_code = ?");
                $stmt->execute([$title, $desc, $og_image, $schema, $page_id, $lang]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO seo_data (page_identifier, lang_code, meta_title, meta_description, og_image, schema_markup) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$page_id, $lang, $title, $desc, $og_image, $schema]);
            }
            $message = 'SEO data updated successfully for ' . strtoupper($lang) . '!';
        } catch (\Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all pages meta data
$pages = ['index', 'photo', 'reels', 'video', 'igtv', 'carousel'];
$seo_data = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM seo_data");
    while ($row = $stmt->fetch()) {
        $seo_data[$row['page_identifier']][$row['lang_code']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SEO Manager - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            /* Slate 900 */
            color: #f8fafc;
            min-height: 100vh;
        }

        .sidebar {
            height: 100vh;
            background: #065f46;
            /* Dark green sidebar is hardcoded here? It should be from sidebar.php */
            color: white;
        }

        /* Improve card visibility in Dark Mode */
        /* Improve card visibility in Dark Mode - Now White */
        .seo-card {
            background: #ffffff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 2px solid #e2e8f0;
        }
    </style>
</head>

<body class="flex">
    <!-- Sidebar (Same as dashboard) -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen bg-[#0f172a]">
        <header
            class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20">
            <div>
                <h3 class="text-xl font-bold text-white">SEO Management</h3>
                <p class="text-xs text-gray-400 mt-0.5">Optimize meta tags and search visibility</p>
            </div>
            <div class="text-gray-400 font-medium text-sm hidden md:block">
                Active Language: <span
                    class="text-white font-bold ml-1"><?php echo $available_langs[$_curr_lang]['flag']; ?>
                    <?php echo $available_langs[$_curr_lang]['label']; ?></span>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-fuchsia-50 text-fuchsia-700 p-4 rounded-xl mb-6 font-bold border border-fuchsia-100">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Info Card (Collapsible) -->
            <div
                class="bg-gradient-to-r from-violet-50 via-fuchsia-50 to-pink-50 border-2 border-fuchsia-200 rounded-2xl mb-8 overflow-hidden">
                <!-- Header (Always Visible) -->
                <button onclick="toggleInfoCard('seo-info')"
                    class="w-full p-4 flex items-center justify-between hover:bg-fuchsia-100/50 transition-all">
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 p-2 rounded-lg shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <h4 class="text-sm font-black text-gray-900">SEO Meta Tags Manager - How It Works</h4>
                            <p class="text-xs text-gray-600">Click to expand/collapse</p>
                        </div>
                    </div>
                    <svg id="seo-info-icon" class="w-5 h-5 text-gray-600 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Content (Collapsible) -->
                <div id="seo-info" class="hidden px-6 pb-6">
                    <p class="text-sm text-gray-600 leading-relaxed mb-3">
                        This feature allows you to optimize your website's search engine visibility by customizing meta
                        tags for each <strong>static page</strong> (Home, Video, Reels, Photo, IGTV, Carousel, etc.).
                        Proper SEO meta tags help search engines understand your content and improve your rankings in
                        search results.
                    </p>

                    <!-- Available Languages -->
                    <div class="bg-white rounded-lg p-3 border border-fuchsia-100 mb-3">
                        <p class="text-xs text-gray-700 font-semibold mb-2">🌍 <strong>Available Languages:</strong></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($available_langs as $code => $info): ?>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-gradient-to-r from-violet-50 to-fuchsia-50 border border-fuchsia-200 rounded-lg text-xs font-bold text-gray-700">
                                    <span class="text-sm"><?php echo $info['flag']; ?></span>
                                    <?php echo $info['label']; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 border border-fuchsia-100">
                        <p class="text-xs text-gray-700 font-semibold mb-2">💡 <strong>What you can manage:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li><strong>Meta Title</strong> - The title that appears in search results and browser tabs
                                (50-60 characters recommended)</li>
                            <li><strong>Meta Description</strong> - A brief summary shown in search results (150-160
                                characters recommended)</li>
                            <li><strong>OG Image</strong> - The image displayed when sharing on social media platforms
                            </li>
                            <li><strong>Static Pages Only</strong> - Manage SEO for Home, Video, Reels, Photo, IGTV,
                                Carousel pages (not blog posts)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Language Navigation Tabs -->
            <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <?php foreach ($available_langs as $code => $info): ?>
                    <a href="?filter_lang=<?php echo $code; ?>"
                        class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 <?php echo $_curr_lang === $code ? 'bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white shadow-lg shadow-fuchsia-500/30' : 'text-gray-500 hover:bg-gray-50'; ?> text-sm">
                        <span class="text-base"><?php echo $info['flag']; ?></span>
                        <span><?php echo $info['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 auto-rows-fr">
                <?php foreach ($pages as $page):
                    // Icons and Colors based on page
                    $icon = '';
                    $accentColor = 'border-emerald-500';
                    $iconColor = 'text-emerald-500';
                    $headerBg = 'bg-white';


                    switch ($page) {
                        case 'index':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />';
                            $accentColor = 'border-t-4 border-fuchsia-500';
                            $iconColor = 'text-fuchsia-500';
                            break;
                        case 'video':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />';
                            $accentColor = 'border-t-4 border-blue-500';
                            $iconColor = 'text-blue-500';
                            break;
                        case 'reels':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />';
                            $accentColor = 'border-t-4 border-rose-500';
                            $iconColor = 'text-rose-500';
                            break;
                        case 'photo':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />';
                            $accentColor = 'border-t-4 border-amber-500';
                            $iconColor = 'text-amber-500';
                            break;
                        case 'igtv':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />';
                            $accentColor = 'border-t-4 border-purple-500';
                            $iconColor = 'text-purple-500';
                            break;
                        case 'carousel':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />';
                            $accentColor = 'border-t-4 border-indigo-500';
                            $iconColor = 'text-indigo-500';
                            break;
                        default:
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                            $accentColor = 'border-t-4 border-gray-500';
                            $iconColor = 'text-gray-500';
                    }
                    ?>
                    <div
                        class="seo-card bg-white rounded-3xl shadow-xl border-2 border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-[1.02] group flex flex-col h-full <?php echo $accentColor; ?>">
                        <!-- Card Header -->
                        <div
                            class="px-8 py-6 border-b border-gray-100 flex items-center justify-between <?php echo $headerBg; ?>">
                            <div class="flex items-center gap-4">
                                <span
                                    class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center <?php echo $iconColor; ?> shadow-sm border border-gray-100 group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php echo $icon; ?>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-xl font-bold text-black capitalize tracking-tight">
                                        <?php echo $page; ?> Page
                                    </h2>
                                    <p class="text-xs text-gray-700 font-bold">SEO Configuration</p>
                                </div>
                            </div>
                            <div
                                class="text-xs font-black text-gray-900 bg-gray-100 px-3 py-1 rounded-full uppercase tracking-wider">
                                Static
                            </div>
                        </div>

                        <div class="p-8 flex-1 flex flex-col">
                            <?php
                            $code = $_curr_lang;
                            $info = $available_langs[$code];
                            $data = $seo_data[$page][$code] ?? [];
                            ?>
                            <form action="" method="POST" class="space-y-4 flex flex-col flex-1">
                                <input type="hidden" name="page_identifier" value="<?php echo $page; ?>">
                                <input type="hidden" name="lang_code" value="<?php echo $code; ?>">

                                <div class="flex-1 space-y-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-black uppercase tracking-widest bg-gray-50 border border-gray-200 text-gray-500 px-3 py-1 rounded-lg shadow-sm">
                                            <?php echo $info['flag']; ?>     <?php echo $info['label']; ?>
                                        </span>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-black text-gray-800 uppercase mb-2">Meta
                                                Title</label>
                                            <input type="text" name="meta_title"
                                                value="<?php echo htmlspecialchars($data['meta_title'] ?? ''); ?>"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none transition-all font-bold text-gray-900 placeholder-gray-400 focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-800 uppercase mb-2">OG Image
                                                URL</label>
                                            <input type="text" name="og_image"
                                                value="<?php echo htmlspecialchars($data['og_image'] ?? ''); ?>"
                                                class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none transition-all font-semibold text-gray-900 placeholder-gray-400 focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-800 uppercase mb-2">Meta
                                            Description</label>
                                        <textarea name="meta_description" rows="3"
                                            class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none transition-all font-semibold text-gray-900 leading-relaxed placeholder-gray-400 focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100"><?php echo htmlspecialchars($data['meta_description'] ?? ''); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-gray-800 uppercase mb-2">Schema
                                            (JSON-LD)</label>
                                        <textarea name="schema_markup" rows="2"
                                            class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none transition-all font-mono text-xs font-semibold text-gray-900 placeholder-gray-400 focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100"><?php echo htmlspecialchars($data['schema_markup'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white py-4 rounded-xl font-bold text-sm hover:shadow-lg hover:shadow-fuchsia-500/30 transition-all flex items-center justify-center gap-2 hover:scale-[1.02]">
                                        <span>Save Changes</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <script>
        // Toggle Info Card Function
        function toggleInfoCard(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</body>

</html>