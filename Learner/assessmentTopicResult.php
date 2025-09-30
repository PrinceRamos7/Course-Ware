<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style>
        /* Custom utility classes based on roots */
        .bg-main { background-color: var(--color-main-bg); }
        .bg-card { background-color: var(--color-card-bg); }
        .text-heading { color: var(--color-heading); }
        .text-heading-secondary { color: var(--color-heading-secondary); }
        .text-primary { color: var(--color-text); }
        .text-secondary { color: var(--color-text-secondary); }
        .border-card { border-color: var(--color-card-border); }
        
        .btn-primary { 
            background-color: var(--color-button-primary); 
            color: white; 
            transition: background-color 0.2s;
        }
        .btn-primary:hover { background-color: var(--color-button-primary-hover); }

        .btn-secondary { 
            background-color: var(--color-button-secondary); 
            color: var(--color-button-secondary-text); 
            transition: background-color 0.2s;
        }
        .btn-secondary:hover { 
            background-color: #fce3a7; /* Slight darker hover for secondary */
        }
        
        .bg-xp { background-color: var(--color-xp-bg); }
        .text-xp { color: var(--color-xp-text); }
        .bg-progress { background-color: var(--color-progress-bg); }
        .bg-progress-fill { background: var(--color-progress-fill); }
        .bg-card-section { 
            background-color: var(--color-card-section-bg); 
            border-color: var(--color-card-section-border);
        }
        .text-on-section { color: var(--color-text-on-section); }
        .text-icon { color: var(--color-icon); }

        /* Animation for XP gain - subtle scale-up */
        @keyframes pop-in {
            0% { transform: scale(0.5); opacity: 0; }
            80% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pop-in {
            animation: pop-in 0.4s ease-out;
        }
        
        /* Add hover effects explicitly since JS isn't adding them anymore */
        .score-block-hover:hover {
            transform: scale(1.03);
            --tw-ring-color: var(--color-heading-secondary);
            --tw-ring-opacity: 0.3;
            box-shadow: 0 0 0 4px var(--tw-ring-color);
            transition: transform 0.3s, box-shadow 0.3s;
        }
    </style>
</head>
<body class="bg-main font-['Inter'] min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8">
    <?php include "sidebar.php";?>
    <!-- Results Card -->
    <div id="results-card" class="bg-card border-card border-4 shadow-xl rounded-md w-full max-w-2xl text-center p-6 sm:p-8 md:p-10 pop-in">
        
        <!-- Header / Rank Display -->
        <header class="mb-8">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-heading mb-2">
                Assessment Complete!
            </h1>
            <h2 class="text-xl sm:text-2xl font-semibold text-primary">
                Topic: <span class="text-heading-secondary">The French Revolution</span>
            </h2>
        </header>

        <!-- Score & Time Section (Grid Layout) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            
            <!-- 1. Score -->
            <div class="bg-card-section p-4 rounded-xl border border-card-section-border shadow-md score-block-hover">
                <div class="text-icon mb-1">
                    <!-- Inline SVG for 'award' icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 sm:w-10 sm:h-10 mx-auto">
                        <path d="m15.4 17.7-1.8-1.2A6.6 6.6 0 0 1 12 17c-1.5 0-3-.5-4.2-1.2l-1.8 1.2c-1 .7-2.3.1-2.9-1.1s-.1-2.4.9-3.1l1.8-1.2c1.2-.7 2.4-1.2 3.6-1.2s2.4.5 3.6 1.2l1.8 1.2c1.2.7 1.9 2 1.9 3.1s-.9 1.8-2 1.1ZM12 4l3.7 1.7L18 8l-3.3 2.5-3.8 1.8-3.7-1.7L6 8l3.3-2.5L12 4Z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-on-section uppercase">Final Score</p>
                <p class="text-3xl sm:text-4xl font-black text-heading leading-tight" id="final-score">
                    92%
                </p>
                <p class="text-lg font-bold text-heading-secondary mt-1">
                    EXPERT RANK
                </p>
            </div>

            <!-- 2. EXP Gained -->
            <div class="bg-card-section p-4 rounded-xl border border-card-section-border shadow-md score-block-hover">
                <div class="text-icon mb-1">
                    <!-- Inline SVG for 'star' icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 sm:w-10 sm:h-10 mx-auto">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-on-section uppercase">XP Earned</p>
                <p class="text-3xl sm:text-4xl font-black text-heading leading-tight" id="xp-gained">
                    +550 XP
                </p>
                <p class="text-lg font-bold text-xp mt-1">
                    NEW LEVEL 7!
                </p>
            </div>

            <!-- 3. Time Spent -->
            <div class="bg-card-section p-4 rounded-xl border border-card-section-border shadow-md score-block-hover">
                <div class="text-icon mb-1">
                    <!-- Inline SVG for 'clock' icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 sm:w-10 sm:h-10 mx-auto">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-on-section uppercase">Time Spent</p>
                <p class="text-3xl sm:text-4xl font-black text-heading leading-tight" id="time-spent">
                    12:34
                </p>
                <p class="text-lg font-bold text-text-secondary mt-1">
                    (Mins:Secs)
                </p>
            </div>
        </div>

        <!-- Progress Bar (Gamified Element) -->
        <div class="mb-10 p-4 rounded-xl bg-xp shadow-inner border border-yellow-500/50">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-bold text-xp">Level 7 Progress</span>
                <span class="text-sm font-bold text-xp">65%</span>
            </div>
            <div class="w-full bg-progress rounded-full h-3">
                <div class="bg-progress-fill h-3 rounded-full" style="width: 65%;"></div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            
            <a href="assessmentTopic.php" id="btn-retry" class="btn-primary w-full sm:w-1/2 flex items-center justify-center p-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                <!-- Inline SVG for 'rotate-ccw' icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                    <path d="M12.9 6c-3.7-.8-7.4 1.5-8.2 5.2-.8 3.7 1.5 7.4 5.2 8.2 3.7.8 7.4-1.5 8.2-5.2s-1.5-7.4-5.2-8.2z"/><path d="M12 2v4"/><path d="M18 10h-4"/>
                </svg>
                Retry Quiz
            </a>

            <!-- Back to Topics Button (Secondary Action) -->
            <a href="topicCard.php" id="btn-back" class="btn-secondary w-full sm:w-1/2 flex items-center justify-center p-3 rounded-xl font-bold text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.02]">
                <!-- Inline SVG for 'layout-grid' icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Back to Topics
            </a>
        </div>

    </div>

    <script>
                function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
    </script>
</body>
</html>
