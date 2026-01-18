<?php
$current_page = basename($_SERVER['PHP_SELF']);
$_sb_action = $_GET['action'] ?? '';
$user_role = $_SESSION['role'] ?? 'super_admin';

// Helper for active state
function isActive($page, $action = null) {
    global $current_page, $_sb_action;
    if ($action) {
        return ($current_page == $page && $_sb_action == $action) ? 'active' : '';
    }
    return ($current_page == $page) ? 'active' : '';
}

function isMenuOpen($pages) {
    global $current_page;
    return in_array($current_page, $pages) ? 'menu-open' : '';
}

// Helper to determine if a group is active
function isGroupActive($pages) {
    global $current_page;
    return in_array($current_page, $pages) ? 'active' : '';
}
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text fw-light">MySeoFan Admin</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">CONTENT MANAGEMENT</li>
                
                <li class="nav-item <?php echo isMenuOpen(['blog.php']); ?>">
                    <a href="#" class="nav-link <?php echo isGroupActive(['blog.php']); ?>">
                        <i class="nav-icon bi bi-pencil-square"></i>
                        <p>
                            Blog Posts
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="blog.php?action=list&filter_lang=en" class="nav-link <?php echo isActive('blog.php') && $_sb_action != 'add' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>All Posts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="blog.php?action=add" class="nav-link <?php echo isActive('blog.php', 'add'); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Create New</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
                <li class="nav-item <?php echo isMenuOpen(['pages.php', 'menus.php']); ?>">
                    <a href="#" class="nav-link <?php echo isGroupActive(['pages.php', 'menus.php']); ?>">
                        <i class="nav-icon bi bi-file-earmark-text"></i>
                        <p>
                            Pages & Menus
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="pages.php?action=list" class="nav-link <?php echo isActive('pages.php'); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>All Pages</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="menus.php?menu_location=header" class="nav-link <?php echo (isset($_GET['menu_location']) && $_GET['menu_location'] == 'header') ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Header Menu</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="menus.php?menu_location=footer" class="nav-link <?php echo (isset($_GET['menu_location']) && $_GET['menu_location'] == 'footer') ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Footer Menu</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
                <li class="nav-header">MEDIA & ASSETS</li>
                <li class="nav-item">
                    <a href="media.php" class="nav-link <?php echo isActive('media.php'); ?>">
                        <i class="nav-icon bi bi-images"></i>
                        <p>Media Library</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="translations.php" class="nav-link <?php echo isActive('translations.php'); ?>">
                        <i class="nav-icon bi bi-translate"></i>
                        <p>Site Translations</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user_role === 'super_admin'): ?>
                <li class="nav-header">SYSTEM CONFIG</li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link <?php echo isActive('settings.php'); ?>">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Site Settings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="seo.php" class="nav-link <?php echo isActive('seo.php'); ?>">
                        <i class="nav-icon bi bi-search"></i>
                        <p>SEO Manager</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="redirects.php" class="nav-link <?php echo isActive('redirects.php'); ?>">
                        <i class="nav-icon bi bi-arrow-repeat"></i>
                        <p>Redirects</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo isActive('users.php'); ?>">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Admin Management</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logs.php" class="nav-link <?php echo isActive('logs.php'); ?>">
                        <i class="nav-icon bi bi-journal-text"></i>
                        <p>Activity Logs</p>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
    </div>
</aside>
