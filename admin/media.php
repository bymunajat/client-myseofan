<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id']) || !in_array(($_SESSION['role'] ?? ''), ['super_admin', 'editor'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$uploadsDir = '../uploads/';

// Handle Deletion
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filePath = $uploadsDir . $file;
    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            $message = "File deleted successfully.";
        } else {
            $error = "Failed to delete file.";
        }
    }
}

// Get Files
$files = [];
if (is_dir($uploadsDir)) {
    $allItems = scandir($uploadsDir);
    foreach ($allItems as $item) {
        if ($item === '.' || $item === '..' || $item === '.gitignore')
            continue;
        // Only show files, hide directories like 'blog'
        if (is_file($uploadsDir . $item)) {
            $files[] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Media Library - MySeoFan Admin</title>
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
        .img-thumbnail-container {
            height: 150px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            overflow: hidden;
        }

        .img-thumbnail-container img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .file-card:hover .img-thumbnail-container img {
            transform: scale(1.1);
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
                            <h3 class="mb-0">Media Library</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Media</li>
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

                    <div class="row">
                        <?php if (empty($files)): ?>
                            <div class="col-12">
                                <div class="callout callout-warning">
                                    <h5>Library is empty</h5>
                                    <p>No files found in the uploads directory.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($files as $f): ?>
                                <div class="col-6 col-md-4 col-lg-2 mb-4">
                                    <div class="card h-100 file-card shadow-sm">
                                        <div class="img-thumbnail-container border-bottom">
                                            <?php
                                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])): ?>
                                                <img src="../uploads/<?php echo $f; ?>" alt="<?php echo htmlspecialchars($f); ?>">
                                            <?php else: ?>
                                                <div class="text-center text-muted">
                                                    <i class="bi bi-file-earmark fs-1"></i>
                                                    <div class="fw-bold mt-1 text-uppercase small"><?php echo $ext; ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body p-2 d-flex flex-column">
                                            <p class="card-text text-truncate small mb-2"
                                                title="<?php echo htmlspecialchars($f); ?>">
                                                <?php echo htmlspecialchars($f); ?>
                                            </p>
                                            <div class="mt-auto d-flex justify-content-between">
                                                <a href="../uploads/<?php echo $f; ?>" download
                                                    class="btn btn-xs btn-outline-primary" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button type="button"
                                                    onclick="confirmDelete('?delete=<?php echo urlencode($f); ?>')"
                                                    class="btn btn-xs btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>

    <script>
        function confirmDelete(url) {
            if (confirm('This file will be permanently deleted from the server.\\nAre you sure?')) {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>