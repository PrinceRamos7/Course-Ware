<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
require_once '../pdoconfig.php';

// Fetch achievements and check which ones the user has unlocked
$achievements_query = "
    SELECT 
        a.*,
        CASE WHEN sa.student_id IS NOT NULL THEN 1 ELSE 0 END as is_unlocked,
        sa.unlocked_at
    FROM achievements a
    LEFT JOIN student_achievements sa ON a.id = sa.achievement_id AND sa.student_id = ?
    ORDER BY a.category, a.id
";

$stmt = $pdo->prepare($achievements_query);
$stmt->execute([$student_id]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert database results to JavaScript format
$achievements_data = [];
foreach ($achievements as $ach) {
    $achievements_data[] = [
        'id' => $ach['id'],
        'title' => $ach['title'],
        'category' => $ach['category'],
        'description' => $ach['description'],
        'icon' => $ach['icon'],
        'iconColor' => $ach['icon_color'],
        'isUnlocked' => (bool)$ach['is_unlocked'],
        'gains' => [
            'xp' => (int)$ach['xp_reward'],
            'intelligence' => (int)$ach['intelligence_reward']
        ]
    ];
}

$achievements_json = json_encode($achievements_data);
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
        .custom-scrollbar-hide::-webkit-scrollbar { 
            display: none; 
        }
        .custom-scrollbar-hide { 
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .locked-achievement {
            opacity: 0.4;
            filter: grayscale(100%);
            cursor: pointer;
            transition: opacity 0.3s, filter 0.3s;
        }

        .unlocked-achievement {
            cursor: pointer;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
            box-shadow: 0 0 0 rgba(0, 0, 0, 0); 
        }
        .unlocked-achievement:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), 0 0 10px rgba(79, 70, 229, 0.3); 
        }
        
        #achievement-modal {
            background-color: rgba(0, 0, 0, 0.7); 
            z-index: 50;
        }

        .color-xp-text { color: var(--color-yellow-500, #f59e0b); }
        .color-intel-text { color: var(--color-blue-500, #0ea5e9); }
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
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }

        // --- DATA ---
        // Use PHP-generated data instead of hardcoded data
        const achievementsData = <?php echo $achievements_json; ?>;

        // --- STATE & UTILITIES ---
        let currentFilter = 'unlocked';

        // Group achievements by category
        const groupedAchievements = achievementsData.reduce((acc, ach) => {
            if (!acc[ach.category]) {
                acc[ach.category] = [];
            }
            acc[ach.category].push(ach);
            return acc;
        }, {});

        // --- RENDERING ---

        function renderAchievements() {
            const container = document.getElementById('achievements-container');
            container.innerHTML = '';

            const unlockedAchievements = achievementsData.filter(a => a.isUnlocked);
            const lockedAchievements = achievementsData.filter(a => !a.isUnlocked);
            
            document.getElementById('unlocked-count').textContent = unlockedAchievements.length;
            document.getElementById('locked-count').textContent = lockedAchievements.length;

            let hasContent = false;

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
                                    onclick="showAchievementModal(${achievement.id})">
                                    
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
            
            document.querySelectorAll('.tab-button').forEach(btn => {
                const isActive = btn.dataset.filter === currentFilter;
                btn.style.borderColor = isActive ? 'var(--color-button-primary)' : 'transparent';
                btn.style.color = isActive ? 'var(--color-heading)' : 'var(--color-text-secondary)';
                btn.classList.toggle('font-bold', isActive);
                btn.classList.toggle('font-medium', !isActive);
            });
        }

        // Handle tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const newFilter = e.currentTarget.dataset.filter;
                if (currentFilter === newFilter) return;
                currentFilter = newFilter;
                renderAchievements(); 
            });
        });

        // Show achievement modal
        function showAchievementModal(achievementId) {
            const achievement = achievementsData.find(a => a.id == achievementId);
            if (!achievement) return;

            const modal = document.getElementById('achievement-modal');
            const modalContent = document.getElementById('modal-content');
            const modalWrapper = document.getElementById('modal-content-wrapper');
            
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

            gsap.to(modal, { opacity: 1, duration: 0.3, display: 'flex' });
            gsap.fromTo(modalWrapper, { scale: 0.8, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.3, ease: "back.out(1.2)" });
        }

        // Close achievement modal
        document.getElementById('close-modal-btn').addEventListener('click', () => {
            const modal = document.getElementById('achievement-modal');
            const modalWrapper = document.getElementById('modal-content-wrapper');

            gsap.to(modalWrapper, { scale: 0.8, opacity: 0, duration: 0.2, ease: "power2.in" });
            gsap.to(modal, { opacity: 0, duration: 0.2, delay: 0.2, display: 'none' });
        });

        // Apply theme and render on page load
        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage(); 
            renderAchievements(); 
            
            new MutationObserver(renderAchievements).observe(document.body, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
</body>
</html>