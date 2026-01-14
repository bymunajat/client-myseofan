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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="flex bg-gray-50/50">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen relative overflow-y-auto">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-gray-100 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Activity Logs</h1>
                    <p class="text-sm text-gray-400 font-medium mt-1">Audit trail of system activities</p>
                </div>
                <div class="flex items-center gap-4">
                    <span
                        class="bg-gray-100 text-gray-500 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                        Total:
                        <?php echo number_format($total_logs); ?> Events
                    </span>
                </div>
            </div>
        </header>

        <div class="p-8 max-w-7xl mx-auto">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-48">Date &
                                Time</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-40">Admin
                            </th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-40">Action
                            </th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Details
                            </th>
                            <th
                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-32 text-right">
                                IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <span class="text-sm font-bold text-gray-700 block">
                                        <?php echo date('M d, Y', strtotime($log['created_at'])); ?>
                                    </span>
                                    <span class="text-xs font-mono text-gray-400">
                                        <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xs">
                                            <?php echo strtoupper(substr($log['username'] ?? '?', 0, 1)); ?>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700">
                                            <?php echo htmlspecialchars($log['username'] ?? 'System/Unknown'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider
                                    <?php
                                    if (strpos($log['action'], 'delete') !== false)
                                        echo 'bg-red-50 text-red-600';
                                    elseif (strpos($log['action'], 'create') !== false)
                                        echo 'bg-emerald-50 text-emerald-600';
                                    elseif (strpos($log['action'], 'update') !== false)
                                        echo 'bg-blue-50 text-blue-600';
                                    else
                                        echo 'bg-gray-100 text-gray-600';
                                    ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-medium text-gray-600 line-clamp-2"
                                        title="<?php echo htmlspecialchars($log['details']); ?>">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </p>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center text-gray-400">
                                    <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                    <p class="text-sm font-bold">No activity recorded yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-8 gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"
                            class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-sm transition-all 
                        <?php echo $i == $page ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-gray-400 hover:bg-emerald-50 hover:text-emerald-600'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>