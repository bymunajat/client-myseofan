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
    $files = array_diff(scandir($uploadsDir), array('.', '..', '.gitignore'));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Media Library - MySeoFan Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 25%, #6ee7b7 50%, #86efac 100%);
            min-height: 100vh;
        }

        .sidebar {
            height: 100vh;
            background: #065f46;
            color: white;
        }

        .nav-active {
            background: #047857;
            border-left: 4px solid #34d399;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Media Library</h3>
                <p class="text-xs text-gray-500 mt-0.5">Upload and manage images and files</p>
            </div>
        </header>

        <div class="p-8">
            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php foreach ($files as $f): ?>
                    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden group relative">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                            <?php
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])): ?>
                                <img src="../uploads/<?php echo $f; ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                            <?php else: ?>
                                <span class="text-xs font-bold text-gray-400 uppercase">
                                    <?php echo $ext; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3">
                            <p class="text-[10px] text-gray-400 truncate mb-2">
                                <?php echo htmlspecialchars($f); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <a href="../uploads/<?php echo $f; ?>" download
                                    class="text-[10px] font-bold text-emerald-500 hover:text-emerald-700 uppercase">Download</a>
                                <a href="?delete=<?php echo urlencode($f); ?>" onclick="return confirm('Delete this file?')"
                                    class="text-[10px] font-bold text-red-400 hover:text-red-600 uppercase">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($files)): ?>
                    <div
                        class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed text-gray-400 font-bold uppercase tracking-widest">
                        Library is empty
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>