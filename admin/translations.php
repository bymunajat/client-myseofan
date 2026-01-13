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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 25%, #6ee7b7 50%, #86efac 100%);
            min-height: 100vh;
        }

        .page-active {
            background: #059669;
            color: white;
            border-color: #059669;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header
            class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm sticky top-0 z-50">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Site UI Translations</h1>
                <p class="text-xs text-gray-500 mt-0.5">Manage multi-language content strings</p>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($active_lang !== 'en'): ?>
                    <form action="" method="POST"
                        onsubmit="return confirm('Use English values to fill all empty fields for this language?');">
                        <input type="hidden" name="action" value="autofill">
                        <input type="hidden" name="lang_code" value="<?php echo $active_lang; ?>">
                        <button type="submit"
                            class="text-sm bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold hover:bg-emerald-50 hover:text-emerald-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Auto-Fill Missing
                        </button>
                    </form>
                <?php endif; ?>

                <a href="?seed=1"
                    onclick="return confirm('WARNING: This will reset translations to the default values. Are you sure?')"
                    class="text-sm bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-emerald-600 transition-all shadow-lg hover:shadow-emerald-200">
                    Reset DB Keys
                </a>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div
                    class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium border border-emerald-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Language Tabs -->
            <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <?php foreach ($supported_langs as $code => $info): ?>
                    <a href="?lang=<?php echo $code; ?>"
                        class="px-5 py-3 rounded-xl font-bold transition-all flex items-center gap-2 <?php echo $active_lang === $code ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'text-gray-500 hover:bg-gray-50'; ?> text-sm">
                        <span class="text-base"><?php echo $info['flag']; ?></span>
                        <span><?php echo $info['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Translating: <span
                            class="text-black"><?php echo $supported_langs[$active_lang]['label']; ?></span>
                    </h3>

                    <!-- PAGINATION -->
                    <?php if ($_total_p > 1): ?>
                        <div class="flex gap-2">
                            <a href="?lang=<?php echo $active_lang; ?>&p=<?php echo max(1, $_curr_p - 1); ?>"
                                class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition-all <?php echo $_curr_p > 1 ? 'bg-white border-gray-200 text-gray-600 hover:bg-emerald-50' : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                            <span class="flex items-center text-xs font-bold text-gray-400">
                                <?php echo $_curr_p; ?> / <?php echo $_total_p; ?>
                            </span>
                            <a href="?lang=<?php echo $active_lang; ?>&p=<?php echo min($_total_p, $_curr_p + 1); ?>"
                                class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition-all <?php echo $_curr_p < $_total_p ? 'bg-white border-gray-200 text-gray-600 hover:bg-emerald-50' : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="divide-y divide-gray-50">
                    <?php foreach ($current_page_keys as $key): ?>
                        <div class="group hover:bg-gray-50/50 transition-all p-6 grid grid-cols-12 gap-6 items-start">
                            <!-- Label Column -->
                            <div class="col-span-12 md:col-span-3">
                                <div class="inline-block">
                                    <code
                                        class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100"><?php echo $key; ?></code>
                                </div>
                                <?php if ($active_lang !== 'en'): ?>
                                    <div class="mt-2 text-xs text-gray-400 italic">
                                        "<?php echo htmlspecialchars($en_translations[$key] ?? '...'); ?>"
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Input Column -->
                            <div class="col-span-12 md:col-span-9">
                                <form action="?lang=<?php echo $active_lang; ?>&p=<?php echo $_curr_p; ?>" method="POST"
                                    class="relative">
                                    <input type="hidden" name="lang_code" value="<?php echo $active_lang; ?>">
                                    <input type="hidden" name="t_key" value="<?php echo $key; ?>">
                                    <input type="hidden" name="p_redirect" value="<?php echo $_curr_p; ?>">

                                    <textarea name="t_value" rows="1" placeholder="Enter translation..."
                                        class="w-full px-5 py-3 rounded-xl border border-gray-200 bg-white focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 outline-none text-sm transition-all resize-none shadow-sm font-semibold text-gray-800"
                                        oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'><?php echo htmlspecialchars($current_translations[$key] ?? ''); ?></textarea>

                                    <button type="submit"
                                        class="absolute right-2 bottom-2 bg-emerald-100 text-emerald-700 p-1.5 rounded-lg hover:bg-emerald-600 hover:text-white transition-all opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('textarea').forEach(el => {
            el.style.height = (el.scrollHeight > 40 ? el.scrollHeight : 48) + "px";
        });
    </script>
</body>

</html>