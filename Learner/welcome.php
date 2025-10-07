<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="stylesheet" href="../images/isu-logo.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
  </style>
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
</head>
<body class="bg-[var(--color-main-bg)] text-[var(--color-text)] font-sans min-h-screen flex flex-col transition-colors duration-500">

    <header class="w-full flex justify-between items-center px-4 sm:px-6 py-4 bg-[var(--color-header-bg)] backdrop-blur-md border-b border-[var(--color-card-border)]">
        <div class="flex items-center gap-2">
            <img src="../images/isu-logo.png" alt="ISU Logo Placeholder" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded-full">
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-wider text-[var(--color-heading)]">
                ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
            </h1>
        </div>
        
        <nav class="flex items-center gap-2 sm:gap-4">
            <button id="darkModeToggle" class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[var(--color-toggle-bg)] text-[var(--color-toggle-handle)] hover:scale-110 transition">
                <i id="darkModeIcon" class="fa-solid fa-moon"></i>
            </button>
            
            <a href="#" class="text-sm px-3 py-1 sm:px-4 sm:py-2 rounded-xl bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-[var(--color-sidebar-link-hover)] transition">Sign Up</a>
            <a href="#" class="text-sm px-3 py-1 sm:px-4 sm:py-2 rounded-xl bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition">Login</a>
        </nav>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center text-center px-4 sm:px-6 py-12">
        <h2 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-6 text-[var(--color-heading)] animate-pulse">
            Level Up Your Learning 🚀
        </h2>
        <p class="max-w-2xl text-base sm:text-lg md:text-xl mb-8 text-[var(--color-text-secondary)]">
            ISUtoLearn turns studying into an adventure. Earn XP, unlock achievements, and progress through adaptive courses at your own pace.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 w-full max-w-sm sm:max-w-none justify-center">
            <a href="#" class="px-6 py-3 rounded-2xl bg-[var(--color-green-button)] text-white font-bold shadow-lg hover:bg-[var(--color-green-button-hover)] transition transform hover:scale-105">
                <i class="fa-solid fa-user-plus mr-2"></i> Start Adventure
            </a>
            <a href="#" class="px-6 py-3 rounded-2xl bg-[var(--color-button-primary)] text-white font-bold shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-105">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Continue Journey
            </a>
        </div>

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full max-w-6xl px-4">
            <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-xl hover:scale-[1.02] transition">
                <i class="fa-solid fa-star text-[var(--color-icon)] text-3xl mb-2"></i>
                <h3 class="font-bold text-xl text-[var(--color-heading)]">Earn XP</h3>
                <p class="text-[var(--color-text-secondary)] mt-2">Complete lessons to gain points and track your growth.</p>
            </div>
            <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-xl hover:scale-[1.02] transition">
                <i class="fa-solid fa-trophy text-[var(--color-heading-secondary)] text-3xl mb-2"></i>
                <h3 class="font-bold text-xl text-[var(--color-heading)]">Unlock Achievements</h3>
                <p class="text-[var(--color-text-secondary)] mt-2">Collect badges as you master new skills.</p>
            </div>
            <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-xl hover:scale-[1.02] transition">
                <i class="fa-solid fa-gamepad text-[var(--color-heading)] text-3xl mb-2"></i>
                <h3 class="font-bold text-xl text-[var(--color-heading)]">Gamified Learning</h3>
                <p class="text-[var(--color-text-secondary)] mt-2">Enjoy adaptive challenges designed just for you.</p>
            </div>
        </div>
    </main>

    <footer class="py-6 text-center text-sm text-[var(--color-text-secondary)] border-t border-[var(--color-card-border)]">
        © 2025 ISUtoLearn. Level up your knowledge.
    </footer>

    <script>
        const body = document.body;
        const toggleBtn = document.getElementById('darkModeToggle');
        const toggleIcon = document.getElementById('darkModeIcon');

        const logoImage = document.querySelector('img[alt="ISU Logo Placeholder"]');

        const lightModeLogo = "https://placehold.co/40x40/007bff/ffffff?text=ISU";
        const darkModeLogo = "https://placehold.co/40x40/3b82f6/1f2937?text=ISU";

        const setLogo = (isDark) => {
            logoImage.src = isDark ? darkModeLogo : lightModeLogo;
        };

        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            toggleIcon.classList.replace('fa-moon', 'fa-sun');
            setLogo(true);
        } else {
             setLogo(false);
        }

        toggleBtn.addEventListener('click', () => {
            const isCurrentlyDark = body.classList.contains('dark-mode');
            
            body.classList.toggle('dark-mode');
            if (isCurrentlyDark) {
                localStorage.setItem('theme', 'light');
                toggleIcon.classList.replace('fa-sun', 'fa-moon');
                setLogo(false);
            } else {
                localStorage.setItem('theme', 'dark');
                toggleIcon.classList.replace('fa-moon', 'fa-sun');
                setLogo(true);
            }
        });
    </script>

</body>
</html>
