<?php
include '../pdoconfig.php';
include '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_modules.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';
include 'functions/daily_goals_function.php';

unset($_SESSION['training_progress']);
unset($_SESSION['topics_id']);
unset($_SESSION['adaptive_current_topic_index']);
unset($_SESSION['topic_index']);
unset($_SESSION['mastery_each_topic']);
unset($_SESSION['answer_result_tracker']);
unset($_SESSION['adaptive_question_history']);
unset($_SESSION['adaptive_questions_by_topic']);
unset($_SESSION['adaptive_question_index_by_topic']);
unset($_SESSION['adaptive_answered_by_topic']);

unset($_SESSION['testing_questions']);
unset($_SESSION['testing_question_index']);
unset($_SESSION['testing_answered']);
unset($_SESSION['testing_flagged']);
unset($_SESSION['topic_distribution']);

$course_id = $_GET['course_id'] ?? 0;

$user_id = $_SESSION['student_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user info
$user_full_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
$first_name_only = $user_data['first_name'];
$exp = count_total_exp($course_id);
$user_exp_data = $exp[0] ?? 0;

unset($_SESSION['training_progress']);
unset($_SESSION['original_questions']);
unset($_SESSION['shuffled_questions_order']);

$user_level = getUserLevel($user_data['experience'], $user_exp_data, 10);
$user_level = $user_lvl ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Mode Selector | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css"> 
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        body {
            font-family: 'bungee', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.6;
            padding: 0;
            margin: 0; /* Ensures no default body margin */
            transition: background-color 0.3s ease; /* Smooth theme transition */
        }

        .container {
            max-width: 1200px;
            /* Added margin auto and padding-x in HTML for robustness */
        }

        .header {
            background-color: var(--color-header-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-card-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        /* * ==============================
         * 3. CARD & MODE SPECIFIC STYLES
         * ==============================
         */

        .mode-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--color-card-border);
            overflow: hidden;
            position: relative;
        }

        /* Highlight bar at the top of a selected card */
        .mode-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-progress-fill);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .mode-card.selected::before {
            transform: scaleX(1);
        }

        .mode-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .mode-card.selected {
            border-color: var(--color-heading);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Background gradient highlights for selected state */
        .training-highlight {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, rgba(34, 197, 94, 0.05) 100%);
        }

        .testing-highlight {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, rgba(249, 115, 22, 0.05) 100%);
        }

        .dark-mode .training-highlight {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, rgba(16, 185, 129, 0.15) 100%);
        }

        .dark-mode .testing-highlight {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, rgba(251, 146, 60, 0.15) 100%);
        }

        /* * ==============================
         * 4. BUTTON & UTILITY STYLES
         * ==============================
         */
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background-color: var(--color-card-border);
            color: var(--color-text-secondary);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        .pulse { animation: pulse 2s infinite; }
    </style>
</head>
<body>
    
    <header class="header py-3">
            <div class="flex items-center justify-between w-full px-8">
                
                <a href="#" class="flex items-center gap-2 flex-shrink-0">
                    <div class="w-8 h-8 rounded-full object-contain flex items-center justify-center" style="background-color: var(--color-heading);">
                        <img src="../images/isu-logo.png" alt="ISU Logo">
                    </div>
                    <div>
                        <h1 class="text-base sm:text-lg font-extrabold tracking-wider truncate text-[var(--color-heading)] leading-none">
                            ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
                        </h1>
                        <p class="text-xs mt-0.5 hidden sm:block" style="color: var(--color-text-secondary);">Mastery Through Practice</p>
                    </div>
                </a>
                
                <div class="flex items-center space-x-4">
                    <div class="profile-info flex items-center gap-2 px-3 py-1 md:px-4 md:py-2 rounded-lg text-sm md:text-base" style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle" style="color: var(--color-heading);"></i>
                        <span class="font-medium" style="color: var(--color-user-text);"><?= $user_full_name ?></span>
                        <span class="hidden md:inline-block px-2 py-1 rounded-full text-xs font-bold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">Level <?= $user_level ?></span>
                    </div>

                    <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-full text-lg" style="color: var(--color-icon); background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);" aria-label="Toggle Dark Mode">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                </div>
            </div>
    </header>

    <main class="py-16 md:py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12 md:mb-16 fade-in">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--color-heading);">Select Learning Mode</h2>
                    <p class="text-lg max-w-2xl mx-auto" style="color: var(--color-text-secondary);">
                        Choose the learning approach that best fits your current goals and preferences
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-10 md:mb-12">
                    <div class="mode-card bg-[var(--color-card-bg)] rounded-xl p-6 md:p-8 cursor-pointer fade-in" id="training-card">
                        <div class="flex flex-col h-full">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-16 h-16 icon-container rounded-xl flex items-center justify-center" style="background-color: rgba(34, 197, 94, 0.1);">
                                    <i class="fas fa-graduation-cap text-3xl" style="color: var(--color-heading);"></i>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold badge-training" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-heading);">
                                    <i class="fas fa-star mr-1"></i> Recommended
                                </span>
                            </div>
                            
                            <h3 class="text-xl md:text-2xl font-bold mb-4" style="color: var(--color-heading);">Training Mode</h3>
                            <p class="mb-6 flex-1 text-base" style="color: var(--color-text);">
                                Build foundational knowledge with guided learning, step-by-step explanations, and unlimited practice opportunities.
                            </p>
                            
                            <div class="mb-6">
                                <h4 class="font-semibold mb-4 text-xs uppercase tracking-wider" style="color: var(--color-text-secondary);">Key Features</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-heading);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Step-by-step guided learning</span>
                                    </div>
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-heading);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Unlimited practice attempts</span>
                                    </div>
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-heading);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Detailed explanations & hints</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto pt-6 border-t flex justify-between items-center" style="border-color: var(--color-card-border);">
                                <div>
                                    <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Best For</p>
                                    <p class="font-semibold text-base" style="color: var(--color-heading);">Knowledge Building</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Pace</p>
                                    <p class="font-semibold text-base" style="color: var(--color-heading);">Self-Directed</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mode-card bg-[var(--color-card-bg)] rounded-xl p-6 md:p-8 cursor-pointer fade-in" id="testing-card">
                        <div class="flex flex-col h-full">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-16 h-16 icon-container rounded-xl flex items-center justify-center" style="background-color: rgba(249, 115, 22, 0.1);">
                                    <i class="fas fa-clipboard-check text-3xl" style="color: var(--color-heading-secondary);"></i>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold badge-testing" style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                    <i class="fas fa-bolt mr-1"></i> Evaluation
                                </span>
                            </div>
                            
                            <h3 class="text-xl md:text-2xl font-bold mb-4" style="color: var(--color-heading-secondary);">Testing Mode</h3>
                            <p class="mb-6 flex-1 text-base" style="color: var(--color-text);">
                                Assess your knowledge under realistic conditions with timed assessments and performance-based scoring.
                            </p>
                            
                            <div class="mb-6">
                                <h4 class="font-semibold mb-4 text-xs uppercase tracking-wider" style="color: var(--color-text-secondary);">Key Features</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Timed assessment conditions</span>
                                    </div>
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Limited attempt simulations</span>
                                    </div>
                                    <div class="flex items-center text-sm" style="color: var(--color-text);">
                                        <div class="w-5 h-5 feature-icon rounded-full flex items-center justify-center mr-3 flex-shrink-0" style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span>Performance analytics & scoring</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto pt-6 border-t flex justify-between items-center" style="border-color: var(--color-card-border);">
                                <div>
                                    <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Best For</p>
                                    <p class="font-semibold text-base" style="color: var(--color-heading-secondary);">Skill Assessment</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Pace</p>
                                    <p class="font-semibold text-base" style="color: var(--color-heading-secondary);">Timed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center fade-in">
                    <button id="continue-btn" class="btn-primary py-4 px-10 rounded-xl text-lg font-semibold disabled:opacity-50 transition-all duration-300 shadow-lg" disabled>
                        <span id="btn-text">Select a Learning Mode</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <p class="text-sm mt-4" style="color: var(--color-text-secondary);">
                        You can switch between modes at any time from your dashboard
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-8 border-t" style="border-color: var(--color-card-border);">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm" style="color: var(--color-text-secondary);">
                        &copy; 2025 ISUtoLearn. All rights reserved.
                    </p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Privacy Policy</a>
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Terms of Service</a>
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Help Center</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trainingCard = document.getElementById('training-card');
            const testingCard = document.getElementById('testing-card');
            const continueBtn = document.getElementById('continue-btn');
            const btnText = document.getElementById('btn-text');
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');

            let selectedMode = null;

            // --- Theme Functions ---

            function isDarkMode() {
                return localStorage.getItem('darkMode') === 'true';
            }

            function applyTheme(isDark) {
                document.body.classList.toggle('dark-mode', isDark);
                localStorage.setItem('darkMode', isDark);
                themeIcon.classList.toggle('fa-moon', !isDark);
                themeIcon.classList.toggle('fa-sun', isDark);
            }

            function initializeTheme() {
                // Check local storage or prefer user's system setting
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = localStorage.getItem('darkMode') !== null 
                    ? isDarkMode() 
                    : prefersDark;
                applyTheme(initialTheme);
            }
            
            themeToggle.addEventListener('click', () => {
                const currentMode = isDarkMode();
                applyTheme(!currentMode);
            });

            initializeTheme();

            // --- Mode Selection Functions ---

            trainingCard.addEventListener('click', function() {
                selectedMode = 'training';
                updateSelection();
            });

            testingCard.addEventListener('click', function() {
                selectedMode = 'testing';
                updateSelection();
            });

            function updateSelection() {
                // 1. Reset all cards
                trainingCard.classList.remove('selected', 'training-highlight');
                testingCard.classList.remove('selected', 'testing-highlight');
                
                // 2. Apply selected state
                if (selectedMode === 'training') {
                    trainingCard.classList.add('selected', 'training-highlight');
                } else if (selectedMode === 'testing') {
                    testingCard.classList.add('selected', 'testing-highlight');
                }

                // 3. Update button state
                updateContinueButton();
            }

            function updateContinueButton() {
                if (selectedMode) {
                    continueBtn.disabled = false;
                    
                    if (selectedMode === 'training') {
                        btnText.textContent = 'Continue to Training Mode';
                    } else {
                        btnText.textContent = 'Continue to Testing Mode';
                    }
                    
                    // Add pulse animation once
                    continueBtn.classList.add('pulse');
                    setTimeout(() => {
                        continueBtn.classList.remove('pulse');
                    }, 2000);
                } else {
                    continueBtn.disabled = true;
                    btnText.textContent = 'Select a Learning Mode';
                }
            }

            continueBtn.addEventListener('click', function() {
                if (selectedMode) {
                    if (selectedMode === 'training') {
                      
                        window.location.href = "training_confirmation.php?course_id=<?= $course_id ?>";

                    } else if (selectedMode === 'testing') {
                        window.location.href = "testing_confirmation.php?course_id=<?= $course_id ?>";
                        
                    }
                }
            });
        });
    </script>
</body>
</html>