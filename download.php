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

    // Use cURL to fetch the file with proper headers
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://www.instagram.com/',
            'Sec-Fetch-Dest: image',
            'Sec-Fetch-Mode: no-cors',
            'Sec-Fetch-Site: cross-site'
        ]
    ]);

    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $fileContent !== false) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($fileContent));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        echo $fileContent;
    } else {
        http_response_code(500);
        echo 'Failed to download file. HTTP Code: ' . $httpCode;
    }
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
        'error' => 'missing_url'
    ]);
    exit;
}

$apiUrl = 'http://localhost:9000';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
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
        'type' => $type,
        'url' => $url
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
            'url' => $url
        ];
    }

    echo json_encode([
        'status' => 'multiple',
        'media' => $media
    ]);
    exit;
}

/* =====================================================
   PICKER (CAROUSEL FROM COBALT)
===================================================== */
if (isset($data['status']) && $data['status'] === 'picker' && isset($data['picker'])) {

    $media = [];

    foreach ($data['picker'] as $item) {

        if (empty($item['url']))
            continue;

        $type = 'file';

        // Cobalt uses: photo | video
        if ($item['type'] === 'photo') {
            $type = 'image';
        } elseif ($item['type'] === 'video') {
            $type = 'video';
        }

        $media[] = [
            'type' => $type,
            'url' => $item['url']
        ];
    }

    if (!empty($media)) {
        if (count($media) === 1) {
            echo json_encode([
                'status' => 'single',
                'type' => $media[0]['type'],
                'url' => $media[0]['url']
            ]);
        } else {
            echo json_encode([
                'status' => 'multiple',
                'media' => $media
            ]);
        }
        exit;
    }
}

/* =====================================================
   ERROR FALLBACK
===================================================== */
echo json_encode([
    'status' => 'error',
    'error' => 'unsupported_instagram_url'
]);

