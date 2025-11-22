<?php
// header.php - Reusable header component
require_once __DIR__ . "/../pdoconfig.php";
require_once __DIR__ . "/functions/get_student_progress.php";

// Fetch user data
$user_id = $_SESSION['student_id'];
$stmt = $pdo->prepare('SELECT first_name, middle_name, last_name FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user info
$user_full_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
$first_name_only = $user_data['first_name'];
$user_level = $user_lvl ?? 1;

// Set default title and subtitle if not provided
$header_title = $header_title ?? "Welcome back, " . htmlspecialchars($user_full_name);
$header_subtitle = $header_subtitle ?? "Continue your learning journey";
?>

<style>
    .header-custom {
        background-color: var(--color-header-bg);
        border-bottom: 2px solid var(--color-heading-secondary);
    }
    .header-title-custom {
        color: var(--color-heading);
    }
    .header-subtitle-custom {
        color: var(--color-text-secondary);
    }
    .mobile-menu-btn-custom {
        background-color: var(--color-card-bg);
        color: var(--color-text);
    }
    .user-card-custom {
        background-color: var(--color-user-bg);
        color: var(--color-user-text);
        border-color: var(--color-icon);
    }
    .user-name-custom {
        color: var(--color-user-text);
    }
    .user-icon-custom {
        color: var(--color-heading);
    }
    .level-badge-custom {
        background-color: var(--color-xp-bg);
        color: var(--color-xp-text);
    }
</style>

<header class="main-header sticky top-0 z-50 backdrop-blur-sm p-4 shadow-lg px-4 md:px-6 py-3 flex justify-between items-center fade-slide header-custom">
    <div class="flex gap-4">
        <button class="mobile-menu-button md:hidden rounded-lg p-2 mobile-menu-btn-custom">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <div class="flex flex-col">
            <h1 class="text-lg md:text-2xl font-bold header-title-custom">
                <?php echo $header_title; ?>
            </h1>
            <h6 class="text-xs font-bold header-subtitle-custom">
                <?php echo $header_subtitle; ?>
            </h6>
        </div>
    </div>
    <div class="flex items-center space-x-3">
        <a href="profile.php" class="flex items-center space-x-2 px-3 md:px-4 py-2 rounded-full transition shadow-md border-2 user-card-custom">
            <i class="fas fa-user-circle text-xl md:text-2xl user-icon-custom"></i>
            <span class="font-bold text-sm md:text-base user-name-custom"><?php echo htmlspecialchars($first_name_only); ?></span>
            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold level-badge-custom">LV <?php echo $user_level; ?></span>
        </a>
    </div>
</header>