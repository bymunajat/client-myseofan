<?php
session_start();
require_once '../includes/db.php';

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
    $_SESSION['last_cat_lang'] = $_curr_lang;
} elseif (isset($_SESSION['last_cat_lang'])) {
    $_curr_lang = $_SESSION['last_cat_lang'];
} else {
    $_curr_lang = 'en';
}

if (!array_key_exists($_curr_lang, $available_langs))
    $_curr_lang = 'en';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    if (empty($slug))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $lang = $_POST['lang_code'] ?? 'en';

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, lang_code) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $lang]);
            $message = "Category added successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, lang_code=? WHERE id=?");
            $stmt->execute([$name, $slug, $lang, $id]);
            $message = "Category updated successfully!";
            $action = 'list';
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
        $message = "Category deleted.";
    } catch (\Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch Data
$categories = [];
$current_cat = null;
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE lang_code = ? ORDER BY name ASC");
    $stmt->execute([$_curr_lang]);
    $categories = $stmt->fetchAll();
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    $current_cat = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Categories - MySeoFan Admin</title>
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
                            <h3 class="mb-0">Post Categories</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Categories</li>
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
                        <!-- List View -->
                        <div class="<?php echo ($action === 'add' || $action === 'edit') ? 'col-lg-8' : 'col-12'; ?>">
                            <div class="card card-primary card-outline mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">Categories List</h3>

                                        <div class="card-tools d-flex gap-2">
                                            <!-- Language Filter -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-tool dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bi bi-globe me-1"></i>
                                                    <?php echo $available_langs[$_curr_lang]['label']; ?>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php foreach ($available_langs as $code => $l): ?>
                                                        <li><a href="?filter_lang=<?php echo $code; ?>"
                                                                class="dropdown-item <?php echo $_curr_lang === $code ? 'active' : ''; ?>"><?php echo $l['flag'] . ' ' . $l['label']; ?></a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>

                                            <?php if ($action !== 'add' && $action !== 'edit'): ?>
                                                <a href="?action=add&lang_code=<?php echo $_curr_lang; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-plus-lg"></i> New Category
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Slug</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($categories)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center py-4 text-muted">
                                                            No categories found for this language.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php foreach ($categories as $cat): ?>
                                                    <tr
                                                        class="<?php echo (isset($current_cat) && $current_cat['id'] == $cat['id']) ? 'table-primary' : ''; ?>">
                                                        <td>
                                                            <span
                                                                class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></span>
                                                        </td>
                                                        <td>
                                                            <code
                                                                class="text-muted"><?php echo htmlspecialchars($cat['slug']); ?></code>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="?action=edit&id=<?php echo $cat['id']; ?>"
                                                                class="btn btn-sm btn-info text-white me-1" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                title="Delete"
                                                                onclick="confirmDelete('?action=delete&id=<?php echo $cat['id']; ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form View -->
                        <?php if ($action === 'add' || $action === 'edit'): ?>
                            <div class="col-lg-4">
                                <div class="card <?php echo $action === 'edit' ? 'card-warning' : 'card-success'; ?> card-outline mb-4 sticky-top"
                                    style="top: 1rem; z-index: 1020;">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h3>
                                    </div>
                                    <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>"
                                        method="POST">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Language</label>
                                                <select name="lang_code" class="form-select">
                                                    <?php foreach ($available_langs as $code => $l): ?>
                                                        <option value="<?php echo $code; ?>" <?php echo (isset($current_cat['lang_code']) && $current_cat['lang_code'] == $code) || ($_curr_lang == $code) ? 'selected' : ''; ?>>
                                                            <?php echo $l['flag'] . ' ' . $l['label']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Category Name</label>
                                                <input type="text" name="name" class="form-control"
                                                    value="<?php echo htmlspecialchars($current_cat['name'] ?? ''); ?>"
                                                    required placeholder="e.g. Tips & Tricks">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Slug</label>
                                                <input type="text" name="slug" class="form-control font-monospace"
                                                    value="<?php echo htmlspecialchars($current_cat['slug'] ?? ''); ?>"
                                                    placeholder="auto-if-empty">
                                                <div class="form-text">Leave empty to auto-generate.</div>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex gap-2">
                                            <button type="submit"
                                                class="btn <?php echo $action === 'edit' ? 'btn-warning' : 'btn-success'; ?> flex-fill">
                                                <i class="bi bi-save me-1"></i> Save
                                            </button>
                                            <?php if ($action === 'edit'): ?>
                                                <a href="?action=list" class="btn btn-default">Cancel</a>
                                            <?php endif; ?>
                                            <?php if ($action === 'add'): ?>
                                                <a href="?action=list" class="btn btn-default">Close</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
            if (confirm('Deleting this category will not delete the posts using it.\\nAre you sure you want to proceed?')) {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>