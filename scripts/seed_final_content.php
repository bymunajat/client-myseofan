<?php
require_once '../includes/db.php';

$all_pages = [
    // --- TERMS OF USE (EN) ---
    [
        'title' => 'Terms of Use',
        'slug' => 'terms-of-service',
        'content' => '
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using MySeoFan (the "Service"), you agree to be bound by these Terms of Use and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.</p>
            
            <h2>2. Use License & Intellectual Property</h2>
            <p>MySeoFan provides a tool for downloading media from Instagram for personal, non-commercial use only. You acknowledge that:</p>
            <ul>
                <li>The Service does not host any media; it facilitates the retrieval of public content from Instagram servers.</li>
                <li>You must respect the intellectual property rights of content owners. You are solely responsible for ensuring you have the legal right to download any content.</li>
                <li>Downloading copyrighted material without permission may violate Instagrams terms of service and copyright laws.</li>
            </ul>

            <h2>3. Usage Restrictions</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use the Service for any illegal purposes or in violation of any local, state, or international law.</li>
                <li>Attempt to gain unauthorized access to our server infrastructure or disrupt the Services operation.</li>
                <li>Use automated scripts or bots to interact with the Service.</li>
                <li>Redistribute or sell content downloaded through our Service for commercial gain.</li>
            </ul>

            <h2>4. Disclaimer</h2>
            <p>The Service is provided "as is". MySeoFan makes no warranties, expressed or implied, and hereby disclaims all other warranties. Further, MySeoFan does not warrant the accuracy or reliability of the materials retrieved, as they are fetched directly from external sources.</p>

            <h2>5. Limitation of Liability</h2>
            <p>In no event shall MySeoFan Studio or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit) arising out of the use or inability to use the Service.</p>

            <h2>6. Affiliate Disclaimer</h2>
            <p>MySeoFan is an independent service and is NOT affiliated with, endorsed by, or sponsored by Instagram, Meta Platforms, Inc., or any of its subsidiaries.</p>
        ',
        'lang_code' => 'en',
        'group' => 'terms_group'
    ],
    // --- CONTACT US (EN) ---
    [
        'title' => 'Contact Us',
        'slug' => 'contact-us',
        'content' => '
            <h2>Get in Touch</h2>
            <p>We value your feedback and are here to assist with any questions you may have about MySeoFan. Our team is dedicated to providing a premium experience for the creative community.</p>
            
            <div class="grid md:grid-cols-2 gap-8 mt-12">
                <div>
                    <h3 class="text-xl font-bold mb-4">General Inquiries</h3>
                    <p class="text-gray-600">For general questions about our features or how the tool works, please drop us an email. We love hearing how you use our tool!</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Technical Support</h3>
                    <p class="text-gray-600">Encountering an issue? Send us a message with the URL you were trying to process and your device type. We aim to fix bugs within 24-48 hours.</p>
                </div>
            </div>

            <div class="mt-12 p-8 bg-gray-50 rounded-3xl border border-gray-100">
                <h3 class="text-xl font-black mb-4">Official Email Address</h3>
                <p class="text-2xl font-black text-emerald-600">support@myseofan.link</p>
                <p class="text-sm text-gray-400 mt-4">Note: We prioritize emails from our active community members.</p>
            </div>

            <h2 class="mt-12">Partnerships</h2>
            <p>If you are interested in API access or business collaborations, please include "[Partnership]" in your subject line.</p>
        ',
        'lang_code' => 'en',
        'group' => 'contact_group'
    ],
    // --- TERMS OF USE (ID) ---
    [
        'title' => 'Ketentuan Layanan',
        'slug' => 'syarat-dan-ketentuan',
        'content' => '
            <h2>1. Penerimaan Ketentuan</h2>
            <p>Dengan mengakses MySeoFan, Anda setuju untuk terikat oleh Ketentuan Layanan ini. Jika Anda tidak setuju, harap jangan gunakan layanan kami.</p>
            
            <h2>2. Lisensi Penggunaan</h2>
            <p>Alat ini disediakan hanya untuk penggunaan pribadi dan non-komersial. Anda bertanggung jawab penuh untuk memastikan bahwa Anda memiliki hak legal untuk mengunduh konten yang diproses.</p>

            <h2>3. Batasan Tanggung Jawab</h2>
            <p>MySeoFan tidak berafiliasi dengan Instagram atau Meta Platforms, Inc. Kami tidak bertanggung jawab atas penyalahgunaan konten yang diunduh melalui alat ini.</p>
        ',
        'lang_code' => 'id',
        'group' => 'terms_group'
    ],
    // --- CONTACT US (ID) ---
    [
        'title' => 'Hubungi Kami',
        'slug' => 'hubungi-kami',
        'content' => '
            <h2>Hubungi Kami</h2>
            <p>Kami sangat menghargai masukan Anda. Tim kami siap membantu jika Anda memiliki pertanyaan atau kendala teknis.</p>
            
            <div class="mt-8">
                <h3 class="font-bold">Dukungan Teknis</h3>
                <p>Silakan kirimkan email ke alamat di bawah jika Anda menemukan bug atau link yang tidak bisa diunduh.</p>
                <p class="text-xl font-black text-emerald-600 mt-2">support@myseofan.link</p>
            </div>
        ',
        'lang_code' => 'id',
        'group' => 'contact_group'
    ]
];

// Seed to DB (Overwrite if exists in same group/lang to reflect content updates)
foreach ($all_pages as $p) {
    $check = $pdo->prepare("SELECT id FROM pages WHERE lang_code = ? AND translation_group = ?");
    $check->execute([$p['lang_code'], $p['group']]);
    $existing = $check->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ? WHERE id = ?");
        $stmt->execute([$p['title'], $p['slug'], $p['content'], $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, translation_group) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$p['title'], $p['slug'], $p['content'], $p['lang_code'], $p['group']]);
    }
}

echo "Terms and Contact pages updated with full content!";
unlink(__FILE__);
