<?php
session_start();
require_once '../includes/db.php';

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
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b-4 border-emerald-300 px-8 h-20 flex items-center justify-between shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Settings</h3>
            </div>
        </header>

        <div class="p-12">


            <?php if ($message): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 max-w-4xl">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase mb-2">Website Name</label>
                            <input type="text" name="site_name"
                                value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>"
                                class="w-full px-4 py-3 rounded-xl border-2 border-emerald-400 bg-white outline-none focus:border-emerald-600 font-bold text-black">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase mb-2">Logo</label>
                            <input type="file" name="logo"
                                class="w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <?php if (!empty($settings['logo_path'])): ?>
                                <img src="../<?php echo $settings['logo_path']; ?>" class="mt-2 h-10">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase mb-2">Favicon</label>
                            <input type="file" name="favicon"
                                class="w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <?php if (!empty($settings['favicon_path'])): ?>
                                <img src="../<?php echo $settings['favicon_path']; ?>" class="mt-2 h-8">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase mb-2">Header Code Injection (e.g.
                            Google
                            Analytics)</label>
                        <textarea name="header_code" rows="4"
                            class="w-full px-4 py-3 rounded-xl border-2 border-emerald-400 bg-white outline-none focus:border-emerald-600 font-mono text-sm font-semibold text-black"><?php echo htmlspecialchars($settings['header_code'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase mb-2">Footer Code
                            Injection</label>
                        <textarea name="footer_code" rows="4"
                            class="w-full px-4 py-3 rounded-xl border-2 border-emerald-400 bg-white outline-none focus:border-emerald-600 font-mono text-sm font-semibold text-black"><?php echo htmlspecialchars($settings['footer_code'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit"
                        class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
        </div>
    </main>
</body>

</html>