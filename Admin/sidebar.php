<?php
// CRITICAL: Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection at the top
include '../pdoconfig.php';

// Global check for context
$current_page = basename($_SERVER['PHP_SELF']);

// Retrieve contextual IDs from URL
$context_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$context_module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null; 
$context_topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : null;
$context_assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : null;

// Store in session for persistence
// CRITICAL FIX: Only overwrite the session if the retrieved ID is greater than 0.
// If the URL is missing the ID, we use the session value (handled below).
if ($context_course_id > 0) $_SESSION['current_course_id'] = $context_course_id;
if ($context_module_id > 0) $_SESSION['current_module_id'] = $context_module_id;
if ($context_topic_id > 0) $_SESSION['current_topic_id'] = $context_topic_id;
if ($context_assessment_id > 0) $_SESSION['current_assessment_id'] = $context_assessment_id;

// Use URL values first. If URL value is 0 or null, use session value.
// We check for URL presence first, then session presence.
$current_course_id = $context_course_id ?? ($_SESSION['current_course_id'] ?? null);
$current_module_id = $context_module_id ?? ($_SESSION['current_module_id'] ?? null);
$current_topic_id = $context_topic_id ?? ($_SESSION['current_topic_id'] ?? null);
$current_assessment_id = $context_assessment_id ?? ($_SESSION['current_assessment_id'] ?? null);

// FINAL CHECK FOR DISPLAY: We will use a stricter check later in the display logic.


/**
 * Renders a navigation link for the sidebar
 */
function renderLink($href, $icon, $label, $check_page, $current_page, $isContextual = false, $level = 0) {
    $isActive = $current_page === $check_page;
    
    // Base classes for all links (px-3 provides centering when sidebar is w-16)
    $baseClass = "flex items-center px-3 py-2 rounded-lg transition-all relative hover:bg-[var(--color-card-border)]";
    $iconClass = "w-5 text-[var(--color-icon)] transition-colors duration-150 flex-shrink-0";
    $textClass = "link-text opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[var(--color-text)] whitespace-nowrap ml-3";

    if ($isActive) {
        // Active link styling: use original background/icon/font weight
        $baseClass .= " active-link bg-[var(--color-active-link-bg)]"; 
        $iconClass = "w-5 text-[var(--color-heading-secondary)] transition-colors duration-150 flex-shrink-0";
        $baseClass .= " font-semibold";
    }
    
    // Core links add space-x-3 
    if (!$isContextual) {
        $baseClass .= " space-x-3";
    }

    // FINAL ACTIVE STYLES
    if ($isActive) {
        $baseClass = str_replace("bg-[var(--color-active-link-bg)]", "bg-[var(--color-card-border)]", $baseClass);
    }
    
    echo "
    <a href='$href' class='$baseClass'>
        <i class='$icon $iconClass'></i>
        <span class='$textClass'>$label</span>
    </a>";
}
?>
<style>
    /* Styling remains the same */
    .custom-scrollbar-hide::-webkit-scrollbar { 
        display: none; 
    }
    .custom-scrollbar-hide { 
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .active-link {
        background-color: var(--color-card-border) !important;
    }
    .active-link .fa-solid, .active-link .link-text {
        color: var(--color-heading-secondary) !important;
        font-weight: 700;
    }

    .contextual-divider {
        border-color: var(--color-card-border);
        margin: 0.75rem 0;
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
// Define the depth of the contextual pages
$pages_hierarchy = [
    'module.php' => 1,
    'topics.php' => 2,
    'assessment.php' => 3,
    'questions.php' => 4, 
];

$current_page_level = $pages_hierarchy[$current_page] ?? 0;

// --- CORE NAVIGATION ---
renderLink('dashboard.php', 'fas fa-chart-pie', 'Dashboard', 'dashboard.php', $current_page);
renderLink('course.php', 'fas fa-book-open', 'Courses', 'course.php', $current_page);


// CRITICAL FIX: Use $current_course_id > 0 to ensure 0 does not break the logic
if ($current_course_id > 0) { 
    // Check if the current page is part of the course content hierarchy (level >= 1)
    if ($current_page_level >= 1) {
        echo '<hr class="contextual-divider opacity-0 group-hover:opacity-100 transition-opacity duration-300">';
        
        // MODULE (Level 1) - Visible if we are at level 1 or deeper
        if ($current_page_level >= 1) {
            $module_link = "module.php?course_id=$current_course_id";
            renderLink($module_link, 'fas fa-chalkboard', 'Module', 'module.php', $current_page, true, 0); 
        }

        // TOPIC (Level 2) - Visible only if a module is selected AND we are at level 2 or deeper
        // CRITICAL FIX: Check module ID > 0
        if ($current_module_id > 0 && $current_page_level >= 2) {
            $topic_link = "topics.php?course_id=$current_course_id&module_id=$current_module_id";
            renderLink($topic_link, 'fas fa-book', 'Topic', 'topics.php', $current_page, true, 0); 
        }

        // ASSESSMENT (Level 3) - Visible only if a module is selected AND we are at level 3 or deeper
        // CRITICAL FIX: Check module ID > 0
        if ($current_module_id > 0 && $current_page_level >= 3) {
            $assessment_link = "assessment.php?course_id=$current_course_id&module_id=$current_module_id";
            renderLink($assessment_link, 'fas fa-cube', 'Assessment', 'assessment.php', $current_page, true, 0); 
        }
        
        // QUESTIONS (Level 4) - Visible only if an assessment is selected AND we are at level 4
        // CRITICAL FIX: Check assessment ID > 0
        if ($current_assessment_id > 0 && $current_page_level >= 4) {
            $questions_link = "questions.php?course_id=$current_course_id&module_id=$current_module_id&assessment_id=$current_assessment_id";
            renderLink($questions_link, 'fas fa-question', 'Questions', 'questions.php', $current_page, true, 0); 
        }
        
        // Add another divider after contextual section
        echo '<hr class="contextual-divider opacity-0 group-hover:opacity-100 transition-opacity duration-300">';
    }
}
// ----------------------------------------------------------------------

// Continue with other core navigation
renderLink('learners.php', 'fas fa-user-graduate', 'Learners', 'learners.php', $current_page);
renderLink('code_redeemer.php', 'fas fa-qrcode', 'Code Redeemer', 'code_redeemer.php', $current_page); 
renderLink('reports_feedback.php', 'fas fa-chart-bar', 'Reports & Feedback', 'reports_feedback.php', $current_page); 
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