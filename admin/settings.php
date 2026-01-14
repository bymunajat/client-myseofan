<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/Logger.php';

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
            Logger::log('update_settings', "Updated site settings", $_SESSION['admin_id'] ?? 0);
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
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
        }
    </style>
</head>

<body class="flex">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen bg-[#0f172a]">
        <header
            class="bg-[#1e293b] border-b-4 border-fuchsia-500/50 px-8 h-20 flex items-center justify-between shadow-lg shadow-black/20">
            <div>
                <h3 class="text-xl font-bold text-white">Settings</h3>
            </div>
        </header>

        <div class="p-12">


            <?php if ($message): ?>
                <div
                    class="bg-fuchsia-50 text-fuchsia-600 p-4 rounded-xl mb-6 font-medium border border-fuchsia-100 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
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
                                class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none font-bold text-black transition-colors focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase mb-2">Logo</label>
                            <input type="file" name="logo"
                                class="w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-fuchsia-50 file:text-fuchsia-700 hover:file:bg-fuchsia-100 transition-colors cursor-pointer">
                            <?php if (!empty($settings['logo_path'])): ?>
                                <img src="../<?php echo $settings['logo_path']; ?>" class="mt-2 h-10">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase mb-2">Favicon</label>
                            <input type="file" name="favicon"
                                class="w-full text-sm font-semibold text-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-fuchsia-50 file:text-fuchsia-700 hover:file:bg-fuchsia-100 transition-colors cursor-pointer">
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
                            class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none font-mono text-sm font-semibold text-black transition-colors focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100"><?php echo htmlspecialchars($settings['header_code'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-700 uppercase mb-2">Footer Code
                            Injection</label>
                        <textarea name="footer_code" rows="4"
                            class="w-full px-4 py-3 rounded-xl border-2 border-fuchsia-500 bg-white outline-none font-mono text-sm font-semibold text-black transition-colors focus:ring-4 focus:ring-fuchsia-500/20 shadow-sm shadow-fuchsia-100"><?php echo htmlspecialchars($settings['footer_code'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit"
                        class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-fuchsia-500/40 transition-all shadow-md shadow-fuchsia-900/20">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
        </div>
    </main>
</body>

</html>