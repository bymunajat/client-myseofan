<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$settings = getSiteSettings($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = $_POST['site_name'] ?? '';
    $header_code = $_POST['header_code'] ?? '';
    $footer_code = $_POST['footer_code'] ?? '';

    // Handle Uploads
    $logo_path = $settings['logo_path'];
    $favicon_path = $settings['favicon_path'];

    if (!empty($_FILES['logo']['name'])) {
        $logo_path = 'uploads/' . time() . '_' . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], '../' . $logo_path);
    }
    if (!empty($_FILES['favicon']['name'])) {
        $favicon_path = 'uploads/' . time() . '_' . $_FILES['favicon']['name'];
        move_uploaded_file($_FILES['favicon']['tmp_name'], '../' . $favicon_path);
    }

    if ($pdo) {
        $stmt = $pdo->prepare("UPDATE site_settings SET site_name = ?, logo_path = ?, favicon_path = ?, header_code = ?, footer_code = ? WHERE id = 1");
        if ($stmt->execute([$site_name, $logo_path, $favicon_path, $header_code, $footer_code])) {
            Logger::log('update_settings', "Updated site settings", $_SESSION['admin_id'] ?? 0);
            $message = 'Settings updated successfully!';
            $settings = getSiteSettings($pdo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Site Settings - MySeoFan Admin</title>
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
                            <h3 class="mb-0">Settings</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Settings</li>
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

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-primary card-outline mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">General Settings</h5>
                                </div>
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Website Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name"
                                                value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>"
                                                placeholder="Enter website name">
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="logo" class="form-label">Logo</label>
                                                <input class="form-control" type="file" id="logo" name="logo">
                                                <?php if (!empty($settings['logo_path'])): ?>
                                                    <div class="mt-2 p-2 border rounded bg-light d-inline-block">
                                                        <img src="../<?php echo $settings['logo_path']; ?>"
                                                            class="img-fluid" style="max-height: 50px;" alt="Current Logo">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="favicon" class="form-label">Favicon</label>
                                                <input class="form-control" type="file" id="favicon" name="favicon">
                                                <?php if (!empty($settings['favicon_path'])): ?>
                                                    <div class="mt-2 p-2 border rounded bg-light d-inline-block">
                                                        <img src="../<?php echo $settings['favicon_path']; ?>"
                                                            class="img-fluid" style="max-height: 32px;"
                                                            alt="Current Favicon">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                            </div>

                            <div class="card card-info card-outline mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Code Injection</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="header_code" class="form-label">Header Code (e.g. Google
                                            Analytics)</label>
                                        <textarea class="form-control font-monospace" id="header_code"
                                            name="header_code" rows="5"
                                            placeholder="<script>...</script>"><?php echo htmlspecialchars($settings['header_code'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="footer_code" class="form-label">Footer Code</label>
                                        <textarea class="form-control font-monospace" id="footer_code"
                                            name="footer_code" rows="5"
                                            placeholder="<script>...</script>"><?php echo htmlspecialchars($settings['footer_code'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Changes
                                    </button>
                                </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-secondary card-outline mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Information</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        Upload transparent PNG or SVG files for best results with the logo and favicon.
                                    </p>
                                    <hr>
                                    <p class="text-muted mb-0">
                                        <strong>Header Code</strong> is injected before the closing
                                        <code>&lt;/head&gt;</code> tag.<br>
                                        <strong>Footer Code</strong> is injected before the closing
                                        <code>&lt;/body&gt;</code> tag.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>
</body>

</html>