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
                <form id="registrationForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-[var(--color-text)] mb-1">First Name</label>
                            <input type="text" id="firstName" name="firstName" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g Juan">
                        </div>

                        <div>
                            <label for="middlename" class="block text-sm font-medium text-[var(--color-text)] mb-1">Middle Name</label>
                            <input type="text" id="middlename" name="middlename" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g Santos">
                        </div>

                        <div>
                            <label for="lastName" class="block text-sm font-medium text-[var(--color-text)] mb-1">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required 
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
                        <input type="text" id="phoneNumber" name="phoneNumber" required 
                            class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200" placeholder="e.g +63 xxx xxxx xxxx">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-[var(--color-text)] mb-1">Password</label>
                            <input type="password" id="password" name="password" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200">
                            
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
                        
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-[var(--color-text)] mb-1">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required 
                                class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition duration-200">
                            <p id="passwordMatch" class="text-xs mt-1 hidden" style="color: var(--color-green-button);"><i class="fas fa-check-circle mr-1"></i> Passwords match</p>
                            <p id="passwordMismatch" class="text-xs mt-1 text-red-500 hidden"><i class="fas fa-times-circle mr-1"></i> Passwords do not match</p>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="terms" name="terms" required 
                                class="rounded text-[var(--color-heading)] focus:ring-[var(--color-heading)]" style="color: var(--color-heading);">
                            <span class="ml-2 text-sm text-[var(--color-text)]">I agree to the <a href="#" class="text-[var(--color-heading)] hover:opacity-75 hover:underline">Terms of Service</a> and <a href="#" class="text-[var(--color-heading)] hover:opacity-75 hover:underline">Privacy Policy</a></span>
                        </label>
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

            <div id="successMessage" class="hidden p-8 border-t" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-section-border);">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-2xl" style="color: var(--color-green-button);"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium" style="color: var(--color-text-on-section);">Registration successful!</h3>
                        <p class="mt-1" style="color: var(--color-text-on-section);">Your account has been created. You can now <a href="login.html" class="font-medium underline text-[var(--color-heading)] hover:opacity-75">sign in</a> to start learning.</p>
                    </div>
                </div>
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
            const successMessage = document.getElementById('successMessage');
            
            // Password strength indicators
            const strengthBars = [
                document.getElementById('strength1'),
                document.getElementById('strength2'),
                document.getElementById('strength3'),
                document.getElementById('strength4')
            ];
            
            const passwordFeedback = document.getElementById('passwordFeedback');

            const COLOR_CARD_BORDER = getComputedStyle(document.documentElement).getPropertyValue('--color-card-border').trim();
            const COLOR_STATUS_NOT_STARTED = getComputedStyle(document.documentElement).getPropertyValue('--color-status-not-started').trim();
            const COLOR_STATUS_IN_PROGRESS = getComputedStyle(document.documentElement).getPropertyValue('--color-status-in-progress').trim();
            const COLOR_STATUS_COMPLETED = getComputedStyle(document.documentElement).getPropertyValue('--color-status-completed').trim();
            const COLOR_TEXT_SECONDARY = getComputedStyle(document.documentElement).getPropertyValue('--color-text-secondary').trim();

            
            // Check password strength
            passwordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                let strength = 0;
                
                // Check password length
                if (password.length > 5) strength++;
                if (password.length > 8) strength++;
                
                // Check for mixed case
                if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength++;
                
                // Check for numbers
                if (password.match(/([0-9])/)) strength++;
                
                // Check for special characters
                if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/)) strength++;
                
                // Update strength bars
                updateStrengthBars(strength);
                validateConfirmPassword(); // Re-validate confirm password on password change
            });
            
            // Confirm password validation
            confirmPasswordInput.addEventListener('input', validateConfirmPassword);
            passwordInput.addEventListener('input', validateConfirmPassword); // Also check when password changes

            function validateConfirmPassword() {
                const isMatch = passwordInput.value === confirmPasswordInput.value && passwordInput.value !== '';
                const hasValue = confirmPasswordInput.value !== '';

                if (isMatch) {
                    passwordMatch.classList.remove('hidden');
                    passwordMismatch.classList.add('hidden');
                    // Use a hardcoded green color for the border on match for visibility
                    confirmPasswordInput.style.borderColor = 'var(--color-green-button)'; 
                } else if (hasValue) {
                    passwordMatch.classList.add('hidden');
                    passwordMismatch.classList.remove('hidden');
                    // Use a hardcoded red color for the border on mismatch
                    confirmPasswordInput.style.borderColor = '#ef4444'; 
                } else {
                    passwordMatch.classList.add('hidden');
                    passwordMismatch.classList.add('hidden');
                    // Reset to default border color
                    confirmPasswordInput.style.borderColor = COLOR_CARD_BORDER; 
                }
            }
            
            // Form submission
            registrationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.focus();
                    return;
                }
                
                // Simulate successful registration
                successMessage.classList.remove('hidden');
                registrationForm.reset();
                
                // Reset strength bars and feedback
                updateStrengthBars(0);
                passwordMatch.classList.add('hidden');
                passwordMismatch.classList.add('hidden');
                
                // Scroll to success message
                successMessage.scrollIntoView({ behavior: 'smooth' });
            });
            
            function updateStrengthBars(strength) {
                // Reset all bars to default background color
                strengthBars.forEach(bar => {
                    bar.style.backgroundColor = COLOR_STATUS_NOT_STARTED;
                });
                
                // Set colors based on strength
                if (strength > 0) {
                    // Weak (Red/Error)
                    strengthBars[0].style.backgroundColor = '#ef4444'; 
                    passwordFeedback.textContent = 'Weak password';
                    passwordFeedback.style.color = '#ef4444';
                }
                
                if (strength > 2) {
                    // Medium (In-Progress/Amber)
                    strengthBars[0].style.backgroundColor = COLOR_STATUS_IN_PROGRESS;
                    strengthBars[1].style.backgroundColor = COLOR_STATUS_IN_PROGRESS;
                    passwordFeedback.textContent = 'Medium strength password';
                    passwordFeedback.style.color = COLOR_STATUS_IN_PROGRESS;
                }
                
                if (strength > 4) {
                    // Strong (Completed/Green)
                    strengthBars[0].style.backgroundColor = COLOR_STATUS_COMPLETED;
                    strengthBars[1].style.backgroundColor = COLOR_STATUS_COMPLETED;
                    strengthBars[2].style.backgroundColor = COLOR_STATUS_COMPLETED;
                    strengthBars[3].style.backgroundColor = COLOR_STATUS_COMPLETED;
                    passwordFeedback.textContent = 'Strong password';
                    passwordFeedback.style.color = COLOR_STATUS_COMPLETED;
                }
                
                if (strength === 0) {
                    passwordFeedback.textContent = 'Password strength indicator';
                    passwordFeedback.style.color = COLOR_TEXT_SECONDARY;
                }
            }

            // Initial state reset for borders (to apply var colors)
            validateConfirmPassword(); 
            updateStrengthBars(0);
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