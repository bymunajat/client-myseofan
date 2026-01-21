<?php
session_start();
require_once '../includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id']) || !in_array(($_SESSION['role'] ?? ''), ['super_admin', 'editor'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Available Languages
$available_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

// Always use English for pages (single source of truth)
$_curr_lang = 'en';


// AJAX Reorder Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    $order = $_POST['order'] ?? [];
    try {
        $pdo->beginTransaction();
        foreach ($order as $index => $id) {
            $stmt = $pdo->prepare("UPDATE pages SET menu_order = ? WHERE id = ?");
            $stmt->execute([$index, $id]);
        }
        $pdo->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } catch (\Exception $e) {
        $pdo->rollBack();
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}



// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'reorder') {
    $title = $_POST['title'] ?? '';
    // Auto slug if empty
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $content = $_POST['content'] ?? '';
    $lang = $_POST['lang_code'] ?? 'en';
    $m_title = $_POST['meta_title'] ?? '';
    $m_desc = $_POST['meta_description'] ?? '';
    $t_group = $_POST['translation_group'] ?? uniqid('group_', true);
    $in_header = isset($_POST['show_in_header']) ? 1 : 0;
    $in_footer = isset($_POST['show_in_footer']) ? 1 : 0;
    $order = (int) ($_POST['menu_order'] ?? 0);
    $f_section = $_POST['footer_section'] ?? 'legal';

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, lang_code, meta_title, meta_description, translation_group, show_in_header, show_in_footer, menu_order, footer_section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order, $f_section]);
            $message = "Page created successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_page_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, lang_code=?, meta_title=?, meta_description=?, translation_group=?, show_in_header=?, show_in_footer=?, menu_order=?, footer_section=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $lang, $m_title, $m_desc, $t_group, $in_header, $in_footer, $order, $f_section, $id]);
            $message = "Page updated successfully!";
            $action = 'list';
            $_curr_lang = $lang;
            $_SESSION['last_page_lang'] = $lang;
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM pages WHERE id=?")->execute([$id]);
        $message = "Page deleted.";
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch Data for Display
$pages = [];
$cu_p = null;
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE lang_code = 'en' ORDER BY title ASC");
        $stmt->execute();
        $pages = $stmt->fetchAll();
    } catch (\Exception $e) {
        $error = "Query Error: " . $e->getMessage();
    }
} elseif (($action === 'edit' || $action === 'add') && $id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
    $stmt->execute([$id]);
    $cu_p = $stmt->fetch();
}

$page_title = "Pages";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?> - MySeoFan Admin</title>
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
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.css">

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons template',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            promotion: false,
            height: 600,
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    </script>
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
                            <h3 class="mb-0"><?php echo $page_title; ?></h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Pages</li>
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

                    <?php if ($action === 'list'): ?>
                        <div class="card card-primary card-outline mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">Pages List</h3>
                                    <div class="card-tools">
                                        <a href="?action=add" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg"></i> New Page
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Page Title</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="page-list">
                                            <?php if (empty($pages)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-muted">
                                                        No pages found in <?php echo $available_langs[$_curr_lang]['label']; ?>.
                                                        <br>
                                                        <a href="?action=add&filter_lang=<?php echo $_curr_lang; ?>"
                                                            class="btn btn-link">Create one?</a>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($pages as $p): ?>
                                                <tr data-id="<?php echo $p['id']; ?>" class="sortable-item"
                                                    style="cursor: move;">
                                                    <td class="text-center text-muted">
                                                        <i class="bi bi-grip-vertical"></i>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($p['title']); ?></div>
                                                        <small
                                                            class="text-secondary">/<?php echo htmlspecialchars($p['slug']); ?></small>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-default dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                Actions
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item"
                                                                        href="?action=edit&id=<?php echo $p['id']; ?>"><i
                                                                            class="bi bi-pencil me-2"></i> Edit</a></li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li><a class="dropdown-item text-danger"
                                                                        href="javascript:void(0);"
                                                                        onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>')"><i
                                                                            class="bi bi-trash me-2"></i> Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <small><i class="bi bi-info-circle"></i> Drag and drop rows to reorder navigation menu
                                    items.</small>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Add/Edit Form -->
                        <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-8">
                                    <div class="card card-primary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Basic Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Page Title</label>
                                                <input type="text" name="title" class="form-control form-control-lg"
                                                    required value="<?php echo htmlspecialchars($cu_p['title'] ?? ''); ?>"
                                                    placeholder="e.g. Terms of Service">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">URL Slug</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">/</span>
                                                    <input type="text" name="slug" class="form-control"
                                                        value="<?php echo htmlspecialchars($cu_p['slug'] ?? ''); ?>"
                                                        placeholder="auto-generated-if-empty">
                                                </div>
                                                <div class="form-text">Leave empty to auto-generate from title.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-indigo card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Page Content</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <textarea name="content"
                                                id="contentEditor"><?php echo htmlspecialchars($cu_p['content'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <!-- Footer Section for Save Buttons placed outside commonly, but here is fine too -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-4">
                                    <div class="card card-secondary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Publish Options</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Language</label>
                                                <div class="input-group">
                                                    <span
                                                        class="input-group-text"><?php echo $available_langs[$cu_p['lang_code'] ?? $_curr_lang]['flag']; ?></span>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $available_langs[$cu_p['lang_code'] ?? $_curr_lang]['label']; ?>"
                                                        disabled>
                                                </div>
                                                <input type="hidden" name="lang_code"
                                                    value="<?php echo htmlspecialchars($cu_p['lang_code'] ?? ($_GET['filter_lang'] ?? $_curr_lang)); ?>">
                                            </div>
                                            <input type="hidden" name="translation_group"
                                                value="<?php echo htmlspecialchars($_GET['translation_group'] ?? ($cu_p['translation_group'] ?? uniqid('group_', true))); ?>">

                                            <!-- Visibility Toggles could go here if schema supports it, for now just standard save -->
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i> Save Page
                                                </button>
                                                <a href="?action=list&filter_lang=<?php echo $_curr_lang; ?>"
                                                    class="btn btn-default">Cancel</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-info card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">SEO Configuration</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                    value="<?php echo htmlspecialchars($cu_p['meta_title'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description" class="form-control"
                                                    rows="3"><?php echo htmlspecialchars($cu_p['meta_description'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>

    <script>
        function confirmDelete(url) {
            if (confirm('Are you sure you want to delete this page?')) {
                window.location.href = url;
            }
        }

        // Sortable Logic
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('page-list');
            if (el) {
                var sortable = Sortable.create(el, {
                    animation: 150,
                    handle: 'tr', // Draggable by the whole row
                    onEnd: function (evt) {
                        var order = [];
                        document.querySelectorAll('#page-list tr').forEach(function (row, index) {
                            order.push(row.getAttribute('data-id'));
                        });

                        // Send new order to server
                        fetch('pages.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=reorder&' + order.map((id, index) => `order[${index}]=${id}`).join('&')
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    alert('Failed to save order');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Network error saving order');
                            });
                    }
                });
            }
        });

        function confirmDelete(url) {
            if (confirm('Are you sure you want to delete this page?')) {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>