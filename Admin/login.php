<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn Admin</title>
    <!-- Load Tailwind CSS CDN for styling -->
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            padding:0;
        }

        /* Diagonal cut for the left panel */
        .auth-left-panel-clip {
            /* Adjusted clip-path for a smoother, modern diagonal */
            clip-path: polygon(0 0, 100% 0, 90% 100%, 0% 100%); 
        }

        /* Active tab indicator */
        .tab-button.active {
            opacity: 1;
        }
        .tab-button.active::after {
            width: 100% !important; 
        }

        /* Form animation styles */
        .form-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        /* Form positions */
        .form-login.slide-in {
            transform: translateX(0);
            opacity: 1;
        }
        .form-login.slide-out {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
        }

        .form-register.slide-in {
            transform: translateX(0);
            opacity: 1;
        }
        .form-register.slide-out {
            transform: translateX(100%);
            opacity: 0;
            pointer-events: none;
        }

        /* Input styling */
        .input-themed {
            background-color: var(--color-input-bg);
            border: 2px solid var(--color-input-border);
            color: var(--color-input-text);
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        .input-themed:focus {
            outline: none;
            border-color: var(--color-heading);
            /* Updated focus glow to use the new green */
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2); 
        }
        .input-themed::placeholder {
            color: var(--color-input-placeholder);
        }

        /* Button styles */
        .btn-primary {
            background: var(--color-button-primary);
            color: white;
            transition: all 0.3s ease;
            /* Updated primary button shadow to new green */
            box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.3), 0 4px 6px -2px rgba(34, 197, 94, 0.1);
        }
        .btn-primary:hover {
            background: var(--color-button-primary-hover);
            transform: translateY(-2px);
            /* Updated primary button hover shadow to new green */
            box-shadow: 0 15px 25px -5px rgba(34, 197, 94, 0.4), 0 6px 10px -3px rgba(34, 197, 94, 0.1);
        }

        .btn-secondary {
            background: var(--color-button-secondary);
            color: var(--color-button-secondary-text);
            transition: all 0.3s ease;
            /* Updated secondary button shadow to new pastel yellow/orange accent */
            box-shadow: 0 10px 15px -3px rgba(253, 230, 138, 0.5), 0 4px 6px -2px rgba(253, 230, 138, 0.3);
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            filter: brightness(0.95);
            /* Updated secondary button hover shadow */
            box-shadow: 0 15px 25px -5px rgba(253, 230, 138, 0.7), 0 6px 10px -3px rgba(253, 230, 138, 0.4);
        }

        /* Card styling */
        .auth-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-card-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Left panel gradient */
        .auth-gradient {
            /* Using new vibrant green and orange-gold for the gradient */
            background: linear-gradient(135deg, var(--color-heading) 0%, var(--color-heading-secondary) 150%);
        }

        /* Progress bar style for loading */
        .loading-bar-container {
            height: 3px;
            overflow: hidden;
            position: relative;
            background: rgba(255, 255, 255, 0.3); /* Subtle track */
            width: 100%;
        }
        .loading-bar {
            background: var(--color-progress-fill);
            height: 3px;
            width: 50%;
            position: absolute;
            left: -50%;
            animation: loading 1.5s ease-in-out infinite;
        }

        @keyframes loading {
            0% { left: -50%; }
            50% { left: 100%; }
            100% { left: -50%; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Main Auth Container -->
    <div class="auth-card flex relative w-full max-w-6xl h-[650px] rounded-2xl overflow-hidden">
        
        <!-- Left Panel: Contextual Text -->
        <div class="hidden md:flex w-1/2 auth-gradient text-white flex-col justify-center items-start p-12 relative z-10 auth-left-panel-clip">
            <div class="mb-8">
              <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-6">
                     <img src="../images/isu-logo.png" alt="error">
                </div>
                <h2 id="left-panel-title" class="text-4xl font-extrabold mb-4 leading-tight">
                    WELCOME BACK!
                </h2>
              </div>
                
                <p id="left-panel-subtitle" class="text-lg leading-relaxed max-w-xs opacity-90">
                    Sign in to manage your e-learning platform and content effectively.
                </p>
            </div>
            
            <!-- Feature highlights -->
            <div class="space-y-3 mt-8">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-graduation-cap text-[var(--color-icon)]"></i>
                    <span class="text-sm">Course Management</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-chart-line text-[var(--color-icon)]"></i>
                    <span class="text-sm">Progress Analytics</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-users text-[var(--color-icon)]"></i>
                    <span class="text-sm">Student Tracking</span>
                </div>
            </div>
        </div>

        <!-- Right Panel: Forms and Tabs -->
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-8 md:p-12">
            <div class="w-full max-w-sm sm:max-w-md">

                <!-- Tab Switcher -->
                <div class="flex justify-center mb-12 gap-10">
                    <button id="tab-login" class="tab-button px-4 py-3 text-2xl font-bold transition-all duration-300 relative text-[var(--color-text)] opacity-70 hover:opacity-100 after:absolute after:bottom-0 after:left-1/2 after:-translate-x-1/2 after:w-0 after:h-1 after:bg-[var(--color-heading)] after:transition-all after:duration-300">
                        LOGIN
                    </button>
                    <button id="tab-register" class="tab-button px-4 py-3 text-2xl font-bold transition-all duration-300 relative text-[var(--color-text)] opacity-70 hover:opacity-100 after:absolute after:bottom-0 after:left-1/2 after:-translate-x-1/2 after:w-0 after:h-1 after:bg-[var(--color-heading-secondary)] after:transition-all after:duration-300">
                        SIGN UP
                    </button>
                </div>
                
                <!-- Messages Container -->
                <div id="message-container" class="h-14 mb-6 flex items-center justify-center">
                    <!-- Dynamic messages appear here -->
                </div>

                <!-- Form Wrapper -->
                <div class="form-wrapper relative w-full h-[450px] overflow-hidden"> 

                    <!-- Login Form -->
                    <form id="form-login" onsubmit="handleFormSubmission(event, 'login')" class="form-content form-login slide-in space-y-6">
                        <h3 class="text-3xl font-bold mb-8 text-center text-[var(--color-heading)]">Admin Login</h3>

                        <div class="relative">
                            <input type="email" name="email" required placeholder="Email Address" class="w-full px-4 py-4 input-themed">
                            <i class="fas fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                        </div>

                        <div class="relative">
                            <input type="password" name="password" required placeholder="Password" class="w-full px-4 py-4 input-themed">
                            <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center space-x-2 text-[var(--color-text-secondary)]">
                                <input type="checkbox" class="rounded border-[var(--color-input-border)] text-[var(--color-heading)]">
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="text-[var(--color-heading-secondary)] hover:underline font-semibold">Forgot password?</a>
                        </div>

                        <button type="submit" class="w-full py-4 px-4 mt-4 btn-primary font-bold text-lg rounded-xl">
                            <i class="fas fa-sign-in-alt mr-3"></i> Log In
                        </button>
                        
                        <!-- Link to switch tab -->
                        <p class="text-center text-sm text-[var(--color-text-secondary)] mt-6 pt-3">
                            Don't have an account? <a href="#" onclick="event.preventDefault(); switchTab('register');" class="text-[var(--color-heading-secondary)] hover:underline font-semibold">Sign Up</a>
                        </p>
                    </form>

                    <!-- Registration Form -->
                    <form id="form-register" onsubmit="handleFormSubmission(event, 'register')" class="form-content form-register slide-out space-y-6">
                        <h3 class="text-3xl font-bold mb-8 text-center text-[var(--color-heading-secondary)]">Create Account</h3>

                        <div class="relative">
                            <input type="text" name="name" required placeholder="Full Name" class="w-full px-4 py-4 input-themed">
                            <i class="fas fa-user absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                        </div>
                        
                        <div class="relative">
                            <input type="email" name="email" required placeholder="Email Address" class="w-full px-4 py-4 input-themed">
                            <i class="fas fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="password" name="password" required placeholder="Password" class="w-full px-4 py-4 input-themed">
                                <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                            </div>

                            <div class="relative">
                                <input type="password" name="confirm_password" required placeholder="Confirm Password" class="w-full px-4 py-4 input-themed">
                                <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"></i>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 text-sm text-[var(--color-text-secondary)]">
                            <input type="checkbox" required class="rounded border-[var(--color-input-border)] text-[var(--color-heading-secondary)]">
                            <span>I agree to the <a href="#" class="text-[var(--color-heading)] hover:underline font-semibold">Terms & Conditions</a></span>
                        </div>

                        <button type="submit" class="w-full py-4 px-4 mt-2 btn-secondary font-bold text-lg rounded-xl">
                            <i class="fas fa-user-plus mr-3"></i> Create Account
                        </button>
                        
                        <!-- Link to switch tab -->
                        <p class="text-center text-sm text-[var(--color-text-secondary)] mt-6 pt-3">
                            Already have an account? <a href="#" onclick="event.preventDefault(); switchTab('login');" class="text-[var(--color-heading)] hover:underline font-semibold">Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const loginTab = document.getElementById('tab-login');
        const registerTab = document.getElementById('tab-register');
        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-register');
        const leftPanelTitle = document.getElementById('left-panel-title');
        const leftPanelSubtitle = document.getElementById('left-panel-subtitle');
        const messageContainer = document.getElementById('message-container');

        /**
         * Displays a message (error or success) in the message container.
         */
        function displayMessage(text, type) {
            messageContainer.innerHTML = '';
            if (!text) return;

            // Loading bar state
            if (type === 'loading') {
                messageContainer.innerHTML = `
                    <div class="w-full flex flex-col items-center">
                        <div class="text-xs font-medium text-[var(--color-text-secondary)] mb-2">${text}</div>
                        <div class="loading-bar-container w-4/5">
                            <div class="loading-bar"></div>
                        </div>
                    </div>
                `;
                return;
            }

            // Standard message box
            const baseClass = "px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 transform w-full text-center shadow-lg";
            let classList = '';
            let icon = '';

            if (type === 'error') {
                classList = `bg-red-100 text-red-700 border border-red-200 ${baseClass}`;
                icon = '<i class="fas fa-exclamation-triangle mr-3"></i>';
            } else if (type === 'success') {
                classList = `bg-green-100 text-green-700 border border-green-200 ${baseClass}`;
                icon = '<i class="fas fa-check-circle mr-3"></i>';
            } else if (type === 'info') {
                classList = `bg-blue-100 text-blue-700 border border-blue-200 ${baseClass}`;
                icon = '<i class="fas fa-info-circle mr-3"></i>';
            } 

            const messageDiv = document.createElement('div');
            messageDiv.className = classList;
            messageDiv.innerHTML = `${icon}${text}`;
            messageContainer.appendChild(messageDiv);
        }

        /**
         * Switches the active form tab with smooth sliding animation.
         */
        function switchTab(target) {
            displayMessage('', ''); // Clear messages on tab switch

            if (target === 'login') {
                // Activate Login Tab
                loginTab.classList.add('active', 'opacity-100');
                loginTab.classList.remove('opacity-70');
                registerTab.classList.remove('active', 'opacity-100');
                registerTab.classList.add('opacity-70');

                // Animate Forms
                loginForm.classList.remove('slide-out');
                loginForm.classList.add('slide-in');
                registerForm.classList.remove('slide-in');
                registerForm.classList.add('slide-out');

                // Update Left Panel
                // Check if the left panel elements exist (they won't on small screens)
                if (leftPanelTitle && leftPanelSubtitle) {
                    leftPanelTitle.innerHTML = "WELCOME<br>BACK!";
                    leftPanelSubtitle.textContent = "Sign in to manage your e-learning platform and content effectively.";
                }

            } else { // target === 'register'
                // Activate Register Tab
                registerTab.classList.add('active', 'opacity-100');
                registerTab.classList.remove('opacity-70');
                loginTab.classList.remove('active', 'opacity-100');
                loginTab.classList.add('opacity-70');

                // Animate Forms
                registerForm.classList.remove('slide-out');
                registerForm.classList.add('slide-in');
                loginForm.classList.remove('slide-in');
                loginForm.classList.add('slide-out');

                // Update Left Panel
                if (leftPanelTitle && leftPanelSubtitle) {
                    leftPanelTitle.innerHTML = "GET STARTED!";
                    leftPanelSubtitle.textContent = "Join the admin team to access powerful course management tools.";
                }
            }
        }

        /**
         * Handles form submission logic.
         */
        function handleFormSubmission(event, action) {
            event.preventDefault();
            displayMessage('Processing your request...', 'loading');
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Simulate backend processing
            setTimeout(() => {
                if (action === 'login') {
                    const email = formData.get('email');
                    const password = formData.get('password');

                    // Demo credentials
                    if (email === 'admin@isu.edu' && password === 'password123') {
                         displayMessage('Login successful! Redirecting to dashboard...', 'success');
                         form.reset();
                         // In a real application: window.location.href = 'dashboard.php';
                    } else {
                        displayMessage('Invalid credentials. Try: admin@isu.edu / password123', 'error');
                    }
                } else if (action === 'register') {
                    const password = formData.get('password');
                    const confirm_password = formData.get('confirm_password');

                    // Simple validation checks
                    if (password !== confirm_password) {
                        displayMessage('Passwords do not match.', 'error');
                        return;
                    }
                    
                    if (password.length < 6) {
                        displayMessage('Password must be at least 6 characters long.', 'error');
                        return;
                    }
                    
                    // Simulate successful registration
                    displayMessage('Account created successfully! Switching to Login...', 'success');
                    form.reset();
                    // Automatically switch to login tab after 2 seconds
                    setTimeout(() => switchTab('login'), 2000);
                }
            }, 1500); // 1.5 second delay
        }

        // Initialize the page
        document.addEventListener("DOMContentLoaded", () => {
            switchTab('login');
        });

        // Attach event listeners for tab switching
        loginTab.addEventListener('click', () => switchTab('login'));
        registerTab.addEventListener('click', () => switchTab('register'));

    </script>
</body>
</html>
