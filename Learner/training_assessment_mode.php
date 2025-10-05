<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Training | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: 'Inter', sans-serif;
            padding:0; 
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

        /* --- STICKY NAVIGATION IMPROVEMENT --- */

        .sidebar-sticky-wrapper {
            /* This is the key change. Apply sticky positioning to the sidebar wrapper. */
            position: sticky;
            top: 70px; /* Adjust this value to clear the fixed header (Header height + padding) */
            align-self: flex-start; /* Important for sticky positioning inside a grid item */
        }

        .module-nav-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); /* Auto-fill with a minimum size */
            gap: 0.5rem; /* Increased gap */
            margin-bottom: 0.75rem;
        }

        /* Increased size and font for module links */
        .module-nav {
            width: 40px; /* Increased width */
            height: 40px; /* Increased height */
            border-radius: 6px; /* Slightly larger border radius */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem; /* Increased font size for better readability */
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

        /* Style for pagination controls */
        .pagination-control {
            padding: 0.5rem 0.75rem;
            border-radius: 5px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            border: 1px solid var(--color-card-border);
        }

        .pagination-control:hover:not(.disabled) {
            background-color: var(--color-card-border);
        }

        .pagination-control.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Adjustments for better visual hierarchy */
        .text-base { font-size: 1rem; }
        .text-xl { font-size: 1.25rem; }
    </style>
</head>
<body>
    <header class="top-0 sticky z-10 shadow-md py-4" style="background-color: var(--color-header-bg);">
        <div class="container">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full object-contain flex items-center justify-center" style="background-color: var(--color-heading);">
                        <img src="../images/isu-logo.png" alt="error">
                    </div>
                    <div>
                        <h1 class="text-base font-bold" style="color: var(--color-heading);">ISUtoLearn Training Module</h1>
                        <p class="text-xs" style="color: var(--color-text-secondary);">Database</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 bg-[var(--color-card-bg)] px-2 py-1 rounded text-sm">
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

    <main class="py-3">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-1 space-y-3 sidebar-sticky-wrapper"> 
                    <div class="training-card p-3">
                        <h3 class="font-semibold text-base mb-3" style="color: var(--color-text);">Module Progress</h3>
                        
                        <div class="flex justify-between items-center mb-2">
                            <button id="prev-page-btn" class="pagination-control text-sm" style="color: var(--color-text); border-color: var(--color-card-border);">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <span id="page-info" class="text-sm font-medium" style="color: var(--color-text-secondary);">Page 1 of 2</span>
                            <button id="next-page-btn" class="pagination-control text-sm" style="color: var(--color-text); border-color: var(--color-card-border);">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div id="module-nav-container" class="module-nav-content">
                            </div>
                        
                        <div class="space-y-2 text-sm pt-3 border-t" style="border-color: var(--color-card-border);">
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

                    <div class="training-card p-3">
                        <button id="hint-toggle" class="btn-secondary w-full py-2 rounded text-sm font-medium">
                            <i class="fas fa-lightbulb mr-1"></i> Show Learning Hint
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-3 space-y-3">
                    <div id="hint-panel" class="hint-panel p-3 hidden border-2 border-[var(--color-card-border)]">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-lightbulb mt-0.5 text-sm" style="color: var(--color-heading-secondary);"></i>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="font-semibold text-sm" style="color: var(--color-heading-secondary);">Learning Guidance</h4>
                                    <button id="close-hint" class="text-gray-500 hover:text-gray-700 text-xs">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="text-sm" style="color: var(--color-text);">
                                    Consider the <strong>security implementation layer</strong> (database vs application), <strong>scalability requirements</strong>, and <strong>operational complexity</strong> when evaluating multi-tenant data isolation strategies.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="training-card p-4">
                        <div class="flex justify-between items-start mb-4 pb-3 border-b" style="border-color: var(--color-card-border);">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="status-badge" style="background-color: var(--color-heading); color: white;">Module 3</span>
                                    <span class="status-badge" style="background-color: rgba(234, 179, 8, 0.1); color: var(--color-icon);">
                                        <i class="fas fa-database mr-1"></i> Advanced
                                    </span>
                                </div>
                                <h2 class="text-xl font-semibold" style="color: var(--color-text);">
                                    Multi-tenant SaaS Data Isolation Strategies
                                </h2>
                            </div>
                            <div class="text-right text-xs" style="color: var(--color-text-secondary);">
                                <div>Unlimited attempts</div>
                                <div>Step-by-step guidance</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-base mb-4" style="color: var(--color-text);">
                                In a multi-tenant SaaS application handling sensitive financial data, which approach provides the optimal balance between security isolation and operational scalability?
                            </p>

                            <div class="space-y-3">
                                <div class="option-item p-3" data-option="A" data-correct="false">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">A</div>
                                        <div class="flex-1">
                                            <p class="text-base" style="color: var(--color-text);">Row-level security with tenant\_id predicates</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-sm" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Uses database-level security policies but can be bypassed with direct access. Requires careful implementation to prevent security gaps.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="B" data-correct="false">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">B</div>
                                        <div class="flex-1">
                                            <p class="text-base" style="color: var(--color-text);">Separate database instances per tenant</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-sm" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Maximum security isolation but expensive to scale and maintain. High operational overhead.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="C" data-correct="true">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">C</div>
                                        <div class="flex-1">
                                            <p class="text-base" style="color: var(--color-text);">Schema-level separation within shared database</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-sm" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> <strong style="color: var(--color-correct);">Optimal approach.</strong> Provides strong security isolation while maintaining reasonable scalability and manageability.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-item p-3" data-option="D" data-correct="false">
                                    <div class="flex items-center space-x-3">
                                        <div class="option-indicator default">D</div>
                                        <div class="flex-1">
                                            <p class="text-base" style="color: var(--color-text);">Application-level filtering with audit logging</p>
                                            <div class="explanation-content hidden mt-2 p-2 rounded text-sm" style="background-color: rgba(0,0,0,0.03);">
                                                <strong>Analysis:</strong> Least secure approach. Vulnerable to coding errors and doesn't provide database-level protection.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t" style="border-color: var(--color-card-border);">
                            <div class="text-xs" style="color: var(--color-text-secondary);">
                                <i class="fas fa-info-circle mr-1"></i> Select an option to check your understanding
                            </div>
                            
                            <div class="flex space-x-2">
                                <button id="submit-btn" class="btn-primary px-4 py-2 rounded text-base font-medium">
                                    <i class="fas fa-check mr-1"></i> Check Answer
                                </button>
                                <button id="next-btn" class="btn-primary px-4 py-2 rounded text-base font-medium hidden">
                                    <i class="fas fa-arrow-right mr-1"></i> Continue
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="explanation-panel" class="explanation-panel p-4 hidden border-2 border-[var(--color-card-border)]">
                        <div class="flex items-start space-x-3 mb-3">
                            <i class="fas fa-check-circle mt-0.5 text-xl" style="color: var(--color-correct);"></i>
                            <div>
                                <h4 class="font-semibold text-lg mb-1" style="color: var(--color-correct);">Correct Answer Analysis</h4>
                                <p class="text-sm" style="color: var(--color-text);">
                                    Schema-level separation provides the optimal balance between security, performance, and maintainability for sensitive data in a scalable multi-tenant environment.
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="p-3 rounded" style="background-color: rgba(34, 197, 94, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-correct);">Advantages (Option C)</h5>
                                <ul class="space-y-1 pl-4 list-disc" style="color: var(--color-text);">
                                    <li>Strong database-level security and tenant isolation</li>
                                    <li>High scalability and efficient resource sharing</li>
                                    <li>Lower operational complexity than separate DB instances</li>
                                </ul>
                            </div>
                            <div class="p-3 rounded" style="background-color: rgba(249, 115, 22, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-heading-secondary);">Key Considerations</h5>
                                <ul class="space-y-1 pl-4 list-disc" style="color: var(--color-text);">
                                    <li>Careful schema and database user permission management</li>
                                    <li>Requires an efficient centralized backup strategy</li>
                                    <li>Slightly more complex querying than a single-table approach</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="training-card p-4">
                        <h4 class="font-semibold text-base mb-3" style="color: var(--color-text);">Learning Objectives</h4>
                        <ul class="text-sm space-y-2" style="color: var(--color-text-secondary);">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-3 mt-1 text-sm" style="color: var(--color-correct);"></i>
                                <span>Understand multi-tenant data isolation strategies and their implementation trade-offs</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-3 mt-1 text-sm" style="color: var(--color-correct);"></i>
                                <span>Evaluate security vs. scalability trade-offs for SaaS data architecture</span>
                            </li>
                            <li class="flex items-start">
                                <i class="far fa-circle mr-3 mt-1 text-sm" style="color: var(--color-text-secondary);"></i>
                                <span>Implement schema-level security patterns for database access control</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>

        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        document.addEventListener('DOMContentLoaded', function() {
            // --- UI Elements ---
            const optionItems = document.querySelectorAll('.option-item');
            const submitBtn = document.getElementById('submit-btn');
            const nextBtn = document.getElementById('next-btn');
            const hintToggle = document.getElementById('hint-toggle');
            const hintPanel = document.getElementById('hint-panel');
            const closeHint = document.getElementById('close-hint');
            const explanationPanel = document.getElementById('explanation-panel');
            const moduleNavContainer = document.getElementById('module-nav-container');
            const prevPageBtn = document.getElementById('prev-page-btn');
            const nextPageBtn = document.getElementById('next-page-btn');
            const pageInfo = document.getElementById('page-info');
            
            // --- State Variables ---
            let selectedOption = null;
            let answerSubmitted = false;
            let hintVisible = false;
            const totalModules = 12;
            const currentModule = 3; 
            const completedModules = [1, 2]; 
            const modulesPerPage = 10;

            // --- Pagination Logic ---
            let currentPage = 1;
            const totalPages = Math.ceil(totalModules / modulesPerPage);

            function renderModuleNavigation(page) {
                moduleNavContainer.innerHTML = '';
                const start = (page - 1) * modulesPerPage;
                const end = Math.min(start + modulesPerPage, totalModules);

                for (let i = start + 1; i <= end; i++) {
                    const moduleElement = document.createElement('div');
                    moduleElement.textContent = i;
                    moduleElement.classList.add('module-nav');

                    if (i === currentModule) {
                        moduleElement.classList.add('current');
                    } else if (completedModules.includes(i)) {
                        moduleElement.classList.add('completed');
                    }

                    // Optional: Add a click handler to change modules
                    moduleElement.addEventListener('click', () => {
                        // In a real application, this would load module 'i'
                        // alert('Navigating to Module ' + i);
                    });

                    moduleNavContainer.appendChild(moduleElement);
                }

                updatePaginationControls();
            }

            function updatePaginationControls() {
                // Update page info text
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

                // Disable/Enable buttons
                if (currentPage === 1) {
                    prevPageBtn.classList.add('disabled');
                    prevPageBtn.setAttribute('disabled', 'true');
                } else {
                    prevPageBtn.classList.remove('disabled');
                    prevPageBtn.removeAttribute('disabled');
                }

                if (currentPage === totalPages) {
                    nextPageBtn.classList.add('disabled');
                    nextPageBtn.setAttribute('disabled', 'true');
                } else {
                    nextPageBtn.classList.remove('disabled');
                    nextPageBtn.removeAttribute('disabled');
                }
            }

            prevPageBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderModuleNavigation(currentPage);
                }
            });

            nextPageBtn.addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderModuleNavigation(currentPage);
                }
            });

            // Initial render
            renderModuleNavigation(currentPage);

            // --- Question/Answer Logic ---
            
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