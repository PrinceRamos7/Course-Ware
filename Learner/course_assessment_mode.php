<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Mode Selector | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.6;
            padding:0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            background-color: var(--color-header-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-card-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .mode-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--color-card-border);
            overflow: hidden;
            position: relative;
        }

        .mode-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-progress-fill);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .mode-card.selected::before {
            transform: scaleX(1);
        }

        .mode-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .mode-card.selected {
            border-color: var(--color-heading);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .training-highlight {
            background: linear-gradient(135deg, var(--color-card-section-bg) 0%, #f0fdf4 100%);
        }

        .testing-highlight {
            background: linear-gradient(135deg, #fef7cd 0%, #fefce8 100%);
        }

        .icon-container {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .feature-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .training-feature .feature-icon {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--color-heading);
        }

        .testing-feature .feature-icon {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-heading-secondary);
        }

        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-training {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--color-heading);
        }

        .badge-testing {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-heading-secondary);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-4">
        <div class="container">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full object-contain flex items-center justify-center" style="background-color: var(--color-heading);">
                        <img src="../images/isu-logo.png" alt="">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold" style="color: var(--color-heading);">ISU Learning Platform</h1>
                        <p class="text-xs" style="color: var(--color-text-secondary);">Mastery Through Practice</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 px-4 py-2 rounded-lg" style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle" style="color: var(--color-heading);"></i>
                        <span class="font-medium" style="color: var(--color-user-text);">Ryuta</span>
                        <span class="px-2 py-1 rounded-full text-xs font-bold" style="background-color: var(--color-xp-bg); color: var(--color-xp-text);">Level 12</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-12">
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="text-center mb-16 fade-in">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--color-heading);">Select Learning Mode</h2>
                    <p class="text-lg max-w-2xl mx-auto" style="color: var(--color-text-secondary);">
                        Choose the learning approach that best fits your current goals and preferences
                    </p>
                </div>

                <!-- Mode Selection Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-2 gap-8 mb-12">
                    <!-- Training Mode Card -->
                    <div class="mode-card bg-[var(--color-card-bg)] rounded-xl p-8 cursor-pointer fade-in" id="training-card">
                        <div class="flex flex-col h-full">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-6">
                                <div class="icon-container" style="background-color: rgba(34, 197, 94, 0.1);">
                                    <i class="fas fa-graduation-cap text-2xl" style="color: var(--color-heading);"></i>
                                </div>
                                <span class="badge badge-training">
                                    <i class="fas fa-star mr-1"></i> Recommended for Beginners
                                </span>
                            </div>
                            
                            <!-- Card Content -->
                            <h3 class="text-2xl font-bold mb-4" style="color: var(--color-heading);">Training Mode</h3>
                            <p class="mb-6 flex-1" style="color: var(--color-text);">
                                Build foundational knowledge with guided learning, step-by-step explanations, and unlimited practice opportunities.
                            </p>
                            
                            <!-- Features -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-4 text-sm uppercase tracking-wide" style="color: var(--color-text-secondary);">Key Features</h4>
                                <div class="space-y-3">
                                    <div class="feature-item training-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Step-by-step guided learning</span>
                                    </div>
                                    <div class="feature-item training-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Unlimited practice attempts</span>
                                    </div>
                                    <div class="feature-item training-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Detailed explanations & hints</span>
                                    </div>
                                    <div class="feature-item training-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Progress tracking & analytics</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="mt-auto pt-6 border-t" style="border-color: var(--color-card-border);">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Best For</p>
                                        <p class="font-semibold" style="color: var(--color-heading);">Knowledge Building</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Pace</p>
                                        <p class="font-semibold" style="color: var(--color-heading);">Self-Directed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testing Mode Card -->
                    <div class="mode-card bg-[var(--color-card-bg)] rounded-xl p-8 cursor-pointer fade-in" id="testing-card">
                        <div class="flex flex-col h-full">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-6">
                                <div class="icon-container" style="background-color: rgba(249, 115, 22, 0.1);">
                                    <i class="fas fa-clipboard-check text-2xl" style="color: var(--color-heading-secondary);"></i>
                                </div>
                                <span class="badge badge-testing">
                                    <i class="fas fa-bolt mr-1"></i> Performance Evaluation
                                </span>
                            </div>
                            
                            <!-- Card Content -->
                            <h3 class="text-2xl font-bold mb-4" style="color: var(--color-heading-secondary);">Testing Mode</h3>
                            <p class="mb-6 flex-1" style="color: var(--color-text);">
                                Assess your knowledge under realistic conditions with timed assessments and performance-based scoring.
                            </p>
                            
                            <!-- Features -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-4 text-sm uppercase tracking-wide" style="color: var(--color-text-secondary);">Key Features</h4>
                                <div class="space-y-3">
                                    <div class="feature-item testing-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Timed assessment conditions</span>
                                    </div>
                                    <div class="feature-item testing-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Limited attempt simulations</span>
                                    </div>
                                    <div class="feature-item testing-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Performance analytics & scoring</span>
                                    </div>
                                    <div class="feature-item testing-feature">
                                        <div class="feature-icon">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <span style="color: var(--color-text);">Competency benchmarking</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="mt-auto pt-6 border-t" style="border-color: var(--color-card-border);">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Best For</p>
                                        <p class="font-semibold" style="color: var(--color-heading-secondary);">Skill Assessment</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium mb-1" style="color: var(--color-text-secondary);">Pace</p>
                                        <p class="font-semibold" style="color: var(--color-heading-secondary);">Timed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Continue Button -->
                <div class="text-center fade-in">
                    <button id="continue-btn" class="btn-primary py-4 px-10 rounded-xl text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300" disabled>
                        <span id="btn-text">Select a Learning Mode</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <p class="text-sm mt-4" style="color: var(--color-text-secondary);">
                        You can switch between modes at any time from your dashboard
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 border-t" style="border-color: var(--color-card-border);">
        <div class="container">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm" style="color: var(--color-text-secondary);">
                        &copy; 2023 ISU Learning Platform. All rights reserved.
                    </p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Privacy Policy</a>
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Terms of Service</a>
                    <a href="#" class="text-sm hover:underline" style="color: var(--color-text-secondary);">Help Center</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        
        document.addEventListener('DOMContentLoaded', function() {
            const trainingCard = document.getElementById('training-card');
            const testingCard = document.getElementById('testing-card');
            const continueBtn = document.getElementById('continue-btn');
            const btnText = document.getElementById('btn-text');
            
            let selectedMode = null;

            // Training card selection
            trainingCard.addEventListener('click', function() {
                selectedMode = 'training';
                updateSelection();
                updateContinueButton();
            });

            // Testing card selection
            testingCard.addEventListener('click', function() {
                selectedMode = 'testing';
                updateSelection();
                updateContinueButton();
            });

            // Update visual selection state
            function updateSelection() {
                // Remove selected class from both cards
                trainingCard.classList.remove('selected', 'training-highlight');
                testingCard.classList.remove('selected', 'testing-highlight');
                
                // Add selected class to chosen card
                if (selectedMode === 'training') {
                    trainingCard.classList.add('selected', 'training-highlight');
                } else if (selectedMode === 'testing') {
                    testingCard.classList.add('selected', 'testing-highlight');
                }
            }

            // Update continue button based on selection
            function updateContinueButton() {
                if (selectedMode) {
                    continueBtn.disabled = false;
                    
                    if (selectedMode === 'training') {
                        btnText.textContent = 'Continue to Training Mode';
                    } else {
                        btnText.textContent = 'Continue to Testing Mode';
                    }
                    
                    // Add pulse animation for emphasis
                    continueBtn.classList.add('pulse');
                    setTimeout(() => {
                        continueBtn.classList.remove('pulse');
                    }, 2000);
                }
            }

            // Continue button functionality
            continueBtn.addEventListener('click', function() {
                if (selectedMode) {
                     if (selectedMode) {
                    if (selectedMode === 'training') {
                        window.location.href = "training_confirmation.php";
                    } else if (selectedMode === 'testing') {
                        window.location.href = "testing_confirmation.php";
                    }
                }
                }
            });
        });
    </script>
</body>
</html>