<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ISUtoLearn</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" type="image/png" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<style>
    .custom-scrollbar-hide::-webkit-scrollbar { 
        display: none; 
    }
    .custom-scrollbar-hide { 
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }

    #dark-mode-track {
        position: relative;
        width: 48px; /* w-12 */
        height: 24px; /* h-6 */
        border-radius: 9999px; /* rounded-full */
        transition: background-color 0.3s ease;
    }

    /* Toggle Handle (The moving circle) */
    #dark-mode-track::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px; /* smaller handle */
        height: 20px; /* smaller handle */
        border-radius: 50%;
        background-color: var(--color-card-bg); /* Handle color */
        border: 1px solid var(--color-card-border);
        transition: transform 0.3s ease, background-color 0.3s ease;
    }

    /* Toggle Icons inside the track */
    .toggle-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px; /* Small icon size */
        transition: color 0.3s ease;
    }
    #dark-mode-icon-sun {
        left: 4px; /* Position on the left */
        color: var(--color-text-secondary); /* Off-state color */
    }
    #dark-mode-icon-moon {
        right: 4px; /* Position on the right */
        color: var(--color-text-secondary); /* Off-state color */
    }

    /* ON State Styles */
    #dark-mode-input:checked + #dark-mode-track {
        background-color: var(--color-button-primary) !important; /* On-state track color */
    }
    #dark-mode-input:checked + #dark-mode-track::after {
        transform: translateX(24px); /* Move 24px (48px - 2px padding * 2 - 20px handle) */
    }
    /* Change icon color when on */
    #dark-mode-input:checked + #dark-mode-track #dark-mode-icon-sun {
        color: var(--color-card-bg); /* Primary color when active */
    }


    /* Standard toggle for other checkboxes (Notifications) */
    .toggle-checkbox:checked + .toggle-track {
        background-color: var(--color-button-primary) !important;
    }
    .toggle-checkbox:checked + .toggle-track::after {
        background-color: white !important;
    }
</style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">
    
    <?php include 'sidebar.php'; ?>
    <div class="flex-1 flex flex-col">
        <header class="backdrop-blur-sm shadow-lg px-4 md:px-8 py-3.5 flex justify-between items-center z-10" 
            style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex gap-2">
                <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)]   rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
            </button>
            <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">⚙️ Settings</h1>
            </div>
            
        </header>

        <main class="flex-1 px-6 md:px-12 py-6 overflow-y-auto custom-scrollbar-hide">
            <div class="rounded-2xl shadow-2xl w-full max-w-2xl p-8 space-y-8 mx-auto"
                style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                
                <div class="text-center space-y-2">
                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">App Settings</h2>
                    <p class="font-medium text-sm" style="color: var(--color-text-secondary);">Customize your FixLearn experience to your liking.</p>
                </div>
                
                <div class="border-t pt-8" style="border-color: var(--color-card-border);">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Appearance</h3>
                    
                    <label for="dark-mode-input" class="flex justify-between items-center py-4 px-6 rounded-xl shadow-inner cursor-pointer" 
                          style="background-color: var(--color-card-section-bg);">
                        <div class="flex items-center space-x-4">
                            <i class="fas fa-palette text-3xl" style="color: var(--color-icon);"></i>
                            <div>
                                <h4 class="font-bold" style="color: var(--color-text);">Dark Mode</h4>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Toggle between light and dark themes.</p>
                            </div>
                        </div>

                        <div class="relative inline-flex items-center">
                            <input type="checkbox" id="dark-mode-input" class="sr-only peer toggle-checkbox">
                            <div id="dark-mode-track">
                                <i id="dark-mode-icon-sun" class="fas fa-sun toggle-icon"></i>
                                <i id="dark-mode-icon-moon" class="fas fa-moon toggle-icon"></i>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="border-t pt-8" style="border-color: var(--color-card-border);">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Account</h3>
                    <div class="space-y-4">
                        
                        <div class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between" 
                             style="background-color: var(--color-card-section-bg);">
                            <div>
                                <h4 class="font-bold" style="color: var(--color-text);">Change Password</h4>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Update your account password securely.</p>
                            </div>
                            <button class="px-4 py-2 rounded-full transition hover:opacity-80 hover:shadow-xl" 
                                    style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </button>
                        </div>
                        
                        <label class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between cursor-pointer" 
                               style="background-color: var(--color-card-section-bg);">
                            <div>
                                <h4 class="font-bold" style="color: var(--color-text);">Notifications</h4>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Manage your email and in-app notifications.</p>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" value="" class="sr-only peer toggle-checkbox">
                                <div class="w-11 h-6 rounded-full transition toggle-track after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"
                                        style="background-color: var(--color-text-secondary); /* Off-state color */">
                                </div>
                            </div>
                        </label>

                    </div>
                </div>
                
                <div class="border-t pt-8" style="border-color: var(--color-card-border);">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Data & Privacy</h3>
                    <div class="space-y-4">
                        

                            <div class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between" 
              style="background-color: var(--color-card-section-bg);">
            <div>
                <h4 class="font-bold" style="color: var(--color-text);">Report an Issue</h4>
                <p class="text-sm" style="color: var(--color-text-secondary);">Submit a bug report, feedback, or technical issue.</p>
            </div>
            <button class="px-4 py-2 rounded-full transition hover:opacity-80 hover:shadow-xl" 
                    style="background-color: var(--color-warning); color: var(--color-text-inverted);">
                <i class="fas fa-exclamation-triangle mr-2"></i> Report
            </button>
        </div>


                        <div class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between" 
                             style="background-color: var(--color-card-section-bg);">
                            <div>
                                <h4 class="font-bold text-red-500">Delete Account</h4>
                                <p class="text-sm" style="color: var(--color-text-secondary);">Permanently remove your account and data.</p>
                            </div>
                            <button class="px-4 py-2 rounded-full transition hover:opacity-80 hover:shadow-xl bg-red-600 text-white">
                                <i class="fas fa-trash-alt mr-2"></i> Delete
                            </button>
                        </div>

                    </div>
                </div>
                
                <div class="border-t pt-8" style="border-color: var(--color-card-border);">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Application</h3>
                    <div class="space-y-4">
                        
                        <div class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between text-sm" 
                             style="background-color: var(--color-card-section-bg); color: var(--color-text-secondary);">
                            <p class="font-medium">Version</p>
                            <p class="font-bold" style="color: var(--color-text);">1.2.0</p>
                        </div>
                        
                        <div class="py-4 px-6 rounded-xl shadow-inner flex items-center justify-between text-sm" 
                             style="background-color: var(--color-card-section-bg); color: var(--color-text-secondary);">
                            <p class="font-medium">Last Updated</p>
                            <p class="font-bold" style="color: var(--color-text);">Sept 2025</p>
                        </div>
                        
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        const body = document.body;
        const darkModeInput = document.getElementById('dark-mode-input');

        // Function to check and set the initial theme based on localStorage
        function loadTheme() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            
            darkModeInput.checked = isDarkMode; // Set the state of the invisible checkbox
            
            if (isDarkMode) {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
        }

        // --- Core Logic: Handle the change event (Click) ---
        darkModeInput.addEventListener('change', () => {
            const newIsDarkMode = darkModeInput.checked;

            localStorage.setItem('darkMode', newIsDarkMode);

            if (newIsDarkMode) {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
        });

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', loadTheme);


        // Use GSAP for a subtle intro animation
        gsap.fromTo(".rounded-2xl", {
            opacity: 0,
            scale: 0.95
        }, {
            opacity: 1,
            scale: 1,
            duration: 0.8,
            ease: "power2.out"
        });
    </script>
</body>
</html>