<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script> 

    <style>
        /* Custom scrollbar hide for consistency */
        .custom-scrollbar-hide::-webkit-scrollbar { 
            display: none; 
        }
        .custom-scrollbar-hide { 
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }

        /* Styles for Locked Achievements */
        .locked-achievement {
            opacity: 0.4;
            filter: grayscale(100%);
            cursor: pointer;
            transition: opacity 0.3s, filter 0.3s;
        }

        /* Styles for Unlocked Achievements (with a subtle celebratory glow) */
        .unlocked-achievement {
            cursor: pointer;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
            box-shadow: 0 0 0 rgba(0, 0, 0, 0); 
        }
        .unlocked-achievement:hover {
            transform: translateY(-4px) scale(1.02);
            /* Using a common blue/purple glow that looks good in both modes */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), 0 0 10px rgba(79, 70, 229, 0.3); 
        }
        
        /* Modal Backdrop */
        #achievement-modal {
            background-color: rgba(0, 0, 0, 0.7); 
            z-index: 50;
        }

        .color-xp-text { color: var(--color-yellow-500, #f59e0b); } /* Fallback included */
        .color-intel-text { color: var(--color-blue-500, #0ea5e9); } /* Fallback included */
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>
        
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar-hide">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center z-10" 
    style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
    
    <div class="flex">
            <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)] rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
    </button>
    
    <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">
        🏆 My Achievements
    </h1>
    </div>

    
    <div class="hidden md:block w-10">
        </div>
</header>

        <main class="flex-1 px-6 md:px-12 py-8">
            
            <div id="achievements-card" class="p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto space-y-10" 
                 style="background-color: var(--color-card-bg); border: 2px solid var(--color-card-border);">

                <div class="flex justify-center border-b pb-1" style="border-color: var(--color-card-border);">
                    <button id="tab-unlocked" data-filter="unlocked" class="tab-button text-lg px-6 py-2 mx-2 transition-all border-b-4" 
                            style="color: var(--color-heading); border-color: var(--color-button-primary);">
                        Unlocked <span id="unlocked-count" class="ml-2 px-3 py-0.5 rounded-full text-sm font-extrabold" style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">0</span>
                    </button>
                    <button id="tab-locked" data-filter="locked" class="tab-button text-lg px-6 py-2 mx-2 transition-all border-b-4" 
                            style="color: var(--color-text-secondary); border-color: transparent;">
                        Locked <span id="locked-count" class="ml-2 px-3 py-0.5 rounded-full text-sm font-extrabold" style="background-color: var(--color-progress-bg); color: var(--color-text-secondary);">0</span>
                    </button>
                </div>

                <div id="achievements-container" class="space-y-8">
                    </div>
            </div>

            <div id="achievement-modal" class="fixed inset-0 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
                <div id="modal-content-wrapper" class="p-8 rounded-2xl shadow-2xl w-11/12 max-w-sm text-center transform scale-90 transition-transform duration-300"
                    style="background-color: var(--color-card-bg); border: 2px solid var(--color-heading-secondary);">
                    
                    <button id="close-modal-btn" class="absolute top-4 right-4 text-3xl font-light transition hover:text-red-500" 
                            style="color: var(--color-text-secondary);">&times;</button>
                    
                    <div id="modal-content" class="space-y-4 pt-4">
                        </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        // --- THEME FUNCTION ---
        // Function to apply the theme from local storage (Ensuring it runs first)
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }

        // --- DATA ---
        // Icon colors now use CSS variables (e.g., --color-yellow-500) for theme compatibility
        const achievementsData = [
            // Mastery Achievements
            { id: "first-step", title: "First Step", category: "Mastery", description: "You successfully finished your first course. This is just the beginning!", icon: "fas fa-medal", iconColor: "var(--color-yellow-500)", isUnlocked: true, gains: { xp: 100, intelligence: 5 } },
            { id: "web-dev-pro", title: "Web Dev Expert", category: "Mastery", description: "Completed the entire Web Development Track.", icon: "fas fa-laptop-code", iconColor: "var(--color-blue-500)", isUnlocked: true, gains: { xp: 200, intelligence: 15 } },
            { id: "python-master", title: "Python Fundamentals", category: "Mastery", description: "Mastered the fundamentals of Python programming.", icon: "fab fa-python", iconColor: "var(--color-indigo-500)", isUnlocked: false, gains: { xp: 250, intelligence: 20 } },
            { id: "data-scientist", title: "Data Explorer", category: "Mastery", description: "Completed the Data Science with Python course.", icon: "fas fa-chart-line", iconColor: "var(--color-teal-500)", isUnlocked: false, gains: { xp: 250, intelligence: 25 } },
            
            // Performance Achievements
            { id: "top-scorer", title: "Quiz Whiz", category: "Performance", description: "Scored a perfect 100% on any major quiz. Precision learning!", icon: "fas fa-star", iconColor: "var(--color-green-400)", isUnlocked: true, gains: { xp: 150, intelligence: 10 } },
            { id: "critical-thinker", title: "Critical Thinker", category: "Performance", description: "Aced a logic-based quiz. Your analytical skills are sharp!", icon: "fas fa-lightbulb", iconColor: "var(--color-purple-500)", isUnlocked: true, gains: { xp: 100, intelligence: 10 } },
            { id: "perfectionist", title: "The Perfectionist", category: "Performance", description: "Completed five courses with an average score above 95%.", icon: "fas fa-trophy", iconColor: "var(--color-red-500)", isUnlocked: false, gains: { xp: 300, intelligence: 25 } },

            // Activity Achievements
            { id: "speed-demon", title: "Speed Demon", category: "Activity", description: "Completed an assessment in under 5 minutes. Efficiency is key!", icon: "fas fa-bolt", iconColor: "var(--color-orange-500)", isUnlocked: false, gains: { xp: 50, intelligence: 3 } },
            { id: "early-bird", title: "Early Bird", category: "Activity", description: "Logged in and studied before 7:00 AM for five consecutive days.", icon: "fas fa-sun", iconColor: "var(--color-amber-400)", isUnlocked: false, gains: { xp: 75, intelligence: 5 } },
            { id: "night-owl", title: "Night Owl", category: "Activity", description: "Logged in and studied past 11:00 PM for five consecutive days.", icon: "fas fa-moon", iconColor: "var(--color-violet-400)", isUnlocked: true, gains: { xp: 75, intelligence: 5 } },
            { id: "collaborator", title: "Community Member", category: "Activity", description: "Successfully worked with a peer on a collaborative project or forum.", icon: "fas fa-users", iconColor: "var(--color-pink-500)", isUnlocked: false, gains: { xp: 100, intelligence: 5 } },
            { id: "streak-master", title: "30-Day Streak", category: "Activity", description: "Achieved a learning streak of 30 days. Unstoppable dedication!", icon: "fas fa-fire", iconColor: "var(--color-red-600)", isUnlocked: false, gains: { xp: 500, intelligence: 30 } },
        ];

        // --- STATE & UTILITIES ---
        let currentFilter = 'unlocked'; // Default view is Unlocked

        // Group achievements by category
        const groupedAchievements = achievementsData.reduce((acc, ach) => {
            if (!acc[ach.category]) {
                acc[ach.category] = [];
            }
            acc[ach.category].push(ach);
            return acc;
        }, {});

        // --- RENDERING ---

        // Function to render achievements
        function renderAchievements() {
            const container = document.getElementById('achievements-container');
            container.innerHTML = ''; // Clear previous content

            // 1. Get the counts for the tabs
            const unlockedAchievements = achievementsData.filter(a => a.isUnlocked);
            const lockedAchievements = achievementsData.filter(a => !a.isUnlocked);
            
            document.getElementById('unlocked-count').textContent = unlockedAchievements.length;
            document.getElementById('locked-count').textContent = lockedAchievements.length;

            let hasContent = false;

            // 2. Render categories and filtered achievements
            for (const category in groupedAchievements) {
                const filteredList = groupedAchievements[category].filter(a => 
                    (currentFilter === 'unlocked' && a.isUnlocked) || 
                    (currentFilter === 'locked' && !a.isUnlocked)
                );

                if (filteredList.length > 0) {
                    hasContent = true;
                    const categoryDiv = document.createElement('div');
                    categoryDiv.className = 'space-y-4';
                    
                    categoryDiv.innerHTML = `
                        <h4 class="text-xl font-extrabold pb-1 border-b" style="color: var(--color-heading-secondary); border-color: var(--color-card-border);">${category}</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">
                            ${filteredList.map(achievement => `
                                <div class="p-4 rounded-xl shadow-lg flex flex-col items-center justify-start space-y-2 text-center transition-all cursor-pointer h-full
                                    ${achievement.isUnlocked ? 'unlocked-achievement' : 'locked-achievement'}"
                                    style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);"
                                    onclick="showAchievementModal('${achievement.id}')">
                                    
                                    <i class="${achievement.icon} text-4xl pt-2 relative" style="color: ${achievement.isUnlocked ? achievement.iconColor : 'var(--color-text-secondary)'};">
                                        ${!achievement.isUnlocked ? '<i class="fas fa-lock absolute text-sm" style="color: var(--color-text-secondary); bottom: 0; right: 0;"></i>' : ''}
                                    </i>
                                    
                                    <div class="space-y-0.5 mt-3 flex-1">
                                        <p class="text-sm font-bold" style="color: var(--color-text);">${achievement.title}</p>
                                        ${achievement.isUnlocked ? `<p class="text-xs text-center" style="color: var(--color-text-secondary);">${achievement.description}</p>` : `<p class="text-xs font-semibold" style="color: var(--color-text-secondary);">LOCKED</p>`}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    container.appendChild(categoryDiv);
                }
            }

            if (!hasContent) {
                container.innerHTML = `
                    <div class="text-center p-12 rounded-xl" style="background-color: var(--color-card-section-bg); color: var(--color-text-secondary);">
                        <i class="fas fa-box-open text-5xl mb-4"></i>
                        <p class="text-lg font-medium">No ${currentFilter} achievements to display in this collection yet.</p>
                        ${currentFilter === 'locked' ? '<p class="text-sm mt-2">Check back after earning more XP, or try the Unlocked tab!</p>' : ''}
                    </div>
                `;
            }
            
            // 3. Re-apply active tab styling
            document.querySelectorAll('.tab-button').forEach(btn => {
                const isActive = btn.dataset.filter === currentFilter;
                // Update font weight and color
                btn.style.borderColor = isActive ? 'var(--color-button-primary)' : 'transparent';
                btn.style.color = isActive ? 'var(--color-heading)' : 'var(--color-text-secondary)';
                btn.classList.toggle('font-bold', isActive);
                btn.classList.toggle('font-medium', !isActive);
            });
        }

        // Handle tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                // Ensure we get the data-filter from the button itself
                const newFilter = e.currentTarget.dataset.filter;
                if (currentFilter === newFilter) return;

                // Update filter state
                currentFilter = newFilter;

                // Rerender content based on new filter
                renderAchievements(); 
            });
        });

        // Show achievement modal
        function showAchievementModal(achievementId) {
            const achievement = achievementsData.find(a => a.id === achievementId);
            if (!achievement) return;

            const modal = document.getElementById('achievement-modal');
            const modalContent = document.getElementById('modal-content');
            const modalWrapper = document.getElementById('modal-content-wrapper');
            
            // Set modal border color based on unlock state
            const iconColor = achievement.isUnlocked ? achievement.iconColor : 'var(--color-text-secondary)';
            modalWrapper.style.borderColor = iconColor;

            modalContent.innerHTML = `
                <i class="${achievement.icon} text-8xl mb-4 transition-colors" style="color: ${iconColor};"></i>
                <h4 class="text-2xl font-extrabold" style="color: var(--color-heading);">${achievement.title}</h4>
                <p class="text-base leading-relaxed mb-6" style="color: var(--color-text-secondary);">${achievement.description}</p>
                
                ${achievement.isUnlocked ? `
                    <div class="flex justify-center space-x-8 font-bold p-4 rounded-xl shadow-inner" style="background-color: var(--color-card-section-bg);">
                        <div class="flex flex-col items-center space-y-1">
                            <span class="text-3xl color-xp-text">${achievement.gains.xp}</span>
                            <p class="text-sm" style="color: var(--color-text-secondary);">XP Gained</p>
                        </div>
                        <div class="flex flex-col items-center space-y-1">
                            <span class="text-3xl color-intel-text">${achievement.gains.intelligence}</span>
                            <p class="text-sm" style="color: var(--color-text-secondary);">Intel Gained</p>
                        </div>
                    </div>
                ` : `
                    <div class="p-4 rounded-xl font-bold" style="background-color: var(--color-card-section-bg); color: var(--color-text);">
                        <p class="text-lg">Achievement Locked</p>
                    </div>
                    <p class="mt-4 text-sm" style="color: var(--color-text-secondary);">Keep learning to earn this badge!</p>
                `}
            `;

            // Use GSAP for modal opening animation
            gsap.to(modal, { opacity: 1, duration: 0.3, display: 'flex' });
            gsap.fromTo(modalWrapper, { scale: 0.8, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.3, ease: "back.out(1.2)" });
        }

        // Close achievement modal
        document.getElementById('close-modal-btn').addEventListener('click', () => {
            const modal = document.getElementById('achievement-modal');
            const modalWrapper = document.getElementById('modal-content-wrapper');

            // Use GSAP for modal closing animation
            gsap.to(modalWrapper, { scale: 0.8, opacity: 0, duration: 0.2, ease: "power2.in" });
            gsap.to(modal, { opacity: 0, duration: 0.2, delay: 0.2, display: 'none' });
        });

        // Apply theme and render on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Apply theme (critical for dark mode variables to be set)
            applyThemeFromLocalStorage(); 
            // Render achievements (critical for content to show initially)
            renderAchievements(); 
            
            new MutationObserver(renderAchievements).observe(document.body, { attributes: true, attributeFilter: ['class'] });
        });

        
    </script>
</body>
</html>