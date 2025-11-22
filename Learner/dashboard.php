<?php
include '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_modules.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';

$_SESSION['current_page'] = "dashboard";

$stmt = $pdo->prepare(
    "SELECT c.*, rc.course_id 
     FROM registration_code_uses rcu
     JOIN registration_codes rc ON rcu.registration_code_id = rc.id
     JOIN courses c ON rc.course_id = c.id
     WHERE rcu.student_id = :student_id"
);
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            color: #ffffff !important; /* Always white text on primary hover */
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

        /* Course Card Styling */
        .course-card {
            border: 1px solid var(--color-card-border);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .course-card:hover {
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
             transform: translateY(-1px);
        }

        /* Exam Readiness Wheel Styles */
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
    // Calculate exam readiness using your actual progress function
    $exam_readiness = 0;
    
    if (isset($registration_code_uses) && $registration_code_uses && isset($course_id)) {
        $exam_readiness = count_progress_percentage($course_id);
    }
    
    // Determine readiness status and color
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

                <div class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md flex flex-col hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-sm md:text-base" style="color: var(--color-text);">Current Streak</h3>
                        <i class="fas fa-fire text-xl md:text-2xl" style="color: var(--color-heading-secondary);"></i>
                    </div>
                    <div class="text-2xl md:text-4xl font-bold mb-2 counter" data-target="7" style="color: var(--color-heading-secondary);">7 days</div>
                    <div class="flex space-x-1 mt-auto justify-center md:justify-start">
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-3 h-3 md:w-4 md:h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                    </div>
                </div>

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
                
                // Get module counts using your function
                $module_counts = count_modules($course_id);
                $completed_modules = $module_counts['completed_modules'];
                $total_modules = $module_counts['total_modules'];
                
                // Get progress percentage using your function
                $progress_percentage = count_progress_percentage($course_id);
                
                // Determine difficulty level (you might want to store this in your courses table)
                $difficulty = "Beginner"; // Default - you should add this to your courses table
                $difficulty_class = "bg-green-100 text-green-800"; // Adjust based on your theme
                ?>
                
                <div class="course-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-0" style="background-color: var(--color-card-section-bg);">
                    <div class="flex items-center space-x-4 w-full md:w-auto">
                        <div class="p-2 md:p-3 rounded-md text-2xl md:text-3xl" style="background-color: var(--color-card-bg);">
                            <i class="fas fa-book" style="color: var(--color-heading);"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-semibold" style="color: var(--color-text);">
                                <?php echo htmlspecialchars($course['title'] ?? 'Unnamed Course'); ?>
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
                    
                    <section class="space-y-4 md:space-y-6 fade-slide p-4 md:p-6 rounded-lg shadow-xl" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center items-start gap-2 md:gap-0">
                            <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading);">Completed Courses</h2>
                            <a href="certificates.php" class="font-medium hover:underline text-sm" style="color: var(--color-green-button);">View Certificates</a>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="course-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-0" style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-green-button);">
                                <div class="flex items-center space-x-4 w-full md:w-auto">
                                    <div class="p-2 md:p-3 rounded-md text-2xl md:text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-database" style="color: var(--color-green-button);"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-base md:text-lg font-semibold" style="color: var(--color-text);">SQL Essentials</h3>
                                        <p class="text-xs md:text-sm font-bold" style="color: var(--color-green-button);">Final Score: 92%</p>
                                        <p class="text-xs" style="color: var(--color-text-secondary);">Completed on 2024-09-20</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between md:justify-end space-x-2 md:space-x-4 w-full md:w-auto">
                                    <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-green-button); color: var(--color-button-secondary-text);">Mastered</span>
                                    <a href="certificate.php?course=sql" class="px-3 md:px-4 py-2 rounded-md transition certificate-button hover:scale-[1.02] text-sm md:text-base">
                                        <i class="fas fa-file-pdf mr-1"></i> Certificate
                                    </a>
                                </div>
                            </div>

                            <div class="course-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-0" style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-green-button);">
                                <div class="flex items-center space-x-4 w-full md:w-auto">
                                    <div class="p-2 md:p-3 rounded-md text-2xl md:text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-sitemap" style="color: var(--color-green-button);"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-base md:text-lg font-semibold" style="color: var(--color-text);">Advanced Algorithms</h3>
                                        <p class="text-xs md:text-sm font-bold" style="color: var(--color-green-button);">Final Score: 88%</p>
                                        <p class="text-xs" style="color: var(--color-text-secondary);">Completed on 2024-08-15</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between md:justify-end space-x-2 md:space-x-4 w-full md:w-auto">
                                    <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-green-button); color: var(--color-button-secondary-text);">Mastered</span>
                                    <a href="certificate.php?course=algos" class="px-3 md:px-4 py-2 rounded-md transition certificate-button hover:scale-[1.02] text-sm md:text-base">
                                        <i class="fas fa-file-pdf mr-1"></i> Certificate
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                
                <div class="md:col-span-1 space-y-6 md:space-y-8">
                    
                    <section class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading);">Level Up</h2>
        <i class="fas fa-star text-xl md:text-2xl" style="color: var(--color-heading-secondary);"></i>
    </div>
    
    <?php if (isset($user_lvl)): ?>
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
        
        <!-- Optional: Show Intelligent Level if you want both -->
        <?php if (isset($intelligent_lvl)): ?>
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
    
    <?php endif; ?>
</section>
                    
                    <section class="backdrop-blur-sm p-4 md:p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <h2 class="text-lg md:text-xl font-bold mb-4" style="color: var(--color-text);">Today's Goals</h2>
                        <ul class="space-y-3 md:space-y-4">
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" checked class="h-4 w-4 md:h-5 md:w-5 rounded-full border-2 focus:ring-4" style="color: var(--color-heading); border-color: var(--color-heading); background-color: var(--color-heading);">
                                    <span class="text-sm md:text-base" style="color: var(--color-text);">Complete 2 lessons</span>
                                </div>
                                <i class="fas fa-check-circle text-sm md:text-base" style="color: var(--color-green-button);"></i>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" class="h-4 w-4 md:h-5 md:w-5 rounded-full border-2 focus:ring-4" style="border-color: var(--color-text); background-color: var(--color-main-bg);">
                                    <span class="text-sm md:text-base" style="color: var(--color-text);">Earn 100 XP</span>
                                </div>
                                <i class="far fa-circle text-sm md:text-base" style="color: var(--color-text-secondary);"></i>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" class="h-4 w-4 md:h-5 md:w-5 rounded-full border-2 focus:ring-4" style="border-color: var(--color-text); background-color: var(--color-main-bg);">
                                    <span class="text-sm md:text-base" style="color: var(--color-text);">Practice quiz</span>
                                </div>
                                <i class="far fa-circle text-sm md:text-base" style="color: var(--color-text-secondary);"></i>
                            </li>
                        </ul>
                        <div class="mt-4">
                            <p class="text-xs md:text-sm mb-1" style="color: var(--color-text-secondary);">Daily Progress</p>
                            <div class="h-1 md:h-2 rounded-full" style="background-color: var(--color-progress-bg);">
                                <div class="h-1 md:h-2 rounded-full" style="width: 67%; background: var(--color-progress-fill);"></div>
                            </div>
                        </div>
                    </section>

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
                
                // Close sidebar when clicking on links (optional)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', closeSidebar);
                });
            }

            // Function to apply theme based on local storage
            function applyThemeFromLocalStorage() {
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
                document.body.classList.toggle('dark-mode', isDarkMode);
            }

            // Apply theme on page load
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
                    // Clean the current text to get the number
                    const current = parseFloat(counter.innerText.replace(/[^0-9.]/g, '')); 
                    const increment = target > 0 ? Math.ceil(target / 100) : 0;

                    if (current < target) {
                        // Update inner text, keeping original suffix (%, days, etc.)
                        counter.innerText = (current + increment) + (counter.innerText.includes('%') ? '%' : counter.innerText.includes('days') ? ' days' : counter.innerText.includes('earned') ? ' earned' : '');
                        setTimeout(updateCount, 30);
                    } else {
                        counter.innerText = target + (counter.innerText.includes('%') ? '%' : counter.innerText.includes('days') ? ' days' : counter.innerText.includes('earned') ? ' earned' : '');
                    }
                };
                updateCount();
            });
        });
    </script>
</body>
</html>