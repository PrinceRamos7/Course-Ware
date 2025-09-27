<?php

    if(isset($_POST['submit'])){
       if("submit"){
        ?>
            <script>
                window.location.href="dashboard.php";
            </script>
        <?php
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FixLearn</title>
    <link rel="stylesheet" href="output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
     
        .login-card {
            background-color: var(--color-card-bg);
            color: var(--color-text);
            transition: background-color 0.5s ease, color 0.5s ease;
        }

        .input-field {
            background-color: var(--color-input-bg);
            border: 1px solid var(--color-input-border);
            color: var(--color-text);
            transition: background-color 0.5s ease, border-color 0.5s ease, color 0.5s ease;
        }
        .input-field::placeholder {
            color: var(--color-text-secondary);
        }

        .submit-button {
            background-color: var(--color-button-primary);
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: var(--color-button-primary-hover);
        }

        .text-gray-800, .text-gray-900 {
            color: var(--color-text);
        }

        .text-gray-600, .text-gray-500 {
            color: var(--color-text-secondary);
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="light-mode min-h-screen flex items-center justify-center">

    <!-- Login Card -->
    <div class="login-card p-10 rounded-2xl shadow-2xl w-96 relative">
        <!-- Theme Toggle Button -->
        <button id="theme-toggle" class="absolute top-4 right-4 p-2 rounded-full transition-all duration-300 transform hover:scale-110" 
                style="background: var(--color-toggle-bg); color: var(--color-toggle-text);">
            <i class="fas fa-moon text-lg dark-icon"></i>
            <i class="fas fa-sun text-lg light-icon hidden"></i>
        </button>

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full shadow-md"
                 style="background-color: var(--color-heading); color: white;">
                <i class="fas fa-graduation-cap text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold" style="color: var(--color-heading);">FixLearn</h1>
            <p class="text-sm" style="color: var(--color-text-secondary);">Welcome back! Please sign in</p>
        </div>

        <!-- Form -->
        <form method="POST"  class="space-y-6" action="login.php">
            
            <!-- Username -->
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2" style="color: var(--color-text-secondary);"></i>
                <input type="text" id="username" name="username" required
                    class="input-field w-full pl-10 pr-4 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                    placeholder="Username"/>
            </div>

            <!-- Password -->
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2" style="color: var(--color-text-secondary);"></i>
                <input type="password" id="password" name="password" required
                    class="input-field w-full pl-10 pr-4 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                    placeholder="Password"/>
            </div>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2" style="color: var(--color-text);">
                    <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>Remember me</span>
                </label>
                <a href="#" class="text-blue-500 hover:text-blue-700" style="color: var(--color-button-primary);">Forgot password?</a>
            </div>

            <!-- Button -->
            <button type="submit" name="submit"
                class="submit-button w-full text-white font-bold py-3 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                Sign In
            </button>
        </form>

        <!-- Register -->
        <p class="text-center text-sm mt-6" style="color: var(--color-text);">
            Don't have an account? 
            <a href="register.php" class="text-blue-500 hover:text-blue-700 font-medium" style="color: var(--color-button-primary);">Register here</a>
        </p>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const body = document.body;
        const themeToggle = document.getElementById("theme-toggle");
        const sunIcon = document.querySelector(".light-icon"); // sun
        const moonIcon = document.querySelector(".dark-icon"); // moon

        // Function to apply theme
        function applyTheme(theme) {
            if (theme === "dark") {
                body.classList.add("dark-mode");
                moonIcon.classList.add("hidden");   // hide moon
                sunIcon.classList.remove("hidden"); // show sun
            } else {
                body.classList.remove("dark-mode");
                sunIcon.classList.add("hidden");    // hide sun
                moonIcon.classList.remove("hidden");// show moon
            }
        }

        // Load saved theme
        const savedTheme = localStorage.getItem("theme") || "light";
        applyTheme(savedTheme);

        // Toggle on click
        themeToggle.addEventListener("click", () => {
            const isDark = body.classList.contains("dark-mode");
            const newTheme = isDark ? "light" : "dark";
            applyTheme(newTheme);
            localStorage.setItem("theme", newTheme);
        });
    });
</script>

</body>
</html>
