<?php
require __DIR__ . '/../config.php'; // DB connection

// Assuming you have a user session with the logged-in student's ID
// *** REPLACE '1' with your actual session variable (e.g., $_SESSION['user_id']) ***
$student_id = 1; 

// Get course ID from query parameter
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$stmt = $conn->prepare("SELECT * FROM modules WHERE course_id = :course_id ORDER BY id ASC");
$stmt->execute(['course_id' => $course_id]);
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);


function get_module_metadata_and_status($conn, $module_id, $student_id) {
    $status = 'locked';
    $progress_percent = 0;

    // Simulate different statuses based on the module ID
    if ($module_id == 1) { 
        $status = 'progress';
        $progress_percent = 50;
    } else if ($module_id == 5) { 
        $status = 'completed';
        $progress_percent = 100;
    }

    // Simulate Metadata (Topics, Duration, XP)
    // You should join these from your database if they are static module attributes
    return [
        'topics_count' => ($module_id * 2) + 3, // E.g., 5, 7, 9...
        'duration' => 30 + ($module_id * 10), // E.g., 40, 50, 60...
        'base_xp' => 100 + ($module_id * 50), // E.g., 150, 200, 250...
        'bonus_xp' => 25 + ($module_id * 5), // E.g., 30, 35, 40...
        'status' => $status,
        'progress_percent' => $progress_percent
    ];
}

// Augment the modules array with the required data
$augmented_modules = [];
foreach ($modules as $module) {
    // Safely get module ID (assuming 'id' exists from SELECT *)
    $module_id = $module['id'] ?? 0;
    
    // Get the dynamic metadata and status
    $metadata = get_module_metadata_and_status($conn, $module_id, $student_id);
    
    // Merge the fetched/calculated metadata into the module data
    $augmented_modules[] = array_merge($module, $metadata);
}
// Overwrite $modules with the augmented array for use in the HTML loop
$modules = $augmented_modules;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        .module-card {
            border: 2px solid var(--color-card-border);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .status-completed { color: var(--color-green-button); font-weight: 900; }
        .status-progress { color: var(--color-heading); font-weight: 900; }
        .status-locked { color: var(--color-text-secondary); font-weight: 500; }
        .progress-bar-container { box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }
        .stat-box { background-color: var(--color-main-bg); border: 1px solid var(--color-card-section-bg); }
        .module-action-button {
            padding: 8px 16px; border-radius: 0.375rem; font-weight: 600; 
            transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
            border: 2px solid transparent;
        }
        .module-action-button.primary { background-color: var(--color-button-primary); color: white; box-shadow: 0 3px 0 var(--color-heading-secondary); }
        .module-action-button.secondary { background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border-color: var(--color-button-secondary-text); box-shadow: 0 3px 0 var(--color-card-border); }
        .module-action-button:active { transform: translateY(1px); box-shadow: 0 2px 0 rgba(0,0,0,0.3); }
        .locked-assessment-button { background-color: var(--color-card-section-bg); color: var(--color-text-secondary); border: 2px solid var(--color-card-border); box-shadow: none; cursor: not-allowed; pointer-events: none; opacity: 0.7; }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">Course Modules</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Introduction to Python</h6>
            </div>

            <div class="flex items-center space-x-4">
              <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 rounded-full transition shadow-md border-2" style="background-color: var(--color-user-bg); color: var(--color-user-text); border-color: var(--color-icon);">
                    <i class="fas fa-user-circle text-2xl" style="color: var(--color-heading);"></i>
                    <span class="hidden sm:inline font-bold" style="color: var(--color-user-text);">Ryuta</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">LV 12</span>
                </a>
            </div>
        </header>

        <main class="p-8 space-y-8 max-w-7xl mx-auto w-full"> 
            <div class="space-y-6">
                <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Available Learning Paths</h2>

                <?php foreach ($modules as $module): 
                    // Use null coalescing to safely get values, preventing "Undefined array key" warnings
                    $statusClass = $module['status'] ?? 'locked';
                    $progressPercent = $module['progress_percent'] ?? 0;
                    $topicsCount = $module['topics_count'] ?? 0;
                    $duration = $module['duration'] ?? 0;
                    $baseXp = $module['base_xp'] ?? 0;
                    $bonusXp = $module['bonus_xp'] ?? 0;
                    $requiredScore = $module['required_score'] ?? 0;
                ?>
                <div class="module-card rounded-xl p-6 shadow-xl space-y-4 <?php echo $statusClass=='locked' ? 'opacity-70' : ''; ?>" style="background-color: var(--color-card-bg);">
                    <div class="flex justify-between items-start border-b pb-4" style="border-color: var(--color-card-section-bg);">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-extrabold status-<?php echo $statusClass; ?>">
                                <?php echo $statusClass=='locked' ? '<i class="fas fa-lock mr-2"></i>' : ''; ?>
                                <?php echo htmlspecialchars($module['title'] ?? 'Module Title Missing'); ?>
                            </h3>
                            <p class="text-sm" style="color: var(--color-text-secondary);"><?php echo htmlspecialchars($module['description'] ?? 'No description available.'); ?></p>
                        </div>
                        <div class="flex items-center space-x-3 text-sm font-semibold <?php echo $statusClass=='locked' ? 'status-locked' : ''; ?>">
                            <span class="flex items-center space-x-1"><i class="fas fa-list-ol"></i> <span><?php echo $topicsCount; ?> Topics</span></span>
                            <span class="flex items-center space-x-1"><i class="fas fa-clock"></i> <span><?php echo $duration; ?> min</span></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium">Base XP</p><p class="text-lg font-bold">+<?php echo $baseXp; ?></p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium">Bonus XP</p><p class="text-lg font-bold">+<?php echo $bonusXp; ?></p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium">Required Score</p><p class="text-lg font-bold"><?php echo $requiredScore; ?>%</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium">Status</p><p class="text-lg status-<?php echo $statusClass; ?>"><?php echo ucfirst($statusClass); ?></p></div>
                    </div>

                    <div class="space-y-3 pt-4 border-t" style="border-color: var(--color-card-border);">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Module Progress</span>
                            <span class="text-sm font-bold"><?php echo $progressPercent; ?>%</span>
                        </div>
                        <div class="h-2 rounded-full progress-bar-container" style="background-color: var(--color-progress-bg);">
                            <div class="h-2 rounded-full" style="width: <?php echo $progressPercent; ?>%; background: <?php echo $statusClass=='completed' ? 'var(--color-green-button)' : 'var(--color-button-primary)'; ?>;"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 pt-2">
                            <div class="flex flex-col items-center justify-center">
                                <a href="<?php echo $statusClass=='locked' ? '#' : 'topicCard.php?module=' . ($module['id'] ?? 0); ?>" class="module-action-button <?php echo $statusClass=='locked' ? 'locked-assessment-button' : 'primary'; ?> w-full">
                                    <?php echo $statusClass=='locked' ? '<i class="fas fa-lock mr-1"></i> Locked' : '<i class="fas fa-play mr-1"></i> Continue Module'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) document.body.classList.add('dark-mode');
            else document.body.classList.remove('dark-mode');
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage();
            document.querySelectorAll('.module-card').forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, i * 150);
            });
        });
    </script>
</body>
</html>