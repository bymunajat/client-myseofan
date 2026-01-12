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
$pages = ['home', 'video', 'reels', 'image'];
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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #064e3b 100%);
            /* Green-Gray Gradient Background */
        }

        .sidebar {
            height: 100vh;
            background: #111827;
            color: white;
        }

        .nav-active {
            background: #374151;
            border-left: 4px solid #10b981;
        }
    </style>
</head>

<body class="flex">
    <!-- Sidebar (Same as dashboard) -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">SEO Management</h3>
            <div class="text-gray-500 font-medium text-sm hidden md:block">
                Active Language: <span
                    class="text-gray-900 font-bold ml-1"><?php echo $available_langs[$_curr_lang]['flag']; ?>
                    <?php echo $available_langs[$_curr_lang]['label']; ?></span>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Language Navigation Tabs -->
            <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <?php foreach ($available_langs as $code => $info): ?>
                    <a href="?filter_lang=<?php echo $code; ?>"
                        class="px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 <?php echo $_curr_lang === $code ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'text-gray-500 hover:bg-gray-50'; ?> text-sm">
                        <span class="text-base"><?php echo $info['flag']; ?></span>
                        <span><?php echo $info['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <?php foreach ($pages as $page):
                    // Icons and Colors based on page
                    $icon = '';
                    $accentColor = 'border-emerald-500';
                    $iconColor = 'text-emerald-500';
                    $headerBg = 'bg-slate-50'; // Reverted to light header
                
                    switch ($page) {
                        case 'home':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />';
                            $accentColor = 'border-t-4 border-emerald-500';
                            $iconColor = 'text-emerald-500';
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
                        case 'image':
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />';
                            $accentColor = 'border-t-4 border-amber-500';
                            $iconColor = 'text-amber-500';
                            break;
                        default:
                            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                            $accentColor = 'border-t-4 border-gray-500';
                            $iconColor = 'text-gray-500';
                    }
                    ?>
                    <div
                        class="bg-white rounded-3xl shadow-xl border border-white/10 overflow-hidden transition-all duration-300 group <?php echo $accentColor; ?>">
                        <!-- Card Header -->
                        <div
                            class="px-8 py-6 border-b border-gray-100 flex items-center justify-between <?php echo $headerBg; ?>">
                            <div class="flex items-center gap-4">
                                <span
                                    class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center <?php echo $iconColor; ?> shadow-sm border border-gray-100 group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php echo $icon; ?>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800 capitalize tracking-tight">
                                        <?php echo $page; ?> Page</h2>
                                    <p class="text-xs text-gray-400 font-medium">SEO Configuration</p>
                                </div>
                            </div>
                            <div
                                class="text-xs font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full uppercase tracking-wider">
                                Static
                            </div>
                        </div>

                        <div class="p-8">
                            <?php
                            $code = $_curr_lang;
                            $info = $available_langs[$code];
                            $data = $seo_data[$page][$code] ?? [];
                            ?>
                            <form action="" method="POST" class="space-y-4">
                                <input type="hidden" name="page_identifier" value="<?php echo $page; ?>">
                                <input type="hidden" name="lang_code" value="<?php echo $code; ?>">
                                <div class="flex items-center gap-2 mb-4">
                                    <span
                                        class="text-xs font-black uppercase tracking-widest bg-white border border-gray-200 text-gray-700 px-3 py-1 rounded-lg shadow-sm">
                                        <?php echo $info['flag']; ?>     <?php echo $info['label']; ?>
                                    </span>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Meta
                                            Title</label>
                                        <input type="text" name="meta_title"
                                            value="<?php echo htmlspecialchars($data['meta_title'] ?? ''); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all font-bold text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">OG Image
                                            URL</label>
                                        <input type="text" name="og_image"
                                            value="<?php echo htmlspecialchars($data['og_image'] ?? ''); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all text-blue-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Meta
                                        Description</label>
                                    <textarea name="meta_description" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all text-gray-600 leading-relaxed"><?php echo htmlspecialchars($data['meta_description'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Schema
                                        (JSON-LD)</label>
                                    <textarea name="schema_markup" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:bg-white focus:border-emerald-500 transition-all font-mono text-xs text-gray-500"><?php echo htmlspecialchars($data['schema_markup'] ?? ''); ?></textarea>
                                </div>
                        </div>
                        <div class="pl-1 pt-2">
                            <button type="submit"
                                class="w-full bg-gray-900 text-white py-4 rounded-xl font-bold text-sm hover:bg-emerald-600 transition-all shadow-lg hover:shadow-emerald-200/50 flex items-center justify-center gap-2 group-hover:translate-y-[-2px]">
                                <span>Save Changes</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            </div>
        <?php endforeach; ?>
        </div>
        </div>
    </main>
</body>

</html>