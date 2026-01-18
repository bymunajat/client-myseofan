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
$_curr_lang = 'en';
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



$page_title = ucfirst($location) . " Menu Manager";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?> - MySeoFan Admin</title>
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
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .nested-sortable {
            min-height: 50px;
            padding-left: 30px;
            border-left: 2px dashed #dee2e6;
        }

        .menu-item {
            cursor: move;
        }

        .menu-item-handle {
            cursor: move;
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
                            <h3 class="mb-0"><?php echo $page_title; ?></h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Menus</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-end mb-3">
                        <button onclick="saveMenu()" id="saveBtn" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Menu
                        </button>
                    </div>

                    <div class="row">
                        <!-- Left Panel: Add Items -->
                        <div class="col-lg-4">
                            <!-- Add Pages -->
                            <div class="card card-outline card-info mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Add Pages</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="max-h-60 overflow-y-auto mb-3 pe-2" style="max-height: 200px;">
                                        <?php foreach ($availPages as $p): ?>
                                            <div class="form-check">
                                                <input class="form-check-input page-checkbox" type="checkbox" value=""
                                                    id="pageCheck<?php echo $p['id']; ?>" data-id="<?php echo $p['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($p['title']); ?>">
                                                <label class="form-check-label" for="pageCheck<?php echo $p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['title']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button onclick="addPages()" class="btn btn-sm btn-outline-secondary w-100">Add to
                                        Menu</button>
                                </div>
                            </div>

                            <!-- Add Custom Link -->
                            <div class="card card-outline card-secondary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Custom Link</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label text-muted small text-uppercase fw-bold">URL</label>
                                        <input type="text" id="custom-url" placeholder="https://"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Label
                                            Text</label>
                                        <input type="text" id="custom-label" placeholder="Menu Text"
                                            class="form-control form-control-sm">
                                    </div>
                                    <button onclick="addCustomLink()" class="btn btn-sm btn-outline-secondary w-100">Add
                                        to Menu</button>
                                </div>
                            </div>

                            <!-- Add Label -->
                            <div class="card card-outline card-secondary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Section Label</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Use this for Footer column headers.</p>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Label
                                            Text</label>
                                        <input type="text" id="section-label" placeholder="e.g. Company"
                                            class="form-control form-control-sm">
                                    </div>
                                    <button onclick="addLabel()" class="btn btn-sm btn-outline-secondary w-100">Add to
                                        Menu</button>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Menu Structure -->
                        <div class="col-lg-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Menu Structure</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">Drag items to reorder. Drag right to create a
                                        sub-menu.</p>

                                    <div id="menu-root" class="nested-sortable pb-4">
                                        <?php
                                        function renderMenuItem($item)
                                        {
                                            $typeLabel = ($item['type'] === 'page') ? 'Page' : (($item['type'] === 'label') ? 'Label' : 'Custom');
                                            $badgeClass = ($item['type'] === 'page') ? 'text-bg-info' : (($item['type'] === 'label') ? 'text-bg-secondary' : 'text-bg-warning');
                                            ob_start();
                                            ?>
                                            <div class="menu-item card mb-2 shadow-sm"
                                                data-type="<?php echo $item['type']; ?>"
                                                data-related-id="<?php echo $item['related_id'] ?? ''; ?>"
                                                data-url="<?php echo htmlspecialchars($item['url'] ?? ''); ?>"
                                                data-label="<?php echo htmlspecialchars($item['label']); ?>"
                                                data-id="<?php echo $item['id']; ?>">

                                                <div
                                                    class="card-header d-flex justify-content-between align-items-center p-2 bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-grip-vertical text-muted cursor-move"></i>
                                                        <span
                                                            class="fw-bold item-label-display"><?php echo htmlspecialchars($item['label']); ?></span>
                                                        <span
                                                            class="badge <?php echo $badgeClass; ?>"><?php echo $typeLabel; ?></span>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button type="button" onclick="toggleEdit(this)"
                                                            class="btn btn-xs btn-link text-decoration-none">Edit</button>
                                                        <button type="button" onclick="removeCreate(this)"
                                                            class="btn btn-xs btn-link text-danger text-decoration-none">Remove</button>
                                                    </div>
                                                </div>

                                                <div class="edit-drawer d-none p-3 border-top bg-body">
                                                    <div class="mb-2">
                                                        <label class="form-label small text-muted">Navigation Label</label>
                                                        <input type="text"
                                                            class="form-control form-control-sm edit-label-input"
                                                            value="<?php echo htmlspecialchars($item['label']); ?>"
                                                            oninput="updateLabelLive(this)">
                                                    </div>
                                                    <?php if ($item['type'] === 'custom_link'): ?>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-muted">URL</label>
                                                            <input type="text"
                                                                class="form-control form-control-sm edit-url-input"
                                                                value="<?php echo htmlspecialchars($item['url']); ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="nested-sortable ms-4 mt-2 mb-2">
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
                </div>
            </div>
        </main>

        <?php include 'includes/footer_lte.php'; ?>
    </div>

    <?php include 'includes/scripts_lte.php'; ?>

    <script>
        // --- Drag & Drop Logic ---
        function initSortable(el) {
            new Sortable(el, {
                group: 'nested',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                handle: '.card-header', // Only drag by header
                onEnd: function (evt) {
                }
            });
        }

        initSortable(document.getElementById('menu-root'));
        document.querySelectorAll('.nested-sortable').forEach(el => initSortable(el));

        // --- Item Management ---

        function createItemHTML(type, label, relatedId = '', url = '') {
            const badgeClass = (type === 'page') ? 'text-bg-info' : ((type === 'label') ? 'text-bg-secondary' : 'text-bg-warning');
            const typeLabel = (type === 'page') ? 'Page' : ((type === 'label') ? 'Label' : 'Custom');

            const template = `
                <div class="menu-item card mb-2 shadow-sm" 
                     data-type="${type}" data-related-id="${relatedId}" data-url="${url}" data-label="${label}" data-id="new-${Date.now()}-${Math.random()}">
                    <div class="card-header d-flex justify-content-between align-items-center p-2 bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-grip-vertical text-muted cursor-move"></i>
                            <span class="fw-bold item-label-display">${label}</span>
                            <span class="badge ${badgeClass}">${typeLabel}</span>
                        </div>
                        <div class="btn-group">
                            <button type="button" onclick="toggleEdit(this)" class="btn btn-xs btn-link text-decoration-none">Edit</button>
                            <button type="button" onclick="removeCreate(this)" class="btn btn-xs btn-link text-danger text-decoration-none">Remove</button>
                        </div>
                    </div>
                    <div class="edit-drawer d-none p-3 border-top bg-body">
                        <div class="mb-2">
                            <label class="form-label small text-muted">Navigation Label</label>
                            <input type="text" class="form-control form-control-sm edit-label-input" value="${label}" oninput="updateLabelLive(this)">
                        </div>
                        ${type === 'custom_link' ? `
                        <div class="mb-2">
                            <label class="form-label small text-muted">URL</label>
                            <input type="text" class="form-control form-control-sm edit-url-input" value="${url}">
                        </div>` : ''}
                    </div>
                    <div class="nested-sortable ms-4 mt-2 mb-2"></div>
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
                c.checked = false;
            });
        }

        function addCustomLink() {
            const url = document.getElementById('custom-url').value;
            const label = document.getElementById('custom-label').value;
            if (!label) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Label text is required for custom links.',
                });
                return;
            }
            appendToRoot(createItemHTML('custom_link', label, '', url));
            document.getElementById('custom-url').value = '';
            document.getElementById('custom-label').value = '';
        }

        function addLabel() {
            const label = document.getElementById('section-label').value;
            if (!label) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a text for the label section.',
                });
                return;
            }
            appendToRoot(createItemHTML('label', label));
            document.getElementById('section-label').value = '';
        }

        function removeCreate(btn) {
            Swal.fire({
                title: 'Remove item?',
                text: "This will remove the item and its children.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.closest('.menu-item').remove();
                }
            });
        }

        function toggleEdit(btn) {
            const drawer = btn.closest('.menu-item').querySelector('.edit-drawer');
            drawer.classList.toggle('d-none');
        }

        function updateLabelLive(input) {
            const newVal = input.value;
            const display = input.closest('.menu-item').querySelector('.item-label-display');
            display.textContent = newVal;
            input.closest('.menu-item').setAttribute('data-label', newVal);
        }

        // --- Save Logic ---
        function scrapMenu(container) {
            const items = [];
            Array.from(container.children).forEach(el => {
                if (!el.classList.contains('menu-item')) return;

                const data = {
                    id: el.getAttribute('data-id'),
                    type: el.getAttribute('data-type'),
                    label: el.querySelector('.edit-label-input').value,
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
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Menu structure has been updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: 'Error: ' + data.error,
                        });
                    }
                })
                .catch(e => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Unable to connect to the server.',
                    });
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
</body>

</html>