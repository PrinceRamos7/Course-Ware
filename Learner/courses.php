<?php
require __DIR__ . '/../config.php'; // Adjust path to your config.php

// Fetch all courses from the database
$stmt = $conn->prepare("SELECT id, title, description FROM courses");
$stmt->execute();
$coursesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        /* Custom Colors based on variables for dynamic feedback */
        .color-success-text { color: var(--color-green-500, #10b981); }
        .color-error-text { color: var(--color-red-500, #ef4444); }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>
    
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar-hide">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center z-10" 
            style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">📚 Courses</h1>
            </div>
        </header>

        <main class="flex-1 px-6 md:px-12 py-8 flex flex-col items-center justify-start">
            
            <div id="code-input-section" class="p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-xl mx-auto text-center space-y-6 transition-all" 
                 style="background-color: var(--color-card-bg); border: 2px solid var(--color-heading-secondary);">
                <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">🔒 Secure Access Required</h2>
                <p class="text-base leading-relaxed" style="color: var(--color-text-secondary);">
                    Please enter the code provided by your administrator to unlock the full course catalog.
                </p>
                <div class="space-y-4">
                    <input id="admin-code-input" type="password" placeholder="Enter code here..."
                           class="w-full px-4 py-3 rounded-xl text-center transition-all focus:outline-none focus:ring-2 focus:ring-offset-2"
                           style="background-color: var(--color-card-section-bg); color: var(--color-text); border: 1px solid var(--color-card-border); focus-ring-color: var(--color-button-primary);">
                    <p id="message" class="font-bold hidden text-sm"></p>
                    <button id="submit-code-btn" class="w-full px-6 py-3 rounded-xl font-bold transition-all hover:opacity-90 flex items-center justify-center"
                            style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-primary);">
                        Unlock Courses <i class="fas fa-key ml-2"></i>
                    </button>
                </div>
            </div>

            <div id="courses-section" class="p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto space-y-8 hidden opacity-0 transition-opacity"
                 style="background-color: var(--color-card-bg); border: 2px solid var(--color-card-border);">
                
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Available Courses</h2>
                    <button id="revoke-access-btn" class="px-4 py-2 text-sm font-semibold rounded-full transition-all hover:opacity-80"
                            style="background-color: var(--color-red-500, #ef4444); color: var(--color-button-secondary-text);">
                        <i class="fas fa-undo-alt mr-2"></i> Revoke Access
                    </button>
                </div>
                
                   <div id="courses-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                    <?php if(!empty($coursesData)): ?>
                        <?php foreach($coursesData as $course): ?>
                            <div class="p-5 rounded-xl space-y-3 shadow-lg transition-all hover:shadow-xl hover:scale-[1.02] cursor-pointer h-full flex flex-col justify-between"
                                 style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-border);">
                                
                                <div class="space-y-3">
                                    <i class="fas fa-book text-3xl" style="color: var(--color-indigo-500);"></i>
                                    <h4 class="text-xl font-bold" style="color: var(--color-heading);"><?= htmlspecialchars($course['title']) ?></h4>
                                    <p class="text-sm line-clamp-3" style="color: var(--color-text-secondary);"><?= htmlspecialchars($course['description']) ?></p>
                                </div>

                                <div class="mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                                    <a href="modules.php?course_id=<?= $course['id'] ?>" class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold transition-all"
                                       style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">
                                        Start Course <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--color-text);">No courses available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        </main>
    </div>

    <script>
        const ACCESS_KEY = 'courseAccessGranted';
        const CORRECT_ADMIN_CODE = "RYUTAPOGIHEHE"; // The required admin code

        // --- DOM Elements ---
        const adminCodeInput = document.getElementById('admin-code-input');
        const submitCodeBtn = document.getElementById('submit-code-btn');
        const messageDisplay = document.getElementById('message');
        const codeInputSection = document.getElementById('code-input-section');
        const coursesSection = document.getElementById('courses-section');
        const coursesList = document.getElementById('courses-list');
        const revokeAccessBtn = document.getElementById('revoke-access-btn'); // New element

  

        // --- Functions ---

        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }

        function renderCourses() {
            coursesList.innerHTML = coursesData.map(course => {
                const isCompleted = course.progress >= 100;
                const isStarted = course.progress > 0 && !isCompleted;

                let statusText = 'Start Course';
                let statusIcon = 'fas fa-arrow-right';
                let statusClass = 'bg-opacity-10';

                if (isCompleted) {
                    statusText = 'Completed';
                    statusIcon = 'fas fa-check-circle';
                    statusClass = 'bg-opacity-15'; 
                } else if (isStarted) {
                    statusText = 'Continue';
                    statusIcon = 'fas fa-play';
                }

                return `
                    <div class="p-5 rounded-xl space-y-3 shadow-lg transition-all hover:shadow-xl hover:scale-[1.02] cursor-pointer h-full flex flex-col justify-between"
                         style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-border);">
                        
                        <div class="space-y-3">
                            <i class="${course.icon} text-3xl" style="color: ${course.color};"></i>
                            <h4 class="text-xl font-bold" style="color: var(--color-heading);">${course.title}</h4>
                            <p class="text-sm line-clamp-3" style="color: var(--color-text-secondary);">${course.description}</p>
                        </div>

                        <div class="mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between items-center text-xs mb-1 font-semibold">
                                <span style="color: var(--color-text-secondary);">Progress</span>
                                <span style="color: var(--color-heading);">${course.progress}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full mb-3" style="background-color: var(--color-progress-bg);">
                                <div class="h-full rounded-full" 
                                     style="width: ${course.progress}%; background-color: ${course.color}; transition: width 0.5s;">
                                </div>
                            </div>

                            <a href="#" class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold transition-all ${statusClass}"
                               style="background-color: ${course.color}; color: var(--color-button-secondary-text);">
                                ${statusText} <i class="${statusIcon} ml-2"></i>
                            </a>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function grantAccessAndDisplayCourses() {
            localStorage.setItem(ACCESS_KEY, 'true'); 
            messageDisplay.classList.add('hidden');
            renderCourses();

            // GSAP animation for smooth transition to courses
            gsap.to(codeInputSection, { 
                opacity: 0, 
                duration: 0.3, 
                onComplete: () => {
                    codeInputSection.classList.add('hidden');
                    coursesSection.classList.remove('hidden');
                    gsap.fromTo(coursesSection, 
                        { opacity: 0, y: 20 }, 
                        { opacity: 1, y: 0, duration: 0.5, ease: "power2.out" }
                    );
                }
            });
        }

        function revokeAccessAndDisplayInput() {
            localStorage.removeItem(ACCESS_KEY); // Clear the persistence key

            // GSAP animation for smooth transition back to input
            gsap.to(coursesSection, {
                opacity: 0,
                duration: 0.3,
                onComplete: () => {
                    coursesSection.classList.add('hidden');
                    codeInputSection.classList.remove('hidden');
                    
                    // Reset input and message for the next time
                    adminCodeInput.value = '';
                    messageDisplay.classList.add('hidden');
                    
                    gsap.fromTo(codeInputSection,
                        { opacity: 0, y: -20 },
                        { opacity: 1, y: 0, duration: 0.5, ease: "power2.out" }
                    );
                }
            });
        }


        // --- Event Listeners ---

        submitCodeBtn.addEventListener('click', () => {
            messageDisplay.classList.remove('hidden');
            const enteredCode = adminCodeInput.value.trim().toUpperCase();

            if (enteredCode === CORRECT_ADMIN_CODE) {
                messageDisplay.textContent = "Success! Access granted.";
                messageDisplay.classList.add('color-success-text');
                messageDisplay.classList.remove('color-error-text');
                
                setTimeout(grantAccessAndDisplayCourses, 500);

            } else {
                messageDisplay.textContent = "Invalid code. Please try again.";
                messageDisplay.classList.add('color-error-text');
                messageDisplay.classList.remove('color-success-text');
                
                gsap.from(adminCodeInput, { x: 0, duration: 0.1, repeat: 5, yoyo: true, x: -5 });
            }
        });

        // Add listener for the new Revoke Access button
        revokeAccessBtn.addEventListener('click', revokeAccessAndDisplayInput);


        // --- Initialization on Load ---

        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage();

            if (localStorage.getItem(ACCESS_KEY) === 'true') {
                codeInputSection.classList.add('hidden');
                coursesSection.classList.remove('hidden');
                coursesSection.style.opacity = '1'; // Show immediately
                renderCourses();
            }
        });

    </script>
</body>
</html>