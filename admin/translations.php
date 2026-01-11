<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Initial translations to seed (matches current index.php)
$default_en = [
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
];

$default_id = [
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
];

// Seed if requested
if (isset($_GET['seed'])) {
    foreach ($default_en as $key => $val) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO translations (lang_code, t_key, t_value) VALUES ('en', ?, ?)");
        $stmt->execute([$key, $val]);
    }
    foreach ($default_id as $key => $val) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO translations (lang_code, t_key, t_value) VALUES ('id', ?, ?)");
        $stmt->execute([$key, $val]);
    }
    $message = 'Database seeded with default translations!';
}

// Handle single update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lang = $_POST['lang_code'];
    $key = $_POST['t_key'];
    $value = $_POST['t_value'];

    $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) 
                           VALUES (?, ?, ?) 
                           ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
    if ($stmt->execute([$lang, $key, $value])) {
        $message = "Translation updated!";
    }
}

$all_en = getTranslations($pdo, 'en');
$all_id = getTranslations($pdo, 'id');
$keys = array_keys($default_en);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Translations - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
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
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Multi-Language Content</h3>
            <a href="?seed=1"
                class="text-sm bg-emerald-100 text-emerald-700 px-4 py-2 rounded-xl font-bold hover:bg-emerald-200 transition-all">Import
                Defaults</a>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Key</th>
                            <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">English
                            </th>
                            <th class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Indonesia
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($keys as $key): ?>
                            <tr>
                                <td class="px-8 py-6 font-bold text-gray-800 bg-gray-50/50">
                                    <?php echo $key; ?>
                                </td>
                                <td class="px-8 py-6">
                                    <form action="" method="POST" class="flex gap-2">
                                        <input type="hidden" name="lang_code" value="en">
                                        <input type="hidden" name="t_key" value="<?php echo $key; ?>">
                                        <input type="text" name="t_value"
                                            value="<?php echo htmlspecialchars($all_en[$key] ?? ''); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-100 focus:border-emerald-500 outline-none text-sm">
                                        <button type="submit"
                                            class="bg-emerald-600 text-white p-2 rounded-lg hover:bg-emerald-700"><svg
                                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg></button>
                                    </form>
                                </td>
                                <td class="px-8 py-6">
                                    <form action="" method="POST" class="flex gap-2">
                                        <input type="hidden" name="lang_code" value="id">
                                        <input type="hidden" name="t_key" value="<?php echo $key; ?>">
                                        <input type="text" name="t_value"
                                            value="<?php echo htmlspecialchars($all_id[$key] ?? ''); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-100 focus:border-emerald-500 outline-none text-sm">
                                        <button type="submit"
                                            class="bg-emerald-600 text-white p-2 rounded-lg hover:bg-emerald-700"><svg
                                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>