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
        'heading' => "Instagram Downloader",
        'subtitle' => 'Download Instagram Videos, Photos, Reels, IGTV & carousel',
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
    'es' => [
        'title' => 'Descargador de Instagram',
        'home' => 'Inicio',
        'how' => 'Guías',
        'about' => 'Acerca de',
        'heading' => "Instagram Downloader",
        'subtitle' => 'Descarga Videos, Fotos, Reels, IGTV y carruseles de Instagram',
        'download' => 'Descargar',
        'paste' => 'Pegar',
        'feat1_t' => 'Ultrarrápido',
        'feat1_d' => 'Impulsado por servidores de primer nivel para entregar tus medios en segundos.',
        'feat2_t' => 'Privado y Seguro',
        'feat2_d' => 'Valoramos tu privacidad. Tus datos nunca se almacenan y no necesitas una cuenta.',
        'feat3_t' => 'Calidad HD',
        'feat3_d' => 'Descarga siempre la resolución más alta disponible para Fotos y Reels.',
        'guide_t' => 'Guía Simple de 3 Pasos',
        'guide1_t' => 'Copiar URL',
        'guide1_d' => 'Abre Instagram y copia la URL del navegador o del menú compartir.',
        'guide2_t' => 'Pegar y Procesar',
        'guide2_d' => 'Pega el enlace arriba y nuestro sistema comenzará a buscar la fuente inmediatamente.',
        'guide3_t' => 'Disfrutar Offline',
        'guide3_d' => 'Presiona descargar para guardar el archivo al instante en tu móvil o PC.',
        'faq_t' => 'Preguntas Frecuentes',
        'q1' => '¿Es gratis?',
        'a1' => 'Sí, nuestro servicio es 100% gratuito y siempre lo será.',
        'q2' => '¿Puedo descargar cuentas privadas?',
        'a2' => 'Actualmente solo soportamos cuentas públicas por seguridad.',
        'q3' => '¿Qué dispositivos son compatibles?',
        'a3' => 'Funciona en iPhone, Android y PC.',
        'about_t' => 'Nuestra Misión',
        'about_d' => 'Creemos que el archivado de contenido debe ser fácil y accesible.'
    ],
    'fr' => [
        'title' => 'Téléchargeur Instagram',
        'home' => 'Accueil',
        'how' => 'Guides',
        'about' => 'À propos',
        'heading' => "Instagram Downloader",
        'subtitle' => 'Téléchargez Vidéos, Photos, Reels, IGTV et carrousels Instagram',
        'download' => 'Télécharger',
        'paste' => 'Coller',
        'feat1_t' => 'Ultra Rapide',
        'feat1_d' => 'Propulsé par une infrastructure de pointe pour livrer vos médias en quelques secondes.',
        'feat2_t' => 'Privé & Sécurisé',
        'feat2_d' => 'Vos données ne sont jamais stockées et aucun compte n\'est nécessaire.',
        'feat3_t' => 'Qualité HD',
        'feat3_d' => 'Téléchargez toujours la plus haute résolution disponible.',
        'guide_t' => 'Guide en 3 Étapes',
        'guide1_t' => 'Copier l\'URL',
        'guide1_d' => 'Ouvrez Instagram et copiez l\'URL depuis le navigateur ou le menu partage.',
        'guide2_t' => 'Coller & Traiter',
        'guide2_d' => 'Collez le lien ci-dessus pour récupérer la source immédiatement.',
        'guide3_t' => 'Profiter Hors-ligne',
        'guide3_d' => 'Cliquez sur télécharger pour sauvegarder le fichier sur votre appareil.',
        'faq_t' => 'Questions Fréquentes',
        'q1' => 'Est-ce gratuit ?',
        'a1' => 'Oui, notre service est 100% gratuit et le restera.',
        'q2' => 'Comptes privés supportés ?',
        'a2' => 'Nous supportons uniquement les comptes publics actuellement.',
        'q3' => 'Quels appareils ?',
        'a3' => 'Compatible avec iPhone, Android et PC.',
        'about_t' => 'Notre Mission',
        'about_d' => 'Nous croyons que l\'archivage de contenu doit être accessible à tous.'
    ],
    'de' => [
        'title' => 'Instagram Downloader',
        'home' => 'Startseite',
        'how' => 'Anleitungen',
        'about' => 'Über uns',
        'heading' => "Instagram Downloader",
        'subtitle' => 'Instagram Videos, Fotos, Reels, IGTV & Karussells herunterladen',
        'download' => 'Laden',
        'paste' => 'Einfügen',
        'feat1_t' => 'Blitzschnell',
        'feat1_d' => 'Angetrieben von Top-Servern, um Ihre Medien in Sekunden zu liefern.',
        'feat2_t' => 'Privat & Sicher',
        'feat2_d' => 'Wir speichern keine Daten und Sie benötigen kein Konto.',
        'feat3_t' => 'HD Qualität',
        'feat3_d' => 'Laden Sie immer die höchste verfügbare Auflösung herunter.',
        'guide_t' => 'Einfache 3-Schritte Anleitung',
        'guide1_t' => 'URL Kopieren',
        'guide1_d' => 'Öffnen Sie Instagram und kopieren Sie die URL.',
        'guide2_t' => 'Einfügen & Starten',
        'guide2_d' => 'Fügen Sie den Link oben ein, um die Quelle abzurufen.',
        'guide3_t' => 'Offline Genießen',
        'guide3_d' => 'Klicken Sie auf Herunterladen, um die Datei zu speichern.',
        'faq_t' => 'Häufige Fragen',
        'q1' => 'Ist das kostenlos?',
        'a1' => 'Ja, unser Service ist zu 100% kostenlos.',
        'q2' => 'Private Konten?',
        'a2' => 'Wir unterstützen derzeit nur öffentliche Konten.',
        'q3' => 'Welche Geräte?',
        'a3' => 'Funktioniert auf iPhone, Android und PC.',
        'about_t' => 'Unsere Mission',
        'about_d' => 'Wir glauben, dass die Archivierung von Inhalten einfach sein sollte.'
    ],
    'ja' => [
        'title' => 'Instagram ダウンローダー',
        'home' => 'ホーム',
        'how' => 'ガイド',
        'about' => '情報',
        'heading' => "Instagram Downloader",
        'subtitle' => 'Instagramの動画、写真、リール、IGTV、カルーセルをダウンロード',
        'download' => 'ダウンロード',
        'paste' => '貼り付け',
        'feat1_t' => '超高速',
        'feat1_d' => '最高レベルのサーバーインフラで、数秒でメディアをお届けします。',
        'feat2_t' => '安心・安全',
        'feat2_d' => 'プライバシーを重視しており、データを保存せず、アカウントも不要です。',
        'feat2_v' => 'HD画質',
        'feat3_t' => 'HD画質',
        'feat3_d' => '常に利用可能な最高解像度でダウンロードできます。',
        'guide_t' => '簡単3ステップガイド',
        'guide1_t' => 'URLをコピー',
        'guide1_d' => 'Instagramを開き、ブラウザや共有メニューからURLをコピーします。',
        'guide2_t' => '貼り付けて処理',
        'guide2_d' => '上の入力欄にリンクを貼り付けると、システムが取得を開始します。',
        'guide3_t' => '保存',
        'guide3_d' => 'ダウンロードボタンを押して、スマホやPCにファイルを保存します。',
        'faq_t' => 'よくある質問',
        'q1' => '無料ですか？',
        'a1' => 'はい、100%完全無料です。',
        'q2' => '非公開アカウントは？',
        'a2' => '現在は公開アカウントのみ対応しています。',
        'q3' => '対応デバイスは？',
        'a3' => 'iPhone、Android、PCすべてに対応しています。',
        'about_t' => '私たちの使命',
        'about_d' => 'コンテンツのアーカイブを誰でも簡単に利用できるようにすることです。'
    ]
];

// Seed if requested
if (isset($_GET['seed'])) {
    foreach ($defaults as $lang => $data) { // Loop through all defaults
        foreach ($data as $key => $val) {
            $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) VALUES (?, ?, ?) 
                                   ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
            $stmt->execute([$lang, $key, $val]);
        }
    }
    $message = 'Database restored with full language pack (ES, FR, DE, JA)!';
}

// Handle Auto-Fill Logic
if (isset($_POST['action']) && $_POST['action'] === 'autofill') {
    $target_lang = $_POST['lang_code'];
    try {
        // Get all English keys
        $stmt = $pdo->query("SELECT t_key, t_value FROM translations WHERE lang_code = 'en'");
        $en_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $count = 0;
        foreach ($en_data as $key => $en_val) {
            // Check if exists in target
            $check = $pdo->prepare("SELECT 1 FROM translations WHERE lang_code = ? AND t_key = ?");
            $check->execute([$target_lang, $key]);

            if (!$check->fetch()) {
                // Insert English value as placeholder
                $ins = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) VALUES (?, ?, ?)");
                $ins->execute([$target_lang, $key, $en_val]);
                $count++;
            }
        }
        $message = "Auto-filled $count missing translations with English values.";
    } catch (\Exception $e) {
        $error = "Auto-fill failed: " . $e->getMessage();
    }
}

// Handle single update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $lang = $_POST['lang_code'];
    $key = $_POST['t_key'];
    $value = $_POST['t_value'];
    $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) 
                           VALUES (?, ?, ?) 
                           ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
    if ($stmt->execute([$lang, $key, $value])) {
        $message = "Saved!";
    }
}

// State
$active_lang = $_GET['lang'] ?? 'en';
$supported_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

if (!array_key_exists($active_lang, $supported_langs))
    $active_lang = 'en';

// Pagination
$stmt = $pdo->prepare("SELECT t_key FROM translations WHERE lang_code = 'en'");
$stmt->execute();
$all_keys = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fallback if DB empty
if (empty($all_keys))
    $all_keys = array_keys($defaults['en']);

$_items_per_page = 15;
$_total_items = count($all_keys);
$_total_p = ceil($_total_items / $_items_per_page);
$_curr_p = (int) ($_GET['p'] ?? 1);
if ($_curr_p < 1)
    $_curr_p = 1;
if ($_curr_p > $_total_p)
    $_curr_p = $_total_p;
$_offset = ($_curr_p - 1) * $_items_per_page;
$current_page_keys = array_slice($all_keys, $_offset, $_items_per_page);

// Data
// Fetch TARGET translations for current keys
if (!empty($current_page_keys)) {
    $placeholders = str_repeat('?,', count($current_page_keys) - 1) . '?';
    $sql = "SELECT t_key, t_value FROM translations WHERE lang_code = ? AND t_key IN ($placeholders)";
    $params = array_merge([$active_lang], $current_page_keys);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $current_translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Also fetch English for reference if not English
    $en_translations = [];
    if ($active_lang !== 'en') {
        $sql_en = "SELECT t_key, t_value FROM translations WHERE lang_code = 'en' AND t_key IN ($placeholders)";
        $params_en = $current_page_keys; // Corrected: removed 'en'
        $stmt_en = $pdo->prepare($sql_en);
        $stmt_en->execute($params_en);
        $en_translations = $stmt_en->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} else {
    $current_translations = [];
    $en_translations = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Global Translations - MySeoFan Admin</title>
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
    <link rel="stylesheet" href="../AdminLTE/dist/css/adminlte.css">

    <style>
        .page-active {
            font-weight: bold;
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
                            <h3 class="mb-0">Site UI Translations</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Translations</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <?php if ($active_lang !== 'en'): ?>
                            <form action="" method="POST"
                                onsubmit="return confirm('Use English values to fill all empty fields for this language?');">
                                <input type="hidden" name="action" value="autofill">
                                <input type="hidden" name="lang_code" value="<?php echo $active_lang; ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-magic me-1"></i> Auto-Fill Missing
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="?seed=1"
                            onclick="return confirm('WARNING: This will reset translations to the default values. Are you sure?')"
                            class="btn btn-danger btn-sm">
                            <i class="bi bi-database-fill-exclamation me-1"></i> Reset DB Keys
                        </a>
                    </div>

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
                            <h3 class="card-title"><i class="bi bi-info-circle me-1"></i> Multi-Language Manager</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <p class="mb-2">This feature allows you to manage translations for all text displayed on
                                your website.</p>
                            <ul>
                                <li><strong>English</strong> is the master language.</li>
                                <li>Other languages will <strong>auto-translate</strong> if left empty (fallback logic
                                    depending on implementation).</li>
                                <li>Manually override auto-translations by entering custom text.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Language Tabs -->
                    <div class="mb-4">
                        <ul class="nav nav-pills">
                            <?php foreach ($supported_langs as $code => $info): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_lang === $code ? 'active' : ''; ?>"
                                        href="?lang=<?php echo $code; ?>">
                                        <span class="me-1"><?php echo $info['flag']; ?></span> <?php echo $info['label']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Main Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                Translation Keys - <?php echo $supported_langs[$active_lang]['label']; ?>
                                <small class="text-muted ms-2">(Showing <?php echo count($current_page_keys); ?> of
                                    <?php echo count($all_keys); ?>)</small>
                            </h3>

                            <!-- Pagination -->
                            <?php if ($_total_p > 1): ?>
                                <nav aria-label="Page navigation" class="card-tools ms-auto">
                                    <ul class="pagination pagination-sm m-0">
                                        <li class="page-item <?php echo $_curr_p <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?lang=<?php echo $active_lang; ?>&p=<?php echo max(1, $_curr_p - 1); ?>">&laquo;</a>
                                        </li>
                                        <li class="page-item disabled"><span class="page-link"><?php echo $_curr_p; ?> /
                                                <?php echo $_total_p; ?></span></li>
                                        <li class="page-item <?php echo $_curr_p >= $_total_p ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?lang=<?php echo $active_lang; ?>&p=<?php echo min($_total_p, $_curr_p + 1); ?>">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-0">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Key</th>
                                        <th style="width: 70%;">Translation</th>
                                        <th style="width: 10%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($current_page_keys as $key): ?>
                                        <tr>
                                            <td class="align-top">
                                                <code><?php echo $key; ?></code>
                                                <?php if ($active_lang !== 'en'): ?>
                                                    <div class="text-muted small mt-1">
                                                        <em>"<?php echo htmlspecialchars($en_translations[$key] ?? '...'); ?>"</em>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form action="?lang=<?php echo $active_lang; ?>&p=<?php echo $_curr_p; ?>"
                                                    method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="lang_code"
                                                        value="<?php echo $active_lang; ?>">
                                                    <input type="hidden" name="t_key" value="<?php echo $key; ?>">
                                                    <input type="hidden" name="p_redirect" value="<?php echo $_curr_p; ?>">

                                                    <textarea name="t_value" rows="1" class="form-control"
                                                        placeholder="Enter translation..."
                                                        oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'><?php echo htmlspecialchars($current_translations[$key] ?? ''); ?></textarea>
                                                    <button type="submit" class="btn btn-primary btn-sm align-self-start">
                                                        <i class="bi bi-save"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>
    <script>
        document.querySelectorAll('textarea').forEach(el => {
            el.style.height = (el.scrollHeight > 38 ? el.scrollHeight : 38) + "px";
        });
    </script>
</body>

</html>