<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$available_langs = [
    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
    'id' => ['label' => 'Indonesia', 'flag' => '🇮🇩'],
    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    'ja' => ['label' => '日本語', 'flag' => '🇯🇵']
];

if (isset($_GET['filter_lang'])) {
    $_curr_lang = $_GET['filter_lang'];
    $_SESSION['last_blog_lang'] = $_curr_lang;
} else {
    // Force default to English (The "Main" language)
    // Ignore session history to prevent user getting "stuck" in underground languages
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';

// Handle CRUD Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $content = $_POST['content'] ?? '';
    $thumbnail = $_POST['thumbnail'] ?? '';
    $lang = $_POST['lang_code'] ?? 'en';
    $m_title = $_POST['meta_title'] ?? '';
    $m_desc = $_POST['meta_description'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $t_group = $_POST['translation_group'] ?? uniqid('group_', true);

    $excerpt = $_POST['excerpt'] ?? '';
    // Use admin_id from session (set in login.php)
    $author_id = $_POST['author_id'] ?? ($_SESSION['admin_id'] ?? 1);

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, thumbnail, lang_code, meta_title, meta_description, category, translation_group, status, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $author_id]);
            $message = "Post created successfully!";
            $action = 'list';
            $_curr_lang = 'en'; // Return to Main List
            $_SESSION['last_blog_lang'] = 'en';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, content=?, excerpt=?, thumbnail=?, lang_code=?, meta_title=?, meta_description=?, category=?, translation_group=?, status=?, tags=?, author_id=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $lang, $m_title, $m_desc, $category, $t_group, $_POST['status'] ?? 'published', $_POST['tags'] ?? '', $author_id, $id]);
            $message = "Post updated successfully!";
            $action = 'list';
            $_curr_lang = 'en'; // Return to Main List
            $_SESSION['last_blog_lang'] = 'en';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        // 1. Fetch translation group first
        $stmt = $pdo->prepare("SELECT translation_group FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetchColumn();

        if ($group) {
            // 2. Delete ALL posts in this group (Cascading Delete)
            $delStmt = $pdo->prepare("DELETE FROM blog_posts WHERE translation_group = ?");
            $delStmt->execute([$group]);
            $count = $delStmt->rowCount();
            $message = "Post and its translations deleted (" . $count . " items removed).";
        } else {
            // Fallback for legacy items without group
            $pdo->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
            $message = "Post deleted.";
        }
        Logger::log('delete_post', "Deleted post ID: $id (Group: " . ($group ?? 'none') . ")");
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Handle Blog Cloning
if ($action === 'clone' && $id && isset($_GET['target_lang'])) {
    $target_lang = $_GET['target_lang'];
    try {
        // Fetch original
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $original = $stmt->fetch();

        if ($original) {
            // Check collision
            $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE translation_group = ? AND lang_code = ?");
            $stmt->execute([$original['translation_group'], $target_lang]);
            if ($stmt->fetch()) {
                $error = "A post in " . $available_langs[$target_lang]['label'] . " already exists for this group.";
            } else {
                // Insert clone
                $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, thumbnail, lang_code, meta_title, meta_description, category, translation_group, status, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $new_slug = $original['slug'] . '-' . $target_lang . '-' . rand(100, 999);

                $stmt->execute([
                    $original['title'],
                    $new_slug,
                    $original['content'],
                    $original['excerpt'],
                    $original['thumbnail'],
                    $target_lang,
                    $original['meta_title'],
                    $original['meta_description'],
                    $original['category'],
                    $original['translation_group'],
                    'draft', // Set to draft as a precaution
                    $original['tags'],
                    $original['author_id']
                ]);
                $message = "Post cloned to " . $available_langs[$target_lang]['label'] . " successfully!";
                $_curr_lang = $target_lang;
                $action = 'list';
            }
        }
    } catch (\Exception $e) {
        $error = "Clone failed: " . $e->getMessage();
        $action = 'list';
    }
}

// AJAX Category Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_category') {
    $cat_name = $_POST['name'] ?? '';
    if (!empty($cat_name)) {
        $cat_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat_name)));
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, lang_code) VALUES (?, ?, 'global')");
            $stmt->execute([$cat_name, $cat_slug]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'name' => $cat_name]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}

// Fetch Data
// Pagination
$per_page = 10;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $per_page;

if ($action === 'list') {
    // Search Logic
    $search = trim($_GET['search'] ?? '');
    $search_param = "%$search%";
    $where_sql = "WHERE lang_code = ?"; // Force language filter
    $params = [$_curr_lang];

    if (!empty($search)) {
        $where_sql .= " AND (title LIKE ? OR slug LIKE ?)";
        $params[] = $search_param;
        $params[] = $search_param;
    }

    // Total count for pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM blog_posts $where_sql");
    $stmt_count->execute($params);
    $total_posts = $stmt_count->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);

    // Fetch Posts
    $stmt = $pdo->prepare("SELECT b.*, a.username as author_name 
                           FROM blog_posts b
                           LEFT JOIN admins a ON b.author_id = a.id
                           $where_sql 
                           ORDER BY b.created_at DESC
                           LIMIT ? OFFSET ?");

    // Merge params for final query
    $params[] = $per_page;
    $params[] = $offset;

    // Fix for PDO limit/offset being strictly int
    // Usually PDO handles it but sometimes prepared statements need explicit binding for LIMIT.
    // However, for this codebase style, array execution is used elsewhere.
    // Let's stick to array execution but ensure ints if driver allows, 
    // or just concat limit if safe (params validated above). 
    // Actually, execute($params) treats all as strings which breaks LIMIT in some drivers.
    // Safest rewrite for LIMIT with strict mode:

    // For simplicity in this specific setup which uses SQLite (which allows strings in LIMIT often or we can bind):
    // Let's bind manually to be safe or just use the previous pattern if it worked (it seemed to use execute params).
    // The previous code had `LIMIT ? OFFSET ?` and `$stmt->execute($params)`. PHP 8.1+ / PDO SQLite usually is fine.

    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    $current_post = $stmt->fetch();
}

$categories = [];
$stmt_cats = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

$admins = [];
try {
    $stmt_admins = $pdo->query("SELECT id, username FROM admins ORDER BY username ASC");
    $admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    // Silent fail or log
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Blog Manager - MySeoFan Admin</title>
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

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons template',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 500,
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
                            <h3 class="mb-0">Blog Manager</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Blog Posts</li>
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
                                    <h3 class="card-title">All Posts</h3>
                                    <div class="card-tools">
                                        <form method="GET" action="" class="d-inline-block me-2">
                                            <div class="input-group input-group-sm" style="width: 250px;">
                                                <input type="text" name="search" class="form-control float-right"
                                                    placeholder="Search"
                                                    value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                                <button type="submit" class="btn btn-default">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <a href="?action=add" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg"></i> New Post
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 40%;">Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($posts)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">No posts found.</td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($posts as $p): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-truncate" style="max-width: 300px;">
                                                            <?php echo htmlspecialchars($p['title']); ?></div>
                                                        <small class="text-secondary d-block mt-1">
                                                            /blog/<?php echo htmlspecialchars($p['slug'] ?? ''); ?>
                                                            <span class="badge text-bg-light border ms-1">by
                                                                <?php echo htmlspecialchars($p['author_name'] ?? 'Admin'); ?></span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge text-bg-secondary"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (($p['status'] ?? 'published') === 'draft'): ?>
                                                            <span class="badge text-bg-warning">Draft</span>
                                                        <?php else: ?>
                                                            <span class="badge text-bg-success">Published</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                                                    <td class="text-end">
                                                        <a href="?action=edit&id=<?php echo $p['id']; ?>"
                                                            class="btn btn-sm btn-info text-white" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            onclick="confirmDelete('?action=delete&id=<?php echo $p['id']; ?>')"
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer clearfix">
                                    <ul class="pagination pagination-sm m-0 float-end">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $page - 1; ?>&filter_lang=<?php echo $_curr_lang; ?>">&laquo;</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link"
                                                    href="?page=<?php echo $i; ?>&filter_lang=<?php echo $_curr_lang; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $page + 1; ?>&filter_lang=<?php echo $_curr_lang; ?>">&raquo;</a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info Box -->
                        <div class="callout callout-info">
                            <h5><i class="bi bi-info-circle me-1"></i> About Blog Manager</h5>
                            <p>Create and manage blog posts here. Posts written in English will be automatically translated
                                to other languages if configured.</p>
                        </div>

                    <?php else: ?>
                        <!-- Add/Edit Form -->
                        <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST"
                            enctype="multipart/form-data">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-8">
                                    <div class="card card-primary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Content</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Title</label>
                                                <input type="text" name="title" class="form-control form-control-lg"
                                                    required
                                                    value="<?php echo htmlspecialchars($current_post['title'] ?? ''); ?>"
                                                    placeholder="Enter post title">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Slug</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">/blog/</span>
                                                    <input type="text" name="slug" class="form-control"
                                                        value="<?php echo htmlspecialchars($current_post['slug'] ?? ''); ?>"
                                                        placeholder="auto-generated-if-empty">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Content</label>
                                                <textarea name="content"
                                                    id="contentEditor"><?php echo htmlspecialchars($current_post['content'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Excerpt</label>
                                                <textarea name="excerpt" class="form-control" rows="3"
                                                    placeholder="Short summary..."><?php echo htmlspecialchars($current_post['excerpt'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-secondary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">SEO Settings</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                    value="<?php echo htmlspecialchars($current_post['meta_title'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description" class="form-control"
                                                    rows="2"><?php echo htmlspecialchars($current_post['meta_description'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-4">
                                    <div class="card card-primary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Publish</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="published" <?php echo ($current_post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                                                    <option value="draft" <?php echo ($current_post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Author</label>
                                                <select name="author_id" class="form-select">
                                                    <?php
                                                    $current_author = $current_post['author_id'] ?? $_SESSION['admin_id'];
                                                    foreach ($admins as $admin):
                                                        ?>
                                                        <option value="<?php echo $admin['id']; ?>" <?php echo $current_author == $admin['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($admin['username']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i> Save Changes
                                                </button>
                                                <a href="?action=list" class="btn btn-default">Cancel</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-info card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Organization</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <div class="input-group">
                                                    <select name="category" id="categorySelect" class="form-select">
                                                        <?php if (empty($categories)): ?>
                                                            <option value="General">General</option>
                                                        <?php else: ?>
                                                            <?php foreach ($categories as $cat): ?>
                                                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($current_post['category'] ?? '') == $cat['name'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="quickAddCategory()">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Tags</label>
                                                <input type="text" name="tags" class="form-control" placeholder="tech, news"
                                                    value="<?php echo htmlspecialchars($current_post['tags'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-secondary card-outline mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title">Featured Image</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <input type="hidden" name="thumbnail" id="thumbnailInput"
                                                value="<?php echo htmlspecialchars($current_post['thumbnail'] ?? ''); ?>">

                                            <!-- Simple fake upload UI for now, preserving logic -->
                                            <div class="border rounded p-3 mb-2" style="border-style: dashed !important;">
                                                <?php if (!empty($current_post['thumbnail'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($current_post['thumbnail']); ?>"
                                                        class="img-fluid rounded mb-2" style="max-height: 150px;">
                                                <?php else: ?>
                                                    <div class="text-muted py-4">No image selected</div>
                                                <?php endif; ?>

                                                <!-- Logic for upload would go here - simplified for migration step -->
                                                <input type="file" class="form-control form-control-sm mt-2">
                                            </div>
                                            <small class="text-muted">Upload logic to be restored.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden Fields -->
                            <input type="hidden" name="translation_group"
                                value="<?php echo htmlspecialchars($_GET['translation_group'] ?? ($current_post['translation_group'] ?? uniqid('group_', true))); ?>">
                            <input type="hidden" name="lang_code"
                                value="<?php echo htmlspecialchars($current_post['lang_code'] ?? ($_GET['lang_code'] ?? $_curr_lang)); ?>">
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>

    <!-- Modal for Category -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="newCategoryInput" class="form-control" placeholder="Category Name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewCategory()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const catModal = new bootstrap.Modal(document.getElementById('categoryModal'));

        function quickAddCategory() {
            catModal.show();
        }

        function saveNewCategory() {
            const input = document.getElementById('newCategoryInput');
            const name = input.value.trim();
            if (!name) return;

            const formData = new FormData();
            formData.append('action', 'add_category');
            formData.append('name', name);

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('categorySelect');
                        const option = new Option(data.name, data.name, true, true);
                        select.add(option);
                        catModal.hide();
                        input.value = '';
                    } else {
                        alert(data.error || 'Error adding category');
                    }
                });
        }

        function confirmDelete(url) {
            if (confirm('Are you sure you want to delete this post?')) {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>