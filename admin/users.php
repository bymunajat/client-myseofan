<?php
/**
 * User Management - REBUILT VERSION 2.1
 * Objective: High-end card UI with robust defaults.
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

// 1. Security Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure super_admin
try {
    $checkStmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $checkStmt->execute([$_SESSION['admin_id']]);
    $currentRole = $checkStmt->fetchColumn();
    $_SESSION['role'] = $currentRole ?: 'author';
} catch (Exception $e) {
    $currentRole = $_SESSION['role'] ?? 'author';
}

if ($currentRole !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

// 2. Variables & Actions
$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
if (empty($action))
    $action = 'list';
$id = $_GET['id'] ?? null;

// 3. Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'author';

    if ($action === 'add') {
        try {
            if (empty($username) || empty($password))
                throw new Exception("Username and password are required.");
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)")->execute([$username, $hash, $role]);
            Logger::log('create_user', "Created new admin user: $username (Role: $role)", $_SESSION['admin_id'] ?? 0);
            $message = "User '{$username}' successfully created.";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'edit' && $id) {
        try {
            if (empty($username))
                throw new Exception("Username cannot be empty.");
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE admins SET username = ?, password_hash = ?, role = ? WHERE id = ?")->execute([$username, $hash, $role, $id]);
            } else {
                $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?")->execute([$username, $role, $id]);
            }
            Logger::log('update_user', "Updated user ID: $id ($username)", $_SESSION['admin_id'] ?? 0);
            $message = "Account updated successfully.";
            $action = 'list';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Delete Logic
if ($action === 'delete' && $id) {
    if ($id == $_SESSION['admin_id']) {
        $error = "Security Alert: You cannot delete your own account.";
    } else {
        try {
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
            Logger::log('delete_user', "Deleted user ID: $id", $_SESSION['admin_id'] ?? 0);
            $message = "Account permanently deleted.";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    $action = 'list';
}

// 4. Fetch Data
$users = [];
$editData = null;

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    if (!$editData)
        $action = 'list';
}

// Always fetch users if we might show the list
if ($action === 'list') {
    try {
        $users = $pdo->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $users = $pdo->query("SELECT id, username, role FROM admins")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Management - MySeoFan Admin</title>
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
    <style>
        .user-avatar-circle {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            border-radius: 50%;
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
                            <h3 class="mb-0">Admin Management</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Users</li>
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

                    <?php if ($action === 'add' || $action === 'edit'): ?>
                        <!-- FORM VIEW -->
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <?php echo ($action === 'add' ? 'Create New Admin' : 'Edit Account'); ?></h3>
                                    </div>
                                    <form action="?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>"
                                        method="POST">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control" required
                                                    value="<?php echo htmlspecialchars($editData['username'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label
                                                    class="form-label"><?php echo ($action === 'add' ? 'Password' : 'New Password (Optional)'); ?></label>
                                                <input type="password" name="password" class="form-control" <?php echo ($action === 'add' ? 'required' : ''); ?>>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role Permissions</label>
                                                <select name="role" class="form-select">
                                                    <option value="super_admin" <?php echo ($editData['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                    <option value="editor" <?php echo ($editData['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                    <option value="author" <?php echo ($editData['role'] ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="bi bi-save me-1"></i>
                                                <?php echo ($action === 'add' ? 'Generate Account' : 'Save Changes'); ?>
                                            </button>
                                            <a href="?action=list" class="btn btn-default">Go Back</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- LIST VIEW (DEFAULT) -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="text-uppercase fw-bold text-secondary mb-0">System Administrators</h4>
                            <a href="?action=add" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill me-1"></i> Add New Admin
                            </a>
                        </div>

                        <div class="row">
                            <?php if (empty($users)): ?>
                                <div class="col-12">
                                    <div class="alert alert-light text-center border">
                                        No administrators found.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                        <div
                                            class="card card-outline card-<?php echo ($u['role'] === 'super_admin' ? 'purple' : ($u['role'] === 'editor' ? 'primary' : 'secondary')); ?> h-100 shadow-sm hover-shadow transition-all">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div
                                                        class="user-avatar-circle bg-gradient-<?php echo ($u['role'] === 'super_admin' ? 'purple' : ($u['role'] === 'editor' ? 'primary' : 'secondary')); ?> text-white bg-opacity-75 bg-secondary">
                                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                                    </div>
                                                    <span
                                                        class="badge <?php echo ($u['role'] === 'super_admin' ? 'text-bg-purple' : ($u['role'] === 'editor' ? 'text-bg-primary' : 'text-bg-secondary')); ?> rounded-pill">
                                                        <?php echo str_replace('_', ' ', $u['role']); ?>
                                                    </span>
                                                </div>

                                                <h5 class="card-title fw-bold text-truncate w-100 mb-1">
                                                    <?php echo htmlspecialchars($u['username']); ?>
                                                </h5>
                                                <p class="card-text text-muted small mb-4">
                                                    Joined <?php echo date('d M Y', strtotime($u['created_at'] ?? 'now')); ?>
                                                </p>

                                                <div class="d-flex gap-2">
                                                    <a href="?action=edit&id=<?php echo $u['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary flex-fill">Edit</a>
                                                    <button type="button"
                                                        onclick="confirmDelete('?action=delete&id=<?php echo $u['id']; ?>', 'This administrator account will be permanently deactivated.')"
                                                        class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
