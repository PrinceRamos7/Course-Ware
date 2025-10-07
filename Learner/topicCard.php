<?php
include 'functions/format_time.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';
include 'functions/completed_info.php';

unset($_SESSION['quiz_end_time']);
unset($_SESSION['quiz_answer_info']);
$_SESSION['current_page'] = "topic";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['module_id']) && isset($_GET['course_id'])) {
        $course_id = (int) $_GET['course_id'];
        $module_id = (int) $_GET['module_id'];
    }
}

$_SESSION['course_id'] = $course_id;
$_SESSION['module_id'] = $module_id;

$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = :module_id");
$stmt->execute([":module_id" => $module_id]);
$module = $stmt->fetch();
$module_name = $module["title"];

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course      = $stmt->fetch();
$course_name = $course['title'];
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
        /* --- Gamified Lesson Card Styles (to be added to output.css) --- */

/* Base 3D Effect for all cards */
.topic-card-horizontal {
    position: relative;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    border-radius: 1rem;
    /* Base Shadow - will be overridden by status-specific shadows */
    box-shadow: 0 4px 0 var(--color-card-border); 
}

/* Hover Effect: "Pushes" the card up slightly */
.topic-card-horizontal:not(.locked-card):hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 0 var(--color-heading-secondary);
}

/* 1. COMPLETED CARD */
.completed-card {
    border-color: var(--color-green-button) !important;
    box-shadow: 0 5px 0 var(--color-green-button-hover); /* Deeper green shadow */
}
.completed-card:hover {
    box-shadow: 0 7px 0 var(--color-green-button-hover);
}

/* 2. IN-PROGRESS CARD */
.in-progress-card {
    border-color: var(--color-heading) !important; /* Use your main accent color */
    box-shadow: 0 5px 0 var(--color-heading);
}
.in-progress-card:hover {
    box-shadow: 0 7px 0 var(--color-heading-secondary); /* Highlight on hover */
}

/* 3. LOCKED CARD */
.locked-card {
    filter: grayscale(80%);
    opacity: 0.5; /* Opacity applied inline, but this reinforces it */
    box-shadow: 0 3px 0 var(--color-text-secondary); /* Flatter, grayed out shadow */
}
.locked-card:hover {
    transform: none; /* Disable hover movement */
    box-shadow: 0 3px 0 var(--color-text-secondary);
}

/* --- Action Button Styles (The Primary/Secondary/Locked Buttons) --- */
.action-button {
    font-weight: bold;
    border-width: 2px;
    display: inline-block;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
}

/* Button Press Effect: Makes it look like the button is pressed down */
.action-button:active {
    transform: translateY(2px);
    box-shadow: none !important; 
}

/* Specific Button Shadows for 3D effect */
.primary-button {
    box-shadow: 0 3px 0 var(--color-green-button-hover); 
}
.review-button {
    box-shadow: 0 3px 0 var(--color-button-secondary-hover);
}
.locked-button {
    box-shadow: 0 1px 0 var(--color-text-secondary);
}
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-button md:hidden fixed top-4 left-4 z-50 bg-[var(--color-card-bg)] border border-[var(--color-card-border)] rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <!-- Overlay -->
    <div class="sidebar-overlay md:hidden"></div>

    <div class="flex-1 flex flex-col ml-0 md:ml-16">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-4 md:px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 2px solid var(--color-heading-secondary);">
            <div class="flex flex-col">
                <h1 class="text-xl md:text-3xl font-extrabold flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-scroll mr-2 md:mr-3 text-lg md:text-xl" style="color: var(--color-heading-secondary);"></i> Course Quests
                </h1>
                <h6 class="text-xs md:text-sm font-bold" style="color: var(--color-text-secondary);"><?= $course_name ?> / <?= $module_name ?></h6>
            </div>

            <div class="flex items-center space-x-3">
                <a href="profile.php" class="flex items-center space-x-2 px-3 md:px-4 py-2 rounded-full transition shadow-md border-2" style="background-color: var(--color-user-bg); color: var(--color-user-text); border-color: var(--color-icon);">
                    <i class="fas fa-user-circle text-xl md:text-2xl" style="color: var(--color-heading);"></i>
                    <span class="font-bold text-sm md:text-base" style="color: var(--color-user-text);"><?= $_SESSION['student_name'] ?></span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">Level <?= $user_lvl ?></span>
                </a>
            </div>
        </header>

        <main class="p-4 md:p-8 space-y-6 md:space-y-8">
            <h2 class="text-xl md:text-2xl font-extrabold border-b pb-2 mb-4 md:mb-6" style="color: var(--color-heading-secondary); border-color: var(--color-card-border);">
                Module 1: Basic Declarations
            </h2>
            
            <div class="space-y-4 md:space-y-6">
                <div class="space-y-4 md:space-y-6">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM topics WHERE module_id = :module_id");
                    $stmt->execute([":module_id" => $module_id]);
                    $topics = $stmt->fetchAll();

                    if ($topics) {
                        $number = 1;
                        $completed = true;
                        foreach ($topics as $topic) {
                            $topic_info = get_completed_info($module_id, $topic['id']);

                            $status = "completed";
                            $status_mark = ($topic_info) ? 
                                            "<i class='fas fa-check-circle text-xl md:text-2xl' style='color: var(--color-green-button);'></i>
                                            <span class='text-xs font-bold' style='color: var(--color-green-button);'>Completed</span>" 
                                            :
                                            "<i class='fas fa-hourglass-half text-xl md:text-2xl' style='color: var(--color-text-secondary);'></i>
                                            <span class='text-xs font-bold' style='color: var(--color-text-secondary);'>Pending</span>";

                            $access_btn = ($topic_info) ?
                                            "<span class='action-button review-button px-4 md:px-6 py-2 rounded-full transition-all hover:opacity-80 text-sm md:text-base' 
                                            style='background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 2px solid var(--color-button-secondary-text);'>
                                                Review
                                            </span>"
                                            :
                                            "<span class='action-button start-button px-4 md:px-6 py-2 rounded-full transition-all hover:opacity-80 text-sm md:text-base' 
                                            style='background-color: var(--color-button-primary); color: #fff;'>
                                                Start
                                            </span>";
                            
                            if ($completed) {
                                echo "
                                <a href='topicContent.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic['id']}' class='topic-card-horizontal completed-card flex flex-col md:flex-row md:items-center justify-between p-4 md:p-6 rounded-2xl shadow-xl transition-all duration-300 transform hover:scale-[1.01] cursor-pointer gap-4 md:gap-0'
                                style='background-color: var(--color-card-bg);'>
                            
                                    <div class='flex items-center space-x-4 md:space-x-6 w-full md:w-auto'>
                                        <div class='flex-shrink-0 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full text-lg md:text-2xl font-extrabold'
                                        style='background-color: var(--color-green-button); color: var(--color-card-bg);'>
                                            <i class='fas fa-check'></i>
                                        </div>
                                        <div class='space-y-1 flex-1'>
                                            <h3 class='text-lg md:text-xl font-extrabold' style='color: var(--color-heading);'>Quest {$number}: {$topic['title']}</h3>
                                            <p class='text-xs md:text-sm' style='color: var(--color-text-secondary);'>{$topic['description']} ({$status}).</p>
                                        </div>
                                    </div>

                                    <div class='flex flex-wrap md:flex-nowrap items-center justify-between md:justify-end gap-3 md:gap-6 w-full md:w-auto'>
            
                                        <div class='flex-shrink-0 flex items-center space-x-3 md:space-x-4'>
                                            <div class='text-center w-14 md:w-16'>
                                                <p class='text-xs font-semibold' style='color: var(--color-text-secondary);'>Total XP</p>
                                                <p class='text-base md:text-lg font-bold' style='color: var(--color-heading);'>+{$topic['total_exp']}</p>
                                            </div>
                                            <div class='text-center w-14 md:w-16 hidden sm:block'>
                                                <p class='text-xs font-semibold' style='color: var(--color-text-secondary);'>Time</p>
                                                <p class='text-base md:text-lg font-bold' style='color: var(--color-text);'>{$topic['estimated_minute']} min</p>
                                            </div>
                                        </div>
            
                                        <div class='w-20 md:w-24 flex flex-col items-center justify-center space-y-1'>
                                            {$status_mark}
                                        </div>
            
                                        <div class='w-24 md:w-28 text-right'>
                                            {$access_btn}
                                        </div>
                                    </div>
                                </a>
                                ";
                            } else {
                                echo "
                                <div class='topic-card-horizontal locked-card flex flex-col md:flex-row md:items-center justify-between p-4 md:p-6 rounded-2xl opacity-50 cursor-not-allowed border-dashed border-2 gap-4 md:gap-0'
                                    style='background-color: var(--color-card-bg); border-color: var(--color-card-border);'>
                                    
                                    <div class='flex items-center space-x-4 md:space-x-6 w-full md:w-auto'>
                                        <div class='flex-shrink-0 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full text-lg md:text-2xl font-extrabold'
                                            style='background-color: var(--color-card-border); color: var(--color-text-secondary);'>
                                            <i class='fas fa-lock text-sm md:text-base'></i>
                                        </div>
                                        <div class='space-y-1 flex-1'>
                                            <h3 class='text-lg md:text-xl font-extrabold' style='color: var(--color-text-secondary);'>Quest {$number}: {$topic['title']}</h3>
                                            <p class='text-xs md:text-sm' style='color: var(--color-text-secondary);'>{$topic['description']} ({$status})</p>
                                        </div>
                                    </div>

                                    <div class='flex flex-wrap md:flex-nowrap items-center justify-between md:justify-end gap-3 md:gap-6 w-full md:w-auto'>
                                        
                                        <div class='flex-shrink-0 flex items-center space-x-3 md:space-x-4'>
                                            <div class='text-center w-14 md:w-16'>
                                                <p class='text-xs font-semibold' style='color: var(--color-text-secondary);'>Total XP</p>
                                                <p class='text-base md:text-lg font-bold' style='color: var(--color-text-secondary);'>+{$topic['total_exp']}</p>
                                            </div>
                                            <div class='text-center w-14 md:w-16 hidden sm:block'>
                                                <p class='text-xs font-semibold' style='color: var(--color-text-secondary);'>Time</p>
                                                <p class='text-base md:text-lg font-bold' style='color: var(--color-text-secondary);'>{$topic['estimated_minute']} min</p>
                                            </div>
                                        </div>
                                        
                                        <div class='w-20 md:w-24 flex flex-col items-center justify-center space-y-1'>
                                            <i class='fas fa-lock text-base md:text-lg' style='color: var(--color-text-secondary);'></i>
                                            <span class='text-xs font-bold' style='color: var(--color-text-secondary);'>Locked</span>
                                        </div>
                                        
                                        <div class='w-24 md:w-28 text-right'>
                                            <button disabled class='action-button locked-button px-4 md:px-6 py-2 rounded-full text-sm md:text-base' 
                                            style='background-color: var(--color-card-border); color: var(--color-text-secondary);'>
                                                Locked
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                ";
                            }
                            $number++;
                            $completed = ($topic_info) ? true : false; 
                        }
                    } else {
                    }
                    ?>
                </div>
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

            // Mobile sidebar functionality
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

            // Staggered animation for the cards
            document.querySelectorAll('.topic-card-horizontal').forEach((el, i) => {
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