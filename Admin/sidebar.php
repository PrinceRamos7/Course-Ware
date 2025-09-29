<?php
// Global check for context
$current_page = basename($_SERVER['PHP_SELF']);

// Retrieve contextual IDs from URL
$context_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$context_module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;
$context_assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : null;

/**
 * Renders a navigation link for the sidebar.
 * The $level parameter controls the padding for nested links.
 */
function renderLink($href, $icon, $label, $check_page, $current_page, $isContextual = false, $level = 0) {
    $isActive = $current_page === $check_page;
    
    // Base classes for all links
    $baseClass = "flex items-center px-3 py-2 rounded-lg transition-all relative hover:bg-[var(--color-card-border)]";
    $iconClass = "w-5 text-[var(--color-icon)] transition-colors duration-150 flex-shrink-0";
    // Text visibility: Hides when collapsed, shows on group-hover (sidebar expansion)
    $textClass = "link-text opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[var(--color-text)] whitespace-nowrap ml-3";

    if ($isActive) {
        $baseClass .= " active-link bg-[var(--color-active-link-bg)]";
    }
    
    // Style adjustments for nested/contextual items
    if ($isContextual) {
        // Calculate padding to align text correctly when expanded (W-56).
        // Base 20px icon width + 16px (4 units) per level for indentation
        $paddingLeft = 20 + ($level * 16); 
        
        // Custom padding left for alignment
        $baseClass .= " pl-[" . $paddingLeft . "px]"; 
        $iconClass = "w-5 text-[var(--color-text-secondary)] flex-shrink-0";
        
        // The text class remains the same for contextual links to appear when hovered
        $textClass = "link-text opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[var(--color-text)] whitespace-nowrap ml-3";
        
        if ($isActive) {
             $baseClass .= " font-semibold";
        }
    } else {
         // Default core link spacing
         $baseClass .= " space-x-3";
    }


    echo "
    <a href='$href' class='$baseClass'>
        <i class='$icon $iconClass'></i>
        <span class='$textClass'>$label</span>
    </a>";
}
?>
<style>
    /* ... (CSS remains the same) ... */
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
// --- CORE NAVIGATION ---
renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
renderLink('course.php', 'fas fa-book-open', 'Courses', 'course.php', $current_page);

// --- CONTEXTUAL LINKS (Visible on course selection, but only display when hovered) ---
if ($context_course_id) {
    // Key Change: Use 'hidden group-hover:block' to ensure this block only appears when the main sidebar is hovered.
    // Setting space-y-2 to match the main navigation spacing.
    echo '<div class="hidden group-hover:block space-y-2">'; 

    // 1. MODULE (Level 1)
    $module_link = "module.php?course_id=$context_course_id";
    renderLink($module_link, 'fas fa-chalkboard', 'Module Details', 'module.php', $current_page, true, 1);

    // 2. TOPICS (Level 2)
    $topic_link = "topic.php?course_id=$context_course_id" . ($context_module_id ? "&module_id=$context_module_id" : '');
    renderLink($topic_link, 'fas fa-book', 'Topics', 'topic.php', $current_page, true, 2);

    // 3. ASSESSMENT (Level 3)
    $assessment_link = "assessment.php?course_id=$context_course_id" . ($context_module_id ? "&module_id=$context_module_id" : '');
    renderLink($assessment_link, 'fas fa-cube', 'Assessment', 'assessment.php', $current_page, true, 3);
    
    // 4. QUESTIONS (Level 4)
    $questions_link = "questions.php?course_id=$context_course_id" . ($context_module_id ? "&module_id=$context_module_id" : '') . ($context_assessment_id ? "&assessment_id=$context_assessment_id" : '');
    renderLink($questions_link, 'fas fa-question', 'Questions', 'questions.php', $current_page, true, 4);

    echo '</div>'; // Close contextual links wrapper
}

renderLink('learners.php', 'fas fa-user-graduate', 'Learners', 'learners.php', $current_page);
renderLink('code_redeemer.php', 'fas fa-qrcode', 'Code Redeemer', 'code_redeemer.php', $current_page); 
renderLink('users.php', 'fas fa-user-tie', 'Users', 'users.php', $current_page);
renderLink('profile.php', 'fas fa-user-circle', 'Profile', 'profile.php', $current_page);
renderLink('settings.php', 'fas fa-cog', 'Settings', 'settings.php', $current_page);
?>
    </nav>

    <div class="p-2 flex-shrink-0">
        <a href="logout.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg transition relative bg-red-600/10 hover:bg-red-600/20 group-hover:hover:bg-red-600/20">
            <i class="fas fa-sign-out-alt w-5 transition text-red-500"></i>
            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-red-500 font-semibold whitespace-nowrap">
                Log Out
            </span>
        </a>
    </div>
</aside>