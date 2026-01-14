<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

// 1. Initialize State
$lang = $_GET['lang'] ?? 'en';
$pageIdentifier = 'carousel';

// 2. Fetch Data
$settings = getSiteSettings($pdo);
$translations = getTranslations($pdo, $lang);

// Helper function for auto-translation
function __($text, $lang)
{
    return Translator::translate($text, $lang);
}

// 3. Fallback Translations
$defaults = [
    'en' => [
        'title' => 'Instagram Carousel Downloader - Download Multiple Photos/Videos',
        'hero_title' => 'Instagram Carousel Downloader',
        'subtitle' => 'Download all photos and videos from an Instagram Carousel post.',
        'placeholder' => 'Paste Instagram Carousel URL here',
        'btn_download' => 'Download',
        'btn_paste' => 'Paste',
        'intro_title' => 'Download Complete Albums',
        'intro_desc' => 'Instagram Carousels allow users to post multiple photos or videos in a single swipeable post. MySeoFan lets you download the entire album at once, ensuring you don\'t miss a single moment.',
        'how_to_title' => 'How to download Carousels from Instagram?',
        'how_to_subtitle' => 'Save entire albums in just a few clicks.',
        'step1_title' => '01. Copy Carousel Link',
        'step1_desc' => 'Open the Instagram post and copy the link from the menu.',
        'step2_title' => '02. Paste Link',
        'step2_desc' => 'Paste the link into the input box above and tap Download.',
        'step3_title' => '03. Save Media',
        'step3_desc' => 'All photos and videos will appear below. Download them individually.',
        'features_title' => 'Best Carousel Downloader',
        'features_subtitle' => 'The most efficient way to save multi-slide posts.',
        'feat1_t' => 'All Media Types',
        'feat1_d' => 'Supports mixed posts with both photos and videos.',
        'feat2_t' => 'Full Quality',
        'feat2_d' => 'Downloads every slide in original high resolution.',
        'feat3_t' => 'Easy Selection',
        'feat3_d' => 'Preview all slides before downloading.',
        'feat4_t' => 'Free',
        'feat4_d' => 'No cost to download full albums.',
        'status_fetching' => 'Fetching Carousel...',
        // FAQ
        'faq_title' => 'Frequently Asked Questions (FAQ)',
        'faq_q1' => 'Can I download all slides at once?',
        'faq_a1' => 'Currently, you must download each item individually, but we fetch them all for you.',
        'faq_q2' => 'Does it work with mixed content?',
        'faq_a2' => 'Yes, if a carousel has both photos and videos, we support both.',
        'faq_q3' => 'Is there a limit on slides?',
        'faq_a3' => 'No, we show all slides contained in the post (up to 10-20).',
        'faq_q4' => 'Do I need to login?',
        'faq_a4' => 'No, you do not need an Instagram account to use our tool.',
    ],
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
        /* Reusing exact CSS from index.php for consistency */
        :root {
            --hero-gradient: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a !important;
            color: #94a3b8;
            scroll-behavior: smooth;
        }

        .hero-section {
            background: rgb(15, 23, 42);
            position: relative;
            min-height: 550px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 100px;
            padding-bottom: 120px;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .glass-header {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-text {
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tool-bar {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            border: 2px solid rgba(139, 92, 246, 0.3);
            overflow: hidden;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15);
        }

        .tool-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            color: #cbd5e1;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            border-right: 1px solid rgba(139, 92, 246, 0.2);
        }

        .tool-item:last-child {
            border-right: none;
        }

        .tool-item:hover,
        .tool-item.active {
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            color: #ffffff;
            font-weight: 600;
            transform: translateY(-2px);
        }

        .input-container {
            background: #ffffff;
            border-radius: 9999px;
            padding: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            animation: shadow-pulse 3s ease-in-out infinite;
        }

        @keyframes shadow-pulse {

            0%,
            100% {
                box-shadow: 0 20px 60px rgba(139, 92, 246, 0.6),
                    0 0 40px rgba(139, 92, 246, 0.4),
                    0 0 80px rgba(139, 92, 246, 0.2);
            }

            33% {
                box-shadow: 0 20px 60px rgba(236, 72, 153, 0.6),
                    0 0 40px rgba(236, 72, 153, 0.4),
                    0 0 80px rgba(236, 72, 153, 0.2);
            }

            66% {
                box-shadow: 0 20px 60px rgba(245, 158, 11, 0.6),
                    0 0 40px rgba(245, 158, 11, 0.4),
                    0 0 80px rgba(245, 158, 11, 0.2);
            }
        }

        .input-container::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            padding: 4px;
            background: linear-gradient(135deg, #8b5cf6, #ec4899, #f59e0b, #8b5cf6);
            background-size: 300% 300%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: gradient-rotate 3s ease infinite;
            pointer-events: none;
            filter: brightness(1.3) blur(0.5px);
        }

        @keyframes gradient-rotate {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .input-field {
            flex: 1;
            padding: 16px 24px;
            border: none;
            outline: none;
            font-size: 1.1rem;
            color: #0f172a;
            background: transparent;
            font-weight: 600;
            border-radius: 9999px;
        }

        .input-field::placeholder {
            color: #64748b;
            font-weight: 500;
            opacity: 1;
        }

        .btn-paste {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            color: #ffffff;
            font-weight: 700;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-paste:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .btn-download {
            padding: 14px 32px;
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            color: #ffffff;
            font-weight: 700;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(139, 92, 246, 0.4);
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

        /* New Styled Components for Premium Look */
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

        .step-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: #e2e8f0;
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
            background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-logo-icon {
            color: #a855f7;
            width: 36px;
            height: 36px;
        }

        .feature-detail-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            display: grid;
            gap: 40px;
            border: 1px solid #e2e8f0;
        }

        @media (min-width: 768px) {
            .feature-detail-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        .intro-card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
            display: flex;
            margin-bottom: 80px;
            border: 1px solid #e2e8f0;
        }

        .intro-visual {
            background: linear-gradient(135deg, #7c3aed 0%, #c026d3 50%, #db2777 100%);
            width: 300px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .intro-content {
            padding: 40px;
            flex: 1;
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

        /* High Contrast Text for Light Sections */
        .intro-card,
        #how-to,
        #features,
        #detailed-features,
        #faq,
        #blog {
            color: #0f172a;
        }

        .intro-card p,
        .step-desc,
        .feature-card-modern p,
        .feature-detail-text,
        .faq-answer,
        .text-slate-700 {
            color: #0f172a !important;
            font-weight: 600 !important;
        }

        .intro-card h2,
        #how-to h3,
        #features h4,
        #detailed-features h3,
        #faq .faq-question,
        #blog h3,
        .step-title,
        .feature-detail-title {
            color: #0f172a !important;
            font-weight: 900 !important;
        }

        /* High Contrast Text for Light Sections - FINAL PRIORITY */
        .intro-card,
        #how-to,
        #features,
        #detailed-features,
        #blog {
            color: #000000 !important;
        }

        .intro-card .text-slate-700,
        #how-to .text-slate-700,
        #features .text-slate-700,
        #detailed-features .text-slate-700,
        #blog .text-slate-700,
        .feature-detail-text,
        .step-desc,
        .feature-card-modern p,
        .intro-card p,
        #features p,
        .feature-card-modern .font-medium {
            color: #000000 !important;
            font-weight: 600 !important;
            font-size: 1.1rem !important;
            line-height: 1.6;
        }

        .intro-card h2,
        #how-to h3,
        #features h4,
        #detailed-features h3,
        #blog h3,
        .step-title,
        .feature-detail-title {
            color: #000000 !important;
            font-weight: 800 !important;
        }

        /* FAQ Section - High Contrast Styling */
        #faq,
        .faq-item,
        .faq-answer {
            color: #ffffff !important;
        }

        .faq-question {
            font-weight: 700 !important;
            font-size: 1.25rem !important;
            color: #3b82f6 !important;
            /* Premium Blue */
            display: block;
            margin-bottom: 0.5rem;
        }

        .faq-answer {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
            line-height: 1.6;
        }

        /* Specific White Subtitles - PREMIUM CAPSULE LOOK */
        .features-subtitle-white {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.15rem !important;
            opacity: 1 !important;
            background: rgba(15, 23, 42, 0.9);
            padding: 12px 32px;
            border-radius: 9999px;
            display: inline-block;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        #features .features-subtitle-white,
        #how-to .features-subtitle-white,
        section#features p.features-subtitle-white,
        section#how-to p.features-subtitle-white {
            color: #ffffff !important;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen">
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

            <div class="flex items-center gap-6 text-white font-bold text-sm uppercase tracking-wider">
                <nav class="hidden md:flex items-center gap-6">
                    <?php foreach ($headerItems as $item): ?>
                        <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                            class="text-white hover:text-[#ec4899] transition-colors">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="relative group cursor-pointer">
                    <div class="flex items-center gap-1 text-white hover:text-[#ec4899] transition-colors uppercase">
                        <?php echo $lang; ?> <i data-lucide="chevron-down" class="w-4 h-4 text-white/50"></i>
                    </div>
                    <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-50">
                        <div class="w-32 bg-slate-900 shadow-2xl rounded-xl p-2 border border-slate-800">
                            <?php foreach (['en' => '🇺🇸 EN', 'id' => '🇮🇩 ID', 'es' => '🇪🇸 ES', 'fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'ja' => '🇯🇵 JA'] as $code => $label): ?>
                                <a href="?lang=<?php echo $code; ?>"
                                    class="block px-4 py-2 text-xs hover:bg-slate-800 rounded-lg <?php echo $lang === $code ? 'text-[#ec4899] font-bold' : 'text-slate-300'; ?>">
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
            <h1 class="section-title animate-fade-up"><?php echo $t['hero_title']; ?></h1>
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
            <div id="result" class="mt-12 max-w-2xl mx-auto"></div>
        </div>
    </section>

    <!-- Content Sections Wrapper -->
    <main class="py-20 bg-slate-200 flex-grow">
        <div class="max-w-6xl mx-auto px-6">

            <!-- Intro Card -->
            <div class="intro-card animate-fade-up">
                <div class="intro-visual">
                    <div class="bg-white/20 backdrop-blur-md p-6 rounded-2xl border border-white/30 text-white">
                        <i data-lucide="layers" class="w-16 h-16"></i>
                    </div>
                </div>
                <div class="intro-content">
                    <h2 class="text-2xl font-bold text-blue-600 mb-4"><?php echo $t['intro_title']; ?></h2>
                    <p class="text-slate-700 font-medium text-lg leading-relaxed">
                        <?php echo $t['intro_desc']; ?>
                    </p>
                </div>
            </div>

            <!-- How to Section (Redesigned) -->
            <section id="how-to" class="mb-32 animate-fade-up" style="animation-delay: 0.1s">
                <h2 class="section-header-blue"><?php echo $t['how_to_title']; ?></h2>
                <div class="text-center mb-12">
                    <p class="features-subtitle-white max-w-2xl mx-auto">
                        <?php echo $t['how_to_subtitle']; ?>
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="step-card step-1">
                        <div class="step-top">
                            <div class="step-visual-mockup">
                                instagram.com/p/CmA3...
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
                                <span>instagram.com/p/...</span>
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

            <!-- Features Section (Redesigned) -->
            <section id="features" class="animate-fade-up" style="animation-delay: 0.2s">
                <h2 class="section-header-blue"><?php echo $t['features_title']; ?></h2>
                <div class="text-center mb-12">
                    <p class="features-subtitle-white max-w-2xl mx-auto">
                        <?php echo $t['features_subtitle']; ?>
                    </p>
                </div>

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

            <!-- Detailed Features -->
            <section id="detailed-features" class="mt-32 animate-fade-up">
                <div class="feature-detail-card">
                    <div class="feature-detail-content">
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-4">
                            <?php echo __('Download Complete Galleries', $lang); ?>
                        </h3>
                        <p class="text-slate-700 font-medium leading-relaxed">
                            <?php echo __('Why download one by one? With our Carousel Downloader, you can view and save every image and video from a multi-post album. It\'s the perfect tool for backing up memories or saving improved content from your favorite creators.', $lang); ?>
                        </p>
                    </div>
                    <div class="flex items-center justify-center p-8 bg-slate-100 rounded-xl">
                        <img src="images/carousel-feature.png" alt="Carousel Features"
                            class="rounded-xl shadow-lg transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-slate-100 animate-fade-up">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="section-header-blue"><?php echo $t['faq_title']; ?></h2>
            <div class="mt-12 space-y-4">
                <div class="border border-slate-100 rounded-xl p-6 hover:shadow-lg transition-shadow bg-white">
                    <span class="text-blue-600 font-bold block mb-2 text-lg"><?php echo $t['faq_q1']; ?></span>
                    <div class="text-slate-700 font-medium leading-relaxed"><?php echo $t['faq_a1']; ?></div>
                </div>
                <div class="border border-slate-100 rounded-xl p-6 hover:shadow-lg transition-shadow bg-white">
                    <span class="text-blue-600 font-bold block mb-2 text-lg"><?php echo $t['faq_q2']; ?></span>
                    <div class="text-slate-700 font-medium leading-relaxed"><?php echo $t['faq_a2']; ?></div>
                </div>
                <div class="border border-slate-100 rounded-xl p-6 hover:shadow-lg transition-shadow bg-white">
                    <span class="text-blue-600 font-bold block mb-2 text-lg"><?php echo $t['faq_q3']; ?></span>
                    <div class="text-slate-700 font-medium leading-relaxed"><?php echo $t['faq_a3']; ?></div>
                </div>
                <div class="border border-slate-100 rounded-xl p-6 hover:shadow-lg transition-shadow bg-white">
                    <span class="text-blue-600 font-bold block mb-2 text-lg"><?php echo $t['faq_q4']; ?></span>
                    <div class="text-slate-700 font-medium leading-relaxed"><?php echo $t['faq_a4']; ?></div>
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

            <div class="flex flex-col items-center mt-8 mb-8">
                <?php foreach ($footerItems as $group): ?>
                    <div class="flex flex-wrap justify-center gap-6 mb-4">
                        <?php if (isset($group['children']) && !empty($group['children'])): ?>
                            <?php foreach ($group['children'] as $index => $item): ?>
                                <a href="<?php echo htmlspecialchars($item['final_url']); ?>"
                                    class="text-sm font-bold text-slate-700 hover:text-blue-600 transition-colors">
                                    <?php echo htmlspecialchars($item['label']); ?>
                                </a>
                                <?php if ($index < count($group['children']) - 1): ?>
                                    <span class="text-slate-200">|</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="w-full h-px bg-slate-200 my-8"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">follow us:</span>
                    <div class="flex gap-4">
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="youtube" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="twitter" class="w-5 h-5"></i></a>
                        <a href="#" class="text-slate-600 hover:text-blue-600 transition-colors"><i
                                data-lucide="music-2" class="w-5 h-5"></i></a>
                    </div>
                </div>
                <p class="text-slate-600 font-bold text-sm">© <?php echo date('Y'); ?>
                    <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="js/app.js?v=1.1"></script>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>