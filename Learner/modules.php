<?php
include 'functions/format_time.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';
include 'functions/completed_info.php';

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['course_id'])) {
        $course_id = (int) $_GET['course_id'];
    }
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course = $stmt->fetch();
$course_name = $course['title'];
?>

<!DOCTYPE html>
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
        transform: translateY(-2px); /* Slight lift on hover */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    
    /* Dedicated status classes for better visual cues */
    .status-completed {
        color: var(--color-green-button);
        font-weight: 900;
    }
    .status-progress {
        color: var(--color-heading); /* Use primary heading color for in-progress */
        font-weight: 900;
    }
    .status-locked {
        color: var(--color-text-secondary);
        font-weight: 500;
    }

    /* Progress Bar Improvement: Use a shadow border for depth */
    .progress-bar-container {
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* XP/Stat Boxes: Made background slightly more subtle */
    .stat-box {
        background-color: var(--color-main-bg); /* Use main bg to distinguish from card bg */
        border: 1px solid var(--color-card-section-bg);
    }

    /* Action Button Fixes */
    /* Ensure action buttons look consistent and use the established interactive style */
    .module-action-button {
        padding: 8px 16px;
        border-radius: 0.375rem; /* rounded-md */
        font-weight: 600;
        transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
        border: 2px solid transparent;
    }
    .module-action-button.primary {
        background-color: var(--color-button-primary);
        color: white;
        box-shadow: 0 3px 0 var(--color-heading-secondary);
    }
    .module-action-button.secondary {
        background-color: var(--color-button-secondary);
        color: var(--color-button-secondary-text);
        border-color: var(--color-button-secondary-text);
        box-shadow: 0 3px 0 var(--color-card-border);
    }
    .module-action-button:active {
        transform: translateY(1px);
        box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
    }
        .locked-assessment-button {
        background-color: var(--color-card-section-bg); 
        color: var(--color-text-secondary);
        border: 2px solid var(--color-card-border);
        box-shadow: none;
        cursor: not-allowed;
        pointer-events: none; /* Stops all click events */
        opacity: 0.7;
    }
</style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">Course Modules</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);"><?=$course_name;?></h6>
            </div>

            <div class="flex items-center space-x-4">
                <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 rounded-full transition shadow-md border-2" style="background-color: var(--color-user-bg); color: var(--color-user-text); border-color: var(--color-icon);">
                    <i class="fas fa-user-circle text-2xl" style="color: var(--color-heading);"></i>
                    <span class="hidden sm:inline font-bold" style="color: var(--color-user-text);"><?=$student_name;?></span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">LV 12</span>
                </a>
            </div>
        </header>

        <main class="p-8 space-y-8 max-w-7xl mx-auto w-full"> 

            <div class="space-y-6">
                <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Available Learning Paths</h2>
                
<?php
$stmt = $pdo->prepare('SELECT * FROM modules WHERE course_id = :course_id ORDER BY id');
$stmt->execute([':course_id' => $course_id]);
$modules = $stmt->fetchAll();

if (!$modules) {
    echo '<p style="color: var(--color-text-secondary);">No modules available for this course.</p>';
} else {
    $prev_module_score = null; // Track score of previous module

    foreach ($modules as $index => $module) {
        $total_minutes = count_estimated_time($course_id, $module['id']);
        $exp_gain = count_total_exp($module['course_id'], $module['id']);
        $progress = count_progress_percentage($course_id, $module['id']);
        $topic_info = get_completed_info($module['id']);

        // Get last student score for this module
        $stmt = $pdo->prepare("
            SELECT s.last_score 
            FROM student_score s
            JOIN assessments a ON a.id = s.assessment_id
            WHERE a.module_id = :module_id AND a.type='module' AND s.user_id = :student_id
            LIMIT 1
        ");
        $stmt->execute([":module_id" => $module['id'], ":student_id" => $student_id]);
        $last_score = $stmt->fetchColumn() ?? 0;

        // Determine if module is locked
        if ($index === 0) {
            // First module is always unlocked
            $locked = false;
        } else {
            // Locked if previous module score < its required_score
            $locked = ($prev_module_score < $modules[$index]['required_score']);
        }

        // Determine status
        if ($progress == 100) {
            $status_class = 'status-completed';
            $status_text = 'Completed';
        } elseif ($progress > 0) {
            $status_class = 'status-progress';
            $status_text = 'In Progress';
        } else {
            $status_class = 'status-locked';
            $status_text = $locked ? 'Locked' : 'Not Started';
        }

        // Prepare link and overlay
        $link = $locked ? '#' : "topicCard.php?course_id={$course_id}&module_id={$module['id']}";
        $overlay = $locked ? "<div class='locked-overlay'>Required score not met</div>" : "";

        echo "
        <a href='{$link}' style='text-decoration: none; color: inherit; display: block; position: relative;'>
            <div class='module-card rounded-xl p-6 shadow-xl space-y-4' style='background-color: var(--color-card-bg);'>
                {$overlay}
                <div class='flex justify-between items-start border-b pb-4' style='border-color: var(--color-card-section-bg);'>
                    <div class='space-y-1'>
                        <h3 class='text-2xl font-extrabold' style='color: var(--color-heading);'>{$module['title']}</h3>
                        <p class='text-sm' style='color: var(--color-text-secondary);'>{$module['description']}</p>
                    </div>
                    <div class='flex items-center space-x-3 text-sm font-semibold' style='color: var(--color-text);'>
                        <span class='flex items-center space-x-1'><i class='fas fa-list-ol' style='color: var(--color-icon);'></i> " . count_topics($module['course_id'], $module['id']) . " Topics</span>
                        <span class='flex items-center space-x-1'><i class='fas fa-clock' style='color: var(--color-icon);'></i> " . formatTime($total_minutes) . " min</span>
                    </div>
                </div>

                <div class='grid grid-cols-2 md:grid-cols-4 gap-4 text-center'>
                    <div class='p-3 rounded-lg stat-box'>
                        <p class='text-xs font-medium' style='color: var(--color-text-secondary);'>Base XP</p>
                        <p class='text-lg font-bold' style='color: var(--color-heading);'>+{$exp_gain[0]}</p>
                    </div>
                    <div class='p-3 rounded-lg stat-box'>
                        <p class='text-xs font-medium' style='color: var(--color-text-secondary);'>Bonus XP</p>
                        <p class='text-lg font-bold' style='color: var(--color-heading-secondary);'>+{$exp_gain[1]}</p>
                    </div>
                    <div class='p-3 rounded-lg stat-box'>
                        <p class='text-xs font-medium' style='color: var(--color-text-secondary);'>Required Score</p>
                        <p class='text-lg font-bold' style='color: var(--color-green-button);'>{$module['required_score']}</p>
                    </div>
                    <div class='p-3 rounded-lg stat-box'>
                        <p class='text-xs font-medium' style='color: var(--color-text-secondary);'>Status</p>
                        <p class='text-lg {$status_class}'>{$status_text}</p>
                    </div>
                </div>

                <div class='space-y-3 pt-4 border-t' style='border-color: var(--color-card-border);'>
                    <div class='flex justify-between items-center'>
                        <span class='text-sm font-medium' style='color: var(--color-text);'>Module Progress</span>
                        <span class='text-sm font-bold {$status_class}'>{$progress}%</span>
                    </div>
                    <div class='h-2 rounded-full progress-bar-container' style='background-color: var(--color-progress-bg);'>
                        <div class='h-2 rounded-full' style='width: {$progress}%; background: var(--color-green-button);'></div>
                    </div>";

        if ($progress == 100) {
            echo "
                    <div class='grid grid-cols-4 gap-4 pt-2'>
                        <div class='flex flex-col items-center'>
                            <p class='text-xs' style='color: var(--color-text-secondary);'>Your Score</p>
                            <p class='text-lg font-extrabold' style='color: var(--color-green-button);'>{$topic_info['score']}%</p>
                        </div>
                        <div class='flex flex-col items-center'>
                            <p class='text-xs' style='color: var(--color-text-secondary);'>Total XP Gained</p>
                            <p class='text-lg font-extrabold' style='color: var(--color-heading);'>+{$topic_info['exp']}</p>
                        </div>
                        <div class='flex flex-col items-center'>
                            <p class='text-xs' style='color: var(--color-text-secondary);'>Intelligent XP Gained</p>
                            <p class='text-lg font-extrabold' style='color: var(--color-heading-secondary);'>{$topic_info['iexp']}</p>
                        </div>
                        <div class='flex flex-col items-center justify-center'>
                            <div class='module-action-button secondary w-full cursor-pointer' 
                                onclick='window.location.href=\"topicCard.php?course_id={$course_id}&module_id={$module['id']}\"'>
                                <i class='fas fa-book-reader mr-1'></i> Review Topics
                            </div>
                        </div>
                    </div>";
        }

        echo "
                </div>
            </div>
        </a>
        ";

        // Update previous module score
        $prev_module_score = $last_score;
    }
}
?>

            </div>
        </main>
    </div>

    <script>
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 

            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage();
            
            // Staggered animation for visual appeal
            document.querySelectorAll('.module-card').forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, i * 150); // Staggered delay
            });
        });
    </script>
</body>
</html>