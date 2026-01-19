<?php
session_start();
require_once '../includes/db.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Pagination
$per_page = 20;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $per_page;

// Fetch Logs
$stmt_count = $pdo->query("SELECT COUNT(*) FROM activity_logs");
$total_logs = $stmt_count->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

$stmt = $pdo->prepare("SELECT l.*, a.username 
                       FROM activity_logs l 
                       LEFT JOIN admins a ON l.admin_id = a.id 
                       ORDER BY l.created_at DESC 
                       LIMIT ? OFFSET ?");
$stmt->execute([$per_page, $offset]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Activity Logs - MySeoFan Admin</title>
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
                            <h3 class="mb-0">Activity Logs</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Logs</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Audit Trail</h3>
                            <div class="card-tools">
                                <span class="badge text-bg-secondary">Total:
                                    <?php echo number_format($total_logs); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Admin</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th class="text-end">IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo date('M d, Y', strtotime($log['created_at'])); ?></strong><br>
                                                    <small
                                                        class="text-muted"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                                                            style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                                            <?php echo strtoupper(substr($log['username'] ?? '?', 0, 1)); ?>
                                                        </div>
                                                        <span><?php echo htmlspecialchars($log['username'] ?? 'System/Unknown'); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = 'text-bg-secondary';
                                                    if (strpos($log['action'], 'delete') !== false)
                                                        $badgeClass = 'text-bg-danger';
                                                    elseif (strpos($log['action'], 'create') !== false)
                                                        $badgeClass = 'text-bg-success';
                                                    elseif (strpos($log['action'], 'update') !== false)
                                                        $badgeClass = 'text-bg-primary';
                                                    ?>
                                                    <span
                                                        class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($log['action']); ?></span>
                                                </td>
                                                <td>
                                                    <span title="<?php echo htmlspecialchars($log['details']); ?>">
                                                        <?php echo htmlspecialchars($log['details']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <?php if (empty($logs)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="bi bi-clipboard-x display-4"></i>
                                                    <p class="mt-2">No activity recorded yet.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-end">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>
</body>

</html>
