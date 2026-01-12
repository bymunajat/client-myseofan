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
$_curr_lang = $_GET['lang'] ?? 'en';
$location = $_GET['menu_location'] ?? 'header';

// --- AJAX HANDLER: Save Menu Structure ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_menu') {
    $menuData = json_decode($_POST['menu_data'], true);

    try {
        $pdo->beginTransaction();

        // 1. Delete existing items for this specific location/language combo
        //    (In a real WP system we might update, but delete-replace is safer for structure changes here)
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_location = ? AND lang_code = ?");
        $stmt->execute([$location, $_curr_lang]);

        // 2. Recursive function to insert items
        function insertMenuItems($items, $pdo, $location, $lang, $parentId = 0)
        {
            foreach ($items as $index => $item) {
                $uniqueId = $item['id']; // This might be "page-12" or "new-34"

                // Extract proper data
                // data-type, data-related-id, data-label, etc.
                $type = $item['type'];
                $relatedId = ($type === 'page') ? $item['related_id'] : null;
                $label = $item['label'];
                $url = ($type === 'custom_link') ? $item['url'] : null;

                $stmt = $pdo->prepare("INSERT INTO menu_items (menu_location, lang_code, type, label, url, related_id, parent_id, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$location, $lang, $type, $label, $url, $relatedId, $parentId, $index]);

                $newId = $pdo->lastInsertId();

                if (isset($item['children']) && !empty($item['children'])) {
                    insertMenuItems($item['children'], $pdo, $location, $lang, $newId);
                }
            }
        }

        insertMenuItems($menuData, $pdo, $location, $_curr_lang);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Menu saved successfully!']);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// --- Fetch Data for UI ---

// 1. Existing Pages for "Add to Menu" sidebar
$stmt = $pdo->prepare("SELECT id, title FROM pages WHERE lang_code = ? ORDER BY title ASC");
$stmt->execute([$_curr_lang]);
$availPages = $stmt->fetchAll();

// 2. Current Menu Items
function buildMenuTree($items, $parentId = 0)
{
    $branch = [];
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildMenuTree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }
    }
    return $branch;
}

$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_location = ? AND lang_code = ? ORDER BY sort_order ASC");
$stmt->execute([$location, $_curr_lang]);
$rawItems = $stmt->fetchAll();
$menuTree = buildMenuTree($rawItems);

$available_langs = [
    'en' => '🇺🇸 English',
    'id' => '🇮🇩 Indonesia',
    'es' => '🇪🇸 Español',
    'fr' => '🇫🇷 Français',
    'de' => '🇩🇪 DE',
    'ja' => '🇯🇵 日本語'
];

$page_title = ucfirst($location) . " Menu Manager";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $page_title; ?> - Admin
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f3f4f6;
        }

        .nested-sortable {
            min-height: 50px;
            padding-left: 30px;
            border-left: 2px dashed #e5e7eb;
        }

        .menu-item {
            cursor: move;
        }

        .menu-item-handle {
            cursor: move;
        }
    </style>
</head>

<body class="flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 min-h-screen">
        <header class="bg-white border-b border-gray-200 px-8 h-20 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">
                <?php echo $page_title; ?>
            </h3>
            <div class="flex items-center gap-4">
                <button onclick="saveMenu()" id="saveBtn"
                    class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-all">
                    Save Menu
                </button>
            </div>
        </header>

        <div class="p-8">
            <!-- Language Tabs -->
            <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <?php foreach ($available_langs as $code => $label): ?>
                    <a href="?menu_location=<?php echo $location; ?>&lang=<?php echo $code; ?>"
                        class="px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 <?php echo $_curr_lang === $code ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'text-gray-500 hover:bg-gray-50'; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-12 gap-8">
                <!-- Left Panel: Add Items -->
                <div class="col-span-4 space-y-6">
                    <!-- Add Pages -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h4 class="font-bold text-gray-800 mb-4">Add Pages</h4>
                        <div class="max-h-60 overflow-y-auto space-y-2 mb-4 pr-2">
                            <?php foreach ($availPages as $p): ?>
                                <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" class="page-checkbox w-4 h-4 text-emerald-600 rounded"
                                        data-id="<?php echo $p['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($p['title']); ?>">
                                    <span class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button onclick="addPages()"
                            class="w-full py-2 border border-gray-300 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Add to Menu
                        </button>
                    </div>

                    <!-- Add Custom Link -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h4 class="font-bold text-gray-800 mb-4">Custom Link</h4>
                        <div class="space-y-4 mb-4">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">URL / Link</label>
                                <input type="text" id="custom-url" placeholder="https://"
                                    class="w-full p-2 border border-gray-200 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Label Text</label>
                                <input type="text" id="custom-label" placeholder="Menu Text"
                                    class="w-full p-2 border border-gray-200 rounded-lg text-sm">
                            </div>
                        </div>
                        <button onclick="addCustomLink()"
                            class="w-full py-2 border border-gray-300 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Add to Menu
                        </button>
                    </div>

                    <!-- Add Label (Header Group) -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h4 class="font-bold text-gray-800 mb-4">Section Label</h4>
                        <p class="text-xs text-gray-400 mb-4">Use this for Footer column headers.</p>
                        <div class="mb-4">
                            <label class="text-xs font-bold text-gray-400 uppercase">Label Text</label>
                            <input type="text" id="section-label" placeholder="e.g. Company"
                                class="w-full p-2 border border-gray-200 rounded-lg text-sm">
                        </div>
                        <button onclick="addLabel()"
                            class="w-full py-2 border border-gray-300 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Add to Menu
                        </button>
                    </div>
                </div>

                <!-- Right Panel: Menu Structure -->
                <div class="col-span-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 min-h-[500px]">
                        <h4 class="font-bold text-gray-800 mb-2">Menu Structure</h4>
                        <p class="text-sm text-gray-400 mb-6">Drag items to reorder. Drag right to create a sub-menu.
                        </p>

                        <div id="menu-root" class="nested-sortable space-y-2 pb-12">
                            <!-- Items rendered via JS mostly, but we can pre-render PHP here if we want or just load via JSON. 
                                 For simplicity in this V1, let's pre-render PHP. -->
                            <?php
                            function renderMenuItem($item)
                            {
                                $typeLabel = ($item['type'] === 'page') ? 'Page' : (($item['type'] === 'label') ? 'Label' : 'Custom Link');
                                ob_start();
                                ?>
                                <div class="menu-item bg-white border border-gray-200 rounded-lg mb-2"
                                    data-type="<?php echo $item['type']; ?>"
                                    data-related-id="<?php echo $item['related_id'] ?? ''; ?>"
                                    data-url="<?php echo htmlspecialchars($item['url'] ?? ''); ?>"
                                    data-label="<?php echo htmlspecialchars($item['label']); ?>"
                                    data-id="<?php echo $item['id']; ?>"> <!-- Track ID for logic -->

                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-t-lg">
                                        <div class="flex items-center gap-3">
                                            <span class="cursor-move text-gray-400">☰</span>
                                            <span class="font-bold text-gray-700 item-label-display">
                                                <?php echo htmlspecialchars($item['label']); ?>
                                            </span>
                                            <span class="text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded">
                                                <?php echo $typeLabel; ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button onclick="toggleEdit(this)"
                                                class="text-xs text-blue-500 hover:underline">Edit</button>
                                            <button onclick="removeCreate(this)"
                                                class="text-xs text-red-500 hover:underline">Remove</button>
                                        </div>
                                    </div>

                                    <!-- Edit Drawer (Hidden) -->
                                    <div class="edit-drawer hidden p-3 border-t border-gray-100 text-sm space-y-2">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400">Navigation Label</label>
                                            <input type="text" class="edit-label-input w-full p-2 border rounded"
                                                value="<?php echo htmlspecialchars($item['label']); ?>"
                                                oninput="updateLabelLive(this)">
                                        </div>
                                        <?php if ($item['type'] === 'custom_link'): ?>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-400">URL</label>
                                                <input type="text" class="edit-url-input w-full p-2 border rounded"
                                                    value="<?php echo htmlspecialchars($item['url']); ?>">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nested Container -->
                                    <div class="nested-sortable pl-4 ml-4 border-l-2 border-gray-100 min-h-[10px] py-1">
                                        <?php
                                        if (isset($item['children'])) {
                                            foreach ($item['children'] as $child) {
                                                echo renderMenuItem($child);
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                                return ob_get_clean();
                            }

                            foreach ($menuTree as $rootItem) {
                                echo renderMenuItem($rootItem);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // --- Drag & Drop Logic ---
        function initSortable(el) {
            new Sortable(el, {
                group: 'nested',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                onEnd: function (evt) {
                    // Logic to handle potential max depth if needed (not implemented for simplicity)
                }
            });
        }

        // Initialize root and recursive children
        initSortable(document.getElementById('menu-root'));
        document.querySelectorAll('.nested-sortable').forEach(el => initSortable(el));

        // --- Item Management ---

        function createItemHTML(type, label, relatedId = '', url = '') {
            const template = `
                <div class="menu-item bg-white border border-gray-200 rounded-lg mb-2" 
                     data-type="${type}" data-related-id="${relatedId}" data-url="${url}" data-label="${label}" data-id="new-${Date.now()}-${Math.random()}">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-t-lg">
                        <div class="flex items-center gap-3">
                            <span class="cursor-move text-gray-400">☰</span>
                            <span class="font-bold text-gray-700 item-label-display">${label}</span>
                            <span class="text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded">${type === 'page' ? 'Page' : (type === 'label' ? 'Label' : 'Custom Link')}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="toggleEdit(this)" class="text-xs text-blue-500 hover:underline">Edit</button>
                            <button onclick="removeCreate(this)" class="text-xs text-red-500 hover:underline">Remove</button>
                        </div>
                    </div>
                    <div class="edit-drawer hidden p-3 border-t border-gray-100 text-sm space-y-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-400">Navigation Label</label>
                            <input type="text" class="edit-label-input w-full p-2 border rounded" value="${label}" oninput="updateLabelLive(this)">
                        </div>
                        ${type === 'custom_link' ? `
                        <div>
                            <label class="block text-xs font-bold text-gray-400">URL</label>
                            <input type="text" class="edit-url-input w-full p-2 border rounded" value="${url}">
                        </div>` : ''}
                    </div>
                    <div class="nested-sortable pl-4 ml-4 border-l-2 border-gray-100 min-h-[10px] py-1"></div>
                </div>
            `;
            return template;
        }

        function appendToRoot(htmlString) {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = htmlString.trim();
            const el = wrapper.firstElementChild;
            document.getElementById('menu-root').appendChild(el);
            initSortable(el.querySelector('.nested-sortable'));
        }

        function addPages() {
            const checks = document.querySelectorAll('.page-checkbox:checked');
            checks.forEach(c => {
                const id = c.getAttribute('data-id');
                const title = c.getAttribute('data-title');
                appendToRoot(createItemHTML('page', title, id));
                c.checked = false; // reset
            });
        }

        function addCustomLink() {
            const url = document.getElementById('custom-url').value;
            const label = document.getElementById('custom-label').value;
            if (!label) return alert("Label required");
            appendToRoot(createItemHTML('custom_link', label, '', url));

            // reset
            document.getElementById('custom-url').value = '';
            document.getElementById('custom-label').value = '';
        }

        function addLabel() {
            const label = document.getElementById('section-label').value;
            if (!label) return alert("Label required");
            appendToRoot(createItemHTML('label', label));
            document.getElementById('section-label').value = '';
        }

        function removeCreate(btn) {
            if (confirm('Remove this item?')) {
                btn.closest('.menu-item').remove();
            }
        }

        function toggleEdit(btn) {
            const drawer = btn.closest('.menu-item').querySelector('.edit-drawer');
            drawer.classList.toggle('hidden');
        }

        function updateLabelLive(input) {
            const newVal = input.value;
            const display = input.closest('.menu-item').querySelector('.item-label-display');
            display.textContent = newVal;
            // update data-att
            input.closest('.menu-item').setAttribute('data-label', newVal);
        }

        // --- Save Logic ---
        // Recursive function to scrape DOM tree into JSON
        function scrapMenu(container) {
            const items = [];
            Array.from(container.children).forEach(el => {
                if (!el.classList.contains('menu-item')) return;

                const data = {
                    id: el.getAttribute('data-id'),
                    type: el.getAttribute('data-type'),
                    label: el.querySelector('.edit-label-input').value, // Use input value directly
                    url: el.querySelector('.edit-url-input')?.value || el.getAttribute('data-url'),
                    related_id: el.getAttribute('data-related-id'),
                    children: []
                };

                const nestedContainer = el.querySelector('.nested-sortable');
                if (nestedContainer) {
                    data.children = scrapMenu(nestedContainer);
                }

                items.push(data);
            });
            return items;
        }

        function saveMenu() {
            const btn = document.getElementById('saveBtn');
            const originalText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;

            const menuData = scrapMenu(document.getElementById('menu-root'));

            const formData = new FormData();
            formData.append('action', 'save_menu');
            formData.append('menu_data', JSON.stringify(menuData));

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Menu saved successfully!');
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(e => alert('Network Error'))
                .finally(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                });
        }
    </script>
</body>

</html>