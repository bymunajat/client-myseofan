<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$text = $_POST['text'] ?? '';
$target_lang = $_POST['lang'] ?? 'en';

if (empty($text)) {
    echo json_encode(['translatedText' => '']);
    exit;
}

// Simple Free Google Translate API mirror logic
$url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=" . urlencode($target_lang) . "&dt=t&q=" . urlencode($text);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$result = curl_exec($ch);
curl_close($ch);

$data = json_decode($result);
$translated_text = "";

if (isset($data[0])) {
    foreach ($data[0] as $segment) {
        $translated_text .= $segment[0];
    }
}

header('Content-Type: application/json');
echo json_encode(['translatedText' => $translated_text]);
