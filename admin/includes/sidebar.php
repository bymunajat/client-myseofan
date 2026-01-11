<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar w-64 hidden md:block flex-shrink-0">
    <div class="p-8">
        <h2 class="text-xl font-bold text-emerald-500">MySeoFan Admin</h2>
    </div>
    <nav class="mt-4 px-4 space-y-2 overflow-y-auto" style="max-height: calc(100vh - 120px);">
        <!-- Dashboard -->
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'dashboard.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Admin Profile -->
        <a href="profile.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'profile.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Admin Profile</span>
        </a>

        <!-- Site Settings -->
        <a href="settings.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'settings.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Site Settings</span>
        </a>

        <!-- SEO Manager -->
        <a href="seo.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'seo.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>SEO Manager</span>
        </a>

        <!-- Media Library -->
        <a href="media.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'media.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Media Library</span>
        </a>

        <!-- Translations -->
        <a href="translations.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'translations.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 9.19 15.683 3 20" />
            </svg>
            <span>Translations</span>
        </a>

        <!-- Blog Posts -->
        <a href="blog.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'blog.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m-6 4h3" />
            </svg>
            <span>Blog Posts</span>
        </a>

        <!-- Page Manager -->
        <a href="pages.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo $current_page == 'pages.php' ? 'nav-active text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>Page Manager</span>
        </a>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-800"></div>

        <!-- Logout -->
        <a href="logout.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Logout</span>
        </a>
    </nav>
</aside>