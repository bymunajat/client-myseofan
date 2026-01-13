<?php
$current_page = basename($_SERVER['PHP_SELF']);
$_sb_action = $_GET['action'] ?? '';

// Session Healing: If role is missing but logged in, fetch from DB
if (isset($_SESSION['admin_id']) && (!isset($_SESSION['role']) || empty($_SESSION['role'])) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $_SESSION['role'] = $stmt->fetchColumn() ?: 'super_admin';
}
$user_role = $_SESSION['role'] ?? 'super_admin';
if (empty($user_role))
    $user_role = 'super_admin';
?>
<!-- Sidebar -->
<aside
    class="sidebar w-64 bg-[#111827] flex flex-col flex-shrink-0 h-screen sticky top-0 overflow-y-auto custom-scrollbar">
    <div class="p-8">
        <h2 class="text-xl font-bold text-emerald-500">MySeoFan Admin</h2>
    </div>
    <nav class="mt-4 px-4 space-y-1 flex-1">
        <!-- Dashboard -->
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'dashboard.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>


        <!-- Group: Main Content -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Content Management</p>

            <!-- Blog Sub-menu -->
            <div class="mb-1">
                <button onclick="toggleSection('blog-submenu')"
                    class="w-full flex items-center justify-between px-4 py-2 text-gray-400 hover:text-white transition-all rounded-xl hover:bg-gray-800 text-left <?php echo $current_page == 'blog.php' ? 'bg-gray-800/50' : ''; ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h3" />
                        </svg>
                        <span class="text-sm font-medium">Articles & Blog</span>
                    </div>
                    <svg id="blog-submenu-chevron"
                        class="w-3 h-3 transition-transform <?php echo $current_page == 'blog.php' ? 'rotate-180' : ''; ?>"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="blog-submenu"
                    class="<?php echo $current_page == 'blog.php' ? 'block' : 'hidden'; ?> mt-1 ml-6 border-l border-gray-700 pl-2 space-y-1">
                    <a href="blog.php?action=list"
                        class="block py-1 text-xs <?php echo ($current_page == 'blog.php' && $_sb_action != 'add') ? 'text-emerald-400 font-bold' : 'text-gray-500 hover:text-white'; ?>">Manage
                        Posts</a>
                    <a href="blog.php?action=add"
                        class="block py-1 text-xs <?php echo ($current_page == 'blog.php' && $_sb_action == 'add') ? 'text-emerald-400 font-bold' : 'text-gray-500 hover:text-white'; ?>">Create
                        New</a>
                </div>
            </div>

            <!-- Static Pages Group -->
            <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
                <div class="mb-1">
                    <button onclick="toggleSection('static-pages-submenu')"
                        class="w-full flex items-center justify-between px-4 py-2 text-gray-400 hover:text-white transition-all rounded-xl hover:bg-gray-800 text-left <?php echo $current_page == 'pages.php' ? 'bg-gray-800/50' : ''; ?>">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium">Static Pages</span>
                        </div>
                        <svg id="static-pages-submenu-chevron"
                            class="w-3 h-3 transition-transform <?php echo $current_page == 'pages.php' ? 'rotate-180' : ''; ?>"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="static-pages-submenu"
                        class="<?php echo in_array($current_page, ['pages.php', 'menus.php']) ? 'block' : 'hidden'; ?> mt-1 ml-6 border-l border-gray-700 pl-2 space-y-1">

                        <!-- Page Content (was All Pages) -->
                        <a href="pages.php?action=list"
                            class="block py-1 text-xs <?php echo ($current_page == 'pages.php') ? 'text-emerald-400 font-bold' : 'text-gray-500 hover:text-white'; ?>">
                            Page Content
                        </a>

                        <!-- Header Menu -->
                        <a href="menus.php?menu_location=header"
                            class="block py-1 text-xs <?php echo ($current_page == 'menus.php' && ($_GET['menu_location'] ?? '') == 'header') ? 'text-emerald-400 font-bold' : 'text-gray-500 hover:text-white'; ?>">
                            Header Menu
                        </a>

                        <!-- Footer Menu -->
                        <a href="menus.php?menu_location=footer"
                            class="block py-1 text-xs <?php echo ($current_page == 'menus.php' && ($_GET['menu_location'] ?? '') == 'footer') ? 'text-emerald-400 font-bold' : 'text-gray-500 hover:text-white'; ?>">
                            Footer Menu
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Group: Media & Assets -->
        <?php if (in_array($user_role, ['super_admin', 'editor'])): ?>
            <div class="pt-4 pb-2">
                <p class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Media & Assets</p>
                <a href="media.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'media.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Media Library</span>
                </a>

                <!-- UNHIDDEN -->
                <a href="translations.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'translations.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 9.19 15.683 3 20" />
                    </svg>
                    <span>Site Translations</span>
                </a>

            </div>
        <?php endif; ?>

        <!-- Group: System -->
        <?php if ($user_role === 'super_admin'): ?>
            <div class="pt-4 pb-2">
                <p class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">System Config</p>
                <a href="settings.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'settings.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Site Settings</span>
                </a>
                <a href="seo.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'seo.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>SEO Manager</span>
                </a>
                <a href="redirects.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'redirects.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Redirects</span>
                </a>
                <a href="users.php"
                    class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'users.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 0 0112 0v1zm0 0h6v-1a6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Admin Management</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Group: Account -->
        <div class="pt-8 pb-8 mt-auto border-t border-gray-800">
            <p class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Account</p>
            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm transition-all <?php echo $current_page == 'profile.php' ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'; ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>My Profile</span>
            </a>
            <a href="logout.php"
                class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm text-red-400 hover:bg-red-400/10 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleSection(id) {
            const element = document.getElementById(id);
            const chevron = document.getElementById(id + '-chevron');

            if (element && element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                element.classList.add('block');
                if (chevron) chevron.classList.add('rotate-180');
            } else if (element) {
                element.classList.add('hidden');
                element.classList.remove('block');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function confirmDelete(url, message = 'You won\'t be able to revert this!') {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                borderRadius: '1.5rem',
                customClass: {
                    popup: 'rounded-[1.5rem] border-none shadow-2xl',
                    confirmButton: 'rounded-xl font-bold px-6 py-3',
                    cancelButton: 'rounded-xl font-bold px-6 py-3'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        }
    </script>
</aside>