<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Results | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-main-bg: #fefce8;
            --color-card-bg: #ffffff;
            --color-header-bg: rgba(255, 255, 255, 0.8);
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
            --color-correct: #22c55e;
            --color-incorrect: #ef4444;
            --color-progress-bg: #e5e7eb;
            --color-progress-fill: linear-gradient(to right, #22c55e, #facc15, #f97316);
            --color-user-bg: #f9fafb;
            --color-user-text: #0f172a;
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
            padding: 0.2rem 0.6rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .section-title {
            color: var(--color-heading);
            font-weight: 600;
            border-bottom: 1px solid var(--color-card-border);
            padding-bottom: 0.75rem;
        }

        .performance-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .performance-excellent {
            background-color: #dcfce7;
            color: #166534;
        }

        .performance-good {
            background-color: #fef3c7;
            color: #92400e;
        }

        .performance-fair {
            background-color: #fed7aa;
            color: #9a3412;
        }

        .performance-poor {
            background-color: #fecaca;
            color: #991b1b;
        }

        .result-progress-bar {
            background-color: var(--color-progress-bg);
            border-radius: 4px;
            overflow: hidden;
        }

        .result-progress-fill {
            background: linear-gradient(90deg, var(--color-heading) 0%, #22c55e 100%);
            height: 8px;
            border-radius: 4px;
        }

        .result-progress-fill-warning {
            background: linear-gradient(90deg, #f97316 0%, #f59e0b 100%);
        }

        .result-progress-fill-critical {
            background: linear-gradient(90deg, #ef4444 0%, #f97316 100%);
        }

        .score-circle {
            width: 120px;
            height: 120px;
        }

        .metric-card {
            transition: all 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .improvement-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfccb 100%);
            border-left: 4px solid var(--color-heading);
        }
        
        .strength-card {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-left: 4px solid var(--color-icon);
        }
    </style>
</head>
<body>
    <header class="header py-4">
        <div class="container">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full object-contain flex items-center justify-center" style="background-color: var(--color-heading);">
                        <i class="fas fa-database text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold" style="color: var(--color-heading);">ISUtoLearn Training Results</h1>
                        <p class="text-xs" style="color: var(--color-text-secondary);">Oracle SQL Certification</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 bg-[var(--color-card-bg)] px-2 py-1 rounded text-sm">
                        <i class="fas fa-clock text-xs" style="color: var(--color-heading);"></i>
                        <span style="color: var(--color-text-secondary);">Completed: Oct 6, 2025</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-2 py-1 rounded text-sm" style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-xs" style="color: var(--color-heading);"></i>
                        <span class="font-medium" style="color: var(--color-user-text);">Learner: Juan</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="container">
            <!-- Summary Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Overall Score Card -->
                <div class="training-card p-6 flex flex-col items-center justify-center metric-card">
                    <div class="relative score-circle flex items-center justify-center mb-4">
                        <svg class="w-full h-full" viewBox="0 0 36 36">
                            <path
                                d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="#e6e6e6"
                                stroke-width="3"
                            />
                            <path
                                d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="#22c55e"
                                stroke-width="3"
                                stroke-dasharray="88, 100"
                            />
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center">
                            <span class="text-3xl font-bold">88%</span>
                            <span class="text-sm text-gray-500">Overall Score</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 p-2 px-4 rounded-full font-bold" 
                         style="background-color: var(--color-green-button); color: white;">
                        <i class="fas fa-check-circle"></i>
                        <span>PASSED</span>
                    </div>
                </div>

                <!-- Assessment Details -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-4" style="color: var(--color-heading);">Assessment Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Assessment:</span>
                            <span class="font-medium">Oracle SQL Certification Prep</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date Completed:</span>
                            <span class="font-medium">Oct 6, 2025</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Time Spent:</span>
                            <span class="font-medium">1h 45m</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Questions:</span>
                            <span class="font-medium">44/50 Correct</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Passing Score:</span>
                            <span class="font-medium">75%</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-4" style="color: var(--color-heading);">Performance Metrics</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">High Score:</span>
                                <span class="font-bold" style="color: var(--color-heading);">92%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 92%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Average Score:</span>
                                <span class="font-bold" style="color: var(--color-heading);">85.5%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85.5%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Attempts:</span>
                                <span class="font-bold" style="color: var(--color-heading-secondary);">4</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Performance Section -->
            <div class="training-card p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="section-title text-xl">Performance by Module</h2>
                    <button class="btn-secondary px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Detailed Report
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Database Design -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium">Database Design</h3>
                            <div class="result-progress-bar mt-2">
                                <div class="result-progress-fill" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="flex items-center ml-4">
                            <span class="font-bold text-lg mr-2">95%</span>
                            <span class="performance-indicator performance-excellent">Excellent</span>
                        </div>
                    </div>

                    <!-- DDL -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium">Database Object Management (DDL)</h3>
                            <div class="result-progress-bar mt-2">
                                <div class="result-progress-fill" style="width: 82%"></div>
                            </div>
                        </div>
                        <div class="flex items-center ml-4">
                            <span class="font-bold text-lg mr-2">82%</span>
                            <span class="performance-indicator performance-good">Good</span>
                        </div>
                    </div>

                    <!-- Data Retrieval -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium">Data Retrieval (SELECT)</h3>
                            <div class="result-progress-bar mt-2">
                                <div class="result-progress-fill" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="flex items-center ml-4">
                            <span class="font-bold text-lg mr-2">90%</span>
                            <span class="performance-indicator performance-excellent">Excellent</span>
                        </div>
                    </div>

                    <!-- DML (Critical) -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium">Data Manipulation (DML)</h3>
                            <div class="result-progress-bar mt-2">
                                <div class="result-progress-fill result-progress-fill-warning" style="width: 78%"></div>
                            </div>
                        </div>
                        <div class="flex items-center ml-4">
                            <span class="font-bold text-lg mr-2">78%</span>
                            <span class="performance-indicator performance-fair">Needs Work</span>
                        </div>
                    </div>

                    <!-- Troubleshooting -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium">Troubleshooting & Error Handling</h3>
                            <div class="result-progress-bar mt-2">
                                <div class="result-progress-fill" style="width: 88%"></div>
                            </div>
                        </div>
                        <div class="flex items-center ml-4">
                            <span class="font-bold text-lg mr-2">88%</span>
                            <span class="performance-indicator performance-good">Good</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Analysis and Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Performance Analysis -->
                <div class="training-card p-6">
                    <h2 class="section-title text-xl mb-4">Performance Analysis</h2>
                    
                    <div class="space-y-4">
                        <div class="improvement-card p-4 rounded-lg">
                            <div class="flex items-start">
                                <div class="p-2 rounded-lg bg-green-100 text-green-600 mr-3 mt-1">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium mb-1">Strong Performance Areas</h3>
                                    <p class="text-sm text-gray-600">You've demonstrated excellent understanding of Database Design (95%) and Data Retrieval (90%). These are your strongest areas.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="strength-card p-4 rounded-lg">
                            <div class="flex items-start">
                                <div class="p-2 rounded-lg bg-yellow-100 text-yellow-600 mr-3 mt-1">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium mb-1">Areas for Development</h3>
                                    <p class="text-sm text-gray-600">Data Manipulation (DML) at 78% could benefit from additional practice with INSERT, UPDATE, and DELETE operations.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-start">
                                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 mr-3 mt-1">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium mb-1">Certification Readiness</h3>
                                    <p class="text-sm text-gray-600">With an overall score of 88%, you are well-prepared for the Oracle SQL certification exam. Continue practicing to maintain your strong performance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="training-card p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="section-title text-xl mb-4">Continue Learning</h2>
                        <p class="text-gray-600 mb-6">Based on your performance, here are the next steps to continue your SQL mastery journey.</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button class="btn-primary w-full py-3 rounded-lg font-medium flex items-center justify-center">
                            <i class="fas fa-redo mr-2"></i>
                            Retake Assessment
                        </button>
                        <button class="btn-secondary w-full py-3 rounded-lg font-medium flex items-center justify-center">
                            <i class="fas fa-download mr-2"></i>
                            Export Results
                        </button>
                        <button class="btn-secondary w-full py-3 rounded-lg font-medium flex items-center justify-center">
                            <i class="fas fa-certificate mr-2"></i>
                            Practice Certification Exam
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-12 text-center text-sm p-4 text-gray-500">
        <p>ISU Learning Platform • Oracle SQL Certification Prep</p>
    </footer>
</body>
</html>