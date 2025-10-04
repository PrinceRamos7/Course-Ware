<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Assessment | ISU Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-main-bg: #fefce8;
            --color-card-bg: #ffffff;
            --color-header-bg: #ffffff;
            --color-heading: #15803d;
            --color-heading-secondary: #f97316;
            --color-text: #0f172a;
            --color-text-secondary: #475569;
            --color-button-primary: #22c55e;
            --color-button-primary-hover: #16a34a;
            --color-button-secondary: #fde68a;
            --color-button-secondary-text: #92400e;
            --color-green-button: #22c55e;
            --color-green-button-hover: #16a34a;
            --color-icon: #eab308;
            --color-card-border: #e5e7eb;
            --color-popup-bg: rgba(0, 0, 0, 0.5);
            --color-popup-content-bg: rgba(255, 255, 255, 0.95);
            --color-toggle-bg: #dcfce7;
            --color-toggle-handle: #22c55e;
            --color-xp-bg: #fef08a;
            --color-xp-text: #ca8a04;
            --color-user-bg: #f9fafb;
            --color-user-text: #0f172a;
            --color-card-section-bg: #ecfccb;
            --color-text-on-section: #14532d;
            --color-profile-bg: linear-gradient(to right, #22c55e, #f59e0b);
            --color-progress-bg: #e5e7eb;
            --color-progress-fill: linear-gradient(to right, #22c55e, #facc15, #f97316);
            --color-card-section-border: #d1d5db;
            --color-sidebar-bg: #fefce8;
            --color-sidebar-border: #facc15;
            --color-sidebar-text: #166534;
            --color-sidebar-text-active: #f97316;
            --color-sidebar-icon: #6b7280;
            --color-sidebar-icon-active: #22c55e;
            --color-input-bg: #ffffff;
            --color-input-border: #d1d5db;
            --color-input-text: #0f172a;
            --color-input-placeholder: #6b7280;
            --color-correct: #16a34a;
            --color-incorrect: #dc2626;
            --color-warning: #d97706;
            --color-time-critical: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header {
            background-color: var(--color-header-bg);
            border-bottom: 1px solid var(--color-card-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .exam-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-card-border);
        }

        .question-panel {
            border-right: 1px solid var(--color-card-border);
        }

        .option-item {
            border: 1.5px solid var(--color-card-border);
            border-radius: 6px;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .option-item:hover {
            border-color: var(--color-heading-secondary);
            background-color: rgba(249, 115, 22, 0.02);
        }

        .option-item.selected {
            border-color: var(--color-heading);
            background-color: rgba(34, 197, 94, 0.05);
        }

        .option-indicator {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1.5px solid var(--color-card-border);
            flex-shrink: 0;
        }

        .option-indicator.default {
            background-color: transparent;
            color: var(--color-text);
        }

        .option-indicator.selected {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .question-nav {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1.5px solid var(--color-card-border);
        }

        .question-nav:hover {
            border-color: var(--color-heading);
        }

        .question-nav.current {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .question-nav.answered {
            background-color: var(--color-button-primary);
            color: white;
            border-color: var(--color-button-primary);
        }

        .question-nav.flagged {
            background-color: var(--color-warning);
            color: white;
            border-color: var(--color-warning);
        }

        .timer-critical {
            animation: pulse 1s infinite;
            color: var(--color-time-critical);
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.2s ease;
            font-weight: 600;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--color-text);
            border: 1.5px solid var(--color-card-border);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: var(--color-card-border);
        }

        .progress-bar {
            height: 4px;
            border-radius: 2px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-progress-fill);
            transition: width 0.3s ease;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .compact-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-3">
        <div class="container">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: var(--color-heading);">
                        <i class="fas fa-clipboard-check text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold" style="color: var(--color-heading);">Professional Certification Assessment</h1>
                        <p class="text-xs" style="color: var(--color-text-secondary);">Database Security Specialist - Section 2</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Timer -->
                    <div class="flex items-center space-x-2 bg-gray-50 px-3 py-2 rounded-lg">
                        <i class="fas fa-clock text-sm" style="color: var(--color-heading);"></i>
                        <span id="timer" class="font-mono font-bold" style="color: var(--color-heading);">44:32</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-3 py-1 rounded-lg text-sm" style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-sm" style="color: var(--color-heading);"></i>
                        <span class="font-medium" style="color: var(--color-user-text);">Candidate: Ryuta</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <!-- Navigation Panel -->
                <div class="lg:col-span-1">
                    <div class="exam-container p-4">
                        <div class="mb-4">
                            <h3 class="font-semibold text-sm mb-3 uppercase tracking-wide" style="color: var(--color-text-secondary);">Question Navigation</h3>
                            <div class="compact-grid">
                                <div class="question-nav current">1</div>
                                <div class="question-nav answered">2</div>
                                <div class="question-nav flagged">3</div>
                                <div class="question-nav">4</div>
                                <div class="question-nav">5</div>
                                <div class="question-nav">6</div>
                                <div class="question-nav">7</div>
                                <div class="question-nav">8</div>
                                <div class="question-nav">9</div>
                                <div class="question-nav">10</div>
                                <div class="question-nav">11</div>
                                <div class="question-nav">12</div>
                                <div class="question-nav">13</div>
                                <div class="question-nav">14</div>
                                <div class="question-nav">15</div>
                                <div class="question-nav">16</div>
                                <div class="question-nav">17</div>
                                <div class="question-nav">18</div>
                                <div class="question-nav">19</div>
                                <div class="question-nav">20</div>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span style="color: var(--color-text-secondary);">Progress</span>
                                <span class="font-semibold" style="color: var(--color-heading);">2/20</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 10%"></div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span style="color: var(--color-text-secondary);">Answered</span>
                                    <span class="font-semibold ml-1" style="color: var(--color-heading);">2</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                                    <span style="color: var(--color-text-secondary);">Flagged</span>
                                    <span class="font-semibold ml-1" style="color: var(--color-heading-secondary);">1</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <button class="btn-secondary w-full py-2 rounded text-sm font-medium">
                                <i class="fas fa-flag mr-1"></i> Flag Current
                            </button>
                            <button class="btn-primary w-full py-2 rounded text-sm font-medium">
                                <i class="fas fa-paper-plane mr-1"></i> Submit Exam
                            </button>
                        </div>
                    </div>

                    <!-- Time Stats -->
                    <div class="exam-container p-4 mt-4">
                        <h4 class="font-semibold text-sm mb-3" style="color: var(--color-text);">Time Management</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Elapsed:</span>
                                <span class="font-semibold" style="color: var(--color-heading);">0:28</span>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Remaining:</span>
                                <span class="font-semibold" style="color: var(--color-heading);">44:32</span>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Avg/Question:</span>
                                <span class="font-semibold" style="color: var(--color-heading);">2:14</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Panel -->
                <div class="lg:col-span-3">
                    <div class="exam-container p-6">
                        <!-- Question Header -->
                        <div class="flex justify-between items-start mb-6 pb-4 border-b" style="border-color: var(--color-card-border);">
                            <div>
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="status-badge" style="background-color: var(--color-heading); color: white;">Question 3</span>
                                    <span class="status-badge" style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                        <i class="fas fa-flag mr-1"></i> Flagged
                                    </span>
                                </div>
                                <h2 class="text-lg font-semibold" style="color: var(--color-text);">
                                    Multi-tenant data isolation security assessment
                                </h2>
                            </div>
                            <div class="text-right">
                                <div class="text-sm" style="color: var(--color-text-secondary);">Points: 1</div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Difficulty: Advanced</div>
                            </div>
                        </div>

                        <!-- Question Content -->
                        <div class="mb-6">
                            <p class="text-sm mb-4" style="color: var(--color-text);">
                                In a multi-tenant SaaS application handling sensitive financial data, which approach provides the optimal balance between security isolation and operational scalability while maintaining compliance with data protection regulations?
                            </p>

                            <!-- Options -->
                            <div class="space-y-3">
                                <div class="option-item p-3" data-option="A">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">A</div>
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--color-text);">Row-level security with tenant_id predicates</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="B">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">B</div>
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--color-text);">Separate database instances per tenant</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="C">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">C</div>
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--color-text);">Schema-level separation within shared database</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="D">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">D</div>
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--color-text);">Application-level filtering with audit logging</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex space-x-2">
                                <button class="btn-secondary px-4 py-2 rounded text-sm font-medium">
                                    <i class="fas fa-flag mr-1"></i> Flag Question
                                </button>
                                <button class="btn-secondary px-4 py-2 rounded text-sm font-medium">
                                    <i class="fas fa-comment mr-1"></i> Add Note
                                </button>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button class="btn-secondary px-4 py-2 rounded text-sm font-medium">
                                    Previous
                                </button>
                                <button id="submit-answer" class="btn-primary px-4 py-2 rounded text-sm font-medium">
                                    Submit Answer
                                </button>
                                <button class="btn-secondary px-4 py-2 rounded text-sm font-medium">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Instructions -->
                    <div class="exam-container p-4 mt-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle mt-0.5" style="color: var(--color-heading);"></i>
                            <div>
                                <h4 class="font-semibold text-sm mb-1" style="color: var(--color-text);">Assessment Guidelines</h4>
                                <p class="text-xs" style="color: var(--color-text-secondary);">
                                    • Time limit: 45 minutes • No backtracking allowed • Single attempt per question • All questions must be answered •
                                    Results available immediately after submission • Passing score: 75% or higher
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionItems = document.querySelectorAll('.option-item');
            const submitBtn = document.getElementById('submit-answer');
            const timerElement = document.getElementById('timer');
            
            let selectedOption = null;
            let examTime = 45 * 60; // 45 minutes in seconds
            let timerInterval;

            // Initialize timer
            function startTimer() {
                updateTimerDisplay();
                timerInterval = setInterval(function() {
                    examTime--;
                    updateTimerDisplay();
                    
                    if (examTime <= 0) {
                        clearInterval(timerInterval);
                        autoSubmitExam();
                    } else if (examTime <= 300) { // 5 minutes remaining
                        timerElement.classList.add('timer-critical');
                    }
                }, 1000);
            }

            function updateTimerDisplay() {
                const minutes = Math.floor(examTime / 60);
                const seconds = examTime % 60;
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }

            function autoSubmitExam() {
                alert('Time is up! Your exam has been automatically submitted.');
                // In real implementation, submit the exam
            }

            // Option selection
            optionItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove selected class from all options
                    optionItems.forEach(i => {
                        i.classList.remove('selected');
                        i.querySelector('.option-indicator').classList.remove('selected');
                        i.querySelector('.option-indicator').classList.add('default');
                    });
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    const indicator = this.querySelector('.option-indicator');
                    indicator.classList.remove('default');
                    indicator.classList.add('selected');
                    
                    selectedOption = this.getAttribute('data-option');
                });
            });

            // Submit answer
            submitBtn.addEventListener('click', function() {
                if (!selectedOption) {
                    alert('Please select an answer before submitting.');
                    return;
                }
                
                // In a real exam, this would save the answer and load next question
                alert('Answer submitted. Loading next question...');
                
                // Reset selection for next question
                optionItems.forEach(item => {
                    item.classList.remove('selected');
                    item.querySelector('.option-indicator').classList.remove('selected');
                    item.querySelector('.option-indicator').classList.add('default');
                });
                selectedOption = null;
            });

            // Start the timer when page loads
            startTimer();
        });
    </script>
</body>
</html>