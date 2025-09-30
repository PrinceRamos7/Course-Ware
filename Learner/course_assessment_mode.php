<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISUtoLearn</title>
  <link rel="stylesheet" href="../output.css">
  <link rel="icon" type="images/png" href="../images/isu-logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="min-h-screen flex bg-[var(--color-main-bg)] p-0">
  <?php include "sidebar.php";?>

  <div class="flex flex-1 flex-col transition-all duration-300 ml-16">
    <header class="backdrop-blur-sm shadow-md px-6 py-4 flex justify-between items-center border-b"
      style="background-color: var(--color-header-bg); border-color: var(--color-card-border);">
      <div>
        <h1 class="text-2xl font-bold" style="color: var(--color-heading);">
          Welcome back, Ryuta
        </h1>
        <h6 class="text-sm font-medium" style="color: var(--color-text-secondary);">
          Continue your learning journey 🚀
        </h6>
      </div>
      <a href="profile.php"
        class="flex items-center space-x-3 px-4 py-2 rounded-full transition border shadow-sm hover:shadow-md"
        style="background-color: var(--color-user-bg); border-color: var(--color-icon);">
        <i class="fas fa-user-circle text-2xl" style="color: var(--color-heading);"></i>
        <span class="hidden sm:inline font-semibold" style="color: var(--color-user-text);">Ryuta</span>
        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold"
          style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">
          LV 12
        </span>
      </a>
    </header>

    <main class="flex-1 p-8 flex flex-col gap-8">
      <h1 class="text-3xl font-bold text-center mb-4" style="color: var(--color-heading);">
        Choose Your Learning Mode
      </h1>
      <p class="text-center text-sm mb-8" style="color: var(--color-text-secondary);">
        Select how you’d like to continue your journey 🚀
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
        <div class="bg-[var(--color-card-bg)] rounded-xl shadow-lg border p-6 flex flex-col justify-between hover:shadow-xl transition duration-300"
          style="border-color: var(--color-card-border);">
          <div>
            <h2 class="text-xl font-bold mb-3 flex items-center gap-2" style="color: var(--color-heading);">
              📝 Training Mode
            </h2>
            <p class="text-sm leading-relaxed mb-4" style="color: var(--color-text-secondary);">
              Practice with full guidance. When you select an answer, you’ll get immediate feedback.
              Wrong answers will show the correct choice with explanations for every option
            </p>
          </div>
          <button class="mt-4 px-5 py-2 rounded-lg font-semibold transition text-white"
            style="background-color: var(--color-button-primary);"
            onmouseover="this.style.backgroundColor='var(--color-button-primary-hover)'"
            onmouseout="this.style.backgroundColor='var(--color-button-primary)'">
            Start Training
          </button>
        </div>

        <div class="bg-[var(--color-card-bg)] rounded-xl shadow-lg border p-6 flex flex-col justify-between hover:shadow-xl transition duration-300"
          style="border-color: var(--color-card-border);">
          <div>
            <h2 class="text-xl font-bold mb-3 flex items-center gap-2" style="color: var(--color-heading-secondary);">
              🎯 Testing Mode
            </h2>
            <p class="text-sm leading-relaxed mb-4" style="color: var(--color-text-secondary);">
              Simulate the actual test environment. No hints, no explanations, and strict scoring.
              Perfect for checking if you’re ready for the real exam under timed conditions.
            </p>
          </div>
          <button class="mt-4 px-5 py-2 rounded-lg font-semibold transition text-white"
            style="background-color: var(--color-heading-secondary);"
            onmouseover="this.style.backgroundColor='#ea580c'"
            onmouseout="this.style.backgroundColor='var(--color-heading-secondary)'">
            Start Test
          </button>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
