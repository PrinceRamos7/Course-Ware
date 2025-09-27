<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ISUtoLearn</title>
  <link rel="stylesheet" href="../output.css" />
  <link rel="icon" type="image/png" href="../images/isu-logo.png">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>

  <style>
    body{
      padding: 0%;
    }
  </style>
</head>
<body class="bg-[var(--color-main-bg)] text-[var(--color-text)] font-sans min-h-screen flex flex-col transition-colors duration-500">

  <!-- Header -->
  <header class="w-full flex justify-between items-center px-6 py-4 bg-[var(--color-header-bg)] backdrop-blur-md border-b border-[var(--color-card-border)]">
    <div class="flex items-center gap-2">
      <img src="../images/isu-logo.png" alt="" class="h-10 w-10 object-contain">
      <h1 class="text-2xl font-extrabold tracking-wider text-[var(--color-heading)]">
      ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
    </h1>
    </div>

    
    <nav class="flex items-center gap-4">
      <!-- Dark Mode Toggle -->
      <button id="darkModeToggle" class="w-10 h-10 flex items-center justify-center rounded-full bg-[var(--color-toggle-bg)] text-[var(--color-toggle-handle)] hover:scale-110 transition">
        <i id="darkModeIcon" class="fa-solid fa-moon"></i>
      </button>
      
      <a href="register.php" class="px-4 py-2 rounded-xl bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-[var(--color-sidebar-link-hover)] transition">Sign Up</a>
      <a href="login.php" class="px-4 py-2 rounded-xl bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition">Login</a>
    </nav>
  </header>

  <!-- Hero Section -->
  <main class="flex-1 flex flex-col items-center justify-center text-center px-6">
    <h2 class="text-4xl md:text-6xl font-extrabold mb-6 text-[var(--color-heading)] animate-pulse">
      Level Up Your Learning 🚀
    </h2>
    <p class="max-w-2xl text-lg md:text-xl mb-8 text-[var(--color-text-secondary)]">
      ISUtoLearn turns studying into an adventure. Earn XP, unlock achievements, and progress through adaptive courses at your own pace.
    </p>

    <!-- CTA Buttons -->
    <div class="flex gap-6">
      <a href="#signin" class="px-6 py-3 rounded-2xl bg-[var(--color-green-button)] text-white font-bold shadow-lg hover:bg-[var(--color-green-button-hover)] transition transform hover:scale-105">
        <i class="fa-solid fa-user-plus mr-2"></i> Start Adventure
      </a>
      <a href="#login" class="px-6 py-3 rounded-2xl bg-[var(--color-button-primary)] text-white font-bold shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-105">
        <i class="fa-solid fa-right-to-bracket mr-2"></i> Continue Journey
      </a>
    </div>

    <!-- Gamified Stats -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl">
      <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-md hover:scale-105 transition">
        <i class="fa-solid fa-star text-[var(--color-icon)] text-3xl mb-2"></i>
        <h3 class="font-bold text-xl">Earn XP</h3>
        <p class="text-[var(--color-text-secondary)] mt-2">Complete lessons to gain points and track your growth.</p>
      </div>
      <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-md hover:scale-105 transition">
        <i class="fa-solid fa-trophy text-[var(--color-heading-secondary)] text-3xl mb-2"></i>
        <h3 class="font-bold text-xl">Unlock Achievements</h3>
        <p class="text-[var(--color-text-secondary)] mt-2">Collect badges as you master new skills.</p>
      </div>
      <div class="p-6 rounded-2xl bg-[var(--color-card-bg)] border border-[var(--color-card-border)] shadow-md hover:scale-105 transition">
        <i class="fa-solid fa-gamepad text-[var(--color-heading)] text-3xl mb-2"></i>
        <h3 class="font-bold text-xl">Gamified Learning</h3>
        <p class="text-[var(--color-text-secondary)] mt-2">Enjoy adaptive challenges designed just for you.</p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="py-6 text-center text-sm text-[var(--color-text-secondary)] border-t border-[var(--color-card-border)]">
    © 2025 ISUtoLearn. Level up your knowledge.
  </footer>

  <!-- Dark Mode Script -->
  <script>
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleIcon = document.getElementById('darkModeIcon');

    // Load preference
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      toggleIcon.classList.replace('fa-moon', 'fa-sun');
    }

    toggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        toggleIcon.classList.replace('fa-moon', 'fa-sun');
      } else {
        localStorage.setItem('theme', 'light');
        toggleIcon.classList.replace('fa-sun', 'fa-moon');
      }
    });
  </script>

</body>
</html>
