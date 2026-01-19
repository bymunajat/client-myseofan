<?php
session_start();
// Simple auth check
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$uploadDir = __DIR__ . '/../uploads/blog/';

// Create dir if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validation
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (!in_array($mime, $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.']);
    exit;
}

// Max 2MB
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['error' => 'File too large. Max 2MB.']);
    exit;
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Return relative path for DB
    $publicPath = 'uploads/blog/' . $filename;
    echo json_encode([
        'success' => true,
        'url' => $publicPath
    ]);
} else {
    echo json_encode(['error' => 'Failed to move uploaded file.']);
}

