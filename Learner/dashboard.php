<?php
include '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_modules.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';

$_SESSION['current_page'] = "dashboard";
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
        /* General button styling for consistency */
        .continue-button {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }

        /* Hover State for Secondary/Continue Button */
        .continue-button:hover {
            background-color: var(--color-button-primary) !important; 
            color: #ffffff !important; /* Always white text on primary hover */
            border-color: var(--color-button-primary) !important; 
        }

        /* Completed/Certificate Button Style */
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

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center fade-slide" style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold header-title" style="color: var(--color-text);">Welcome back, Ryuta</h1>
                <h6 class="text-xs font-bold header-subtitle" style="color: var(--color-text-secondary);">Continue your learning journey</h6>
            </div>
            
            <div class="flex items-center space-x-4">
              <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 rounded-full transition shadow-md border-2" style="background-color: var(--color-user-bg); color: var(--color-user-text); border-color: var(--color-icon);">
                    <i class="fas fa-user-circle text-2xl" style="color: var(--color-heading);"></i>
                    <span class="hidden sm:inline font-bold" style="color: var(--color-user-text);">Ryuta</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">LV 12</span>
                </a>
            </div>
        </header>

        <main class="p-8 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-slide">
                
                <!-- Exam Readiness Wheel Card -->
                <div class="backdrop-blur-sm p-6 rounded-lg shadow-md flex flex-col items-center hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4 w-full">
                        <h3 class="font-semibold" style="color: var(--color-text);">Exam Readiness</h3>
                        <i class="fas fa-crosshairs text-2xl" style="color: var(--color-heading);"></i>
                    </div>
                    <div class="readiness-wheel mb-3">
                        <div class="readiness-percentage">75%</div>
                    </div>
                    <div class="readiness-label">Based on your progress</div>
                    <div class="readiness-status">Well Prepared</div>
                </div>

                <div class="backdrop-blur-sm p-6 rounded-lg shadow-md flex flex-col hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold" style="color: var(--color-text);">Current Streak</h3>
                        <i class="fas fa-fire text-2xl" style="color: var(--color-heading-secondary);"></i>
                    </div>
                    <div class="text-4xl font-bold mb-2 counter" data-target="7" style="color: var(--color-heading-secondary);">7 days</div>
                    <div class="flex space-x-1 mt-auto">
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                        <span class="w-4 h-4 rounded-full" style="background-color: var(--color-heading-secondary);"></span>
                    </div>
                </div>

                <div class="backdrop-blur-sm p-6 rounded-lg shadow-md flex flex-col hover:scale-105 transition-transform" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold" style="color: var(--color-text);">Achievements</h3>
                        <i class="fas fa-medal text-2xl" style="color: var(--color-green-button);"></i>
                    </div>
                    <div class="text-4xl font-bold mb-2 counter" data-target="2" style="color: var(--color-green-button);">2 earned</div>
                    <div class="flex space-x-2 mt-auto">
                        <div class="p-2 rounded-lg" style="background-color: var(--color-card-section-bg);">🏅</div>
                        <div class="p-2 rounded-lg" style="background-color: var(--color-card-section-bg);">⚡️</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="md:col-span-2 space-y-8">
                    
                    <section class="space-y-6 fade-slide p-6 rounded-lg shadow-xl" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold" style="color: var(--color-heading);">My Courses (In Progress)</h2>
                            <a href="courses.php" class="font-medium hover:underline text-sm" style="color: var(--color-heading);">View All</a>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="course-card rounded-xl p-4 flex items-center justify-between" style="background-color: var(--color-card-section-bg);">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 rounded-md text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-book" style="color: var(--color-heading);"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold" style="color: var(--color-text);">Introduction to Python</h3>
                                        <p class="text-sm" style="color: var(--color-text-secondary);">6/15 modules completed</p>
                                        <div class="h-1 rounded-full w-48 mt-2" style="background-color: var(--color-progress-bg);">
                                            <div class="h-1 rounded-full" style="width: 40%; background: var(--color-progress-fill);"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-heading-secondary); color: var(--color-button-secondary-text);">Beginner</span>
                                    <a href="modules.php" class="px-4 py-2 rounded-md transition continue-button hover:scale-[1.02]" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-secondary-text);">Continue</a>
                                </div>
                            </div>
                            
                            <div class="course-card rounded-xl p-4 flex items-center justify-between" style="background-color: var(--color-card-section-bg);">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 rounded-md text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-code" style="color: var(--color-heading);"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold" style="color: var(--color-text);">Web Development Fundamentals</h3>
                                        <p class="text-sm" style="color: var(--color-text-secondary);">10/20 modules completed</p>
                                        <div class="h-1 rounded-full w-48 mt-2" style="background-color: var(--color-progress-bg);">
                                            <div class="h-1 rounded-full" style="width: 50%; background: var(--color-progress-fill);"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-heading-secondary); color: var(--color-button-secondary-text);">Intermediate</span>
                                    <a href="module.php" class="px-4 py-2 rounded-md transition continue-button hover:scale-[1.02]" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-secondary-text);">Continue</a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <section class="space-y-6 fade-slide p-6 rounded-lg shadow-xl" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold" style="color: var(--color-heading);">Completed Courses</h2>
                            <a href="certificates.php" class="font-medium hover:underline text-sm" style="color: var(--color-green-button);">View Certificates</a>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="course-card rounded-xl p-4 flex items-center justify-between" style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-green-button);">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 rounded-md text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-database" style="color: var(--color-green-button);"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold" style="color: var(--color-text);">SQL Essentials</h3>
                                        <p class="text-sm font-bold" style="color: var(--color-green-button);">Final Score: 92%</p>
                                        <p class="text-xs" style="color: var(--color-text-secondary);">Completed on 2024-09-20</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-green-button); color: var(--color-button-secondary-text);">Mastered</span>
                                    <a href="certificate.php?course=sql" class="px-4 py-2 rounded-md transition certificate-button hover:scale-[1.02]">
                                        <i class="fas fa-file-pdf mr-1"></i> Certificate
                                    </a>
                                </div>
                            </div>

                            <div class="course-card rounded-xl p-4 flex items-center justify-between" style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-green-button);">
                                <div class="flex items-center space-x-4">
                                    <div class="p-3 rounded-md text-3xl" style="background-color: var(--color-card-bg);">
                                        <i class="fas fa-sitemap" style="color: var(--color-green-button);"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold" style="color: var(--color-text);">Advanced Algorithms</h3>
                                        <p class="text-sm font-bold" style="color: var(--color-green-button);">Final Score: 88%</p>
                                        <p class="text-xs" style="color: var(--color-text-secondary);">Completed on 2024-08-15</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-green-button); color: var(--color-button-secondary-text);">Mastered</span>
                                    <a href="certificate.php?course=algos" class="px-4 py-2 rounded-md transition certificate-button hover:scale-[1.02]">
                                        <i class="fas fa-file-pdf mr-1"></i> Certificate
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                
                <div class="md:col-span-1 space-y-8">
                    
                    <section class="backdrop-blur-sm p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold" style="color: var(--color-heading);">Level Up</h2>
                            <i class="fas fa-star text-2xl" style="color: var(--color-heading-secondary);"></i>
                        </div>
                        <div class="text-center mb-4">
                            <p class="text-4xl font-extrabold" style="color: var(--color-button-primary);">Level 7</p>
                            <p class="text-sm" style="color: var(--color-text-secondary);">Next level at 5,000 XP</p>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-sm font-medium">
                                <span style="color: var(--color-text);">XP Progress</span>
                                <span style="color: var(--color-heading);">3,450 / 5,000</span>
                            </div>
                            <div class="h-3 rounded-full" style="background-color: var(--color-progress-bg);">
                                <div class="h-3 rounded-full" style="width: 69%; background: var(--color-progress-fill);"></div>
                            </div>
                        </div>
                    </section>
                    
                    <section class="backdrop-blur-sm p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <h2 class="text-xl font-bold mb-4" style="color: var(--color-text);">Today's Goals</h2>
                        <ul class="space-y-4">
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" checked class="h-5 w-5 rounded-full border-2 focus:ring-4" style="color: var(--color-heading); border-color: var(--color-heading); background-color: var(--color-heading);">
                                    <span style="color: var(--color-text);">Complete 2 lessons</span>
                                </div>
                                <i class="fas fa-check-circle" style="color: var(--color-green-button);"></i>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" class="h-5 w-5 rounded-full border-2 focus:ring-4" style="border-color: var(--color-text); background-color: var(--color-main-bg);">
                                    <span style="color: var(--color-text);">Earn 100 XP</span>
                                </div>
                                <i class="far fa-circle" style="color: var(--color-text-secondary);"></i>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" class="h-5 w-5 rounded-full border-2 focus:ring-4" style="border-color: var(--color-text); background-color: var(--color-main-bg);">
                                    <span style="color: var(--color-text);">Practice quiz</span>
                                </div>
                                <i class="far fa-circle" style="color: var(--color-text-secondary);"></i>
                            </li>
                        </ul>
                        <div class="mt-4">
                            <p class="text-sm mb-1" style="color: var(--color-text-secondary);">Daily Progress</p>
                            <div class="h-2 rounded-full" style="background-color: var(--color-progress-bg);">
                                <div class="h-2 rounded-full" style="width: 67%; background: var(--color-progress-fill);"></div>
                            </div>
                        </div>
                    </section>

                    <section class="backdrop-blur-sm p-6 rounded-lg shadow-md fade-slide" style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        <h2 class="text-xl font-bold mb-4" style="color: var(--color-text);">Recent Activity</h2>
                        <ul class="space-y-4 text-sm">
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-book-open" style="color: var(--color-heading);"></i>
                                    <span style="color: var(--color-text);">Completed SQL Joins</span>
                                </div>
                                <span class="font-bold" style="color: var(--color-text-secondary);">+50 XP</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-trophy" style="color: var(--color-icon);"></i>
                                    <span style="color: var(--color-text);">Earned Week Warrior badge</span>
                                </div>
                                <span class="font-bold" style="color: var(--color-text-secondary);">+100 XP</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-check-circle" style="color: var(--color-green-button);"></i>
                                    <span style="color: var(--color-text);">Quiz Score: 85% on SQL Basics</span>
                                </div>
                                <span class="font-bold" style="color: var(--color-text-secondary);">+75 XP</span>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Function to apply theme based on local storage
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

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
    </script>
</body>
</html>