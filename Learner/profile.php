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
        /* Custom scrollbar hide for consistency, if needed */
        .custom-scrollbar-hide::-webkit-scrollbar { 
            display: none; 
        }
        .custom-scrollbar-hide { 
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }

        /* Specific style for the Progress Bar (for a sleek look) */
        .progress-bar-track {
            height: 6px; /* Reduced height for sleeker look */
            border-radius: 9999px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-out;
        }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">
    
    <?php include 'sidebar.php'?>
    
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar-hide">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center z-10" 
                    style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)]  rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
            </button>
                <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">👤 User Profile</h1>
            </div>
        </header>

        <main class="flex-1 px-6 md:px-12 py-8">
            <div class="w-full max-w-5xl mx-auto space-y-8">
                
                <div class="rounded-2xl shadow-2xl" 
                     style="background-color: var(--color-card-bg); border: 2px solid var(--color-card-border);">
                    
                    <div class="h-48 rounded-t-2xl bg-gray-600 overflow-hidden" 
                         style="background-image: url(../images/mochay.jpg); background-size: cover; background-position: center; border-bottom: 1px solid var(--color-card-border);">
                    </div>

                    <div class="px-8 pb-8">
                        <div class="flex flex-col md:flex-row md:items-end -mt-16 md:-mt-10">
                            <div class="w-32 h-32 rounded-full overflow-hidden shadow-xl flex-shrink-0 bg-white" 
                                 style="border: 6px solid var(--color-card-bg);">
                                <img src="../images/yuta.jpg" alt="User Avatar" class="w-full h-full object-cover">
                            </div>

                            <div class="pt-4 md:pt-0 md:pl-6 flex-1 text-left md:flex md:justify-between md:items-end">
                                <div class="space-y-1">
                                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Juan Dela Cruz</h2>
                                    <p class="text-md font-medium" style="color: var(--color-text-secondary);">juanDelaCruz@gmail.com</p>
                                </div>
                                <div class="mt-4 md:mt-0 space-x-4">
                                    <button class="px-5 py-2 rounded-full text-sm font-semibold transition hover:opacity-90 shadow-md"
                                            style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">
                                        <i class="fas fa-edit mr-2"></i> Edit Profile
                                    </button>
                                    <button class="px-5 py-2 rounded-full text-sm font-semibold transition hover:opacity-90 shadow-md"
                                            style="background-color: var(--color-progress-bg); color: var(--color-text-secondary);">
                                        <i class="fas fa-cog mr-2"></i> Settings
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4">
                            <h3 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Learning Progress</h3>
                            <div class="flex flex-col md:flex-row md:space-x-8 space-y-4 md:space-y-0">
                                
                                <div class="flex-1 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium" style="color: var(--color-text);">Experience Points</p>
                                        <p class="text-sm font-bold" style="color: var(--color-text-secondary);"><span id="xp-text">650</span> / 1000 XP</p>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" id="xp-progress" style="background: var(--color-progress-fill); width: 65%;"></div>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium" style="color: var(--color-text);">Intelligence Level</p>
                                        <p class="text-sm font-bold" style="color: var(--color-text-secondary);"><span id="intel-text">Advanced</span></p>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" id="intel-progress" style="background: linear-gradient(to right, var(--color-heading), var(--color-heading-secondary)); width: 85%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 space-y-8">
                        
                        <div class="p-8 rounded-2xl shadow-lg space-y-4" 
                              style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">📝 About Juan</h3>
                            <p class="text-base leading-relaxed font-light" style="color: var(--color-text);">
                                I'm a passionate learner on a mission to master new skills and collect all the knowledge I can. My journey with FixLearn has been amazing so far, and I'm excited to see what's next! Currently focusing on **advanced JavaScript**.
                            </p>
                            <div class="flex items-center space-x-2 text-sm pt-2" style="color: var(--color-text-secondary);">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>Tokyo, Japan</p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm" style="color: var(--color-text-secondary);">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Joined October 2024</p>
                            </div>
                        </div>

                        <div class="p-8 rounded-2xl shadow-lg space-y-4"
                             style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">🔥 Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center border-b pb-2" style="border-color: var(--color-card-border);">
                                    <p class="font-medium" style="color: var(--color-text);">Day Streak</p>
                                    <p class="text-xl font-extrabold text-orange-500">45</p>
                                </div>
                                <div class="flex justify-between items-center border-b pb-2" style="border-color: var(--color-card-border);">
                                    <p class="font-medium" style="color: var(--color-text);">Courses Completed</p>
                                    <p class="text-xl font-extrabold" style="color: var(--color-button-primary);">12</p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <p class="font-medium" style="color: var(--color-text);">Average Score</p>
                                    <p class="text-xl font-extrabold text-green-500">92%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="p-8 rounded-2xl shadow-lg space-y-6"
                             style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">🎖️ Achievements Unlocked (4)</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                                
                                <div class="p-4 rounded-xl shadow-md flex flex-col items-center justify-center space-y-2 text-center transition hover:scale-105"
                                      style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);">
                                    <i class="fas fa-medal text-4xl text-yellow-500"></i>
                                    <p class="text-sm font-bold mt-2" style="color: var(--color-text);">First Course</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">Completed 1st course</p>
                                </div>
                                
                                <div class="p-4 rounded-xl shadow-md flex flex-col items-center justify-center space-y-2 text-center transition hover:scale-105"
                                      style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);">
                                    <i class="fas fa-star text-4xl text-green-500"></i>
                                    <p class="text-sm font-bold mt-2" style="color: var(--color-text);">Top Scorer</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">95%+ on any quiz</p>
                                </div>
                                
                                <div class="p-4 rounded-xl shadow-md flex flex-col items-center justify-center space-y-2 text-center transition hover:scale-105"
                                      style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);">
                                    <i class="fas fa-trophy text-4xl text-blue-500"></i>
                                    <p class="text-sm font-bold mt-2" style="color: var(--color-text);">Web Dev Pro</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">Completed Web Dev Track</p>
                                </div>
                                
                                <div class="p-4 rounded-xl shadow-md flex flex-col items-center justify-center space-y-2 text-center transition hover:scale-105"
                                      style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);">
                                    <i class="fas fa-bolt text-4xl text-orange-500"></i>
                                    <p class="text-sm font-bold mt-2" style="color: var(--color-text);">Speed Demon</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">10-Day Streak achieved</p>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="p-8 rounded-2xl shadow-lg space-y-6"
                             style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">💡 Recent Activity</h3>
                            
                            <div class="flex space-x-4 p-4 rounded-lg" style="background-color: var(--color-card-section-bg);">
                                <i class="fas fa-chart-line text-2xl pt-1 text-green-500"></i>
                                <div>
                                    <p class="font-semibold" style="color: var(--color-text);">Scored <span class="text-green-500">98%</span> on the Advanced JS Functions Quiz.</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">5 hours ago</p>
                                </div>
                            </div>

                            <div class="flex space-x-4 p-4 rounded-lg" style="background-color: var(--color-card-section-bg);">
                                <i class="fas fa-lock-open text-2xl pt-1 text-purple-500"></i>
                                <div>
                                    <p class="font-semibold" style="color: var(--color-text);">Unlocked a new level: <span class="text-purple-500">Expert Learner</span>.</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">1 day ago</p>
                                </div>
                            </div>
                            
                            <div class="flex space-x-4 p-4 rounded-lg" style="background-color: var(--color-card-section-bg);">
                                <i class="fas fa-plus text-2xl pt-1" style="color: var(--color-button-primary);"></i>
                                <div>
                                    <p class="font-semibold" style="color: var(--color-text);">Started the course: <span style="color: var(--color-button-primary);">SQL Database Fundamentals</span>.</p>
                                    <p class="text-xs" style="color: var(--color-text-secondary);">3 days ago</p>
                                </div>
                            </div>

                            <button class="w-full text-center py-2 rounded-lg font-medium transition hover:opacity-80"
                                    style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-border); color: var(--color-text-secondary);">
                                View All Activity
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Function to apply the theme from local storage
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

        // Sample data for profile
        const profileData = {
            xp: 650,
            xpGoal: 1000,
            intelligence: 85,
        };

        // Function to render the profile data
        function renderProfile() {
            // Update XP progress
            const xpProgress = document.getElementById('xp-progress');
            const xpText = document.getElementById('xp-text');
            const xpPercentage = (profileData.xp / profileData.xpGoal) * 100;
            xpProgress.style.width = `${xpPercentage}%`;
            xpText.textContent = profileData.xp;

            // Update Intelligence progress
            const intelProgress = document.getElementById('intel-progress');
            const intelText = document.getElementById('intel-text');
            const intelPercentage = profileData.intelligence;
            intelProgress.style.width = `${intelPercentage}%`;
            
            if (intelPercentage > 90) {
                intelText.textContent = "Master";
            } else if (intelPercentage > 70) {
                intelText.textContent = "Advanced";
            } else if (intelPercentage > 40) {
                intelText.textContent = "Intermediate";
            } else {
                intelText.textContent = "Beginner";
            }
        }

        // Initial render on page load
        document.addEventListener('DOMContentLoaded', renderProfile);

    </script>
</body>
</html>