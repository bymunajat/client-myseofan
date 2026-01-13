<?php
require_once __DIR__ . '/../admin/includes/db.php';

$data = [
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

echo "Seeding translations...\n";

foreach ($data as $lang => $translations) {
    $count = 0;
    foreach ($translations as $key => $val) {
        $stmt = $pdo->prepare("INSERT INTO translations (lang_code, t_key, t_value) VALUES (?, ?, ?) 
                               ON CONFLICT(lang_code, t_key) DO UPDATE SET t_value = excluded.t_value");
        $stmt->execute([$lang, $key, $val]);
        $count++;
    }
    echo "[$lang] Inserted/Updated $count keys.\n";
}

echo "Done!\n";
?>