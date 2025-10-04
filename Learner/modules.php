<?php
include 'functions/format_time.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';
include 'functions/completed_info.php';

$student_id   = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$_SESSION['current_page'] = "module";

unset($_SESSION['answeredCount']);
unset($_SESSION['quiz_answer_info']);
unset($_SESSION['gainedExp']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['course_id'])) {
        $course_id = (int) $_GET['course_id'];
    }
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course      = $stmt->fetch();
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
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .status-completed {
            color: var(--color-green-button);
            font-weight: 900;
        }
        .status-progress {
            color: var(--color-heading);
            font-weight: 900;
        }
        .status-locked {
            color: var(--color-text-secondary);
            font-weight: 500;
        }
        .progress-bar-container {
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .stat-box {
            background-color: var(--color-main-bg);
            border: 1px solid var(--color-card-section-bg);
        }
        .module-action-button {
            padding: 8px 16px;
            border-radius: 0.375rem;
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
            pointer-events: none;
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
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);"><?= $course_name; ?></h6>
            </div>

            <div class="flex items-center space-x-4">
                <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 rounded-full transition shadow-md border-2" 
                style="background-color: var(--color-user-bg); color: var(--color-user-text); border-color: var(--color-icon);">
                    <i class="fas fa-user-circle text-2xl" style="color: var(--color-heading);"></i>
                    <span class="hidden sm:inline font-bold" style="color: var(--color-user-text);"><?= $student_name; ?></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" 
                        style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">Level <?= $user_lvl ?></span>
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
                    $prev_module_score = null;
                    $number = 1;
                    foreach ($modules as $index => $module) {
                        $total_minutes = count_estimated_time($course_id, $module['id']);
                        $exp_gain      = count_total_exp($module['course_id'], $module['id']);
                        $progress      = count_progress_percentage($course_id, $module['id']);
                        $completed_info= get_completed_info($module['id']);

                        if ($completed_info) {
                            $completed['score'] = $completed_info['score'];
                            $completed['exp'] = $completed_info['exp'];
                        }

                        $stmt = $pdo->prepare("
                            SELECT s.last_score 
                            FROM student_score s
                            JOIN assessments a ON a.id = s.assessment_id
                            WHERE a.module_id = :module_id AND a.type='module' AND s.user_id = :student_id
                            LIMIT 1
                        ");
                        $stmt->execute([":module_id" => $module['id'], ":student_id" => $student_id]);
                        $last_score = $stmt->fetchColumn() ?? 0;

                        if ($index === 0) {
                            $locked = false;
                        } else {
                            $locked = ($prev_module_score < $module['required_score']);
                        }

                        if ($progress == 100) {
                            $status_class = 'status-completed';
                            $status_text  = 'Completed';
                            $button_label = "<i class='fas fa-book-reader mr-1'></i> Review Topics";

                            $item_grid = ["Score Performance", "Total XP Gained", "Module Rank"];
                        } elseif ($progress > 0) {
                            $status_class = 'status-progress';
                            $status_text  = 'In Progress';
                            $button_label = "<i class='fas fa-play mr-1'></i> Continue Topics";

                            $item_grid = ["Topics Completed", "XP Earned", "Next Topic"];
                        } else {
                            $status_class = 'status-locked';
                            $status_text  = $locked ? 'Locked' : 'Not Started';
                            $button_label = $locked ? "<i class='fas fa-lock mr-1'></i> Locked" : "<i class='fas fa-play mr-1'></i> Start Topics";
                        }

                        $link    = $locked ? "javascript:void(0)" : "topicCard.php?course_id={$course_id}&module_id={$module['id']}";
                        $overlay = $locked ? "<div class='locked-overlay'>Required score not met</div>" : "";

                        if ($completed_info) {
                            if ($completed_info['score'] == 100.00) {
                                $rank = 'A++';
                            } elseif ($completed_info['score'] >= 90.00) {
                                $rank = 'A+';
                            } elseif ($completed_info['score'] >= 75.00) {
                                $rank = 'B+';
                            } else {
                                $rank = 'C+';
                            }
                        }

                        $stmt = $pdo->prepare("SELECT 
                                COUNT(DISTINCT t.id) AS total_topics,
                                COUNT(DISTINCT tc.id) AS completed_topics
                            FROM topics t
                            LEFT JOIN topics_completed tc 
                                ON t.id = tc.topic_id AND tc.student_id = :student_id
                            WHERE t.module_id = :module_id
                        ");
                        $stmt->execute([":student_id" => $student_id, ":module_id" => $module['id']]);
                        $topics_info = $stmt->fetch();
                        
                        $stmt = $pdo->prepare("SELECT * FROM topics WHERE module_id = :module_id");
                        $stmt->execute([":module_id" => $module['id']]);
                        $topics = $stmt->fetchAll();
                        $topics_title = array_column($topics, 'title');
                        
                        $exp_earned = 0;
                        foreach ($topics as $topic) {
                            $comp_inf = get_completed_info($module['id'], $topic['id']);
                            $exp = ($comp_inf['exp']) ?? 0;

                            $exp_earned += $exp;
                        }

                        $completed_topics = "{$topics_info['completed_topics']} / {$topics_info['total_topics']}";
                        $next_topics = ($topics_info['completed_topics'] == $topics_info['total_topics']) ? 0 : $topics_title[$topics_info['completed_topics']];
                        
                        $onprogress_value = [$completed_topics, $exp_earned, $next_topics];
                        $completed_value = ($completed_info) ? ["{$completed_info['score']}%", "+{$completed_info['exp']}", $rank] : null;

                        $display_value = ($progress == 100) ? $completed_value : $onprogress_value;
                        $assessment_btn = ($progress == 100) ? "<i class='fas fa-clipboard-list mr-1'></i> Take Module Assessment" : "<i class='fas fa-lock mr-1'></i> Assessment Locked";

                        echo "
                            <div class='module-card rounded-xl p-6 shadow-xl space-y-4' style='background-color: var(--color-card-bg);'>
                                {$overlay}
                                <div class='flex justify-between items-start border-b pb-4' style='border-color: var(--color-card-section-bg);'>
                                    <div class='space-y-1'>
                                        <h3 class='text-2xl font-extrabold' style='color: var(--color-heading);'>{$number}. {$module['title']}</h3>
                                        <p class='text-sm' style='color: var(--color-text-secondary);'>{$module['description']}</p>
                                    </div>
                                    <div class='flex items-center space-x-3 text-sm font-semibold' style='color: var(--color-text);'>
                                        <span class='flex items-center space-x-1'>
                                            <i class='fas fa-list-ol' style='color: var(--color-icon);' ></i> <span>" . count_topics($module['course_id'], $module['id']) . " Topics</span>
                                        </span>
                                        <span class='flex items-center space-x-1'>
                                            <i class='fas fa-clock' style='color: var(--color-icon);'> </i> <span>" . formatTime($total_minutes) . "</span>
                                        </span>
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
                                    </div>
                                    ". ((!$locked) ? 
                                    "<div class='grid grid-cols-4 gap-4 pt-2'>
                                        <div class='flex flex-col items-center'>
                                            <p class='text-xs' style='color: var(--color-text-secondary);'>{$item_grid[0]}</p>
                                            <p class='text-lg font-extrabold' style='color: var(--color-green-button);'>{$display_value[0]}</p>
                                        </div>
                                        <div class='flex flex-col items-center'>
                                            <p class='text-xs' style='color: var(--color-text-secondary);'>{$item_grid[1]}</p>
                                            <p class='text-lg font-extrabold' style='color: var(--color-heading);'>{$display_value[1]}</p>
                                        </div>
                                        <div class='flex flex-col items-center'>
                                            <p class='text-xs' style='color: var(--color-text-secondary);'>{$item_grid[2]}</p>
                                            <p class='text-lg font-extrabold' style='color: var(--color-heading-secondary);'>{$display_value[2]}</p>
                                        </div>

                                        <div class='flex flex-col items-center justify-center space-y-2'>
                                            <button type='button' onclick=\"window.location.href='{$link}'\" class='module-action-button " . (($progress == 100) ? "secondary" : "primary") . " w-full'>
                                                {$button_label}
                                            </button>


                                            <button type='button' onclick=\"window.location.href='assessmentModule.php?course_id={$course_id}&module_id={$module['id']}'\" class='" . (($progress == 100) ? "module-action-button primary w-full" : "module-action-button locked-assessment-button w-full") . "' " . (($progress == 100) ? "" : "disabled") . ">
                                                {$assessment_btn}
                                            </button>
                                        </div>
                                    </div>" : 
                                    "<div class='flex justify-between items-center pt-2'>
                                        <div><!-- Empty space on left --></div>
                                        <button disabled class='module-action-button primary opacity-50 cursor-not-allowed w-auto'>
                                            <i class='fas fa-lock mr-2'></i> Requires Module ". $number - 1 ." Completion
                                        </button>
                                    </div>"
                                    ) ."
                                </div>
                            </div>
                        ";
                        $prev_module_score = $last_score;
                        $number++;
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
