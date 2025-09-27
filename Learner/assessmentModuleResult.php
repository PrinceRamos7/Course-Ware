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

        .result-frame {
            border: 3px solid var(--color-heading);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3), 0 0 0 5px var(--color-heading-secondary); 
        }

 
        .status-passed {
            color: var(--color-green-button); /* Use your defined success color */
            font-weight: bold;
        }
        .status-failed {
            color: var(--color-red-button); /* Use your defined failure color */
            font-weight: bold;
        }

        /* EXP and Rank box styling */
        .exp-box {
            background-color: var(--color-card-section-bg);
            border-left: 5px solid var(--color-heading);
        }

        /* Performance Bar styling */
        .performance-bar {
            height: 8px;
            background-color: var(--color-card-border);
            border-radius: 9999px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-out;
        }
        
        /* Set specific colors for progress based on score */
        .bar-fill[data-score="80"] { background-color: #3b82f6; } /* Blue */
        .bar-fill[data-score="60"] { background-color: #f59e0b; } /* Amber */
        .bar-fill[data-score="70"] { background-color: #8b5cf6; } /* Violet */
        .bar-fill[data-score="90"] { background-color: var(--color-green-button); } /* Green/Success */


        /* Action Button Styles (Matching your previous interactive styles) */
        .interactive-button {
            font-weight: bold;
            border-width: 2px;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
        }
        .interactive-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
        }
        .review-button {
            background-color: var(--color-button-primary);
            color: white;
            border-color: var(--color-button-primary);
            box-shadow: 0 4px 0 var(--color-heading-secondary);
        }
        .retry-button {
            background-color: var(--color-red-button);
            color: white;
            border-color: var(--color-red-button);
            box-shadow: 0 4px 0 var(--color-red-button-hover);
        }

    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-2xl mr-3" style="color: var(--color-heading);"></i>
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">Assessment Debrief</h1>
            </div>
            <a href="modules.php" class="px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold">
                 <i class="fas fa-home mr-2"></i> Back to Module
            </a>
        </header>

        <main class="p-8 max-w-7xl mx-auto flex-1 w-full">

            <div class="flex justify-between items-start mb-6 pb-2 border-b" style="border-color: var(--color-card-border);">
                <div>
                    <h2 class="text-3xl font-extrabold mb-1" style="color: var(--color-heading);">Assessment Result</h2>
                    <p class="text-sm" style="color: var(--color-text-secondary);">
                        Lesson: Introduction Quiz • Date: 2025-09-19 • Duration: 00:12:34
                    </p>
                </div>
                <div class="flex items-center">
                    <span class="text-xl font-bold mr-2" style="color: var(--color-text-secondary);">Status</span>
                    <span class="text-2xl status-passed">Passed</span> 
                </div>
            </div>

            <div class="result-frame p-6 rounded-xl shadow-2xl flex" 
                 style="background-color: var(--color-card-bg);">
                 
                <div class="w-1/3 pr-6 border-r" style="border-color: var(--color-card-border);">
                    
                    <div class="mb-6">
                        <p class="text-6xl font-extrabold mb-1" style="color: var(--color-button-primary);">75 / 100</p>
                        <p class="text-xl font-bold mb-4" style="color: var(--color-text);">75% — Good job</p>
                        <p class="text-sm" style="color: var(--color-text-secondary);">Time Spent: <span class="font-bold">12m 34s</span></p>
                    </div>

                    <div class="exp-box p-4 rounded-lg shadow-inner mb-6">
                        <p class="text-lg font-bold mb-2" style="color: var(--color-heading);">EXP Gained <span class="float-right font-extrabold text-xl status-passed">+150</span></p>
                        <ul class="text-sm space-y-1" style="color: var(--color-text);">
                            <li class="flex justify-between">Base EXP: <span>100</span></li>
                            <li class="flex justify-between">Bonus EXP (speed): <span>+50</span></li>
                            <li class="flex justify-between font-bold" style="color: var(--color-heading-secondary);">Rank (class): <span>3 / 24</span></li>
                        </ul>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: var(--color-button-primary); color: white;">Accuracy 75%</span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: var(--color-green-button); color: white;">Speed Bonus</span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: var(--color-heading-secondary); color: white;">Quiz Completed</span>
                    </div>

                </div>

                <div class="w-2/5 px-6 border-r" style="border-color: var(--color-card-border);">
                    
                    <h3 class="text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Score Breakdown</h3>
                    <ul class="text-lg space-y-2 mb-8" style="color: var(--color-text);">
                        <li class="flex justify-between font-bold">Correct: <span class="status-passed">15 / 20</span></li>
                        <li class="flex justify-between">Wrong: <span class="font-bold" style="color: var(--color-red-button);">3</span></li>
                        <li class="flex justify-between">Unanswered: <span class="font-bold" style="color: var(--color-text-secondary);">2</span></li>
                    </ul>

                    <h3 class="text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Time Details</h3>
                    <ul class="text-lg space-y-2" style="color: var(--color-text);">
                        <li class="flex justify-between">Total time: <span class="font-bold">12m 34s</span></li>
                        <li class="flex justify-between">Average per question: <span class="font-bold">38s</span></li>
                        <li class="flex justify-between">Fastest: <span class="font-bold status-passed">6s</span></li>
                        <li class="flex justify-between">Slowest: <span class="font-bold" style="color: var(--color-red-button);">1m 12s</span></li>
                    </ul>

                </div>

                <div class="w-1/3 pl-6 flex flex-col justify-between">
                    
                    <div>
                        <h3 class="text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Per-topic performance</h3>
                        <div class="space-y-4">
                            <div class="topic-item">
                                <div class="flex justify-between mb-1 text-sm" style="color: var(--color-text);">
                                    <span class="font-bold">Variables & Data Types</span>
                                    <span class="font-extrabold">80%</span>
                                </div>
                                <div class="performance-bar">
                                    <div class="bar-fill" style="width: 80%;" data-score="80"></div>
                                </div>
                            </div>
                            <div class="topic-item">
                                <div class="flex justify-between mb-1 text-sm" style="color: var(--color-text);">
                                    <span class="font-bold">Control Structures</span>
                                    <span class="font-extrabold" style="color: var(--color-red-button);">60%</span>
                                </div>
                                <div class="performance-bar">
                                    <div class="bar-fill" style="width: 60%;" data-score="60"></div>
                                </div>
                            </div>
                            <div class="topic-item">
                                <div class="flex justify-between mb-1 text-sm" style="color: var(--color-text);">
                                    <span class="font-bold">Functions</span>
                                    <span class="font-extrabold" style="color: #8b5cf6;">70%</span>
                                </div>
                                <div class="performance-bar">
                                    <div class="bar-fill" style="width: 70%;" data-score="70"></div>
                                </div>
                            </div>
                            <div class="topic-item">
                                <div class="flex justify-between mb-1 text-sm" style="color: var(--color-text);">
                                    <span class="font-bold">Arrays & Loops</span>
                                    <span class="font-extrabold status-passed">90%</span>
                                </div>
                                <div class="performance-bar">
                                    <div class="bar-fill" style="width: 90%;" data-score="90"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-6 space-y-3">
                        <a href="reviewAnswers.php" class="w-full py-3 rounded-full transition interactive-button review-button flex items-center justify-center font-extrabold text-lg">
                            <i class="fas fa-eye mr-2"></i> Review Answers
                        </a>
                        <a href="#" class="w-full py-2 rounded-full transition interactive-button secondary-action flex items-center justify-center font-semibold text-sm">
                            <i class="fas fa-file-pdf mr-2"></i> Download Report (PDF)
                        </a>
                        <a href="assessmentModule.php" class="w-full py-2 rounded-full transition interactive-button retry-button flex items-center justify-center font-semibold text-sm">
                            <i class="fas fa-redo-alt mr-2"></i> Retry Assessment
                        </a>
                    </div>
                </div>

            </div>
            </main>
    </div>

    <script>
        // Placeholder for theme function and any dynamic loading (e.g., fetching actual scores)
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            // Assume your dark mode CSS classes are handled by this function
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        
        // Dynamic color application for bars (Optional, but good practice)
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.bar-fill').forEach(bar => {
                const score = bar.getAttribute('data-score');
                // The style block already handles color based on data-score
            });
        });
    </script>
</body>
</html>