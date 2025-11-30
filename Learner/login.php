<?php
session_start();
include '../pdoconfig.php';
$_SESSION['current_page'] = "";

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']);
    $password = trim($_POST['password']);

    error_log("Login attempt - Email: $email"); // Debug

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($users) {
        error_log("User found: " . $users['email']); // Debug
        error_log("Password verify result: " . (password_verify($password, $users['password_hash']) ? 'true' : 'false')); // Debug
        
        if (password_verify($password, $users['password_hash'])) {
            $_SESSION['student_id'] = $users['id'];
            $_SESSION['student_name'] =
                $users['first_name'] .
                ' ' .
                ($users['middle_name'] ? $users['middle_name'][0] . '.' : '') .
                ' ' .
                $users['last_name'];
            $_SESSION['experience'] = $users['experience'];
            $_SESSION['intelligent_exp'] = $users['intelligent_exp'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = true;
            error_log("Password verification failed"); // Debug
        }
    } else {
        $error = true;
        error_log("No user found with email: $email"); // Debug
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[var(--color-main-bg)] text-[var(--color-text)] min-h-screen flex items-center justify-center font-inter">

    <!-- Login Card: MODIFIED FOR MOBILE -->
    <div class="bg-[var(--color-card-bg)] text-[var(--color-text)] transition-colors duration-500 
                p-8 sm:p-10 rounded-2xl shadow-2xl 
                w-full max-w-md sm:w-96 relative mx-4"> 
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full shadow-md bg-[var(--color-heading)] text-white">
                <img src="../images/isu-logo.png" class="h-16 w-16 object-contain">
            </div>
            <h1 class="text-2xl font-extrabold tracking-wider text-[var(--color-heading)]">
                ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
            </h1>
            <p class="text-sm text-[var(--color-text-secondary)]">Welcome back! Please sign in</p>
        </div>

        <!-- Form -->
        <form method="POST" class="space-y-6">

            <!-- Username -->
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                <input type="text" id="username" name="username" required
                    class="w-full pl-10 pr-4 py-3 rounded-lg shadow-sm 
                            bg-[var(--color-input-bg)] border border-[var(--color-input-border)] 
                            text-[var(--color-text)] placeholder-[var(--color-text-secondary)]
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                    placeholder="Email"/>
            </div>

            <!-- Password -->
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                <input type="password" id="password" name="password" required
                    class="w-full pl-10 pr-4 py-3 rounded-lg shadow-sm 
                            bg-[var(--color-input-bg)] border border-[var(--color-input-border)] 
                            text-[var(--color-text)] placeholder-[var(--color-text-secondary)]
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                    placeholder="Password"/>
            </div>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 text-[var(--color-text)]">
                    <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>Remember me</span>
                </label>
                <a href="#" class="font-medium hover:underline text-[var(--color-button-primary)]">Forgot password?</a>
            </div>

            <!-- Button -->
            <button type="submit"
                class="w-full text-white font-bold py-3 rounded-lg shadow-md 
                        bg-[var(--color-button-primary)] hover:bg-[var(--color-button-primary-hover)]
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                Sign In
            </button>

            <!-- Error Message -->
            <?php if ($error): ?>
                <p class="mt-4 text-center text-sm text-red-500 font-medium">
                    Invalid username or password. Please try again.
                </p>
            <?php endif; ?>
        </form>

        <!-- Register -->
        <p class="text-center text-sm mt-6 text-[var(--color-text)]">
            Don't have an account? 
            <a href="register.php" class="font-medium hover:underline text-[var(--color-button-primary)]">Register here</a>
        </p>
    </div>

    <!-- Theme Toggle Floating Bottom Right -->
    <button id="theme-toggle"
        class="fixed bottom-6 right-6 p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110
                bg-[var(--color-toggle-bg)] text-[var(--color-toggle-text)]">
        <i class="fas fa-moon text-lg dark-icon"></i>
        <i class="fas fa-sun text-lg light-icon hidden"></i>
    </button>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const themeToggle = document.getElementById("theme-toggle");
            const body = document.body;
            const darkIcon = document.querySelector(".dark-icon");
            const lightIcon = document.querySelector(".light-icon");

            // Load saved theme
            const savedTheme = localStorage.getItem("theme") || "light";
            applyTheme(savedTheme);

            themeToggle.addEventListener("click", () => {
                const newTheme = body.classList.contains("dark-mode") ? "light" : "dark";
                applyTheme(newTheme);
                localStorage.setItem("theme", newTheme);
            });

            function applyTheme(theme) {
                if (theme === "dark") {
                    body.classList.add("dark-mode");
                    darkIcon.classList.add("hidden");    // Hide moon
                    lightIcon.classList.remove("hidden"); // Show sun
                } else {
                    body.classList.remove("dark-mode");
                    darkIcon.classList.remove("hidden"); // Show moon
                    lightIcon.classList.add("hidden");   // Hide sun
                }
            }
        });
    </script>

</body>
</html>
