<?php
include '../pdoconfig.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = !empty(trim($_POST['middle_name'])) ? trim($_POST['middle_name']) : null;
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $check_account = $stmt->fetchColumn();

    if ($check_account) {
        $result = 'failed';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO users (first_name, middle_name, last_name, email, contact_number, address, password_hash) VALUES (:first_name, :middle_name, :last_name, :email, :contact_number, :address, :password_hash)',
        );
        $stmt->execute([
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':contact_number' => $contact_number,
            ':address' => $address,
            ':password_hash' => $password_hash,
        ]);
        $result = 'success';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom CSS to link the JS password strength logic with your theme variables */
        .password-strength {
            height: 5px;
            transition: all 0.3s ease;
        }
        /* The body background is handled by the main-bg variable */
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        input, textarea, select {
        background-color: var(--color-input-bg);
        border: 1px solid var(--color-input-border);
        color: var(--color-input-text);
        transition: all 0.3s ease;
        }

        input::placeholder,
        textarea::placeholder {
        color: var(--color-input-placeholder);
        }


    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 transition-colors duration-300" style="background-color: var(--color-main-bg); color: var(--color-text);">
    <div class="container max-w-4xl mx-auto p-5">
        <div class="card bg-[var(--color-card-bg)] rounded-2xl shadow-xl overflow-hidden border border-[var(--color-card-border)]">
            <header class= "text-white p-6 text-center shadow-lg">
                <div class="flex items-center justify-center space-x-3">
                    <img src="../images/isu-logo.png" alt="" class="h-10 w-10 object-contain">
                    <h1 class="text-2xl font-extrabold tracking-wider text-[var(--color-heading)]">
                        ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
                    </h1>
                    
                </div>
                <p class="mt-2 text-[var(--color-text-secondary)] opacity-90">Create your account to start your learning journey</p>
            </header>

            <div class="px-8 pt-6">
                <div class="flex justify-between items-center mb-8">
                    <div class="flex-1 text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-[var(--color-heading)] text-white flex items-center justify-center font-bold">1</div>
                        <p class="text-sm mt-1 text-[var(--color-heading)] font-medium">Account</p>
                    </div>
                    <div class="flex-1 h-1 bg-[var(--color-card-border)]"></div>
                    <div class="flex-1 text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-[var(--color-card-border)] text-[var(--color-text-secondary)] flex items-center justify-center font-bold">2</div>
                        <p class="text-sm mt-1 text-[var(--color-text-secondary)]">Profile</p>
                    </div>
                    <div class="flex-1 h-1 bg-[var(--color-card-border)]"></div>
                    <div class="flex-1 text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-[var(--color-card-border)] text-[var(--color-text-secondary)] flex items-center justify-center font-bold">3</div>
                        <p class="text-sm mt-1 text-[var(--color-text-secondary)]">Confirm</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <form id="registrationForm" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-[var(--color-text)] mb-1">First Name</label>
                            <input type="text" id="firstName" name="first_name" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g Juan">
                        </div>

                        <div>
                            <label for="middlename" class="block text-sm font-medium text-[var(--color-text)] mb-1">Middle Name</label>
                            <input type="text" id="middlename" name="middle_name" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g Santos">
                        </div>

                        <div>
                            <label for="lastName" class="block text-sm font-medium text-[var(--color-text)] mb-1">Last Name</label>
                            <input type="text" id="lastName" name="last_name" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g Dela Cruz">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-[var(--color-text)] mb-1">Email Address</label>
                        <input type="email" id="email" name="email" required 
                            class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g example@email.com">
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-[var(--color-text)] mb-1">Address</label>
                        <input type="text" id="address" name="address" required 
                            class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g. 123 Rizal St., Brgy. Mabini, Quezon City">
                    </div>

                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-[var(--color-text)] mb-1">Phone Number</label>
                        <input type="text" id="phoneNumber" name="contact_number" required 
                            class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g +63 xxx xxxx xxxx">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-[var(--color-text)] mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required 
                                    class="w-full px-4 py-3 rounded-lg pr-12 focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200 border border-[var(--color-card-border)]">
                                <button type="button" id="togglePassword" 
                                    class="absolute top-1/2 right-0 mr-3 transform -translate-y-1/2 flex items-center text-[var(--color-text-secondary)] hover:text-[var(--color-heading)]">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
        
                            <div class="mt-2">
                                <div class="flex space-x-1">
                                    <div id="strength1" class="password-strength w-1/4 bg-[var(--color-card-border)] rounded"></div>
                                    <div id="strength2" class="password-strength w-1/4 bg-[var(--color-card-border)] rounded"></div>
                                    <div id="strength3" class="password-strength w-1/4 bg-[var(--color-card-border)] rounded"></div>
                                    <div id="strength4" class="password-strength w-1/4 bg-[var(--color-card-border)] rounded"></div>
                                </div>
                                <p id="passwordFeedback" class="text-xs mt-1 text-[var(--color-text-secondary)]">Password strength indicator</p>
                            </div>
                        </div>
    
                        <!-- Confirm Password -->
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-[var(--color-text)] mb-1">Confirm Password</label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" name="confirmPassword" required 
                                    class="w-full px-4 py-3 rounded-lg pr-12 focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200 border border-[var(--color-card-border)]">
                                <button type="button" id="toggleConfirmPassword" 
                                    class="absolute top-1/2 right-0 mr-3 transform -translate-y-1/2 flex items-center text-[var(--color-text-secondary)] hover:text-[var(--color-heading)]">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p id="passwordMatch" class="text-xs mt-1 hidden" style="color: var(--color-green-button);">
                                <i class="fas fa-check-circle mr-1"></i> Passwords match
                            </p>
                            <p id="passwordMismatch" class="text-xs mt-1 text-red-500 hidden">
                                <i class="fas fa-times-circle mr-1"></i> Passwords do not match
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="terms" name="terms" required 
                                class="rounded text-[var(--color-heading)] focus:ring-[var(--color-heading)]" style="color: var(--color-heading);">
                            <span class="ml-2 text-sm text-[var(--color-text)]">I agree to the <a href="#" class="text-[var(--color-heading)] hover:opacity-75 hover:underline">Terms of Service</a> and <a href="#" class="text-[var(--color-heading)] hover:opacity-75 hover:underline">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <div id="formMessage" class="hidden mt-4 p-3 rounded-lg border text-sm flex items-center space-x-2"
                    style="background-color: var(--color-card-section-bg); border-color: #ef4444; color: #ef4444;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="formMessageText">Validation message here</span>
                    </div>

                    <div>
                        <button type="submit" 
                            class="w-full text-white py-3 px-4 rounded-lg font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-button-primary)]"
                            style="background-color: var(--color-button-primary); --tw-ring-offset-color: var(--color-card-bg);"
                            onmouseover="this.style.backgroundColor='var(--color-button-primary-hover)'"
                            onmouseout="this.style.backgroundColor='var(--color-button-primary)'">
                            Create Account
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-[var(--color-text-secondary)]">
                            Already have an account? <a href="login.php" class="font-medium text-[var(--color-heading)] hover:opacity-75">Sign in</a>
                        </p>
                    </div>
                </form>
            </div>
            
            <div id="successMessage"
            class="p-8 border-t <?= empty($result) ? 'hidden' : '' ?>"
            style="background-color: var(--color-card-section-bg); border-color: var(--color-card-section-border);">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                    <?php if ($result === 'success'): ?>
                        <i class="fas fa-check-circle text-2xl" style="color: var(--color-green-button);"></i>
                    <?php elseif ($result === 'failed'): ?>
                        <i class="fas fa-exclamation-circle text-2xl text-red-500"></i>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                <h3 class="font-medium" style="color: var(--color-text-on-section);">
                    <?= $result === 'success' ? 'Registration successful!' : 'Registration failed' ?>
                </h3>
                <p class="mt-1" style="color: var(--color-text-on-section);">
                <?php if ($result === 'success'): ?>
                    Your account has been created. You can now
                    <a href="login.php" class="font-medium underline text-[var(--color-heading)] hover:opacity-75">sign in</a>
                    to start learning.
                <?php elseif ($result === 'failed'): ?>
                    An account with this email already exists. Please
                    <a href="login.php" class="font-medium underline text-[var(--color-heading)] hover:opacity-75">sign in</a>
                    or use a different email address.
                <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[var(--color-card-bg)] p-5 rounded-lg shadow-sm border border-[var(--color-card-border)]">
                <div class="flex items-center justify-center w-12 h-12 rounded-lg mb-4" style="background-color: var(--color-card-section-bg); color: var(--color-heading);">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-[var(--color-text)] mb-2">Interactive Courses</h3>
                <p class="text-[var(--color-text-secondary)]">Engage with interactive content designed to maximize learning retention.</p>
            </div>
            <div class="bg-[var(--color-card-bg)] p-5 rounded-lg shadow-sm border border-[var(--color-card-border)]">
                <div class="flex items-center justify-center w-12 h-12 rounded-lg mb-4" style="background-color: var(--color-card-section-bg); color: var(--color-green-button);">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-[var(--color-text)] mb-2">Track Progress</h3>
                <p class="text-[var(--color-text-secondary)]">Monitor your learning journey with detailed progress analytics.</p>
            </div>
            <div class="bg-[var(--color-card-bg)] p-5 rounded-lg shadow-sm border border-[var(--color-card-border)]">
                <div class="flex items-center justify-center w-12 h-12 rounded-lg mb-4" style="background-color: var(--color-card-section-bg); color: var(--color-icon);">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-[var(--color-text)] mb-2">Earn Rewards</h3>
                <p class="text-[var(--color-text-secondary)]">Gain experience points and unlock achievements as you learn.</p>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button id="themeToggle"
        class="fixed bottom-4 right-4 p-2 rounded-full shadow-md transition-all duration-300 hover:scale-110"
        style="background: var(--color-toggle-bg); color: var(--color-toggle-text);">
        <i class="fas fa-moon" id="moonIcon"></i>
        <i class="fas fa-sun hidden" id="sunIcon"></i>
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registrationForm = document.getElementById('registrationForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordMatch = document.getElementById('passwordMatch');
        const passwordMismatch = document.getElementById('passwordMismatch');
        const contact_number = document.getElementById('phoneNumber').value;
        let filledCount = 0;

        const strengthBars = [
            document.getElementById('strength1'),
            document.getElementById('strength2'),
            document.getElementById('strength3'),
            document.getElementById('strength4')
        ];
        const passwordFeedback = document.getElementById('passwordFeedback');

        // Colors from CSS variables
        const comp = getComputedStyle(document.documentElement);
        const COLOR_STATUS_NOT_STARTED = comp.getPropertyValue('--color-status-not-started').trim() || '#e5e7eb';
        const COLOR_STATUS_IN_PROGRESS = comp.getPropertyValue('--color-status-in-progress').trim() || '#f59e0b';
        const COLOR_STATUS_COMPLETED = comp.getPropertyValue('--color-status-completed').trim() || '#10b981';
        const COLOR_TEXT_SECONDARY = comp.getPropertyValue('--color-text-secondary').trim() || '#6b7280';

        // Confirm password validation
        function validateConfirmPassword() {
            const isMatch = passwordInput.value === confirmPasswordInput.value && passwordInput.value !== '';
            const hasValue = confirmPasswordInput.value !== '';

            if (isMatch) {
                passwordMatch.classList.remove('hidden');
                passwordMismatch.classList.add('hidden');
                confirmPasswordInput.style.borderColor = 'var(--color-green-button)';
            } else if (hasValue) {
                passwordMatch.classList.add('hidden');
                passwordMismatch.classList.remove('hidden');
                confirmPasswordInput.style.borderColor = '#ef4444';
            } else {
                passwordMatch.classList.add('hidden');
                passwordMismatch.classList.add('hidden');
                confirmPasswordInput.style.borderColor = COLOR_STATUS_NOT_STARTED;
            }
            return isMatch;
        }

        // Strength calculation
        function updateStrengthBars(filledCount) {
            strengthBars.forEach(bar => {
                bar.style.backgroundColor = COLOR_STATUS_NOT_STARTED;
            });

            if (filledCount === 0) {
                passwordFeedback.textContent = 'Password strength indicator';
                passwordFeedback.style.color = COLOR_TEXT_SECONDARY;
                return;
            }

            let color = '#ef4444';
            let label = 'Weak password';

            if (filledCount === 1) {
                color = '#ef4444';
                label = 'Weak password';
            } else if (filledCount < 4) {
                color = COLOR_STATUS_IN_PROGRESS;
                label = 'Medium strength password';
            } else if (filledCount === 4) {
                color = COLOR_STATUS_COMPLETED;
                label = 'Strong password';
            }

            for (let i = 0; i < filledCount; i++) {
                strengthBars[i].style.backgroundColor = color;
            }

            passwordFeedback.textContent = label;
            passwordFeedback.style.color = color;
        }

        // Calculate strength
        function calculateStrength(pwd) {
            let points = 0;
            if (pwd.length >= 6) points++;
            if (pwd.length >= 10) points++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) points++;
            if (/\d/.test(pwd)) points++;
            if (/[!@#$%^&*?_~]/.test(pwd)) points++;
            return Math.min(4, Math.round((points / 5) * 4));
        }

        // Live updates
        passwordInput.addEventListener('input', () => {
            const strength = calculateStrength(passwordInput.value);
            updateStrengthBars(strength);
            validateConfirmPassword();
        });
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);

        // Final form validation (client-side)
        registrationForm.addEventListener('submit', function(e) {
    let valid = true;
    const pwd = passwordInput.value;
    const contact_number = document.getElementById('phoneNumber').value;

    // Contact number validation
    if (!/^\+63[0-9]{10}$/.test(contact_number)) {
        document.getElementById('formMessageText').textContent = 
            '⚠️ Enter valid PH number (+639xxxxxxxxx)';
        document.getElementById('formMessage').style.display = "block";
        e.preventDefault();
        valid = false;
    }   

    // Password validation (explicit rules)
    let has_lowercase        = /[a-z]/.test(pwd);
    let has_uppercase        = /[A-Z]/.test(pwd);
    let has_number           = /[0-9]/.test(pwd);
    let has_specialcharacter = /[^A-Za-z0-9]/.test(pwd);
    let has_longenough       = pwd.length >= 8;

    if (!(has_lowercase && has_uppercase && has_number && has_specialcharacter && has_longenough)) {
        document.getElementById('formMessageText').textContent = 
            "Invalid password! Must have lowercase, uppercase, number, special character, and at least 8 chars.";
        document.getElementById('formMessage').style.display = "block";
        e.preventDefault();
        valid = false;
    }

    // Confirm password validation
    if (!validateConfirmPassword()) {
        e.preventDefault();
        confirmPasswordInput.focus();
        valid = false;
    }

    // Final check
    if (!valid) {
        e.preventDefault();
    } else {
        document.getElementById('formMessage').style.display = 'none';
        document.getElementById('formMessageText').textContent = '';
    }
});

        // Init state
        updateStrengthBars(0);
        validateConfirmPassword();
    });

    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
    
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Toggle Confirm Password Visibility
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const icon = this.querySelector('i');
    
        if (confirmPasswordInput.type === 'password') {
            confirmPasswordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            confirmPasswordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Theme toggle logic
    const themeToggle = document.getElementById("themeToggle");
    const moonIcon = document.getElementById("moonIcon");
    const sunIcon = document.getElementById("sunIcon");

    function applyTheme(theme) {
        if (theme === "dark") {
            document.body.classList.add("dark-mode");
            moonIcon.classList.add("hidden");
            sunIcon.classList.remove("hidden");
        } else {
            document.body.classList.remove("dark-mode");
            sunIcon.classList.add("hidden");
            moonIcon.classList.remove("hidden");
        }
    }

    // Load saved theme
    const savedTheme = localStorage.getItem("theme") || "light";
    applyTheme(savedTheme);

    // Toggle on click
    themeToggle.addEventListener("click", () => {
        const isDark = document.body.classList.contains("dark-mode");
        const newTheme = isDark ? "light" : "dark";
        applyTheme(newTheme);
        localStorage.setItem("theme", newTheme);
    });
    </script>
</body>
</html>