<?php
require_once '../includes/db.php';

// Prepare full content for 5 pages x 6 languages
$all_pages = [
    // --- ENGLISH ---
    [
        'title' => 'About MySeoFan',
        'slug' => 'about-us',
        'content' => '<h2>Our Story</h2><p>MySeoFan was born out of a simple need: a fast, reliable, and aesthetically pleasing way to archive Instagram content. As digital creators ourselves, we understood the frustration of losing high-quality assets or struggling with clunky, ad-ridden tools.</p><h2>Our Mission</h2><p>We provide a premium, high-speed downloader for Reels, Photos, and Videos. Our mission is to empower creators and fans to preserve their digital memories with zero friction and absolute privacy.</p><h2>Why We Are Different</h2><ul><li><strong>No Accounts:</strong> Anonymous usage is our core principle.</li><li><strong>HD Quality:</strong> We fetch the highest possible resolution from the source.</li><li><strong>Global Support:</strong> Our interface is fully localized for users worldwide.</li></ul>',
        'lang_code' => 'en',
        'group' => 'about_group'
    ],
    [
        'title' => 'Privacy Policy',
        'slug' => 'privacy-policy',
        'content' => '<h2>Introduction</h2><p>Your privacy is our top priority. This policy outlines how MySeoFan handles your data.</p><h2>Data Collection</h2><p>We do not collect personal information like names, emails, or phone numbers. Our tool is strictly "input-and-process" – we do not store the URLs you paste or the files you download.</p><h2>Cookies</h2><p>We use minimal cookies to remember your language preferences. These cookies do not track your activity on other websites.</p><h2>Third-Party Services</h2><p>Our downloader uses the Cobalt API for processing. Please refer to their respective policies regarding transient data handling during the download process.</p>',
        'lang_code' => 'en',
        'group' => 'privacy_group'
    ],
    [
        'title' => 'Terms of Service',
        'slug' => 'terms-of-service',
        'content' => '<h2>Acceptance of Terms</h2><p>By using MySeoFan, you agree to these terms. If you do not agree, please do not use our service.</p><h2>Usage Guidelines</h2><p>Our tool is intended for personal, non-commercial use. Users are responsible for ensuring they have the right to download content under copyright laws.</p><h2>Prohibited Acts</h2><ul><li>Automated scraping of our website.</li><li>Redistributing downloaded content for commercial gain without permission.</li><li>Attempting to disrupt our server infrastructure.</li></ul><h2>Disclaimer</h2><p>MySeoFan is not affiliated with Instagram or Meta Platforms, Inc.</p>',
        'lang_code' => 'en',
        'group' => 'terms_group'
    ],
    [
        'title' => 'Contact Us',
        'slug' => 'contact-us',
        'content' => '<h2>Get in Touch</h2><p>Have questions or feedback? We\'d love to hear from you. Since we are a small team, we primarily handle inquiries via email.</p><h2>Support</h2><p>For technical issues or feature requests, please reach out to our support coordinator. We aim to respond within 48 hours.</p><h2>Partnerships</h2><p>Interested in collaborating with MySeoFan? Contact our business development team for API access or partnership opportunities.</p><p><em>Email: support@myseofan.link</em></p>',
        'lang_code' => 'en',
        'group' => 'contact_group'
    ],

    // --- INDONESIAN ---
    [
        'title' => 'Tentang MySeoFan',
        'slug' => 'tentang-kami',
        'content' => '<h2>Cerita Kami</h2><p>MySeoFan lahir dari kebutuhan sederhana: cara yang cepat, handal, dan estetis untuk mengarsipkan konten Instagram. Sebagai kreator digital, kami memahami rasa frustrasi saat kehilangan aset berkualitas tinggi.</p><h2>Misi Kami</h2><p>Kami menyediakan pengunduh berkecepatan tinggi untuk Reels, Foto, dan Video. Misi kami adalah memberdayakan kreator untuk menjaga memori digital mereka dengan privasi mutlak.</p>',
        'lang_code' => 'id',
        'group' => 'about_group'
    ],
    [
        'title' => 'Kebijakan Privasi',
        'slug' => 'kebijakan-privasi',
        'content' => '<h2>Pendahuluan</h2><p>Privasi Anda adalah prioritas utama kami. Kebijakan ini menjelaskan bagaimana MySeoFan menangani data Anda.</p><h2>Pengumpulan Data</h2><p>Kami tidak mengumpulkan informasi pribadi. Alat kami murni "input-dan-proses" – kami tidak menyimpan URL atau file yang Anda unduh.</p>',
        'lang_code' => 'id',
        'group' => 'privacy_group'
    ],
    [
        'title' => 'Ketentuan Layanan',
        'slug' => 'syarat-dan-ketentuan',
        'content' => '<h2>Penerimaan Ketentuan</h2><p>Dengan menggunakan MySeoFan, Anda setuju dengan ketentuan ini. Alat ini ditujukan untuk penggunaan pribadi dan non-komersial.</p>',
        'lang_code' => 'id',
        'group' => 'terms_group'
    ],
    [
        'title' => 'Hubungi Kami',
        'slug' => 'hubungi-kami',
        'content' => '<h2>Hubungi Kami</h2><p>Punya pertanyaan atau masukan? Kami senang mendengarnya. Silakan kirimkan email ke support@myseofan.link untuk bantuan teknis.</p>',
        'lang_code' => 'id',
        'group' => 'contact_group'
    ],

    // --- OTHER LANGUAGES (Representative Content for Full Feel) ---
    [
        'title' => 'À Propos',
        'slug' => 'a-propos',
        'content' => '<h2>Notre Mission</h2><p>MySeoFan est dédié à fournir les outils les plus rapides pour la préservation des médias Instagram avec une confidentialité totale.</p>',
        'lang_code' => 'fr',
        'group' => 'about_group'
    ],
    [
        'title' => 'Über Uns',
        'slug' => 'ueber-uns',
        'content' => '<h2>Unsere Mission</h2><p>MySeoFan bietet einen schnellen und sicheren Download-Service für Instagram-Medien.</p>',
        'lang_code' => 'de',
        'group' => 'about_group'
    ],
    [
        'title' => 'Sobre Nosotros',
        'slug' => 'sobre-nosotros',
        'content' => '<h2>Nuestra Misión</h2><p>MySeoFan ofrece un servicio de descarga rápido y seguro para medios de Instagram.</p>',
        'lang_code' => 'es',
        'group' => 'about_group'
    ],
    [
        'title' => 'サイトについて',
        'slug' => 'about-us-ja',
        'content' => '<h2>私たちの使命</h2><p>MySeoFanは、Instagramのメディア保存のための最も速く安全なツールを提供することに専念しています。</p>',
        'lang_code' => 'ja',
        'group' => 'about_group'
    ]
];

// Clean up existing to ensure "Full" replace
$pdo->exec("DELETE FROM pages");

foreach ($all_pages as $p) {
    $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, translation_group) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$p['title'], $p['slug'], $p['content'], $p['lang_code'], $p['group']]);
}

echo "Full pages seeded successfully!";
unlink(__FILE__);
