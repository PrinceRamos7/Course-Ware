<?php
session_start();

// Include required files
include '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_modules.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';
include 'functions/daily_goals_function.php';

$_SESSION['current_page'] = "dashboard";

// Get enrolled courses
$stmt = $pdo->prepare(
    "SELECT c.*, rc.course_id 
     FROM registration_code_uses rcu
     JOIN registration_codes rc ON rcu.registration_code_id = rc.id
     JOIN courses c ON rc.course_id = c.id
     WHERE rcu.student_id = :student_id"
);
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize goals system
try {
    $goalsSystem = new DailyGoalsSystem($pdo);
    $goalsSystem->updateLoginStreak($_SESSION['student_id']);
    $todaysGoals = $goalsSystem->getTodaysGoals($_SESSION['student_id']);
    $completionStats = $goalsSystem->getTodaysCompletionStats($_SESSION['student_id']);
    
    // Calculate progress percentage
    $progressPercentage = $completionStats['total_goals'] > 0 
        ? ($completionStats['completed_goals'] / $completionStats['total_goals']) * 100 
        : 0;
        
} catch (Exception $e) {
    error_log("Goals system error: " . $e->getMessage());
    $todaysGoals = [];
    $completionStats = ['total_goals' => 0, 'completed_goals' => 0];
    $progressPercentage = 0;
}

// Get completed courses
$completed_courses = [];
if (function_exists('get_completed_courses')) {
    $completed_courses = get_completed_courses($_SESSION['student_id']);
}

// Get user progress for level display
$user_lvl = $user_exp = $next_goal_exp = $progress = 0;
$intelligent_lvl = $intelligent_exp = $next_goal_intelligent_exp = $intelligent_progress = 0;

try {
    $stmt = $pdo->prepare("SELECT id, experience, intelligent_exp FROM users WHERE id = :student_id");
    $stmt->execute([":student_id" => $_SESSION['student_id']]);
    $users = $stmt->fetch();

    $stmt = $pdo->prepare(
        "SELECT rc.course_id FROM registration_code_uses rcu
            JOIN registration_codes rc ON rcu.registration_code_id = rc.id
        WHERE rcu.student_id = :student_id"
    );
    $stmt->execute([":student_id" => $_SESSION['student_id']]);
    $registration_code_uses = $stmt->fetch();

    if ($registration_code_uses && function_exists('count_total_exp') && function_exists('getUserLevel')) {
        $course_id = $registration_code_uses['course_id'];
        $exp = count_total_exp($course_id);
        $user_exp_data = $exp[0] ?? 0;
        $intelligent_exp_data = $exp[1] ?? 0;

        $user_level = getUserLevel($users['experience'], $user_exp_data, 10);
        $intelligent_level = getUserLevel($users['intelligent_exp'], $intelligent_exp_data, 10);

        $user_exp = $user_level[2] ?? 0;
        $user_lvl = $user_level[0] ?? 1;
        $next_goal_exp = $user_level[3] ?? 100;
        $progress = number_format($user_level[1] ?? 0, 2);

        $intelligent_exp = $intelligent_level[2] ?? 0;
        $intelligent_lvl = $intelligent_level[0] ?? 1;
        $next_goal_intelligent_exp = $intelligent_level[3] ?? 100;
        $intelligent_progress = number_format($intelligent_level[1] ?? 0, 2);
    }
} catch (Exception $e) {
    error_log("User progress error: " . $e->getMessage());
}

// Get actual streak data from database
try {
    $stmt = $pdo->prepare("SELECT current_streak, longest_streak FROM student_login_streak WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $_SESSION['student_id']]);
    $streak_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $current_streak = $streak_data['current_streak'] ?? 0;
    $longest_streak = $streak_data['longest_streak'] ?? 0;
    
} catch (Exception $e) {
    // Fallback if there's an error
    $current_streak = 0;
    $longest_streak = 0;
    error_log("Streak data error: " . $e->getMessage());
}

// Determine streak color based on length
if ($current_streak >= 7) {
    $streak_color = "var(--color-green-button)";
    $streak_icon = "fas fa-fire-flame-curved";
} elseif ($current_streak >= 3) {
    $streak_color = "var(--color-heading-secondary)";
    $streak_icon = "fas fa-fire";
} else {
    $streak_color = "var(--color-text-secondary)";
    $streak_icon = "fas fa-fire";
}

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
        .continue-button {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }

        .continue-button:hover {
            background-color: var(--color-button-primary) !important; 
            color: #ffffff !important;
            border-color: var(--color-button-primary) !important; 
        }

        .certificate-button {
            background-color: var(--color-green-button);
            color: white;
            border: 1px solid var(--color-green-button);
            box-shadow: 0 3px 0 var(--color-green-button-dark);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .certificate-button:hover {
            background-color: var(--color-green-button-hover);
        }
        .certificate-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 0 var(--color-green-button-dark);
        }

        .course-card {
            border: 1px solid var(--color-card-border);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .course-card:hover {
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
             transform: translateY(-1px);
        }

        .readiness-wheel {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(
                var(--color-heading) 0% 75%,
                var(--color-progress-bg) 75% 100%
            );
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .readiness-wheel::before {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            background-color: var(--color-card-bg);
            border-radius: 50%;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .readiness-percentage {
            position: relative;
            z-index: 2;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--color-heading);
        }

        .readiness-label {
            font-size: 0.75rem;
            color: var(--color-text-secondary);
            margin-top: 0.25rem;
        }

        .readiness-status {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--color-heading);
            margin-top: 0.5rem;
        }
        
    </style>
</head>
<body class="min-h-screen flex dark-mode" style="background-color: var(--color-main-bg); color: var(--color-text);">
    
    <?php include "sidebar.php";?> 

    <div class="flex-1 flex flex-col ml-0 md:ml-16">
        <?php include 'header.php'?>

        <main class="p-4 md:p-8 space-y-6 md:space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 fade-slide">
                
                <!-- Exam Readiness Wheel Card -->
                <div class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md flex flex-col items-center hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4 w-full">
                        <h3 class="font-semibold text-sm md:text-base" style="color: var(--color-text);">Exam Readiness</h3>
                        <i class="fas fa-crosshairs text-xl md:text-2xl" style="color: var(--color-heading);"></i>
                    </div>
                    
                    <?php
                    $exam_readiness = 0;
                    if (isset($registration_code_uses) && $registration_code_uses && isset($course_id)) {
                        $exam_readiness = count_progress_percentage($course_id);
                    }
                    
                    if ($exam_readiness >= 80) {
                        $status = "Well Prepared";
                        $color = "var(--color-green-button)";
                    } elseif ($exam_readiness >= 60) {
                        $status = "Almost Ready";
                        $color = "var(--color-button-primary)";
                    } elseif ($exam_readiness >= 40) {
                        $status = "Making Progress";
                        $color = "var(--color-heading)";
                    } elseif ($exam_readiness >= 20) {
                        $status = "Getting Started";
                        $color = "var(--color-heading-secondary)";
                    } else {
                        $status = "Just Beginning";
                        $color = "var(--color-text-secondary)";
                    }
                    ?>
                    
                    <div class="readiness-wheel mb-3 scale-75 md:scale-100" 
                         style="background: conic-gradient(
                            <?php echo $color; ?> 0% <?php echo $exam_readiness; ?>%,
                            var(--color-progress-bg) <?php echo $exam_readiness; ?>% 100%
                         );">
                        <div class="readiness-percentage text-xl md:text-1.75rem"><?php echo $exam_readiness; ?>%</div>
                    </div>
                    <div class="readiness-label text-xs">Based on topic completion</div>
                    <div class="readiness-status text-sm md:text-base" style="color: <?php echo $color; ?>;">
                        <?php echo $status; ?>
                    </div>
                </div>

                <!-- Current Streak Card -->
                <div class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md flex flex-col hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-sm md:text-base" style="color: var(--color-text);">Current Streak</h3>
        <i class="<?php echo $streak_icon; ?> text-xl md:text-2xl" style="color: <?php echo $streak_color; ?>;"></i>
    </div>
    <div class="text-2xl md:text-4xl font-bold mb-2" style="color: <?php echo $streak_color; ?>;">
        <?php echo $current_streak; ?> day<?php echo $current_streak != 1 ? 's' : ''; ?>
    </div>
    
    <!-- Streak Visualization -->
    <div class="flex space-x-1 mt-auto justify-center md:justify-start">
        <?php 
        $max_dots = 7; // Show up to 7 days
        for($i = 0; $i < $max_dots; $i++): 
            $is_active = $i < $current_streak;
            $dot_color = $is_active ? $streak_color : 'var(--color-progress-bg)';
            $dot_size = $is_active ? 'w-3 h-3 md:w-4 md:h-4' : 'w-2 h-2 md:w-3 md:h-3';
        ?>
            <span class="<?php echo $dot_size; ?> rounded-full transition-all duration-300" 
                  style="background-color: <?php echo $dot_color; ?>;"
                  title="Day <?php echo $i + 1; ?>"></span>
        <?php endfor; ?>
    </div>
    
    <!-- Optional: Show longest streak as small text -->
    <?php if ($longest_streak > 0): ?>
    <div class="mt-2 text-xs text-center" style="color: var(--color-text-secondary);">
        Longest: <?php echo $longest_streak; ?> days
    </div>
    <?php endif; ?>
</div>

                <!-- Achievements Card -->
                <div class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md flex flex-col hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-sm md:text-base" style="color: var(--color-text);">Achievements</h3>
                        <i class="fas fa-medal text-xl md:text-2xl" style="color: var(--color-green-button);"></i>
                    </div>
                    <div class="text-2xl md:text-4xl font-bold mb-2 counter" data-target="2" style="color: var(--color-green-button);">2 earned</div>
                    <div class="flex space-x-2 mt-auto justify-center md:justify-start">
                        <div class="p-1 md:p-2 rounded-lg text-sm md:text-base" style="background-color: var(--color-card-section-bg);">🏅</div>
                        <div class="p-1 md:p-2 rounded-lg text-sm md:text-base" style="background-color: var(--color-card-section-bg);">⚡️</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="md:col-span-2 space-y-6 md:space-y-8">
                    
                    <!-- My Courses Section -->
                    <section class="space-y-4 md:space-y-6 fade-slide p-4 md:p-6 rounded-lg shadow-xl" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center items-start gap-2 md:gap-0">
                            <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading);">My Courses (In Progress)</h2>
                            <a href="courses.php" class="font-medium hover:underline text-sm" style="color: var(--color-heading);">View All</a>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if (empty($enrolled_courses)): ?>
                                <div class="text-center py-8" style="color: var(--color-text-secondary);">
                                    <p>You are not enrolled in any courses yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($enrolled_courses as $course): ?>
                                    <?php
                                    $course_id = $course['id'];
                                    $module_counts = count_modules($course_id);
                                    $completed_modules = $module_counts['completed_modules'];
                                    $total_modules = $module_counts['total_modules'];
                                    $progress_percentage = count_progress_percentage($course_id);
                                    $difficulty = "Beginner";
                                    ?>
                                    
                                    <div class="course-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-0" style="background-color: var(--color-card-section-bg);">
                                        <div class="flex items-center space-x-4 w-full md:w-auto">
                                            <div class="p-2 md:p-3 rounded-md text-2xl md:text-3xl" style="background-color: var(--color-card-bg);">
                                                <i class="fas fa-book" style="color: var(--color-heading);"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-base md:text-lg font-semibold" style="color: var(--color-text);">
                                                    <?php echo htmlspecialchars($course['title'] ?? $course['name'] ?? 'Unnamed Course'); ?>
                                                </h3>
                                                <p class="text-xs md:text-sm" style="color: var(--color-text-secondary);">
                                                    <?php echo $completed_modules . '/' . $total_modules; ?> modules completed
                                                </p>
                                                <div class="h-1 rounded-full w-full md:w-48 mt-2" style="background-color: var(--color-progress-bg);">
                                                    <div class="h-1 rounded-full" style="width: <?php echo $progress_percentage; ?>%; background: var(--color-progress-fill);"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between md:justify-end space-x-2 md:space-x-4 w-full md:w-auto">
                                            <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-heading-secondary); color: var(--color-button-secondary-text);">
                                                <?php echo $difficulty; ?>
                                            </span>
                                            <a href="modules.php?course_id=<?php echo $course_id; ?>" class="px-3 md:px-4 py-2 rounded-md transition continue-button hover:scale-[1.02] text-sm md:text-base" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-secondary-text);">
                                                Continue
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                    
                    <!-- Completed Courses Section -->
                    <section class="space-y-4 md:space-y-6 fade-slide p-4 md:p-6 rounded-lg shadow-xl" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center items-start gap-2 md:gap-0">
                            <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading);">Completed Courses</h2>
                            <a href="certificates.php" class="font-medium hover:underline text-sm" style="color: var(--color-green-button);">View Certificates</a>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if (empty($completed_courses)): ?>
                                <div class="text-center py-8" style="color: var(--color-text-secondary);">
                                    <p>No courses completed yet. Keep learning!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($completed_courses as $course): ?>
                                    <?php
                                    $mastery_level = "Completed";
                                    $mastery_color = "var(--color-green-button)";
                                    
                                    if ($course['final_score'] >= 90) {
                                        $mastery_level = "Mastered";
                                    } elseif ($course['final_score'] >= 80) {
                                        $mastery_level = "Proficient";
                                        $mastery_color = "var(--color-blue-button)";
                                    } elseif ($course['final_score'] >= 70) {
                                        $mastery_level = "Competent";
                                        $mastery_color = "var(--color-orange-button)";
                                    }
                                    ?>
                                    
                                    <div class="course-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-0" style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-green-button);">
                                        <div class="flex items-center space-x-4 w-full md:w-auto">
                                            <div class="p-2 md:p-3 rounded-md text-2xl md:text-3xl" style="background-color: var(--color-card-bg);">
                                                <i class="fas fa-database" style="color: var(--color-green-button);"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-base md:text-lg font-semibold" style="color: var(--color-text);">
                                                    <?php echo htmlspecialchars($course['name']); ?>
                                                </h3>
                                                <p class="text-xs md:text-sm font-bold" style="color: var(--color-green-button);">
                                                    Final Score: <?php echo $course['final_score']; ?>%
                                                </p>
                                                <p class="text-xs" style="color: var(--color-text-secondary);">
                                                    Completed on <?php echo $course['completion_date']; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between md:justify-end space-x-2 md:space-x-4 w-full md:w-auto">
                                            <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold" style="background-color: <?php echo $mastery_color; ?>; color: var(--color-button-secondary-text);">
                                                <?php echo $mastery_level; ?>
                                            </span>
                                            <a href="certificate.php?course_id=<?php echo $course['id']; ?>" class="px-3 md:px-4 py-2 rounded-md transition certificate-button hover:scale-[1.02] text-sm md:text-base" style="background-color: var(--color-green-button); color: white;">
                                                <i class="fas fa-file-pdf mr-1"></i> Certificate
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
                
                <!-- Sidebar Column -->
                <div class="md:col-span-1 space-y-6 md:space-y-8">
                    
                    <!-- Level Up Section -->
                    <section class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading);">Level Up</h2>
                            <i class="fas fa-star text-xl md:text-2xl" style="color: var(--color-heading-secondary);"></i>
                        </div>
                        
                        <?php if ($user_lvl > 0): ?>
                            <div class="text-center mb-4">
                                <p class="text-2xl md:text-4xl font-extrabold" style="color: var(--color-button-primary);">Level <?php echo $user_lvl; ?></p>
                                <p class="text-xs md:text-sm" style="color: var(--color-text-secondary);">
                                    Next level at <?php echo number_format($next_goal_exp); ?> XP
                                </p>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-xs md:text-sm font-medium">
                                    <span style="color: var(--color-text);">XP Progress</span>
                                    <span style="color: var(--color-heading);">
                                        <?php echo number_format($user_exp); ?> / <?php echo number_format($next_goal_exp); ?>
                                    </span>
                                </div>
                                <div class="h-2 md:h-3 rounded-full" style="background-color: var(--color-progress-bg);">
                                    <div class="h-2 md:h-3 rounded-full" style="width: <?php echo $progress; ?>%; background: var(--color-progress-fill);"></div>
                                </div>
                            </div>
                            
                            <?php if ($intelligent_lvl > 0): ?>
                            <div class="mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                                <div class="text-center mb-2">
                                    <p class="text-lg md:text-xl font-bold" style="color: var(--color-heading-secondary);">Intelligent Level <?php echo $intelligent_lvl; ?></p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">
                                        Next: <?php echo number_format($next_goal_intelligent_exp); ?> XP
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between items-center text-xs font-medium">
                                        <span style="color: var(--color-text);">Smart XP</span>
                                        <span style="color: var(--color-heading-secondary);">
                                            <?php echo number_format($intelligent_exp); ?> / <?php echo number_format($next_goal_intelligent_exp); ?>
                                        </span>
                                    </div>
                                    <div class="h-2 rounded-full" style="background-color: var(--color-progress-bg);">
                                        <div class="h-2 rounded-full" style="width: <?php echo $intelligent_progress; ?>%; background: var(--color-heading-secondary);"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-sm text-center" style="color: var(--color-text-secondary);">Start learning to level up!</p>
                        <?php endif; ?>
                    </section>
                    
                    <!-- Today's Goals Section -->
                   <section class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
    <h2 class="text-lg md:text-xl font-bold mb-4" style="color: var(--color-text);">Today's Goals</h2>
    
    <?php if (empty($todaysGoals)): ?>
        <p class="text-sm md:text-base" style="color: var(--color-text-secondary);">No goals set for today.</p>
    <?php else: ?>
        <ul class="space-y-3 md:space-y-4">
            <?php foreach ($todaysGoals as $goal): ?>
                <li class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <input 
                            type="checkbox" 
                            <?php echo $goal['is_completed'] ? 'checked' : ''; ?>
                            class="h-4 w-4 md:h-5 md:w-5 rounded-full border-2 focus:ring-4" 
                            style="<?php echo $goal['is_completed'] 
                                ? 'color: var(--color-heading); border-color: var(--color-heading); background-color: var(--color-heading);' 
                                : 'border-color: var(--color-text); background-color: var(--color-main-bg);'; ?>"
                            disabled
                        >
                        <span class="text-sm md:text-base" style="color: var(--color-text);">
                            <?php echo htmlspecialchars($goal['description']); ?>
                            <?php if ($goal['is_completed']): ?>
                                <span class="text-xs ml-1" style="color: var(--color-green-button);">
                                    (+<?php echo $goal['reward_exp']; ?> XP)
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($goal['is_completed']): ?>
                            <i class="fas fa-check-circle text-sm md:text-base" style="color: var(--color-green-button);"></i>
                        <?php endif; ?>
                        <div class="text-xs" style="color: var(--color-text-secondary);">
                            <?php echo $goal['progress_current']; ?>/<?php echo $goal['target_value']; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="mt-4">
            <p class="text-xs md:text-sm mb-1" style="color: var(--color-text-secondary);">
                Daily Progress (<?php echo $completionStats['completed_goals']; ?>/<?php echo $completionStats['total_goals']; ?>)
            </p>
            <div class="h-1 md:h-2 rounded-full" style="background-color: var(--color-progress-bg);">
                <div class="h-1 md:h-2 rounded-full transition-all duration-500" style="width: <?php echo $progressPercentage; ?>%; background: var(--color-progress-fill);"></div>
            </div>
        </div>
    <?php endif; ?>
</section>

                    <!-- Recent Activity Section -->
                    <section class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <h2 class="text-lg md:text-xl font-bold mb-4" style="color: var(--color-text);">Recent Activity</h2>
                        <ul class="space-y-3 md:space-y-4 text-xs md:text-sm">
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <i class="fas fa-book-open text-sm md:text-base" style="color: var(--color-heading);"></i>
                                    <span style="color: var(--color-text);">Completed SQL Joins</span>
                                </div>
                                <span class="font-bold text-xs md:text-sm" style="color: var(--color-text-secondary);">+50 XP</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <i class="fas fa-trophy text-sm md:text-base" style="color: var(--color-icon);"></i>
                                    <span style="color: var(--color-text);">Earned Week Warrior badge</span>
                                </div>
                                <span class="font-bold text-xs md:text-sm" style="color: var(--color-text-secondary);">+100 XP</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-2 md:space-x-3">
                                    <i class="fas fa-check-circle text-sm md:text-base" style="color: var(--color-green-button);"></i>
                                    <span style="color: var(--color-text);">Quiz Score: 85% on SQL Basics</span>
                                </div>
                                <span class="font-bold text-xs md:text-sm" style="color: var(--color-text-secondary);">+75 XP</span>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const body = document.body;
            
            if (mobileMenuButton && sidebar && overlay) {
                function openSidebar() {
                    sidebar.classList.add('mobile-open');
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                }
                
                function closeSidebar() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
                
                mobileMenuButton.addEventListener('click', openSidebar);
                overlay.addEventListener('click', closeSidebar);
                
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', closeSidebar);
                });
            }

            // Apply theme
            function applyThemeFromLocalStorage() {
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
                document.body.classList.toggle('dark-mode', isDarkMode);
            }
            applyThemeFromLocalStorage();

            // Fade-in sections
            document.querySelectorAll('.fade-slide').forEach((el, i) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, i * 150);
            });

            // Counter animation
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const current = parseFloat(counter.innerText.replace(/[^0-9.]/g, '')); 
                    const increment = target > 0 ? Math.ceil(target / 100) : 0;

                    if (current < target) {
                        counter.innerText = (current + increment) + (counter.innerText.includes('%') ? '%' : counter.innerText.includes('days') ? ' days' : counter.innerText.includes('earned') ? ' earned' : '');
                        setTimeout(updateCount, 30);
                    } else {
                        counter.innerText = target + (counter.innerText.includes('%') ? '%' : counter.innerText.includes('days') ? ' days' : counter.innerText.includes('earned') ? ' earned' : '');
                    }
                };
                updateCount();
            });

            // Goals functionality
            document.querySelectorAll('.claim-reward-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const goalId = this.getAttribute('data-goal-id');
                    claimGoalReward(goalId, this);
                });
            });
        });

        function claimGoalReward(goalId, buttonElement) {
            console.log('Claiming reward for goal:', goalId);
            
            buttonElement.innerHTML = 'Claiming...';
            buttonElement.disabled = true;
            
            fetch('claim_goal_reward.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'goal_id=' + goalId
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    buttonElement.outerHTML = '<i class="fas fa-check-circle text-sm md:text-base" style="color: var(--color-green-button);"></i>';
                    showRewardNotification(data.exp, data.intelligent_exp);
                    setTimeout(() => {
                        updateProgressBar();
                    }, 1000);
                } else {
                    buttonElement.innerHTML = 'Claim';
                    buttonElement.disabled = false;
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                buttonElement.innerHTML = 'Claim';
                buttonElement.disabled = false;
                alert('Network error: ' + error.message);
            });
        }

        function showRewardNotification(exp, intelligentExp) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 p-3 rounded-lg shadow-lg z-50 animate-bounce';
            notification.style.backgroundColor = 'var(--color-green-button)';
            notification.style.color = 'white';
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-trophy"></i>
                    <span>Reward claimed! +${exp} XP, +${intelligentExp} IEXP</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function updateProgressBar() {
            location.reload();
        }

        function initializeGoals() {
            fetch('initialize_goals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Network error: ' + error.message);
                });
        }
    </script>
</body>
</html>