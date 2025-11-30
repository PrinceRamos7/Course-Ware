<?php
// CHANGE ONLY THESE 2 LINES:
require_once __DIR__ . "/../pdoconfig.php";
require_once __DIR__ . "/functions/get_student_progress.php";

// EVERYTHING BELOW THIS STAYS EXACTLY THE SAME:
// Fetch current user's name for display
$user_id = $_SESSION['student_id'];
$stmt = $pdo->prepare('SELECT first_name, middle_name, last_name FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Use the data from get_student_progress.php with proper fallbacks
$user_level = $user_lvl ?? 1;
$intelligence_level = $intelligent_lvl ?? 1;
$progress = $progress ?? 0;
$intelligent_progress = $intelligent_progress ?? 0;
$user_exp = $user_exp ?? 0;
$intelligent_exp = $intelligent_exp ?? 0;
$next_goal_exp = $next_goal_exp ?? 100;
$next_goal_intelligent_exp = $next_goal_intelligent_exp ?? 100;
?>

<!-- The rest of your HTML/CSS/JavaScript stays exactly the same -->

<header><link href="https://fonts.googleapis.com/css2?family=Bungee&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</header>

<style>
    /* Your existing CSS styles remain the same */
    .custom-scrollbar-hide::-webkit-scrollbar { display: none; }
    .custom-scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .active-link { background-color: var(--color-card-border); }
    .active-link .fa-solid, .active-link .link-text { color: var(--color-heading-secondary) !important; font-weight: 700; }
    .level-progress-fill { background-color: var(--color-heading-secondary); transition: width 0.5s ease; }
    aside{ z-index: 100; }

    /* Mobile Sidebar Styles */
    @media (max-width: 768px) {
        #sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; width: 280px !important; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); }
        #sidebar.mobile-open { transform: translateX(0); }
        #sidebar.mobile-open .link-text, #sidebar.mobile-open .group-hover\:opacity-100, #sidebar.mobile-open .text-sm, #sidebar.mobile-open .flex.justify-between.w-full, #sidebar.mobile-open .bg-gradient-to-r { opacity: 1 !important; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 40; }
        .sidebar-overlay.active { display: block; }
    }

    /* Logout Confirmation Modal Styles */
    #logoutModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: var(--color-popup-bg); z-index: 1000; backdrop-filter: blur(4px); }
    #logoutModal.active { display: flex; align-items: center; justify-content: center; }
    .logout-modal-content { background: var(--color-popup-content-bg); border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); border: 2px solid var(--color-card-border); text-align: center; }
    .logout-icon { font-size: 3rem; color: var(--color-heading-secondary); margin-bottom: 1rem; }
    .logout-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin-bottom: 0.5rem; font-family: 'Inter', sans-serif; }
    .logout-message { color: var(--color-text-secondary); margin-bottom: 2rem; line-height: 1.5; }
    .logout-buttons { display: flex; gap: 1rem; justify-content: center; }
    .logout-btn { padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; font-family: 'Inter', sans-serif; transition: all 0.3s ease; border: none; cursor: pointer; min-width: 120px; }
    .logout-confirm { background: var(--color-button-primary); color: white; }
    .logout-confirm:hover { background: var(--color-button-primary-hover); transform: translateY(-2px); }
    .logout-cancel { background: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 2px solid var(--color-card-border); }
    .logout-cancel:hover { background: var(--color-card-border); transform: translateY(-2px); }
</style>

<!-- Logout Confirmation Modal -->
<div id="logoutModal">
    <div class="logout-modal-content">
        <div class="logout-icon"><i class="fas fa-sign-out-alt"></i></div>
        <h2 class="logout-title">Ready to Log Out?</h2>
        <p class="logout-message">Are you sure you want to log out?<br>Your progress has been saved and you can continue learning when you return!</p>
        <div class="logout-buttons">
            <button class="logout-btn logout-cancel">Stay Learning</button>
            <a href="login.php" class="logout-btn logout-confirm">Yes, Log Out</a>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay md:hidden"></div>

<aside id="sidebar" class="fixed left-0 top-0 bottom-0 h-screen w-16 bg-[var(--color-card-bg)] border-r border-[var(--color-card-border)] backdrop-blur-lg transition-all duration-300 ease-in-out group hover:w-56 overflow-hidden md:translate-x-0 -translate-x-full flex flex-col">
    
    <!-- Logo Section -->
    <div class="p-4 flex items-center space-x-2 border-b border-[var(--color-card-border)] h-16 flex-shrink-0">
        <img src="../images/isu-logo.png" alt="ISU Logo" class="w-8 h-8 object-contain">
        <h1 class="text-xl font-extrabold tracking-wider bg-gradient-to-r bg-clip-text text-transparent from-green-600 to-green-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
            ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
        </h1>
    </div>

    <!-- Main Navigation Links -->
    <nav class="flex-1 px-3 pt-4 space-y-2 overflow-y-auto custom-scrollbar-hide"> 
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);

        function renderLink($href, $icon, $label, $current_page_name, $current_page) {
            $isActive = $current_page === $current_page_name;
            $baseClass = "flex items-center space-x-3 px-3 py-2 rounded-lg transition-all relative hover:bg-[var(--color-card-border)]";
            $iconClass = "w-5 text-[var(--color-icon)] transition-colors duration-150";
            $textClass = "link-text opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[var(--color-text)] whitespace-nowrap";

            if ($isActive) {
                $baseClass .= " active-link";
            }

            echo "<a href='$href' class='$baseClass'><i class='$icon $iconClass'></i><span class='$textClass'>$label</span></a>";
        }

        // Your navigation logic remains the same
        if ($_SESSION['current_page'] == "dashboard") {
            renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
            renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
        } elseif ($_SESSION['current_page'] == "course") {
            renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
            renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
        } elseif ($_SESSION['current_page'] == "module") {
            renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
            renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
            renderLink('javascript:void(0)', 'fas fa-book', 'Modules', 'modules.php', $current_page);
        } elseif ($_SESSION['current_page'] == "topic") {
            renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
            renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
            renderLink('modules.php?course_id=' . $course_id . '', 'fas fa-book', 'Modules', 'modules.php', $current_page);
            renderLink('javascript:void(0)', 'fas fa-book-open', 'Topics', 'topics.php', $current_page);
        } else {
            renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
            renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
            renderLink('modules.php?course_id=' . $course_id . '', 'fas fa-book', 'Modules', 'modules.php', $current_page);
            renderLink('topicCard.php?course_id='.$_SESSION['course_id'].'&module_id='.$_SESSION['module_id'].'', 'fas fa-book-open', 'Topics', 'topics.php', $current_page);
        }
        renderLink('achievements.php', 'fas fa-trophy', 'Achievements', 'achievements.php', $current_page);
        echo '<div class="h-px mx-3 my-4 bg-[var(--color-card-border)]"></div>';
        
        // Utility Navigation
        renderLink('profile.php', 'fas fa-user', 'Profile', 'profile.php', $current_page); 
        renderLink('settings.php', 'fas fa-cog', 'Settings', 'settings.php', $current_page);
        ?>
    </nav>

    <!-- Bottom Section: Intelligence, Experience, and Logout -->
    <div class="mt-auto border-t border-[var(--color-card-border)] pt-2 pb-4 flex-shrink-0">
        <!-- Intelligence Section -->
        <div class="px-3 py-3 mb-2 border-b-4 border-[var(--color-card-border)] rounded-lg flex flex-col justify-center space-y-1 w-full transition-all duration-300 ease-in-out">
            <div class="text-sm font-mono font-semibold w-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Intelligence</div> 
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-brain text-xl text-[var(--color-heading-secondary)] flex-shrink-0"></i>
                <div class="flex justify-between w-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    <div class="text-sm font-semibold text-[var(--color-text)]">Level: <span class="text-[var(--color-heading)]"><?php echo $intelligence_level; ?></span></div>
                    <div class="text-xs text-[var(--color-text-secondary)] font-mono"><?php echo $intelligent_exp; ?> / <?php echo $next_goal_intelligent_exp; ?> XP</div>
                </div>
            </div>
            <div class="h-1 rounded-full w-full" style="background-color: var(--color-progress-bg);">
                <div class="h-1 rounded-full level-progress-fill" style="width: <?php echo $intelligent_progress; ?>%;"></div>
            </div>
        </div>

        <!-- Experience Section -->
        <div class="px-3 py-3 mb-2 border-b-4 border-[var(--color-card-border)] rounded-lg flex flex-col justify-center space-y-1 w-full transition-all duration-300 ease-in-out">
            <div class="text-sm font-mono font-semibold w-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">Experience</div> 
            <div class="flex items-center space-x-2">
                <i class="fas fa-rocket text-xl text-[var(--color-heading-secondary)] flex-shrink-0"></i>
                <div class="flex justify-between w-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    <div class="text-sm font-semibold text-[var(--color-text)]">Level: <span class="text-[var(--color-heading)]"><?php echo $user_level; ?></span></div>
                    <div class="text-xs text-[var(--color-text-secondary)] font-mono"><?php echo $user_exp; ?> / <?php echo $next_goal_exp; ?> XP</div>
                </div>
            </div>
            <div class="h-1 rounded-full w-full" style="background-color: var(--color-progress-bg);">
                <div class="h-1 rounded-full level-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
            </div>
        </div>
        
        <!-- Logout Button -->
        <div class="px-3">
            <button id="logoutButton" class="flex items-center space-x-3 px-3 py-2 rounded-lg transition relative bg-red-600/10 hover:bg-red-600/20 group-hover:hover:bg-red-600/20 w-full text-left">
                <i class="fas fa-sign-out-alt w-5 transition text-red-500"></i>
                <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-red-500 font-semibold whitespace-nowrap">Log Out</span>
            </button>
        </div>
    </div>
</aside>

<script>
    // Your existing JavaScript remains the same
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;
        
        if (mobileMenuButton && sidebar && overlay) {
            function openSidebar() { sidebar.classList.add('mobile-open'); overlay.classList.add('active'); body.classList.add('sidebar-open'); }
            function closeSidebar() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('active'); body.classList.remove('sidebar-open'); }
            mobileMenuButton.addEventListener('click', openSidebar);
            overlay.addEventListener('click', closeSidebar);
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => { link.addEventListener('click', closeSidebar); });
        }

        const logoutButton = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const cancelButton = document.querySelector('.logout-cancel');
        const confirmButton = document.querySelector('.logout-confirm');

        if (logoutButton && logoutModal) {
            logoutButton.addEventListener('click', function() { logoutModal.classList.add('active'); document.body.style.overflow = 'hidden'; });
            cancelButton.addEventListener('click', function() { logoutModal.classList.remove('active'); document.body.style.overflow = ''; });
            logoutModal.addEventListener('click', function(e) { if (e.target === logoutModal) { logoutModal.classList.remove('active'); document.body.style.overflow = ''; } });
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && logoutModal.classList.contains('active')) { logoutModal.classList.remove('active'); document.body.style.overflow = ''; } });
            confirmButton.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-2px)'; });
            confirmButton.addEventListener('mouseleave', function() { this.style.transform = 'translateY(0)'; });
        }
    });
</script>