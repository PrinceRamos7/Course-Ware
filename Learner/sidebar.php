
<style>
    .custom-scrollbar-hide::-webkit-scrollbar { 
        display: none; 
    }
    .custom-scrollbar-hide { 
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }

    /* Active Link Accent */
    .active-link {
        background-color: var(--color-card-border); /* Slightly darker background */
    }
    .active-link .fa-solid, .active-link .link-text {
        color: var(--color-heading-secondary) !important;
        font-weight: 700;
        /* GLOW REMOVED: filter: drop-shadow(0 0 2px var(--color-heading-secondary)); */ 
    }
    
    /* Level Bar Progress Fill */
    .level-progress-fill {
        background-color: var(--color-heading-secondary);
        transition: width 0.5s ease;
    }
</style>

<aside id="sidebar" 
    class="hidden md:flex flex-col fixed left-0 top-0 bottom-0 h-screen w-16 
    bg-[var(--color-card-bg)] border-r border-[var(--color-card-border)] 
    backdrop-blur-lg transition-[width] duration-300 ease-in-out group hover:w-56 z-50 overflow-hidden">
    
    <div class="p-4 flex items-center space-x-2 border-b border-[var(--color-card-border)] h-16 flex-shrink-0">
        <img src="../images/isu-logo.png" alt="ISU Logo" class="w-8 h-8 object-contain">
        <h1 class="text-xl font-extrabold tracking-wider bg-gradient-to-r bg-clip-text text-transparent from-green-600 to-green-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
            ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
        </h1>
    </div>

    <nav class="flex-1 px-3 pt-4 space-y-2 overflow-y-auto custom-scrollbar-hide"> 
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);

        function renderLink($href, $icon, $label, $current_page_name, $current_page) {
            $isActive = $current_page === $current_page_name;
            // Base classes for all links
            $baseClass = "flex items-center space-x-3 px-3 py-2 rounded-lg transition-all relative hover:bg-[var(--color-card-border)]";
            $iconClass = "w-5 text-[var(--color-icon)] transition-colors duration-150";
            
            // Text visibility: Uses group-hover:opacity-100 for expansion
            $textClass = "link-text opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[var(--color-text)] whitespace-nowrap";

            if ($isActive) {
                // Apply specific active styles via active-link class
                $baseClass .= " active-link";
            }

            echo "
            <a href='$href' class='$baseClass'>
                <i class='$icon $iconClass'></i>
                <span class='$textClass'>$label</span>
            </a>";
        }

        // --- CORE NAVIGATION ---
        renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
        renderLink('courses.php', 'fas fa-layer-group', 'Courses', 'courses.php', $current_page);
        renderLink('modules.php', 'fas fa-book', 'Modules', 'modules.php', $current_page);
        renderLink('achievements.php', 'fas fa-trophy', 'Achievements', 'achievements.php', $current_page);
        
        echo '<div class="h-px mx-3 my-4 bg-[var(--color-card-border)]"></div>'; // Divider
        
        // --- UTILITY NAVIGATION ---
        renderLink('profile.php', 'fas fa-user', 'Profile', 'profile.php', $current_page);
        renderLink('history.php', 'fas fa-history', 'Activity History', 'history.php', $current_page);
        renderLink('support.php', 'fas fa-question-circle', 'Help & Support', 'support.php', $current_page);
        renderLink('settings.php', 'fas fa-cog', 'Settings', 'settings.php', $current_page);
        ?>
    </nav>

    <div class="p-2 pt-0 flex-shrink-0">
        <div class="px-3 py-3 bg-[var(--color-card-border)] rounded-lg flex flex-col justify-center space-y-1 w-full transition-all duration-300 ease-in-out">
            
            <div class="flex items-center space-x-2">
                <i class="fas fa-rocket text-xl text-[var(--color-heading-secondary)] flex-shrink-0"></i>
                <div class="flex justify-between w-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    <div class="text-sm font-semibold text-[var(--color-text)]">
                        Level: <span class="text-[var(--color-heading)]">7</span>
                    </div>
                    <div class="text-xs text-[var(--color-text-secondary)] font-mono">
                        3450 / 5000 XP
                    </div>
                </div>
            </div>
            
            <div class="h-1 rounded-full w-full" style="background-color: var(--color-progress-bg);">
                <div class="h-1 rounded-full level-progress-fill" style="width: 69%;"></div>
            </div>
        </div>
    </div>
    
    <div class="p-2 flex-shrink-0">
        <a href="logout.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg transition relative bg-red-600/10 hover:bg-red-600/20 group-hover:hover:bg-red-600/20">
            <i class="fas fa-sign-out-alt w-5 transition text-red-500"></i>
            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-red-500 font-semibold whitespace-nowrap">
                Log Out
            </span>
        </a>
    </div>
</aside>