<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle Actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $_POST['source_url'] ?? '';
    $target = $_POST['target_url'] ?? '';
    $type = (int) ($_POST['redirect_type'] ?? 301);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO redirects (source_url, target_url, redirect_type, is_active) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$source, $target, $type, $active])) {
            Logger::log('create_redirect', "Created redirect: $source -> $target", $_SESSION['admin_id'] ?? 0);
            header('Location: redirects.php?msg=added');
            exit;
        }
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE redirects SET source_url = ?, target_url = ?, redirect_type = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$source, $target, $type, $active, $id])) {
            Logger::log('update_redirect', "Updated redirect ID: $id", $_SESSION['admin_id'] ?? 0);
            header('Location: redirects.php?msg=updated');
            exit;
        }
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM redirects WHERE id = ?")->execute([$id]);
    Logger::log('delete_redirect', "Deleted redirect ID: $id", $_SESSION['admin_id'] ?? 0);
    header('Location: redirects.php?msg=deleted');
    exit;
}

// Fetch Data
$redirects = $pdo->query("SELECT * FROM redirects ORDER BY created_at DESC")->fetchAll();
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM redirects WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Redirects Manager - MySeoFan Admin</title>
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
                            <h3 class="mb-0">Redirects Manager</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Redirects</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <?php if (isset($_GET['msg'])): ?>
                        <?php if ($_GET['msg'] == 'added'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> Redirect added successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($_GET['msg'] == 'updated'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> Redirect updated successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($_GET['msg'] == 'deleted'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-trash-fill me-2"></i> Redirect deleted successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($action === 'add' || $action === 'edit'): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <?php echo $action === 'add' ? 'Add New Redirect' : 'Edit Redirect'; ?></h3>
                                    </div>
                                    <form method="POST">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Source URL <small class="text-muted">(e.g.
                                                        /old-page)</small></label>
                                                <input type="text" name="source_url"
                                                    value="<?php echo htmlspecialchars($editData['source_url'] ?? ''); ?>"
                                                    class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Target URL <small class="text-muted">(e.g.
                                                        /new-page)</small></label>
                                                <input type="text" name="target_url"
                                                    value="<?php echo htmlspecialchars($editData['target_url'] ?? ''); ?>"
                                                    class="form-control" required>
                                            </div>

                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Type</label>
                                                        <select name="redirect_type" class="form-select">
                                                            <option value="301" <?php echo ($editData['redirect_type'] ?? 301) == 301 ? 'selected' : ''; ?>>301 (Permanent)</option>
                                                            <option value="302" <?php echo ($editData['redirect_type'] ?? 301) == 302 ? 'selected' : ''; ?>>302 (Temporary)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="mb-3">
                                                        <label class="form-label d-block">&nbsp;</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_active"
                                                                id="is_active" <?php echo ($editData['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="is_active">
                                                                Is Active
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">Save Redirect</button>
                                            <a href="redirects.php" class="btn btn-default float-end">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Manage Redirects</h3>
                                <div class="card-tools">
                                    <a href="?action=add" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg"></i> New Redirect
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Source</th>
                                                <th>Target</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($redirects as $r): ?>
                                                <tr>
                                                    <td><code><?php echo htmlspecialchars($r['source_url']); ?></code></td>
                                                    <td><code><?php echo htmlspecialchars($r['target_url']); ?></code></td>
                                                    <td><span
                                                            class="badge text-bg-secondary"><?php echo $r['redirect_type']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($r['is_active']): ?>
                                                            <span class="badge text-bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge text-bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="?action=edit&id=<?php echo $r['id']; ?>"
                                                            class="btn btn-sm btn-info" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            onclick="confirmDelete('?action=delete&id=<?php echo $r['id']; ?>', 'This redirect rule will be removed.')"
                                                            class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <?php if (empty($redirects)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">No redirects found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>
    <script>
        function confirmDelete(url, msg) {
            if (confirm(msg)) {
                window.location.href = url;
            }
        }
    </script>
</body>

</html>
