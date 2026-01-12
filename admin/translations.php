<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id']) || !in_array(($_SESSION['role'] ?? ''), ['super_admin', 'editor'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

/**
 * 6-LANGUAGE DEFAULT DATASET (Static UI & Menus)
 */
$defaults = [
    'en' => [
        'title' => 'Instagram Downloader',
        'home' => 'Home',
        'how' => 'Guides',
        'about' => 'About',
        'heading' => "Instantly Save <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Any</span> Instagram Media",
        'subtitle' => 'Experience the fastest, most reliable Instagram downloader. No accounts, no fees, just pure high-speed downloads for Reels, Photos, and Videos.',
        'download' => 'Go',
        'paste' => 'Paste',
        'feat1_t' => 'Lightning Fast',
        'feat1_d' => 'Powered by top-tier server infrastructure to deliver your media in seconds.',
        'feat2_t' => 'Private & Secure',
        'feat2_d' => 'We value your privacy. Your data is never stored, and you don\'t need an account.',
        'feat3_t' => 'HD Quality',
        'feat3_d' => 'Always download the highest resolution available for Photos and Reels.',
        'guide_t' => 'Simple 3-Step Guide',
        'guide1_t' => 'Copy Content URL',
        'guide1_d' => 'Open Instagram and copy the URL from the browser bar or share menu.',
        'guide2_t' => 'Paste & Process',
        'guide2_d' => 'Paste the link above and our system immediately begins fetching the source.',
        'guide3_t' => 'Enjoy Offline',
        'guide3_d' => 'Hit download to instantly save the file to your smartphone or PC.',
        'faq_t' => 'Common Questions',
        'q1' => 'Is this tool free to use?',
        'a1' => 'Yes, our service is 100% free and will always remain so. No subscriptions needed.',
        'q2' => 'Can I download private account posts?',
        'a2' => 'Currently, we only support public accounts to respect Instagram\'s security measures.',
        'q3' => 'What devices are supported?',
        'a3' => 'Our tool works on all modern devices including iPhone, Android, and PC.',
        'about_t' => 'Our Mission',
        'about_d' => 'We believe content archiving should be easy and accessible. Our platform is built by enthusiasts for the creative community.'
    ],
    'id' => [
        'title' => 'Pengunduh Instagram',
        'home' => 'Beranda',
        'how' => 'Panduan',
        'about' => 'Tentang',
        'heading' => "Simpan <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Semua</span> Media Instagram Instan",
        'subtitle' => 'Pengalaman unduh Instagram tercepat dan terpercaya. Tanpa akun, tanpa biaya, unduhan Reels, Foto, dan Video berkecepatan tinggi.',
        'download' => 'Buka',
        'paste' => 'Tempel',
        'feat1_t' => 'Sangat Cepat',
        'feat1_d' => 'Didukung infrastruktur server kelas atas untuk mengirim media Anda dalam hitungan detik.',
        'feat2_t' => 'Privasi Aman',
        'feat2_d' => 'Kami menghargai privasi Anda. Data Anda tidak disimpan dan tanpa pendaftaran.',
        'feat3_t' => 'Kualitas HD',
        'feat3_d' => 'Selalu unduh resolusi tertinggi yang tersedia untuk Foto dan Reels.',
        'guide_t' => 'Panduan Mudah 3 Langkah',
        'guide1_t' => 'Salin Tautan',
        'guide1_d' => 'Buka Instagram dan salin URL konten dari browser atau menu bagikan.',
        'guide2_t' => 'Tempel & Proses',
        'guide2_d' => 'Tempel tautan di atas dan sistem kami akan segera mengambil sumbernya.',
        'guide3_t' => 'Unduh Media',
        'guide3_d' => 'Pilih unduh untuk menyimpan file langsung ke smartphone atau PC Anda.',
        'faq_t' => 'Pertanyaan Umum',
        'q1' => 'Apakah alat ini gratis?',
        'a1' => 'Ya, layanan kami 100% gratis selamanya tanpa perlu berlangganan apapun.',
        'q2' => 'Bisa unduh akun privasi?',
        'a2' => 'Saat ini hanya mendukung akun publik untuk menghargai keamanan Instagram.',
        'q3' => 'Perangkat apa saja yang didukung?',
        'a3' => 'Alat kami berbasis web, bekerja di iPhone, Android, dan PC Desktop.',
        'about_t' => 'Misi Kami',
        'about_d' => 'Kami percaya pengarsipan konten harus mudah diakses semua orang. Platform ini dibangun untuk komunitas kreatif.'
    ],
    'es' => [
        'title' => 'Descargador de Instagram',
        'home' => 'Inicio',
        'how' => 'Guías',
        'about' => 'Acerca de',
        'heading' => "Guarda <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Cualquier</span> Contenido de Instagram",
        'subtitle' => 'La forma más rápida y confiable de bajar Reels, Fotos y Videos de Instagram sin costo.',
        'download' => 'Ir',
        'paste' => 'Pegar'
    ],
    'fr' => [
        'title' => 'Téléchargeur Instagram',
        'home' => 'Accueil',
        'how' => 'Guides',
        'about' => 'À propos',
        'heading' => "Enregistrez <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Tout</span> Media Instagram",
        'subtitle' => 'Téléchargez des Reels, Photos et Vidéos Instagram rapidement et gratuitement.',
        'download' => 'Go',
        'paste' => 'Coller'
    ],
    'de' => [
        'title' => 'Instagram Downloader',
        'home' => 'Startseite',
        'how' => 'Anleitungen',
        'about' => 'Über uns',
        'heading' => "Speichere <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Jedes</span> Instagram-Medium",
        'subtitle' => 'Der schnellste Weg, um Instagram Reels, Fotos und Videos kostenlos herunterzuladen.',
        'download' => 'Los',
        'paste' => 'Einfügen'
    ],
    'ja' => [
        'title' => 'Instagram ダウンローダー',
        'home' => 'ホーム',
        'how' => 'ガイド',
        'about' => 'サイトについて',
        'heading' => "Instagram メディアを <span class='text-emerald-600 border-b-4 border-emerald-400/30'>即座に</span> 保存",
        'subtitle' => 'Instagramのリール、写真、動画を素早く無料でダウンロードできる最も信頼性の高いツールです。',
        'download' => '実行',
        'paste' => '貼り付け'
    ]
];

// Seed if requested
if (isset($_GET['seed'])) {
    $allowed_keys = array_keys($defaults['en']);
    $pdo->exec("DELETE FROM translations WHERE t_key NOT IN ('" . implode("','", $allowed_keys) . "')");
    foreach ($defaults as $lang => $data) {
        foreach ($data as $key => $val) {
            $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) VALUES (?, ?, ?) 
                                   ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
            $stmt->execute([$lang, $key, $val]);
        }
    }
    $message = 'Database restored and cleaned!';
}

// Handle single update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lang = $_POST['lang_code'];
    $key = $_POST['t_key'];
    $value = $_POST['t_value'];
    $p_redirect = $_POST['p_redirect'] ?? 1;
    $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) 
                           VALUES (?, ?, ?) 
                           ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
    if ($stmt->execute([$lang, $key, $value])) {
        $message = "[$lang] $key updated!";
    }
}

// State
$active_lang = $_GET['lang'] ?? 'en';
$supported_langs = ['en' => 'English', 'id' => 'Indonesia', 'es' => 'Español', 'fr' => 'Français', 'de' => 'Deutsch', 'ja' => '日本語'];
if (!array_key_exists($active_lang, $supported_langs))
    $active_lang = 'en';

// Pagination (Renamed to avoid sidebar collision)
$all_keys = array_keys($defaults['en']);
$_items_per_page = 10;
$_total_items = count($all_keys);
$_total_p = ceil($_total_items / $_items_per_page);
$_curr_p = (int) ($_GET['p'] ?? 1);
if ($_curr_p < 1)
    $_curr_p = 1;
if ($_curr_p > $_total_p)
    $_curr_p = $_total_p;
$_offset = ($_curr_p - 1) * $_items_per_page;
$keys = array_slice($all_keys, $_offset, $_items_per_page);

// Data
$stmt = $pdo->prepare("SELECT t_key, t_value FROM translations WHERE lang_code = ?");
$stmt->execute([$active_lang]);
$current_translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Global Translations - MySeoFan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
        }

        .nav-active {
            background: #374151;
            border-left: 4px solid #10b981;
        }

        .tab-active {
            background: white;
            color: #10b981;
            border-color: #10b981;
            font-weight: 700;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .page-active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between sticky top-0 z-50">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Site UI Translations</h1>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Multi-Language Management
                    | Page <?php echo $_curr_p; ?> of <?php echo $_total_p; ?></p>
            </div>
            <a href="?seed=1"
                class="text-sm bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all">Reset
                & Fix Database</a>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div
                    class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-8 border border-emerald-100 flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Language Tabs -->
            <div class="flex gap-2 mb-8 bg-gray-200/50 p-1.5 rounded-2xl overflow-x-auto">
                <?php foreach ($supported_langs as $code => $name): ?>
                    <a href="?lang=<?php echo $code; ?>"
                        class="px-6 py-3 rounded-xl text-sm transition-all <?php echo $active_lang === $code ? 'tab-active' : 'text-gray-500 hover:text-gray-800'; ?>">
                        <?php echo $name; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        Editing: <?php echo $supported_langs[$active_lang]; ?>
                    </h3>

                    <!-- TOP PAGINATION -->
                    <?php if ($_total_p > 1): ?>
                        <div class="flex gap-2">
                            <a href="?lang=<?php echo $active_lang; ?>&p=<?php echo max(1, $_curr_p - 1); ?>"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $_curr_p > 1 ? 'bg-white border-gray-200 text-emerald-600 hover:bg-emerald-50' : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'; ?>">Back</a>
                            <a href="?lang=<?php echo $active_lang; ?>&p=<?php echo min($_total_p, $_curr_p + 1); ?>"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?php echo $_curr_p < $_total_p ? 'bg-white border-gray-200 text-emerald-600 hover:bg-emerald-50' : 'bg-gray-100 border-gray-100 text-gray-300 cursor-not-allowed'; ?>">Next</a>
                        </div>
                    <?php endif; ?>
                </div>

                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-1/4">
                                Identifier Key</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Translated Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($keys as $key): ?>
                            <tr class="group hover:bg-gray-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <code
                                        class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg"><?php echo $key; ?></code>
                                </td>
                                <td class="px-8 py-6">
                                    <form action="?lang=<?php echo $active_lang; ?>&p=<?php echo $_curr_p; ?>" method="POST"
                                        class="flex items-center gap-4">
                                        <input type="hidden" name="lang_code" value="<?php echo $active_lang; ?>">
                                        <input type="hidden" name="t_key" value="<?php echo $key; ?>">
                                        <input type="hidden" name="p_redirect" value="<?php echo $_curr_p; ?>">
                                        <div class="flex-1 relative">
                                            <textarea name="t_value" rows="1"
                                                class="w-full px-5 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 focus:bg-white focus:border-emerald-500 outline-none text-sm transition-all resize-none shadow-sm"
                                                oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'><?php echo htmlspecialchars($current_translations[$key] ?? ''); ?></textarea>
                                        </div>
                                        <button type="submit"
                                            class="bg-emerald-600 text-white px-6 py-4 rounded-2xl hover:bg-emerald-700 font-bold shadow-lg shadow-emerald-100 transition-all">SAVE</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- BOTTOM PAGINATION -->
                <?php if ($_total_p > 1): ?>
                    <div class="p-8 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Page
                            <?php echo $_curr_p; ?> of <?php echo $_total_p; ?></p>
                        <div class="flex gap-2">
                            <?php for ($i = 1; $i <= $_total_p; $i++): ?>
                                <a href="?lang=<?php echo $active_lang; ?>&p=<?php echo $i; ?>"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl border text-xs font-bold transition-all <?php echo $i == $_curr_p ? 'page-active' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('textarea').forEach(el => {
            el.style.height = el.scrollHeight + "px";
        });
    </script>
</body>

</html>