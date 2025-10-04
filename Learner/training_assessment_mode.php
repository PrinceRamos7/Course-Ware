<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Training | ISU Learning Platform</title>
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
            --color-explanation-bg: #f0fdf4;
            --color-hint-bg: #fffbeb;
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
            line-height: 1.4;
        }

        .container {
            max-width: 1400px;
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

        .training-card {
            background-color: var(--color-card-bg);
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--color-card-border);
        }

        .option-item {
            border: 1.5px solid var(--color-card-border);
            border-radius: 5px;
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

        .option-item.correct {
            border-color: var(--color-correct);
            background-color: rgba(34, 197, 94, 0.1);
        }

        .option-item.incorrect {
            border-color: var(--color-incorrect);
            background-color: rgba(220, 38, 38, 0.05);
        }

        .option-indicator {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
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

        .option-indicator.correct {
            background-color: var(--color-correct);
            color: white;
            border-color: var(--color-correct);
        }

        .option-indicator.incorrect {
            background-color: var(--color-incorrect);
            color: white;
            border-color: var(--color-incorrect);
        }

        .explanation-panel {
            background-color: var(--color-explanation-bg);
            border-radius: 5px;
            border-left: 3px solid var(--color-heading);
            animation: slideIn 0.3s ease-out;
        }

        .hint-panel {
            background-color: var(--color-hint-bg);
            border-radius: 5px;
            border-left: 3px solid var(--color-heading-secondary);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.2s ease;
            font-weight: 600;
            border: none;
            font-size: 0.875rem;
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
            font-size: 0.875rem;
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
            padding: 0.2rem 0.6rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, #f8fafc 100%);
            border: 1px solid var(--color-card-border);
            border-radius: 5px;
            padding: 0.75rem;
        }

        .compact-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0.4rem;
        }

        .module-nav {
            width: 32px;
            height: 32px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1.5px solid var(--color-card-border);
        }

        .module-nav:hover {
            border-color: var(--color-heading);
        }

        .module-nav.current {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .module-nav.completed {
            background-color: var(--color-button-primary);
            color: white;
            border-color: var(--color-button-primary);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-2">
        <div class="container">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded flex items-center justify-center" style="background-color: var(--color-heading);">
                        <i class="fas fa-graduation-cap text-white text-xs"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold" style="color: var(--color-heading);">Professional Training Module</h1>
                        <p class="text-xs" style="color: var(--color-text-secondary);">Database Security & Architecture</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 bg-gray-50 px-2 py-1 rounded text-sm">
                        <i class="fas fa-clock text-xs" style="color: var(--color-heading);"></i>
                        <span style="color: var(--color-text-secondary);">Self-paced</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-2 py-1 rounded text-sm" style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-xs" style="color: var(--color-heading);"></i>
                        <span class="font-medium" style="color: var(--color-user-text);">Learner: Ryuta</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-3">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-3">
                    <!-- Module Navigation -->
                    <div class="training-card p-3">
                        <h3 class="font-semibold text-sm mb-2" style="color: var(--color-text);">Module Progress</h3>
                        <div class="compact-grid mb-3">
                            <div class="module-nav completed">1</div>
                            <div class="module-nav completed">2</div>
                            <div class="module-nav current">3</div>
                            <div class="module-nav">4</div>
                            <div class="module-nav">5</div>
                            <div class="module-nav">6</div>
                            <div class="module-nav">7</div>
                            <div class="module-nav">8</div>
                            <div class="module-nav">9</div>
                            <div class="module-nav">10</div>
                            <div class="module-nav">11</div>
                            <div class="module-nav">12</div>
                        </div>
                        
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Progress:</span>
                                <span class="font-semibold" style="color: var(--color-heading);">2/12</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 17%"></div>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Mastery:</span>
                                <span class="font-semibold" style="color: var(--color-heading);">85%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="training-card p-3">
                        <h4 class="font-semibold text-sm mb-2" style="color: var(--color-text);">Learning Metrics</h4>
                        <div class="stats-grid">
                            <div class="stat-card text-center">
                                <div class="text-base font-bold mb-1" style="color: var(--color-heading);">85%</div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Accuracy</div>
                            </div>
                            <div class="stat-card text-center">
                                <div class="text-base font-bold mb-1" style="color: var(--color-heading-secondary);">12</div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Completed</div>
                            </div>
                            <div class="stat-card text-center">
                                <div class="text-base font-bold mb-1" style="color: var(--color-icon);">92%</div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Retention</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hint Toggle -->
                    <div class="training-card p-3">
                        <button id="hint-toggle" class="btn-secondary w-full py-2 rounded text-sm font-medium">
                            <i class="fas fa-lightbulb mr-1"></i> Show Learning Hint
                        </button>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-3 space-y-3">
                    <!-- Hint Panel -->
                    <div id="hint-panel" class="hint-panel p-3 hidden">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-lightbulb mt-0.5 text-sm" style="color: var(--color-heading-secondary);"></i>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="font-semibold text-sm" style="color: var(--color-heading-secondary);">Learning Guidance</h4>
                                    <button id="close-hint" class="text-gray-500 hover:text-gray-700 text-xs">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="text-xs" style="color: var(--color-text);">
                                    Consider the <strong>security implementation layer</strong> (database vs application), <strong>scalability requirements</strong>, and <strong>operational complexity</strong> when evaluating multi-tenant data isolation strategies.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Question Card -->
                    <div class="training-card p-4">
                        <!-- Question Header -->
                        <div class="flex justify-between items-start mb-4 pb-3 border-b" style="border-color: var(--color-card-border);">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="status-badge" style="background-color: var(--color-heading); color: white;">Module 3</span>
                                    <span class="status-badge" style="background-color: rgba(234, 179, 8, 0.1); color: var(--color-icon);">
                                        <i class="fas fa-database mr-1"></i> Advanced
                                    </span>
                                </div>
                                <h2 class="text-base font-semibold" style="color: var(--color-text);">
                                    Multi-tenant SaaS Data Isolation Strategies
                                </h2>
                            </div>
                            <div class="text-right text-xs" style="color: var(--color-text-secondary);">
                                <div>Unlimited attempts</div>
                                <div>Step-by-step guidance</div>
                            </div>
                        </div>

                        <!-- Question Content -->
                        <div class="mb-4">
                            <p class="text-sm mb-3" style="color: var(--color-text);">
                                In a multi-tenant SaaS application handling sensitive financial data, which approach provides the optimal balance between security isolation and operational scalability?
                            </p>

                            <!-- Options -->
                            <div class="space-y-2">
                                <div class="option-item p-2" data-option="A" data-correct="false">
                                    <div class="flex items-center space-x-2">
                                        <div class="option-indicator default">A</div>
                                        <div class="flex-1">
                                            <p class="text-sm" style="color: var(--color-text);">Row-level security with tenant_id predicates</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-xs" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Uses database-level security policies but can be bypassed with direct access. Requires careful implementation to prevent security gaps.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-2" data-option="B" data-correct="false">
                                    <div class="flex items-center space-x-2">
                                        <div class="option-indicator default">B</div>
                                        <div class="flex-1">
                                            <p class="text-sm" style="color: var(--color-text);">Separate database instances per tenant</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-xs" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Maximum security isolation but expensive to scale and maintain. High operational overhead.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-2" data-option="C" data-correct="true">
                                    <div class="flex items-center space-x-2">
                                        <div class="option-indicator default">C</div>
                                        <div class="flex-1">
                                            <p class="text-sm" style="color: var(--color-text);">Schema-level separation within shared database</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-xs" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> <strong style="color: var(--color-correct);">Optimal approach.</strong> Provides strong security isolation while maintaining reasonable scalability and manageability.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-2" data-option="D" data-correct="false">
                                    <div class="flex items-center space-x-2">
                                        <div class="option-indicator default">D</div>
                                        <div class="flex-1">
                                            <p class="text-sm" style="color: var(--color-text);">Application-level filtering with audit logging</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-xs" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Least secure approach. Vulnerable to coding errors and doesn't provide database-level protection.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center pt-3 border-t" style="border-color: var(--color-card-border);">
                            <div class="text-xs" style="color: var(--color-text-secondary);">
                                <i class="fas fa-info-circle mr-1"></i> Select an option to check your understanding
                            </div>
                            
                            <div class="flex space-x-2">
                                <button id="submit-btn" class="btn-primary px-3 py-2 rounded text-sm font-medium">
                                    <i class="fas fa-check mr-1"></i> Check Answer
                                </button>
                                <button id="next-btn" class="btn-primary px-3 py-2 rounded text-sm font-medium hidden">
                                    <i class="fas fa-arrow-right mr-1"></i> Continue
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Explanation Panel -->
                    <div id="explanation-panel" class="explanation-panel p-3 hidden">
                        <div class="flex items-start space-x-2 mb-2">
                            <i class="fas fa-check-circle mt-0.5 text-sm" style="color: var(--color-correct);"></i>
                            <div>
                                <h4 class="font-semibold text-sm mb-1" style="color: var(--color-correct);">Correct Answer Analysis</h4>
                                <p class="text-xs" style="color: var(--color-text);">
                                    Schema-level separation provides the optimal balance between security, performance, and maintainability.
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                            <div class="p-2 rounded" style="background-color: rgba(34, 197, 94, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-correct);">Advantages</h5>
                                <ul class="space-y-1" style="color: var(--color-text);">
                                    <li>• Strong database-level security</li>
                                    <li>• Better scalability than separate DBs</li>
                                    <li>• More maintainable than row security</li>
                                </ul>
                            </div>
                            <div class="p-2 rounded" style="background-color: rgba(249, 115, 22, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-heading-secondary);">Considerations</h5>
                                <ul class="space-y-1" style="color: var(--color-text);">
                                    <li>• Requires schema management</li>
                                    <li>• Backup strategy planning</li>
                                    <li>• Permission configuration</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Learning Objectives -->
                    <div class="training-card p-3">
                        <h4 class="font-semibold text-sm mb-2" style="color: var(--color-text);">Learning Objectives</h4>
                        <ul class="text-xs space-y-1" style="color: var(--color-text-secondary);">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle mr-2 text-xs" style="color: var(--color-correct);"></i>
                                Understand multi-tenant data isolation strategies
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle mr-2 text-xs" style="color: var(--color-correct);"></i>
                                Evaluate security vs scalability trade-offs
                            </li>
                            <li class="flex items-center">
                                <i class="far fa-circle mr-2 text-xs" style="color: var(--color-text-secondary);"></i>
                                Implement schema-level security patterns
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionItems = document.querySelectorAll('.option-item');
            const submitBtn = document.getElementById('submit-btn');
            const nextBtn = document.getElementById('next-btn');
            const hintToggle = document.getElementById('hint-toggle');
            const hintPanel = document.getElementById('hint-panel');
            const closeHint = document.getElementById('close-hint');
            const explanationPanel = document.getElementById('explanation-panel');
            
            let selectedOption = null;
            let answerSubmitted = false;
            let hintVisible = false;

            // Option selection
            optionItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (answerSubmitted) return;
                    
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
                    alert('Please select an answer to check your understanding.');
                    return;
                }
                
                answerSubmitted = true;
                
                // Show explanations for all options
                optionItems.forEach(item => {
                    const isCorrect = item.getAttribute('data-correct') === 'true';
                    const indicator = item.querySelector('.option-indicator');
                    const explanation = item.querySelector('.explanation-content');
                    
                    explanation.classList.remove('hidden');
                    
                    if (isCorrect) {
                        item.classList.add('correct');
                        indicator.classList.remove('default', 'selected');
                        indicator.classList.add('correct');
                    } else if (item.getAttribute('data-option') === selectedOption) {
                        item.classList.add('incorrect');
                        indicator.classList.remove('default', 'selected');
                        indicator.classList.add('incorrect');
                    }
                });
                
                // Show explanation panel
                explanationPanel.classList.remove('hidden');
                
                // Update buttons
                submitBtn.classList.add('hidden');
                nextBtn.classList.remove('hidden');
            });

            // Hint functionality
            hintToggle.addEventListener('click', function() {
                if (hintVisible) {
                    hintPanel.classList.add('hidden');
                    hintToggle.innerHTML = '<i class="fas fa-lightbulb mr-1"></i> Show Learning Hint';
                } else {
                    hintPanel.classList.remove('hidden');
                    hintToggle.innerHTML = '<i class="fas fa-times mr-1"></i> Hide Hint';
                }
                hintVisible = !hintVisible;
            });

            closeHint.addEventListener('click', function() {
                hintPanel.classList.add('hidden');
                hintToggle.innerHTML = '<i class="fas fa-lightbulb mr-1"></i> Show Learning Hint';
                hintVisible = false;
            });

            // Next question
            nextBtn.addEventListener('click', function() {
                // In a real app, this would load the next question
                alert('Loading next learning module...');
            });
        });
    </script>
</body>
</html>