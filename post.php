<?php
require_once 'includes/db.php';
require_once 'includes/SEO_Helper.php';
require_once 'includes/Translator.php';

$slug = $_GET['slug'] ?? '';
$lang = $_GET['lang'] ?? 'en';

// Fetch post (Try requested language first)
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND lang_code = ?");
$stmt->execute([$slug, $lang]);
$post = $stmt->fetch();

// If not found, try English version to auto-translate
if (!$post && $lang !== 'en') {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND lang_code = 'en'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if ($post) {
        $post['title'] = Translator::translate($post['title'], $lang);
        $post['content'] = Translator::translate($post['content'], $lang);
        $post['lang_code'] = $lang;
    }
}

if (!$post) {
    header("Location: blog.php?lang=$lang");
    exit;
}

$settings = getSiteSettings($pdo);

// Helper function for auto-translation
function __($text, $lang)
{
    return Translator::translate($text, $lang);
}


// 3. Fallback Translations (Expanded for 6 Languages)
$defaults = [
    'en' => [
        'back' => 'Back to Insights',
        'home' => 'Home',
        'blog' => 'Blog'
    ],
    'id' => [
        'back' => 'Kembali ke Wawasan',
        'home' => 'Beranda',
        'blog' => 'Blog'
    ],
    'es' => [
        'back' => 'Volver al Blog',
        'home' => 'Inicio',
        'blog' => 'Blog'
    ],
    'fr' => [
        'back' => 'Retour au Blog',
        'home' => 'Accueil',
        'blog' => 'Blog'
    ],
    'de' => [
        'back' => 'Zurück zum Blog',
        'home' => 'Startseite',
        'blog' => 'Blog'
    ],
    'ja' => [
        'back' => 'ブログに戻る',
        'home' => 'ホーム',
        'blog' => 'ブログ'
    ]
];

// Merge with defaults (EN as primary fallback)
$t = array_merge($defaults['en'], $defaults[$lang] ?? [], $translations);

$seoHelper = new SEO_Helper($pdo, 'post', $lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $post['lang_code']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seoHelper->getTitle(); ?></title>
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
            background: #fff;
            color: #1f2937;
            line-height: 1.8;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .hero-title {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Premium Article Typography */
        .article-content h2 {
            font-size: 2.25rem;
            font-weight: 900;
            margin-top: 4rem;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
            color: #111827;
        }

        .article-content p {
            margin-bottom: 1.75rem;
            font-size: 1.25rem;
            color: #374151;
            font-weight: 400;
        }

        .article-content ul {
            margin-bottom: 2rem;
            list-style-type: disc;
            padding-left: 1.5rem;
        }

        .article-content li {
            margin-bottom: 0.75rem;
            font-size: 1.125rem;
        }
    </style>
    <?php echo $settings['header_code'] ?? ''; ?>
</head>

<body class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-50 glass-header">
        <div class="max-w-4xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php?lang=<?php echo $lang; ?>" class="flex items-center gap-3 group">
                <div class="flex items-center">
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" class="h-8 w-auto"
                            alt="<?php echo htmlspecialchars($settings['site_name']); ?>">
                    <?php else: ?>
                        <div
                            class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-200">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2.5"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <span
                            class="ml-2 text-lg font-black tracking-tighter text-gray-800"><?php echo htmlspecialchars($settings['site_name']); ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <a href="blog.php?lang=<?php echo $lang; ?>"
                class="text-sm font-bold text-gray-500 hover:text-emerald-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="3" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo $t['back']; ?>
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-3xl mx-auto px-6 py-20">
        <article>
            <header class="mb-16">
                <div
                    class="flex items-center gap-4 mb-6 text-xs font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl w-fit">
                    <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                </div>
                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-[1.1] mb-12">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                <?php if ($post['thumbnail']): ?>
                    <div
                        class="relative group rounded-[3rem] overflow-hidden shadow-2xl shadow-emerald-900/10 mb-16 border-8 border-white">
                        <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>" class="w-full h-auto"
                            alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                <?php endif; ?>
            </header>

            <div class="article-content">
                <?php echo $post['content']; // HTML allowed from Admin ?>
            </div>
        </article>
    </main>

    <footer class="bg-gray-50 border-t border-gray-100 py-12 mt-20">
        <div class="max-w-4xl mx-auto px-6 flex flex-col items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-gray-900 shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h4 class="text-xl font-black hero-title">MySeoFan</h4>
            </div>
            <p class="text-gray-400 font-medium text-sm">&copy; <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['site_name'] ?? 'MySeoFan'); ?>.
            </p>
        </div>
    </footer>
    <?php echo $settings['footer_code'] ?? ''; ?>
</body>

</html>