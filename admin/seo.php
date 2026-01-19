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
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous">
    <!-- OVerlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.css">
    <style>
        .seo-card {
            transition: all 0.3s ease;
        }

        .seo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include 'includes/header_lte.php'; ?>
        <?php include 'includes/sidebar_lte.php'; ?>

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">SEO Management</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">SEO Manager</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Info Card -->
                    <div class="card card-outline card-info collapsed-card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-info-circle me-2"></i> SEO Meta Tags Manager - How It Works
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <p class="text-muted">
                                This feature allows you to optimize your website's search engine visibility by
                                customizing meta
                                tags for each <strong>static page</strong> (Home, Video, Reels, Photo, IGTV, Carousel,
                                etc.).
                                Proper SEO meta tags help search engines understand your content and improve your
                                rankings in
                                search results.
                            </p>

                            <div class="callout callout-info">
                                <h5>🌍 Available Languages:</h5>
                                <p>
                                    <?php foreach ($available_langs as $code => $info): ?>
                                        <span class="badge text-bg-light border me-1"><?php echo $info['flag']; ?>
                                            <?php echo $info['label']; ?></span>
                                    <?php endforeach; ?>
                                </p>
                            </div>

                            <div class="callout callout-warning">
                                <h5>💡 What you can manage:</h5>
                                <ul class="mb-0">
                                    <li><strong>Meta Title</strong> - The title that appears in search results and
                                        browser tabs (50-60 characters recommended)</li>
                                    <li><strong>Meta Description</strong> - A brief summary shown in search results
                                        (150-160 characters recommended)</li>
                                    <li><strong>OG Image</strong> - The image displayed when sharing on social media
                                        platforms</li>
                                    <li><strong>Static Pages Only</strong> - Manage SEO for Home, Video, Reels, Photo,
                                        IGTV, Carousel pages (not blog posts)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Language Filter -->
                    <div class="card mb-4">
                        <div class="card-body p-2">
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <?php foreach ($available_langs as $code => $info): ?>
                                    <a href="?filter_lang=<?php echo $code; ?>"
                                        class="btn <?php echo $_curr_lang === $code ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                        <span class="fs-5 me-1"><?php echo $info['flag']; ?></span>
                                        <?php echo $info['label']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach ($pages as $page):
                            // Map page to AdminLTE Card colors
                            $cardClass = 'card-default';
                            $iconClass = 'bi-file-earmark-text';

                            switch ($page) {
                                case 'index':
                                    $cardClass = 'card-primary';
                                    $iconClass = 'bi-house-door';
                                    break;
                                case 'video':
                                    $cardClass = 'card-info';
                                    $iconClass = 'bi-camera-video';
                                    break;
                                case 'reels':
                                    $cardClass = 'card-danger';
                                    $iconClass = 'bi-film';
                                    break;
                                case 'photo':
                                    $cardClass = 'card-warning';
                                    $iconClass = 'bi-image';
                                    break;
                                case 'igtv':
                                    $cardClass = 'card-indigo';
                                    $iconClass = 'bi-tv';
                                    break;
                                case 'carousel':
                                    $cardClass = 'card-purple'; // Not standard bootstrap, use custom or closest
                                    $iconClass = 'bi-images';
                                    $cardClass = 'card-secondary'; // Fallback
                                    break;
                                default:
                                    $cardClass = 'card-secondary';
                                    $iconClass = 'bi-file-earmark';
                            }

                            $code = $_curr_lang;
                            $info = $available_langs[$code];
                            $data = $seo_data[$page][$code] ?? [];
                            ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card <?php echo $cardClass; ?> card-outline h-100 seo-card">
                                    <form action="" method="POST" class="h-100 d-flex flex-column">
                                        <div class="card-header">
                                            <h3 class="card-title text-capitalize">
                                                <i class="bi <?php echo $iconClass; ?> me-2"></i> <?php echo $page; ?> Page
                                            </h3>
                                            <div class="card-tools">
                                                <span class="badge text-bg-light border">Static</span>
                                            </div>
                                        </div>
                                        <div class="card-body flex-grow-1">
                                            <input type="hidden" name="page_identifier" value="<?php echo $page; ?>">
                                            <input type="hidden" name="lang_code" value="<?php echo $code; ?>">

                                            <div class="d-flex align-items-center mb-3">
                                                <span class="badge text-bg-light border">
                                                    <?php echo $info['flag']; ?>     <?php echo $info['label']; ?>
                                                </span>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                    value="<?php echo htmlspecialchars($data['meta_title'] ?? ''); ?>"
                                                    placeholder="Page Title">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">OG Image URL</label>
                                                <input type="text" name="og_image" class="form-control"
                                                    value="<?php echo htmlspecialchars($data['og_image'] ?? ''); ?>"
                                                    placeholder="https://example.com/image.jpg">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Meta Description</label>
                                                <textarea name="meta_description" class="form-control" rows="3"
                                                    placeholder="Page summary..."><?php echo htmlspecialchars($data['meta_description'] ?? ''); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-uppercase">Schema
                                                    (JSON-LD)</label>
                                                <textarea name="schema_markup" class="form-control font-monospace" rows="2"
                                                    style="font-size: 0.8rem;"
                                                    placeholder="{...}"><?php echo htmlspecialchars($data['schema_markup'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>
</body>

</html>
