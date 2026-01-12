<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';

// 1. Initialize State
$lang = $_GET['lang'] ?? 'en';
$pageIdentifier = 'home';

// 2. Fetch Data
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// 3. Fallback Translations (Expanded for 6 Languages)
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
    ],
    'es' => [
        'title' => 'Descargador de Instagram',
        'home' => 'Inicio',
        'how' => 'Guías',
        'about' => 'Acerca de',
        'heading' => "Guarda <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Cualquier</span> Contenido de Instagram",
        'subtitle' => 'La forma más rápida y confiable de bajar Reels, Fotos y Videos de Instagram sin costo.',
        'download' => 'Ir',
        'paste' => 'Pegar',
        'feat1_t' => 'Súper Rápido',
        'feat1_d' => 'Servidores de alto nivel que entregan tu contenido en segundos.',
        'feat2_t' => 'Privacidad Segura',
        'feat2_d' => 'Respetamos tu privacidad. Sin cuentas, sin registros.',
        'feat3_t' => 'Calidad HD',
        'feat3_d' => 'Siempre baja la mejor resolución disponible.',
        'guide_t' => 'Guía Sencilla de 3 Pasos',
        'guide1_t' => 'Copiar URL',
        'guide1_d' => 'Copia el enlace del post de Instagram.',
        'guide2_t' => 'Pegar y Procesar',
        'guide2_d' => 'Pega el enlace arriba y procesaremos la fuente.',
        'guide3_t' => 'Disfruta Offline',
        'guide3_d' => 'Guarda el archivo en tu dispositivo al instante.',
        'faq_t' => 'Preguntas Comunes',
        'q1' => '¿Es gratis?',
        'a1' => 'Sí, 100% gratuito siempre.',
        'q2' => '¿Cuentas privadas?',
        'a2' => 'Solo perfiles públicos permitidos.',
        'q3' => '¿Dispositivos?',
        'a3' => 'Funciona en iPhone, Android y PC.',
        'about_t' => 'Misión',
        'about_d' => 'Hacemos que el archivo de contenido sea fácil para todos.',
        'footer_desc' => 'La mejor herramienta para preservar media de Instagram.',
        'status_fetching' => 'Obteniendo contenido...',
        'status_error' => 'Error de enlace.',
        'status_clipboard' => 'Pega manualmente.'
    ],
    'fr' => [
        'title' => 'Téléchargeur Instagram',
        'home' => 'Accueil',
        'how' => 'Guides',
        'about' => 'À propos',
        'heading' => "Enregistrez <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Tout</span> Media Instagram",
        'subtitle' => 'Téléchargez des Reels, Photos et Vidéos Instagram rapidement et gratuitement.',
        'download' => 'Go',
        'paste' => 'Coller',
        'feat1_t' => 'Ultra Rapide',
        'feat1_d' => 'Serveurs haute performance pour un téléchargement immédiat.',
        'feat2_t' => 'Confidentialité',
        'feat2_d' => 'Aucun compte requis, aucune donnée stockée.',
        'feat3_t' => 'Qualité HD',
        'feat3_d' => 'Toujours la meilleure résolution possible.',
        'guide_t' => 'Guide en 3 Étapes',
        'guide1_t' => 'Copier l\'URL',
        'guide1_d' => 'Copiez le lien depuis Instagram.',
        'guide2_t' => 'Coller et Traiter',
        'guide2_d' => 'Collez le lien ci-dessus pour analyse.',
        'guide3_t' => 'Enregistrer',
        'guide3_d' => 'Téléchargez directement sur votre appareil.',
        'faq_t' => 'Questions Fréquentes',
        'q1' => 'Est-ce gratuit?',
        'a1' => 'Oui, totalement gratuit.',
        'q2' => 'Comptes privés?',
        'a2' => 'Uniquement les comptes publics.',
        'q3' => 'Support?',
        'a3' => 'Marche sur tous les navigateurs.',
        'about_t' => 'Mission',
        'about_d' => 'Simplifier l\'accès au partage de contenu.',
        'footer_desc' => 'L\'outil ultime pour préserver vos médias Instagram.',
        'status_fetching' => 'Chargement...',
        'status_error' => 'Erreur de lien.',
        'status_clipboard' => 'Coller manuellement.'
    ],
    'de' => [
        'title' => 'Instagram Downloader',
        'home' => 'Startseite',
        'how' => 'Anleitungen',
        'about' => 'Über uns',
        'heading' => "Speichere <span class='text-emerald-600 border-b-4 border-emerald-400/30'>Jedes</span> Instagram-Medium",
        'subtitle' => 'Der schnellste Weg, um Instagram Reels, Fotos und Videos kostenlos herunterzuladen.',
        'download' => 'Los',
        'paste' => 'Einfügen',
        'feat1_t' => 'Blitzschnell',
        'feat1_d' => 'Optimierte Server für schnellste Downloads.',
        'feat2_t' => 'Sicher & Privat',
        'feat2_d' => 'Keine Registrierung, keine Datenspeicherung.',
        'feat3_t' => 'HD Qualität',
        'feat3_d' => 'Immer in bestmöglicher Auflösung.',
        'guide_t' => '3-Schritte Anleitung',
        'guide1_t' => 'URL kopieren',
        'guide1_d' => 'Kopieren Sie den Instagram-Link.',
        'guide2_t' => 'Einfügen',
        'guide2_d' => 'Fügen Sie den Link oben ein.',
        'guide3_t' => 'Speichern',
        'guide3_d' => 'Direkt auf Ihr Gerät herunterladen.',
        'faq_t' => 'Häufige Fragen',
        'q1' => 'Kostenlos?',
        'a1' => 'Ja, komplett kostenlos.',
        'q2' => 'Private Konten?',
        'a2' => 'Nur öffentliche Profile.',
        'q3' => 'Geräte?',
        'a3' => 'Mobil und Desktop.',
        'about_t' => 'Mission',
        'about_d' => 'Einfacher Zugriff auf Online-Medien.',
        'footer_desc' => 'Das beste Tool zum Speichern von Instagram-Inhalten.',
        'status_fetching' => 'Laden...',
        'status_error' => 'Fehler beim Link.',
        'status_clipboard' => 'Manuell einfügen.'
    ],
    'ja' => [
        'title' => 'Instagram ダウンローダー',
        'home' => 'ホーム',
        'how' => 'ガイド',
        'about' => 'サイトについて',
        'heading' => "Instagram メディアを <span class='text-emerald-600 border-b-4 border-emerald-400/30'>即座に</span> 保存",
        'subtitle' => 'Instagramのリール、写真、動画を素早く無料でダウンロードできる最も信頼性の高いツールです。',
        'download' => '実行',
        'paste' => '貼り付け',
        'feat1_t' => '超高速',
        'feat1_d' => '最新のサーバーで瞬時にダウンロード。',
        'feat2_t' => '安全・匿名',
        'feat2_d' => '登録不要、プライバシーを重視。',
        'feat3_t' => '高画質',
        'feat3_d' => '可能な限り最高の解像度で。',
        'guide_t' => '簡単な3ステップ',
        'guide1_t' => 'リンクをコピー',
        'guide1_d' => 'InstagramからURLをコピー。',
        'guide2_t' => '貼り付け',
        'guide2_d' => '上の欄にリンクを貼り付け。',
        'guide3_t' => '保存',
        'guide3_d' => 'デバイスに直接保存。',
        'faq_t' => 'よくある質問',
        'q1' => '無料ですか？',
        'a1' => 'はい、永久に無料です。',
        'q2' => '個人アカウント？',
        'a2' => '公開プロフィールのみ対応。',
        'q3' => '対応機種？',
        'a3' => 'スマホでもPCでも動作。',
        'about_t' => '私たちの使命',
        'about_d' => 'コンテンツ保存を誰でも簡単に。',
        'footer_desc' => 'Instagramメディア保存の究極のツール。',
        'status_fetching' => '読み込み中...',
        'status_error' => 'リンクエラー。',
        'status_clipboard' => '手動で貼り付け。'
    ]
];

// Merge with defaults (EN as primary fallback for missing keys in other langs)
$t = array_merge($defaults['en'], $defaults[$lang] ?? [], $translations);

// Fetch dynamic navigation links
$headerLinks = $pdo->prepare("SELECT title, slug FROM pages WHERE lang_code = ? AND show_in_header = 1 ORDER BY menu_order ASC");
$headerLinks->execute([$lang]);
$headerLinks = $headerLinks->fetchAll();

$rawFooterLinks = $pdo->prepare("SELECT title, slug, footer_section FROM pages WHERE lang_code = ? AND show_in_footer = 1 ORDER BY menu_order ASC");
$rawFooterLinks->execute([$lang]);
$rawFooterLinks = $rawFooterLinks->fetchAll();

$footerGroups = [];
foreach ($rawFooterLinks as $fl) {
    $section = $fl['footer_section'] ?: 'legal';
    $footerGroups[$section][] = $fl;
}

// 4. Initialize SEO
$seoHelper = new SEO_Helper($pdo ?? null, $pageIdentifier, $lang);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seoHelper->getTitle(); ?></title>
    <meta name="description" content="<?php echo $seoHelper->getDescription(); ?>">
    <?php echo $seoHelper->getOGTags(); ?>
    <?php echo $seoHelper->getHreflangTags(); ?>
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
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto">
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
                <a href="#" class="nav-link text-emerald-600 border-b-2 border-emerald-600 py-1"
                    data-page="home"><?php echo $t['home']; ?></a>
                <a href="#" class="nav-link hover:text-emerald-600 transition-colors py-1"
                    data-page="how"><?php echo $t['how']; ?></a>
                <a href="#" class="nav-link hover:text-emerald-600 transition-colors py-1"
                    data-page="about"><?php echo $t['about']; ?></a>

                <?php foreach ($headerLinks as $hl): ?>
                    <a href="page.php?slug=<?php echo htmlspecialchars($hl['slug']); ?>&lang=<?php echo $lang; ?>"
                        class="hover:text-emerald-600 transition-colors py-1"><?php echo htmlspecialchars($hl['title']); ?></a>
                <?php endforeach; ?>

                <!-- Language Switcher -->
                <div class="relative group">
                    <select onchange="location.href = this.value"
                        class="appearance-none bg-white border border-gray-200 text-gray-700 font-bold py-2.5 pl-5 pr-12 rounded-2xl outline-none focus:border-emerald-500 transition-all cursor-pointer shadow-sm">
                        <?php
                        $langs = ['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'];
                        foreach ($langs as $code => $label):
                            $targetUrl = "index.php?lang=$code";
                            ?>
                            <option value="<?php echo $targetUrl; ?>" <?php echo $lang === $code ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12 md:py-20 flex-1">
        <!-- Hero Section -->
        <div class="max-w-4xl mx-auto text-center mb-16 fade-in">
            <h2 class="text-4xl md:text-7xl font-bold text-gray-900 mb-8 leading-[1.1] tracking-tight">
                <?php echo $t['heading']; ?>
            </h2>
            <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed"><?php echo $t['subtitle']; ?>
            </p>
        </div>

        <!-- Download Box -->
        <div class="max-w-3xl mx-auto mb-24">
            <div class="glass-card rounded-[2.5rem] p-6 md:p-12 shadow-2xl shadow-emerald-900/5 fade-in">
                <form id="downloadForm" class="relative group">
                    <input type="text" id="instaUrl" placeholder="Paste Instagram link here..."
                        class="w-full bg-white/80 border-2 border-gray-100/50 rounded-3xl py-6 pl-8 pr-36 focus:outline-none focus:border-emerald-500 transition-all text-xl"
                        required>
                    <div class="absolute right-3 top-3 bottom-3 flex gap-2">
                        <button type="submit"
                            class="px-8 bg-emerald-600 text-white rounded-2xl font-black shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">Go</button>
                    </div>
                </form>
                <div id="result" class="mt-12 transition-all duration-700 overflow-hidden"></div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-3 gap-8 mb-32">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="glass-card p-8 rounded-[2rem] hover:translate-y-[-8px] transition-all">
                    <div
                        class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-3"><?php echo $t['feat' . $i . '_t']; ?></h4>
                    <p class="text-gray-500 leading-relaxed text-sm"><?php echo $t['feat' . $i . '_d']; ?></p>
                </div>
            <?php endfor; ?>
        </div>

        <!-- How & About (JS Toggled) -->
        <div id="page-how" class="page hidden max-w-5xl mx-auto mb-32 fade-in">
            <h3 class="text-3xl font-bold text-center mb-16"><?php echo $t['guide_t']; ?></h3>
            <div class="grid md:grid-cols-3 gap-12 text-center">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                        <div
                            class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mb-8 font-black text-2xl mx-auto">
                            <?php echo $i; ?>
                        </div>
                        <h4 class="text-xl font-bold mb-4"><?php echo $t['guide' . $i . '_t']; ?></h4>
                        <p class="text-gray-500 leading-relaxed"><?php echo $t['guide' . $i . '_d']; ?></p>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div id="page-about" class="page hidden max-w-4xl mx-auto mt-20 fade-in">
            <div class="glass-card p-12 rounded-[3rem] text-center">
                <h3 class="text-4xl font-black mb-8"><?php echo $t['about_t']; ?></h3>
                <p class="text-xl text-gray-600 leading-relaxed"><?php echo $t['about_d']; ?></p>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-auto pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-24">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-gray-900"><svg
                                class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg></div>
                        <h4 class="text-3xl font-black hero-title">MySeoFan</h4>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed max-w-md"><?php echo $t['footer_desc']; ?></p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Downloader</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="#" class="hover:text-emerald-400">Video Downloader</a></li>
                        <li><a href="#" class="hover:text-emerald-400">Reels Downloader</a></li>
                        <li><a href="#" class="hover:text-emerald-400">Story Downloader</a></li>
                        <li><a href="blog.php?lang=<?php echo $lang; ?>" class="hover:text-emerald-400">Blog & News</a>
                        </li>
                    </ul>
                </div>

                <?php foreach ($footerGroups as $section => $links): ?>
                <div>
                    <h4 class="text-white font-bold mb-6">
                        <?php echo $t['footer_section_' . $section] ?? ucfirst($section); ?>
                    </h4>
                    <ul class="space-y-4 text-gray-400">
                        <?php foreach($links as $fl): ?>
                        <li><a href="page.php?slug=<?php echo htmlspecialchars($fl['slug']); ?>&lang=<?php echo $lang; ?>" class="hover:text-emerald-400"><?php echo htmlspecialchars($fl['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-white/5 pt-12 text-center text-gray-400 font-medium text-xs">
                &copy; 2026 MySeoFan Studio. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('text-emerald-600', 'border-b-2', 'border-emerald-600'));
                link.classList.add('text-emerald-600', 'border-b-2', 'border-emerald-600');
                document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
                const targetId = 'page-' + link.dataset.page;
                const homeSections = [document.getElementById('downloadForm').closest('.max-w-3xl'), document.querySelector('.grid.md\\:grid-cols-3.gap-8'), document.querySelector('.max-w-4xl.text-center')].filter(el => el);
                if (link.dataset.page === 'home') homeSections.forEach(el => el.classList.remove('hidden'));
                else { homeSections.forEach(el => el.classList.add('hidden')); const target = document.getElementById(targetId); if (target) target.classList.remove('hidden'); }
            });
        });

        document.getElementById('downloadForm').addEventListener('submit', async e => {
            e.preventDefault();
            const input = document.getElementById('instaUrl');
            const resDiv = document.getElementById('result');
            const url = input.value.trim();
            if (!url) return;
            resDiv.innerHTML = `<div class='flex flex-col items-center gap-6 py-10'><div class='w-16 h-16 border-[6px] border-emerald-500 border-t-transparent rounded-full animate-spin'></div><p class='font-black text-gray-400 uppercase tracking-widest text-sm animate-pulse'><?php echo $t['status_fetching']; ?></p></div>`;
            try {
                const res = await fetch('download.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url }) });
                const data = await res.json();
                resDiv.innerHTML = '';
                if (data.status === 'single') renderSingle(data);
                else throw new Error(data.error || 'Error');
            } catch (e) {
                resDiv.innerHTML = `<div class='p-8 bg-red-50 text-red-600 rounded-3xl font-bold flex flex-col items-center gap-4 border-2 border-red-100 fade-in'><span class="text-lg text-center">${e.message}</span></div>`;
            }
        });

        function renderSingle(data) {
            const dl = `download.php?action=download&url=${encodeURIComponent(data.url)}`;
            document.getElementById('result').innerHTML = `
                <div class="flex flex-col gap-8 items-center fade-in">
                    <div class="relative group max-w-sm rounded-[2rem] overflow-hidden shadow-2xl border-8 border-white">
                        ${data.type === 'video' ? `<video controls class="w-full h-auto"><source src="${dl}"></video>` : `<img src="${dl}" class="w-full h-auto">`}
                    </div>
                    <a href="${dl}" class="w-full max-w-xs bg-emerald-600 text-white text-center py-5 rounded-2xl font-black text-xl shadow-2xl hover:bg-emerald-700 transition-all">Download</a>
                </div>`;
        }
    </script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>