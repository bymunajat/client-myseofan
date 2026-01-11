<?php

/* =====================================================
   DIRECT DOWNLOAD (GET)
===================================================== */
if (
    isset($_GET['action']) &&
    $_GET['action'] === 'download' &&
    !empty($_GET['url'])
) {
    $url = urldecode($_GET['url']);
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    $filename = 'instagram_media_' . time() . '.' . ($ext ?: 'file');

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    readfile($url);
    exit;
}

/* =====================================================
   COBALT API HANDLER (POST)
===================================================== */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['url'])) {
    echo json_encode([
        'status' => 'error',
        'error'  => 'missing_url'
    ]);
    exit;
}

$apiUrl = 'http://localhost:9000';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS     => json_encode([
        'url' => $input['url']
    ])
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

/* =====================================================
   SINGLE MEDIA (VIDEO / IMAGE)
===================================================== */
if (isset($data['url'])) {

    $url = $data['url'];
    $type = 'file';

    if (preg_match('/\.(mp4|webm|mov)/i', $url)) {
        $type = 'video';
    } elseif (preg_match('/\.(jpg|jpeg|png|webp)/i', $url)) {
        $type = 'image';
    }

    echo json_encode([
        'status' => 'single',
        'type'   => $type,
        'url'    => $url
    ]);
    exit;
}

/* =====================================================
   MULTIPLE MEDIA (CAROUSEL)
===================================================== */
if (isset($data['files']) && is_array($data['files'])) {

    $media = [];

    foreach ($data['files'] as $url) {
        $type = 'file';

        if (preg_match('/\.(mp4|webm|mov)/i', $url)) {
            $type = 'video';
        } elseif (preg_match('/\.(jpg|jpeg|png|webp)/i', $url)) {
            $type = 'image';
        }

        $media[] = [
            'type' => $type,
            'url'  => $url
        ];
    }

    echo json_encode([
        'status' => 'multiple',
        'media'  => $media
    ]);
    exit;
}

/* =====================================================
   PICKER (CAROUSEL FROM COBALT)
===================================================== */
if (isset($data['status']) && $data['status'] === 'picker' && isset($data['picker'])) {

    $media = [];

    foreach ($data['picker'] as $item) {

        if (empty($item['url'])) continue;

        $type = 'file';

        // Cobalt uses: photo | video
        if ($item['type'] === 'photo') {
            $type = 'image';
        } elseif ($item['type'] === 'video') {
            $type = 'video';
        }

        $media[] = [
            'type' => $type,
            'url'  => $item['url']
        ];
    }

    if (!empty($media)) {
        echo json_encode([
            'status' => count($media) === 1 ? 'single' : 'multiple',
            'media'  => $media
        ]);
        exit;
    }
}

/* =====================================================
   ERROR FALLBACK
===================================================== */
echo json_encode([
    'status' => 'error',
    'error'  => 'unsupported_instagram_url'
]);
