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

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 2px solid var(--color-heading-secondary);">
            <div class="flex flex-col">
                <h1 class="text-3xl font-extrabold flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-scroll mr-3" style="color: var(--color-heading-secondary);"></i> Course Quests
                </h1>
                <h6 class="text-sm font-bold" style="color: var(--color-text-secondary);">Introduction to Python / Variables & Data Types</h6>
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
            <h2 class="text-2xl font-extrabold border-b pb-2 mb-6" style="color: var(--color-heading-secondary); border-color: var(--color-card-border);">
                Module 1: Basic Declarations
            </h2>
            
            <div class="space-y-6">
                <div class="space-y-6">
                    
                    <a href="topicContent.php?topic=1" class="topic-card-horizontal completed-card flex items-center justify-between p-6 rounded-2xl shadow-xl transition-all duration-300 transform hover:scale-[1.01] cursor-pointer"
                           style="background-color: var(--color-card-bg);">
                        
                        <div class="flex items-center space-x-6">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full text-2xl font-extrabold"
                                 style="background-color: var(--color-green-button); color: var(--color-card-bg);">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-xl font-extrabold" style="color: var(--color-heading);">Quest 1: The Variable Container</h3>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Learn how variables store information (Completed).</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 flex-wrap md:flex-nowrap justify-end gap-y-4">
                            
                            <div class="flex-shrink-0 flex items-center space-x-4">
                                <div class="text-center w-16">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Total XP</p>
                                    <p class="text-lg font-bold" style="color: var(--color-heading);">+25</p>
                                </div>
                                <div class="text-center w-16 hidden sm:block">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Time</p>
                                    <p class="text-lg font-bold" style="color: var(--color-text);">5 min</p>
                                </div>
                            </div>
                            
                            <div class="w-24 space-y-1">
                                <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status: <span class="font-bold" style="color: var(--color-green-button);">100%</span></p>
                                <div class="h-2 rounded-full overflow-hidden" style="background-color: var(--color-progress-bg);">
                                    <div class="h-2 rounded-full" style="width: 100%; background: var(--color-green-button);"></div>
                                </div>
                            </div>
                            
                            <div class="w-28 text-right">
                                <span class="action-button review-button px-6 py-2 rounded-full transition-all hover:opacity-80" 
                                      style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); border: 2px solid var(--color-button-secondary-text);">
                                    Review
                                </span>
                            </div>
                        </div>
                    </a>

                    <a href="lesson.php?topic=2" class="topic-card-horizontal in-progress-card flex items-center justify-between p-6 rounded-2xl shadow-xl transition-all duration-300 transform hover:scale-[1.01] cursor-pointer"
                           style="background-color: var(--color-card-bg);">
                        
                        <div class="flex items-center space-x-6">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full text-2xl font-extrabold"
                                 style="background-color: var(--color-heading); color: var(--color-card-bg);">2</div>
                            <div class="space-y-1">
                                <h3 class="text-xl font-extrabold" style="color: var(--color-heading);">Quest 2: Exploring Data Types</h3>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Explore integers, strings, floats, and booleans.</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 flex-wrap md:flex-nowrap justify-end gap-y-4">
                            
                            <div class="flex-shrink-0 flex items-center space-x-4">
                                <div class="text-center w-16">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Total XP</p>
                                    <p class="text-lg font-bold" style="color: var(--color-heading);">+40</p>
                                </div>
                                <div class="text-center w-16 hidden sm:block">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Time</p>
                                    <p class="text-lg font-bold" style="color: var(--color-text);">10 min</p>
                                </div>
                            </div>
                            
                            <div class="w-24 space-y-1">
                                <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status: <span class="font-bold" style="color: var(--color-heading);">45%</span></p>
                                <div class="h-2 rounded-full overflow-hidden" style="background-color: var(--color-progress-bg);">
                                    <div class="h-2 rounded-full" style="width: 45%; background: var(--color-heading);"></div>
                                </div>
                            </div>
                            
                            <div class="w-28 text-right">
                                <span class="action-button primary-button px-6 py-2 rounded-full transition-all hover:opacity-80" 
                                      style="background-color: var(--color-button-primary); color: white; border: 2px solid var(--color-button-primary);">
                                    Continue
                                </span>
                            </div>
                        </div>
                    </a>

                    <div class="topic-card-horizontal locked-card flex items-center justify-between p-6 rounded-2xl opacity-50 cursor-not-allowed border-dashed border-2"
                           style="background-color: var(--color-card-bg); border-color: var(--color-card-border);">
                        
                        <div class="flex items-center space-x-6">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full text-2xl font-extrabold"
                                 style="background-color: var(--color-card-border); color: var(--color-text-secondary);"><i class="fas fa-lock text-base"></i></div>
                            <div class="space-y-1">
                                <h3 class="text-xl font-extrabold" style="color: var(--color-text-secondary);">Quest 3: Naming Conventions</h3>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Best practices for naming variables in code.</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 flex-wrap md:flex-nowrap justify-end gap-y-4">
                            
                            <div class="flex-shrink-0 flex items-center space-x-4">
                                <div class="text-center w-16">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Total XP</p>
                                    <p class="text-lg font-bold" style="color: var(--color-text-secondary);">+33</p>
                                </div>
                                <div class="text-center w-16 hidden sm:block">
                                    <p class="text-xs font-semibold" style="color: var(--color-text-secondary);">Time</p>
                                    <p class="text-lg font-bold" style="color: var(--color-text-secondary);">8 min</p>
                                </div>
                            </div>
                            
                            <div class="w-24 space-y-1">
                                <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Status: <span class="font-bold">Locked</span></p>
                                <div class="h-2 rounded-full overflow-hidden" style="background-color: var(--color-progress-bg);">
                                    <div class="h-2 rounded-full" style="width: 0%; background: var(--color-progress-fill);"></div>
                                </div>
                            </div>
                            
                            <div class="w-28 text-right">
                                <button disabled class="action-button locked-button px-6 py-2 rounded-full" 
                                   style="background-color: var(--color-card-border); color: var(--color-text-secondary);">
                                    Locked
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

        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
    </script>
</body>
</html>