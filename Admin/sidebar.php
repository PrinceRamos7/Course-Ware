<aside class="hidden md:flex flex-col min-h-screen w-20 md:w-64 bg-white/30 backdrop-blur-lg shadow-lg transition-all duration-300 ease-in-out sidebar">
    
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between p-6 sidebar-header">
        <h2 class="text-xl font-bold flex items-center space-x-2 sidebar-logo">
            <i class="fas fa-graduation-cap sidebar-logo-icon text-blue-600"></i>
            <span class="hidden md:inline">FixLearn</span>
        </h2>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 sidebar-nav">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        function renderLink($href, $icon, $label, $current_page_name) {
            $isActive = basename($_SERVER['PHP_SELF']) == $current_page_name;
            $class = "flex items-center space-x-3 px-4 py-3 rounded-lg font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200 sidebar-link";
            if ($isActive) {
                $class .= " bg-blue-100 text-blue-600";
            }
            echo "<a href='$href' class='$class'>
                    <i class='$icon sidebar-link-icon text-lg'></i>
                    <span class='hidden md:inline'>$label</span>
                  </a>";
        }

        renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php');
        renderLink('course.php', 'fas fa-book-open', 'Courses', 'course.php');
        renderLink('learners.php', 'fas fa-user', 'Learners', 'learners.php');
        renderLink('code_redeemer.php', 'fas fa-user-circle', 'Code Redemer', 'code_redeemer.php');
        renderLink('users.php', 'fas fa-user', 'Users', 'users.php');
        renderLink('profile.php', 'fas fa-user-circle', 'Profile', 'profile.php');
        renderLink('settings.php', 'fas fa-cog', 'Settings', 'settings.php');
        ?>
    </nav>
</aside>

<script>
const sidebar = document.querySelector('.sidebar');

sidebar.addEventListener('mouseenter', () => {
  sidebar.classList.add('w-64'); // expand width
  sidebar.querySelectorAll('.sidebar-logo span, .sidebar-link span').forEach(el => el.classList.remove('hidden'));
});

sidebar.addEventListener('mouseleave', () => {
  sidebar.classList.remove('w-64'); // collapse width
  sidebar.querySelectorAll('.sidebar-logo span, .sidebar-link span').forEach(el => el.classList.add('hidden'));
});
</script>
