<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$settings = getSiteSettings($pdo);

// Fetch Stats
$postCount = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE lang_code = 'en'")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang_code = 'en'")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang_code = 'en'")->fetchColumn();
$logCount = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$recentPosts = $pdo->query("SELECT title, created_at FROM blog_posts WHERE lang_code = 'en' ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MySeoFan</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous">

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
        <!-- Header -->
        <?php include 'includes/header_lte.php'; ?>

        <!-- Sidebar -->
        <?php include 'includes/sidebar_lte.php'; ?>

        <!-- Main Content -->
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Dashboard</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <!-- Stats Rows -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-primary">
                                <div class="inner">
                                    <h3><?php echo $postCount; ?></h3>
                                    <p>Blog Posts</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <a href="blog.php?action=list&filter_lang=en"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-success">
                                <div class="inner">
                                    <h3><?php echo $pageCount; ?></h3>
                                    <p>Static Pages</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 01-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 01-.837.552c-.676.328-1.028.774-1.028 1.152v.202a.75.75 0 01-1.5 0v-.202c0-.944.606-1.786 1.45-2.194a2.25 2.25 0 00.5-2.607zM12.75 16.75a.75.75 0 10-1.5 0 .75.75 0 001.5 0z">
                                    </path>
                                </svg>
                                <a href="pages.php?action=list"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-warning">
                                <div class="inner">
                                    <h3><?php echo $logCount; ?></h3>
                                    <p>Activity Logs</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M12 2.25a.75.75 0 01.75.75v.756a9.006 9.006 0 016.944 6.944h.756a.75.75 0 010 1.5h-.756a9.006 9.006 0 01-6.944 6.944v.756a.75.75 0 01-1.5 0v-.756a9.006 9.006 0 01-6.944-6.944h-.756a.75.75 0 010-1.5h.756a9.006 9.006 0 016.944-6.944V3a.75.75 0 01.75-.75zM8.25 12a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0z">
                                    </path>
                                </svg>
                                <a href="logs.php"
                                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-info">
                                <div class="inner">
                                    <h3><?php echo phpversion(); ?></h3>
                                    <p>PHP Version</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M2.25 6a3 3 0 013-3h13.5a3 3 0 013 3v12a3 3 0 01-3 3H5.25a3 3 0 01-3-3V6zm3.97.97a.75.75 0 011.06 0l2.25 2.25a.75.75 0 010 1.06l-2.25 2.25a.75.75 0 01-1.06-1.06l1.72-1.72-1.72-1.72a.75.75 0 010-1.06zm4.28 4.28a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z">
                                    </path>
                                </svg>
                                <a href="#"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    System Info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity and Actions -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4 card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Blog Posts</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentPosts as $p): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                                                        <td>
                                                            <a href="blog.php?action=list&filter_lang=en"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($recentPosts)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No posts found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-4 card-secondary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Quick Actions</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="blog.php?action=add" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i> New Blog Post
                                        </a>
                                        <a href="../index.php" target="_blank" class="btn btn-success">
                                            <i class="bi bi-globe me-1"></i> View Live Site
                                        </a>
                                    </div>
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