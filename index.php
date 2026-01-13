<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

// 1. Initialize State
$lang = $_GET['lang'] ?? 'en';
$pageIdentifier = 'index';

// 2. Fetch Data
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// Helper function for auto-translation
function __($text, $lang)
{
    return Translator::translate($text, $lang);
}

// 3. Fallback Translations (Updated to match demo1 design copy)
$defaults = [
    'en' => [
        'title' => 'Instagram Downloader - Download Videos, Photos, Reels & IGTV',
        'heading' => 'Instagram Downloader',
        'subtitle' => 'Download Instagram Videos, Photos, Reels, IGTV & carousel',
        'placeholder' => 'Insert instagram link here',
        'btn_download' => 'Download',
        'btn_paste' => 'Paste',
        'intro_title' => 'Instagram Videos and Photos Download',
        'intro_desc' => 'MySeoFan is an online web tool that helps you download Instagram Videos, Photos, Reels, and IGTV. MySeoFan.app is designed to be easy to use on any device, such as a mobile phone, tablet, or computer.',
        'how_to_title' => 'How to download from Instagram?',
        'how_to_subtitle' => 'You must follow these three easy steps to download video, reels, and photo from Instagram (IG, Insta). Follow the simple steps below.',
        'step1_title' => 'Copy the URL',
        'step1_desc' => 'Open the Instagram application or website, copy the URL of the photo, video, reels, carousel, IGTV.',
        'step2_title' => 'Paste the link',
        'step2_desc' => 'Return to the MySeoFan website, paste the link into the input field and click the "Download" button.',
        'step3_title' => 'Download',
        'step3_desc' => 'Quickly you will get the results with several quality options. Download what fits your needs.',
        'features_title' => 'Choose MySeoFan.app for download from Instagram',
        'features_subtitle' => 'Downloading videos from Instagram in just two clicks is possible without compromising on quality. Avoid using unreliable applications and appreciate the videos, even if they are of lower quality.',
        'feat1_t' => 'Fast download',
        'feat1_d' => 'Our servers are optimized to provide you with the fastest download speeds.',
        'feat2_t' => 'Support for all devices',
        'feat2_d' => 'Whether you\'re on a mobile, tablet, or desktop, MySeoFan has got you covered.',
        'feat3_t' => 'High quality',
        'feat3_d' => 'Download Instagram content in its original quality without any loss.',
        'feat4_t' => 'Security',
        'feat4_d' => 'We prioritize your privacy. No login required and all downloads are processed securely.',
        'status_fetching' => 'Fetching media content...',
    ],
    // Other languages can be added or auto-translated via Translator::translate
];

// Merge with defaults (EN as primary fallback)
$t = array_merge($defaults['en'], $translations);

// Auto-translate missing keys for other languages
if ($lang !== 'en') {
    foreach ($t as $key => $value) {
        if (!isset($translations[$key]) || empty($translations[$key])) {
            $t[$key] = Translator::translate($value, $lang);
        }
    }
}

// Fetch dynamic navigation links
$headerItems = getMenuTree($pdo, 'header', $lang);
$footerItems = getMenuTree($pdo, 'footer', $lang);

// 4. Initialize SEO
$seoHelper = new SEO_Helper($pdo ?? null, $pageIdentifier, $lang);
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
    <?php echo $seoHelper->getSchemaMarkup(); ?>

    <!-- Favicon -->
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

    <!-- Custom Header Code -->
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

        .input-field::placeholder {
            color: #475569;
            font-weight: 500;
            opacity: 1;
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

        .faq-item {
            margin-bottom: 40px;
        }

        .faq-question {
            color: #3b82f6;
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 12px;
            display: block;
        }

        .faq-answer {
            color: #334155;
            font-size: 0.875rem;
            line-height: 1.7;
            font-weight: 500;
        }

        .footer-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .footer-logo-text {
            font-size: 2.25rem;
            font-weight: 800;
            color: #7c3aed;
        }

        .footer-logo-icon {
            color: #a855f7;
            width: 36px;
            height: 36px;
        }

        .footer-links-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 12px;
        }

        .footer-link {
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: color 0.2s;
            text-decoration: none;
        }

        .footer-link:hover {
            color: #3b82f6;
        }

        .footer-divider {
            width: 100%;
            height: 1px;
            background: #e2e8f0;
            margin: 40px 0;
        }

        .social-label {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: lowercase;
            margin-bottom: 20px;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .social-icon {
            width: 24px;
            height: 24px;
            transition: transform 0.2s;
        }

        .social-icon:hover {
            transform: scale(1.1);
        }

        .copyright-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .intro-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            margin-bottom: 80px;
            border: 1px solid #e2e8f0;
        }

        .intro-visual {
            background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
            width: 35%;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .intro-content {
            padding: 40px;
            flex: 1;
        }

        .section-header-blue {
            color: #3b82f6;
            font-size: 1.875rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-header-blue::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6 0%, #db2777 100%);
        }

        .step-card {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .step-top {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .step-1 .step-top {
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
        }

        .step-2 .step-top {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        .step-3 .step-top {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        }

        .step-card-modern {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .step-visual-mockup {
            background: #ffffff;
            border-radius: 6px;
            padding: 8px 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            font-size: 0.75rem;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .step-visual-cursor {
            position: absolute;
            bottom: -20px;
            right: 20px;
            width: 24px;
            height: 24px;
        }

        .step-body {
            padding: 24px;
            text-align: center;
            flex: 1;
        }

        .step-title {
            color: #db2777;
            font-weight: 700;
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }

        .step-desc {
            color: #1e293b;
            font-size: 0.875rem;
            line-height: 1.6;
            border-top: 1px solid #f1f5f9;
            padding-top: 1rem;
            font-weight: 600;
        }

        .feature-card-modern {
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);

            /* Full Gradient Border Magic */
            border: 2px solid transparent;
            background-image: linear-gradient(white, white),
                linear-gradient(135deg, #3b82f6 0%, #db2777 100%);
            background-origin: border-box;
            background-clip: padding-box, border-box;

            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .feature-card-modern:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px -10px rgba(59, 130, 246, 0.2);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1);
        }

        .feature-card-modern:hover .icon-circle {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #3b82f6 0%, #db2777 100%);
            color: #ffffff;
            box-shadow: 0 15px 30px -5px rgba(219, 39, 119, 0.3);
        }

        .feature-detail-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            margin-bottom: 2rem;
            min-height: 240px;
            border: 1px solid #e2e8f0;
        }

        .feature-detail-card:nth-child(even) {
            flex-direction: row-reverse;
        }

        .feature-detail-visual {
            background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
            width: 40%;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-detail-visual img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
        }

        .feature-detail-content {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .feature-detail-title {
            color: #3b82f6;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .step-desc {
            color: #334155;
            font-size: 0.875rem;
            line-height: 1.6;
            border-top: 1px solid #f1f5f9;
            padding-top: 1rem;
            font-weight: 500;
        }

        .feature-detail-text {
            color: #334155;
            font-size: 0.875rem;
            line-height: 1.6;
            font-weight: 500;
        }

        .faq-answer {
            color: #334155;
            font-size: 0.875rem;
            line-height: 1.7;
            font-weight: 500;
        }

        @media (max-width: 768px) {

            .intro-card,
            .feature-detail-card,
            .feature-detail-card:nth-child(even) {
                flex-direction: column;
            }

            .intro-visual,
            .feature-detail-visual {
                width: 100%;
                height: 250px;
            }
        }

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

<body>
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
                    <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-50">
                        <div class="w-32 bg-white shadow-xl rounded-xl p-2 border border-gray-100">
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
                <a href="photo.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'photo' ? 'active' : ''; ?>"><i data-lucide="image"
                        class="w-4 h-4"></i> Photo</a>
                <a href="reels.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'reels' ? 'active' : ''; ?>"><i
                        data-lucide="clapperboard" class="w-4 h-4"></i> Reels</a>
                <a href="igtv.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'igtv' ? 'active' : ''; ?>"><i data-lucide="tv"
                        class="w-4 h-4"></i> IGTV</a>
                <a href="carousel.php?lang=<?php echo $lang; ?>"
                    class="tool-item <?php echo $pageIdentifier == 'carousel' ? 'active' : ''; ?>"><i
                        data-lucide="layout" class="w-4 h-4"></i> Carousel</a>
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
            <div id="result" class="mt-12 max-w-2xl mx-auto fade-in"></div>
        </div>
    </section>

    <!-- Content Sections Wrapper -->
    <main class="py-20 bg-slate-200 flex-grow">
        <div class="max-w-6xl mx-auto px-6">

            <!-- Intro Card -->
            <div class="intro-card">
                <div class="intro-visual">
                    <div
                        class="bg-white p-4 rounded-xl shadow-lg relative z-10 w-32 h-32 flex items-center justify-center">
                        <i data-lucide="layers" class="w-16 h-16 text-purple-600"></i>
                    </div>
                </div>
                <div class="intro-content">
                    <h2 class="text-2xl font-bold text-blue-600 mb-4"><?php echo $t['intro_title']; ?></h2>
                    <p class="text-slate-700 font-medium text-lg leading-relaxed">
                        <?php echo $t['intro_desc']; ?>
                    </p>
                </div>
            </div>

            <!-- How to Section -->
            <section id="how-to" class="mb-32">
                <h2 class="section-header-blue"><?php echo $t['how_to_title']; ?></h2>
                <p class="text-center text-slate-500 text-sm mb-12 max-w-2xl mx-auto">
                    <?php echo $t['how_to_subtitle']; ?>
                </p>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="step-card step-1">
                        <div class="step-top">
                            <div class="step-visual-mockup">
                                instagram.com/p/CmcRCI...
                                <img src="https://api.iconify.design/lucide:pointer.svg" class="step-visual-cursor"
                                    alt="pointer">
                            </div>
                        </div>
                        <div class="step-body">
                            <h3 class="step-title"><?php echo $t['step1_title']; ?></h3>
                            <p class="step-desc"><?php echo $t['step1_desc']; ?></p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="step-card step-2">
                        <div class="step-top">
                            <div class="step-visual-mockup flex justify-between items-center">
                                <span>instagram.com/p/C...</span>
                                <span class="bg-slate-200 px-2 py-1 rounded text-[10px] flex items-center gap-1">
                                    <i data-lucide="clipboard" class="w-2 h-2 text-slate-400"></i> Paste
                                </span>
                                <img src="https://api.iconify.design/lucide:pointer.svg" class="step-visual-cursor"
                                    alt="pointer">
                            </div>
                        </div>
                        <div class="step-body">
                            <h3 class="step-title"><?php echo $t['step2_title']; ?></h3>
                            <p class="step-desc"><?php echo $t['step2_desc']; ?></p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="step-card step-3">
                        <div class="step-top">
                            <div
                                class="step-visual-mockup bg-blue-600 text-white border-none text-center py-2 h-auto flex items-center justify-center">
                                Download
                                <img src="https://api.iconify.design/lucide:pointer.svg" class="step-visual-cursor"
                                    alt="pointer">
                            </div>
                        </div>
                        <div class="step-body">
                            <h3 class="step-title"><?php echo $t['step3_title']; ?></h3>
                            <p class="step-desc"><?php echo $t['step3_desc']; ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features">
                <h2 class="section-header-blue"><?php echo $t['features_title']; ?></h2>
                <p class="text-center text-slate-700 font-medium text-sm mb-12 max-w-2xl mx-auto">
                    <?php echo $t['features_subtitle']; ?>
                </p>

                <div class="grid md:grid-cols-4 gap-6">
                    <!-- Lightning Fast -->
                    <div class="feature-card-modern group">
                        <div class="icon-circle">
                            <i data-lucide="zap" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 mb-3 group-hover:text-blue-600 transition-colors">
                            Lightning Fast</h4>
                        <p class="text-slate-700 font-medium text-sm leading-relaxed">Powered by top-tier server
                            infrastructure to
                            deliver your media in seconds.</p>
                    </div>

                    <!-- Private & Secure -->
                    <div class="feature-card-modern group">
                        <div class="icon-circle">
                            <i data-lucide="shield-check" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 mb-3 group-hover:text-blue-600 transition-colors">
                            Private & Secure</h4>
                        <p class="text-slate-700 font-medium text-sm leading-relaxed">We value your privacy. Your data
                            is never
                            stored, and you don't need an account.</p>
                    </div>

                    <!-- HD Quality -->
                    <div class="feature-card-modern group">
                        <div class="icon-circle">
                            <i data-lucide="sparkles" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 mb-3 group-hover:text-blue-600 transition-colors">
                            HD Quality</h4>
                        <p class="text-slate-700 font-medium text-sm leading-relaxed">Always download the highest
                            resolution
                            available for Photos and Reels.</p>
                    </div>

                    <!-- Unlimited Downloads -->
                    <div class="feature-card-modern group">
                        <div class="icon-circle">
                            <i data-lucide="infinity" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 mb-3 group-hover:text-blue-600 transition-colors">
                            Unlimited</h4>
                        <p class="text-slate-700 font-medium text-sm leading-relaxed">No limits on how many videos or
                            photos you can
                            download. Completely free.</p>
                    </div>
                </div>
            </section>
            <!-- Detailed Feature Cards -->
            <section id="detailed-features" class="mt-32">
                <h2 class="section-header-blue">MySeoFan.app features</h2>
                <p class="text-center text-slate-700 font-medium text-sm mb-12 max-w-2xl mx-auto">
                    With MySeoFan you can download any type of content from Instagram. Our service has an IG video
                    downloader, Reels, IGTV, photo or carousel.
                </p>

                <div class="space-y-8">
                    <!-- Video Downloader -->
                    <div class="feature-detail-card">
                        <div class="feature-detail-content">
                            <h3 class="feature-detail-title">Video Downloader</h3>
                            <p class="feature-detail-text">MySeoFan.app supports Instagram video download for singular
                                videos and multiple videos from carousel. MySeoFan is created to enable you to download
                                IG
                                videos from your personal page.</p>
                        </div>
                        <div class="feature-detail-visual">
                            <img src="images/video-feature.png" alt="Video Downloader">
                        </div>
                    </div>

                    <!-- Photos Downloader -->
                    <div class="feature-detail-card">
                        <div class="feature-detail-content">
                            <h3 class="feature-detail-title">Photos Downloader</h3>
                            <p class="feature-detail-text">Instagram photo download provided by MySeoFan.app is a great
                                tool for saving images from Instagram posts. With MySeoFan, you can download a single
                                post
                                image and multiple Instagram photos (carousel).</p>
                        </div>
                        <div class="feature-detail-visual">
                            <img src="images/photo-feature.png" alt="Photos Downloader">
                        </div>
                    </div>

                    <!-- Reels Downloader -->
                    <div class="feature-detail-card">
                        <div class="feature-detail-content">
                            <h3 class="feature-detail-title">Reels Downloader</h3>
                            <p class="feature-detail-text">Reels is a new video format that clones the principle of
                                TikTok. Instagram Reels download with the help of MySeoFan. Our Instagram Reels
                                downloader
                                can help you to save your favorite Reels videos.</p>
                        </div>
                        <div class="feature-detail-visual">
                            <img src="images/reels-feature.png" alt="Reels Downloader">
                        </div>
                    </div>

                    <!-- IGTV Downloader -->
                    <div class="feature-detail-card">
                        <div class="feature-detail-content">
                            <h3 class="feature-detail-title">IGTV Downloader</h3>
                            <p class="feature-detail-text">IGTV is a long video type. If you can't watch it now, you can
                                download IGTV videos to your device to be sure that you can return to watching later,
                                without the need to be online or in case the IGTV can be deleted.</p>
                        </div>
                        <div class="feature-detail-visual">
                            <img src="images/igtv-feature.png" alt="IGTV Downloader">
                        </div>
                    </div>

                    <!-- Carousel Downloader -->
                    <div class="feature-detail-card">
                        <div class="feature-detail-content">
                            <h3 class="feature-detail-title">Carousel / Album Downloader</h3>
                            <p class="feature-detail-text">Carousel, also known as Album or Gallery posts type with
                                multiple photos, videos, or mixed content. If you need to download multiple photos from
                                Instagram, the MySeoFan.app is the best to download gallery.</p>
                        </div>
                        <div class="feature-detail-visual">
                            <img src="images/carousel-feature.png" alt="Carousel Downloader">
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- FAQ -->
    <section id="faq" class="py-24 bg-slate-100">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="section-header-blue">Frequently asked questions (FAQ)</h2>
            <div class="faq-list mt-12">
                <!-- FAQ items remain static as they are part of the demo1 design -->
                <div class="faq-item">
                    <span class="faq-question">What exactly is MySeoFan.app?</span>
                    <div class="faq-answer">
                        MySeoFan.app is a browser-based helper that lets you save public Instagram content - videos,
                        photos, Reels, Stories, IGTV and carousels - to your own device for offline viewing. It works
                        straight from the website; no software install is required.
                    </div>
                </div>
                <div class="faq-item">
                    <span class="faq-question">Is downloading from Instagram legal?</span>
                    <div class="faq-answer">
                        Saving public posts for personal use is generally allowed, but copyright always belongs to the
                        creator. Please keep the files private unless you have the owner's permission to share or reuse
                        them.
                    </div>
                </div>
                <div class="faq-item">
                    <span class="faq-question">Do I need to log in or create an account?</span>
                    <div class="faq-answer">
                        No. Just paste the Instagram link - there's no registration, no Instagram credentials, and no
                        cookies that track you across the web.
                    </div>
                </div>
                <div class="faq-item">
                    <span class="faq-question">Can I grab content from private accounts?</span>
                    <div class="faq-answer">
                        Sorry, no. We respect user privacy, so only public posts are accessible. If you can't view the
                        post in a logged-out browser tab, MySeoFan.app can't fetch it either.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-16 bg-white border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-6">
            <div class="footer-brand">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-10 w-auto" alt="Logo">
                <?php else: ?>
                    <i data-lucide="layers" class="footer-logo-icon"></i>
                <?php endif; ?>
                <span
                    class="footer-logo-text"><?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?></span>
            </div>

            <?php foreach ($footerItems as $group): ?>
                <div class="footer-links-group">
                    <?php if (isset($group['children']) && !empty($group['children'])): ?>
                        <?php foreach ($group['children'] as $index => $item): ?>
                            <a href="<?php echo htmlspecialchars($item['final_url']); ?>" class="footer-link">
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                            <?php if ($index < count($group['children']) - 1): ?>
                                <span class="text-slate-200 px-1">|</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="footer-divider"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-400 uppercase tracking-widest">follow us:</span>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="youtube" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="twitter" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-600 transition-colors"><i
                                data-lucide="music-2" class="w-5 h-5"></i></a>
                    </div>
                </div>
                <p class="copyright-text">© 2020-2026
                    <?php echo htmlspecialchars($settings['site_name'] ?: 'MySeoFan'); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="js/app.js"></script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>