<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

// 1. Initialize State
$lang = $_GET['lang'] ?? 'en';
$pageIdentifier = 'home';

// 2. Fetch Data
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// 3. Fallback Translations (if DB is empty)
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
        'about_d' => 'We believe content archiving should be easy and accessible. Our platform is built by enthusiasts for the creative community.',
        'footer_desc' => 'The ultimate tool for Instagram media preservation. We help creators and fans archive their favorite moments with ease and style.',
        'status_fetching' => 'Fetching media content...',
        'status_error' => 'Check account privacy or link.',
        'status_clipboard' => 'Allow clipboard access or paste manually.'
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
        'guide2_d' => 'Tempel tautan di atas dan sistem kami akan segera mengirimkan sumbernya.',
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
        'about_d' => 'Kami percaya pengarsipan konten harus mudah diakses semua orang. Platform ini dibangun untuk komunitas kreatif.',
        'footer_desc' => 'Alat terbaik untuk pelestarian media Instagram. Kami membantu kreator dan penggemar mengarsipkan momen favorit dengan mudah dan bergaya.',
        'status_fetching' => 'Mengambil konten media...',
        'status_error' => 'Periksa privasi akun atau tautan.',
        'status_clipboard' => 'Izinkan akses papan klip atau tempel secara manual.'
    ]
];

// Merge with defaults
$t = array_merge($defaults[$lang] ?? $defaults['en'], $translations);

// 4. Initialize SEO
$seoHelper = new SEO_Helper($pdo ?? null, $pageIdentifier, $lang);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $seoHelper->getTitle(); ?>
    </title>
    <meta name="description" content="<?php echo $seoHelper->getDescription(); ?>">
    <?php echo $seoHelper->getOGTags(); ?>
    <?php echo $seoHelper->getSchemaMarkup(); ?>

    <!-- Favicon -->
    <?php if (!empty($settings['favicon_path'])): ?>
            <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($settings['favicon_path']); ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- UI Framework -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Header Code -->
    <?php echo $settings['header_code'] ?? ''; ?>

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

        /* Animated Gradient Background */
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
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 70%);
            border-radius: 50%;
            filter: blur(80px);
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from {
                transform: translate(-10%, -10%);
            }

            to {
                transform: translate(20%, 20%);
            }
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .hero-title {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }

        /* Micro-animations */
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

        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '.';
            }

            40% {
                content: '..';
            }

            60% {
                content: '...';
            }
        }
    </style>
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
                            <span
                                class="ml-3 text-xl font-black tracking-tighter text-gray-800"><?php echo htmlspecialchars($settings['site_name']); ?></span>
                    <?php endif; ?>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 font-semibold text-gray-500">
                <a href="#" class="nav-link text-emerald-600 border-b-2 border-emerald-600 py-1" data-page="home"
                    id="navHome"><?php echo $t['home']; ?></a>
                <a href="#" class="nav-link hover:text-emerald-600 transition-colors py-1" data-page="how"
                    id="navHow"><?php echo $t['how']; ?></a>
                <a href="#" class="nav-link hover:text-emerald-600 transition-colors py-1" data-page="about" id="navAbout"><?php echo $t['about']; ?></a>

                <!-- Language Switcher -->
                <div class="relative group">
                    <select id="langSelector" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 font-bold py-2 pl-4 pr-10 rounded-xl outline-none focus:border-emerald-500 transition-all cursor-pointer">
                        <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>🇺🇸 EN</option>
                        <option value="id" <?php echo $lang === 'id' ? 'selected' : ''; ?>>🇮🇩 ID</option>
                        <option value="es" <?php echo $lang === 'es' ? 'selected' : ''; ?>>🇪🇸 ES</option>
                        <option value="fr" <?php echo $lang === 'fr' ? 'selected' : ''; ?>>🇫🇷 FR</option>
                        <option value="de" <?php echo $lang === 'de' ? 'selected' : ''; ?>>🇩🇪 DE</option>
                        <option value="ja" <?php echo $lang === 'ja' ? 'selected' : ''; ?>>🇯🇵 JA</option>
                    </select>
                </div>
            </nav>

            <button class="md:hidden text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12 md:py-20 flex-1">
        <!-- Hero Section -->
        <div class="max-w-4xl mx-auto text-center mb-16 fade-in">
            <h2 id="mainHeading" class="text-4xl md:text-7xl font-bold text-gray-900 mb-8 leading-[1.1] tracking-tight">
                <?php echo $t['heading']; ?>
            </h2>
            <p id="subtitle" class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed">
                <?php echo $t['subtitle']; ?>
            </p>
        </div>

        <!-- Download Box -->
        <div class="max-w-3xl mx-auto mb-24">
            <div class="glass-card rounded-[2.5rem] p-6 md:p-12 shadow-2xl shadow-emerald-900/5 fade-in"
                style="animation-delay: 0.2s">
                <form id="downloadForm" class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <input type="text" id="instaUrl" placeholder="Paste Instagram link here..."
                        class="w-full bg-white/80 border-2 border-gray-100/50 rounded-3xl py-6 pl-16 pr-36 focus:outline-none focus:border-emerald-500 focus:ring-[12px] focus:ring-emerald-500/5 transition-all text-xl text-gray-800 placeholder-gray-400 font-medium"
                        required>
                    <div class="absolute right-3 top-3 bottom-3 flex gap-2">
                        <button type="button" id="pasteBtn"
                            class="px-5 text-emerald-600 hover:bg-emerald-50 rounded-2xl font-bold transition-all hidden sm:block">
                            <?php echo $t['paste']; ?>
                        </button>
                        <button type="submit" id="downloadBtn"
                            class="px-8 bg-emerald-600 text-white rounded-2xl font-black shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-3">
                            <span><?php echo $t['download']; ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div id="result" class="mt-12 transition-all duration-700 overflow-hidden"></div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-3 gap-8 mb-32">
            <div class="glass-card p-8 rounded-[2rem] hover:translate-y-[-8px] transition-all duration-500">
                <div
                    class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h4 class="text-xl font-bold mb-3" id="feat1_title"><?php echo $t['feat1_t']; ?></h4>
                <p class="text-gray-500 leading-relaxed text-sm md:text-base" id="feat1_desc">
                    <?php echo $t['feat1_d']; ?></p>
            </div>
            <div class="glass-card p-8 rounded-[2rem] hover:translate-y-[-8px] transition-all duration-500">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h4 class="text-xl font-bold mb-3" id="feat2_title"><?php echo $t['feat2_t']; ?></h4>
                <p class="text-gray-500 leading-relaxed text-sm md:text-base" id="feat2_desc">
                    <?php echo $t['feat2_d']; ?></p>
            </div>
            <div class="glass-card p-8 rounded-[2rem] hover:translate-y-[-8px] transition-all duration-500">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h4 class="text-xl font-bold mb-3" id="feat3_title"><?php echo $t['feat3_t']; ?></h4>
                <p class="text-gray-500 leading-relaxed text-sm md:text-base" id="feat3_desc">
                    <?php echo $t['feat3_d']; ?></p>
            </div>
        </div>

        <!-- How it works (Guides) -->
        <div id="page-how" class="page hidden max-w-5xl mx-auto mb-32 fade-in">
            <h3 class="text-3xl font-bold text-center mb-16" id="guide_title"><?php echo $t['guide_t']; ?></h3>
            <div class="grid md:grid-cols-3 gap-12 relative">
                <div class="hidden md:block absolute top-10 left-[20%] right-[20%] h-0.5 bg-gray-200"></div>

                <div class="relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm z-10">
                    <div
                        class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mb-8 font-black text-2xl shadow-xl shadow-emerald-200 mx-auto">
                        1</div>
                    <h4 class="text-xl font-bold mb-4 text-center" id="guide1_title"><?php echo $t['guide1_t']; ?></h4>
                    <p class="text-gray-500 text-center leading-relaxed" id="guide1_desc"><?php echo $t['guide1_d']; ?>
                    </p>
                </div>
                <div class="relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm z-10">
                    <div
                        class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mb-8 font-black text-2xl shadow-xl shadow-emerald-200 mx-auto">
                        2</div>
                    <h4 class="text-xl font-bold mb-4 text-center" id="guide2_title"><?php echo $t['guide2_t']; ?></h4>
                    <p class="text-gray-500 text-center leading-relaxed" id="guide2_desc"><?php echo $t['guide2_d']; ?></p>
                </div>
                <div class="relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm z-10">
                    <div
                        class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mb-8 font-black text-2xl shadow-xl shadow-emerald-200 mx-auto">
                        3</div>
                    <h4 class="text-xl font-bold mb-4 text-center" id="guide3_title"><?php echo $t['guide3_t']; ?></h4>
                    <p class="text-gray-500 text-center leading-relaxed" id="guide3_desc"><?php echo $t['guide3_d']; ?></p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq-section" class="max-w-3xl mx-auto mb-32">
            <h3 class="text-3xl font-bold text-center mb-16" id="faq_title"><?php echo $t['faq_t']; ?></h3>
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <button
                        class="faq-btn w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-all font-bold group">
                        <span id="q1"><?php echo $t['q1']; ?></span>
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-ans hidden p-6 pt-0 text-gray-500 leading-relaxed border-t border-gray-50" id="a1">
                        <?php echo $t['a1']; ?>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <button
                        class="faq-btn w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-all font-bold group">
                        <span id="q2"><?php echo $t['q2']; ?></span>
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-ans hidden p-6 pt-0 text-gray-500 leading-relaxed border-t border-gray-50" id="a2">
                        <?php echo $t['a2']; ?>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <button
                        class="faq-btn w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-all font-bold group">
                        <span id="q3"><?php echo $t['q3']; ?></span>
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-ans hidden p-6 pt-0 text-gray-500 leading-relaxed border-t border-gray-50" id="a3">
                        <?php echo $t['a3']; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- About Page -->
        <div id="page-about" class="page hidden max-w-4xl mx-auto mt-20 fade-in">
            <div class="glass-card p-12 rounded-[3rem] text-center">
                <h3 class="text-4xl font-black mb-8" id="about_title"><?php echo $t['about_t']; ?></h3>
                <p class="text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto mb-12" id="about_desc">
                    <?php echo $t['about_d']; ?>
                </p>
                <div class="flex flex-wrap justify-center gap-10">
                    <div class="flex flex-col items-center gap-3">
                        <div
                            class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <span class="font-bold">Global Network</span>
                    </div>
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="font-bold">Original Quality</span>
                    </div>
                    <div class="flex flex-col items-center gap-3">
                        <div
                            class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <span class="font-bold">Secure Access</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-auto pt-24 pb-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-16 mb-24">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-8">
                        <div
                            class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-gray-900 shadow-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black hero-title">MySeoFan</h4>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-md mb-8">
                        <?php echo $t['footer_desc']; ?>
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Company</h4>
                    <ul class="space-y-4 text-gray-400 font-medium">
                        <li><a href="blog.php?lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">Blog & News</a></li>
                        <li><a href="page.php?slug=about-us&lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">About MySeoFan</a></li>
                        <li><a href="page.php?slug=contact&lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Legal & Support</h4>
                    <ul class="space-y-4 text-gray-400 font-medium">
                        <li><a href="page.php?slug=privacy-policy&lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="page.php?slug=terms-of-use&lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">Terms of Use</a></li>
                        <li><a href="page.php?slug=support&lang=<?php echo $lang; ?>"
                                class="hover:text-emerald-400 transition-colors">Support Center</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-500 font-medium text-xs">
                &copy; <?php echo date('Y'); ?> MySeoFan Studio. All rights reserved. Built for the community.
            </div>
        </div>
    </footer>

    <script>
        const pasteBtn = document.getElementById('pasteBtn');
        const downloadBtnText = document.querySelector('#downloadBtn span');
        const langSelector = document.getElementById('langSelector');

        langSelector.addEventListener('change', e => {
            const newLang = e.target.value;
            const url = new URL(window.location.href);
            url.searchParams.set('lang', newLang);
            window.location.href = url.href;
        });

        // FAQ Toggle Logic
        document.querySelectorAll('.faq-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const ans = btn.nextElementSibling;
                const icon = btn.querySelector('svg');
                ans.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('text-emerald-600', 'border-b-2', 'border-emerald-600'));
                link.classList.add('text-emerald-600', 'border-b-2', 'border-emerald-600');

                document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
                const targetId = 'page-' + link.dataset.page;

                // Special handling for sections on Home page
                const homeSections = [
                    document.getElementById('downloadForm').closest('.max-w-3xl'),
                    document.getElementById('faq-section'),
                    document.getElementById('page-how')
                ].filter(el => el);

                if (link.dataset.page === 'home') {
                    homeSections.forEach(el => el.classList.remove('hidden'));
                } else {
                    homeSections.forEach(el => el.classList.add('hidden'));
                    const target = document.getElementById(targetId);
                    if (target) target.classList.remove('hidden');
                }
            });
        });

        pasteBtn.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                document.getElementById('instaUrl').value = text;
            } catch {
                alert('<?php echo $t['status_clipboard']; ?>');
            }
        });

        document.getElementById('downloadForm').addEventListener('submit', async e => {
            e.preventDefault();
            const urlInput = document.getElementById('instaUrl');
            const resultDiv = document.getElementById('result');
            const url = urlInput.value.trim();
            if (!url) return;

            resultDiv.innerHTML = `<div class='flex flex-col items-center gap-6 py-10 fade-in py-10 fade-in'><div class='w-16 h-16 border-[6px] border-emerald-500 border-t-transparent rounded-full animate-spin'></div><p class='font-black text-gray-400 uppercase tracking-widest text-sm animate-pulse'><?php echo $t['status_fetching']; ?></p></div>`;

            try {
                const res = await fetch('download.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url })
                });

                const data = await res.json();
                resultDiv.innerHTML = '';

                if (data.status === 'single') renderSingle(data);
                else if (data.status === 'multiple') renderCarousel(data.media);
                else throw new Error(data.error || '<?php echo $t['status_error']; ?>');
            } catch (err) {
                resultDiv.innerHTML = `
                    <div class='p-8 bg-red-50 text-red-600 rounded-3xl font-bold flex flex-col items-center gap-4 border-2 border-red-100 fade-in'>
                        <svg class='w-12 h-12 opacity-50' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-width="2" d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg> 
                        <span class="text-lg text-center">${err.message}</span>
                        <button onclick="location.reload()" class="text-sm px-6 py-2 bg-red-600 text-white rounded-xl shadow-lg shadow-red-200 hover:bg-red-700 transition-all">Try Again</button>
                    </div>`;
            }
        });

        function renderSingle(data) {
            const resultDiv = document.getElementById('result');
            const dLink = `download.php?action=download&url=${encodeURIComponent(data.url)}`;
            resultDiv.innerHTML = `
                <div class="flex flex-col gap-8 items-center fade-in">
                    <div class="relative group max-w-sm rounded-[2rem] overflow-hidden shadow-2xl shadow-emerald-900/10 border-8 border-white">
                        ${data.type === 'video'
                    ? `<video controls class="w-full h-auto"><source src="${dLink}"></video>`
                    : `<img src="${dLink}" class="w-full h-auto transform transition-all duration-700 group-hover:scale-110">`}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>
                    </div>
                    <a href="${dLink}" class="w-full max-w-xs bg-emerald-600 text-white text-center py-5 rounded-2xl font-black text-xl shadow-2xl shadow-emerald-200 hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Files
                    </a>
                </div>
            `;
        }

        function renderCarousel(media) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 fade-in">
                    ${media.map((item, i) => {
                const dl = `download.php?action=download&url=${encodeURIComponent(item.url)}`;
                return `
                            <div class="glass-card p-4 rounded-3xl flex flex-col gap-4 group">
                                <div class="relative rounded-2xl overflow-hidden bg-gray-100 aspect-square flex items-center justify-center">
                                    ${item.type === 'video'
                        ? `<video class="w-full h-full object-cover"><source src="${dl}"></video>`
                        : `<img src="${dl}" class="w-full h-full object-cover group-hover:scale-110 transition-all duration-700">`}
                                    <div class="absolute top-3 left-3 px-4 py-1.5 bg-black/60 backdrop-blur-xl text-white text-[10px] font-black rounded-full uppercase tracking-[0.2em] shadow-lg">${item.type}</div>
                                </div>
                                <a href="${dl}" class="w-full bg-emerald-600/5 text-emerald-600 text-center py-4 rounded-xl font-black hover:bg-emerald-600 hover:text-white hover:shadow-xl hover:shadow-emerald-200 transition-all">
                                    Slide ${i + 1}
                                </a>
                            </div>
                        `;
            }).join('')}
                </div>
            `;
        }
    </script>
    <!-- Custom Footer Code -->
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>