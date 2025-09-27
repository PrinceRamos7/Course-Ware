<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
    /* --- Global Styles (Assume existing) ---
    .interactive-button { ... } 
    .success-action, .primary-action, .secondary-action { ... } 
    */

    /* Module Card Frame & Hover Effect */
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
                
                <div class="module-card rounded-xl p-6 shadow-xl space-y-4" style="background-color: var(--color-card-bg);">
                    <div class="flex justify-between items-start border-b pb-4" style="border-color: var(--color-card-section-bg);">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading);">1. Introduction to Variables</h3>
                            <p class="text-sm" style="color: var(--color-text-secondary);">Understanding data types and basic storage.</p>
                        </div>
                        <div class="flex items-center space-x-3 text-sm font-semibold" style="color: var(--color-text);">
                            <span class="flex items-center space-x-1"><i class="fas fa-list-ol" style="color: var(--color-icon);"></i> <span>5 Topics</span></span>
                            <span class="flex items-center space-x-1"><i class="fas fa-clock" style="color: var(--color-icon);"></i> <span>30 min</span></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Base XP</p><p class="text-lg font-bold" style="color: var(--color-heading);">+150</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Bonus XP</p><p class="text-lg font-bold" style="color: var(--color-heading-secondary);">+50</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Required Score</p><p class="text-lg font-bold" style="color: var(--color-green-button);">80%</p></div>
                         <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status</p><p class="text-lg status-completed">Completed</p></div>
                    </div>

                    <div class="space-y-3 pt-4 border-t" style="border-color: var(--color-card-border);">
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium" style="color: var(--color-text);">Module Progress</span>
                            <span class="text-sm font-bold status-completed">100%</span>
                        </div>
                        <div class="h-2 rounded-full progress-bar-container" style="background-color: var(--color-progress-bg);">
                            <div class="h-2 rounded-full" style="width: 100%; background: var(--color-green-button);"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 pt-2">
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">Your Score</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-green-button);">92%</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">Total XP Gained</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-heading);">+200</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">Module Rank</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-heading-secondary);">A+</p>
                            </div>
                            <div class="flex flex-col items-center justify-center">
                                <a href="topicCard.php?module=1" class="module-action-button secondary w-full">
                                    <i class="fas fa-book-reader mr-1"></i> Review Topics
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="module-card rounded-xl p-6 shadow-xl space-y-4"
                     style="background-color: var(--color-card-bg);">
                    
                    <div class="flex justify-between items-start border-b pb-4" style="border-color: var(--color-card-section-bg);">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading);">2. Basic Operations & Math</h3>
                            <p class="text-sm" style="color: var(--color-text-secondary);">Arithmetic, assignment, and comparison.</p>
                        </div>
                        <div class="flex items-center space-x-3 text-sm font-semibold" style="color: var(--color-text);">
                            <span class="flex items-center space-x-1"><i class="fas fa-list-ol" style="color: var(--color-icon);"></i> <span>7 Topics</span></span>
                            <span class="flex items-center space-x-1"><i class="fas fa-clock" style="color: var(--color-icon);"></i> <span>45 min</span></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Base XP</p><p class="text-lg font-bold" style="color: var(--color-heading);">+200</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Bonus XP</p><p class="text-lg font-bold" style="color: var(--color-heading-secondary);">+70</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Required Score</p><p class="text-lg font-bold" style="color: var(--color-green-button);">80%</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status</p><p class="text-lg status-progress">In Progress</p></div>
                    </div>

                    <div class="space-y-3 pt-4 border-t" style="border-color: var(--color-card-border);">
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium" style="color: var(--color-text);">Module Progress</span>
                            <span class="text-sm font-bold" style="color: var(--color-heading);">40%</span>
                        </div>
                        <div class="h-2 rounded-full progress-bar-container" style="background-color: var(--color-progress-bg);">
                            <div class="h-2 rounded-full" style="width: 40%; background: var(--color-button-primary);"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 pt-2">
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">Topics Completed</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-heading);">3 / 7</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">XP Earned</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-heading);">+85</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <p class="text-xs" style="color: var(--color-text-secondary);">Next Topic</p>
                                <p class="text-lg font-extrabold" style="color: var(--color-heading-secondary);">Operators</p>
                            </div>
                            
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <a href="lesson.php?module=2" class="module-action-button primary w-full">
                                    <i class="fas fa-play mr-1"></i> Continue Module
                                </a>
                                
                                <a href="#" class="module-action-button locked-assessment-button w-full">
                                    <i class="fas fa-lock mr-1"></i> Assessment Locked
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="module-card rounded-xl p-6 shadow-xl space-y-4 opacity-70"
                     style="background-color: var(--color-card-bg);">
                    
                    <div class="flex justify-between items-start border-b pb-4" style="border-color: var(--color-card-section-bg);">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-extrabold status-locked"><i class="fas fa-lock mr-2"></i> 3. Control Flow (Locked)</h3>
                            <p class="text-sm" style="color: var(--color-text-secondary);">Conditional statements and looping structures.</p>
                        </div>
                        <div class="flex items-center space-x-3 text-sm font-semibold status-locked">
                            <span class="flex items-center space-x-1"><i class="fas fa-list-ol"></i> <span>10 Topics</span></span>
                            <span class="flex items-center space-x-1"><i class="fas fa-clock"></i> <span>60 min</span></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Base XP</p><p class="text-lg font-bold status-locked">+250</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Bonus XP</p><p class="text-lg font-bold status-locked">+80</p></div>
                        <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Required Score</p><p class="text-lg font-bold status-locked">80%</p></div>
                         <div class="p-3 rounded-lg stat-box"><p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status</p><p class="text-lg status-locked">Locked</p></div>
                    </div>

                    <div class="space-y-3 pt-4 border-t" style="border-color: var(--color-card-border);">
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium status-locked">Module Progress</span>
                            <span class="text-sm font-bold status-locked">0%</span>
                        </div>
                        <div class="h-2 rounded-full progress-bar-container" style="background-color: var(--color-progress-bg);">
                            <div class="h-2 rounded-full" style="width: 0%; background: var(--color-text-secondary);"></div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 pt-2">
                            <div class="flex flex-col items-center col-span-4">
                                <button disabled class="module-action-button primary w-full opacity-50 cursor-not-allowed">
                                    <i class="fas fa-lock mr-2"></i> Requires Module 2 Completion
                                </button>
                            </div>
                        </div>
                    </div>
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