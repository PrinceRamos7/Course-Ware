<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Results | ISU Learning Platform</title>
    <!-- Load Tailwind CSS CDN -->
    <link rel="stylesheet" href="../output.css">
    <link rel="stylesheet" href="../images/isu-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Root Variables (Theme Preserved) --- */
        :root {
            --color-main-bg: #fefce8;
            --color-card-bg: #ffffff;
            --color-header-bg: rgba(255, 255, 255, 0.9); /* Slightly more opaque for professionalism */
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

        /* --- Base Styles --- */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem; /* Increased padding for desktop */
        }

        .header {
            background-color: var(--color-header-bg);
            border-bottom: 1px solid var(--color-card-border);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            /* Added frosted glass effect for professional look */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        /* Enhanced card styling for a more professional depth */
        .training-card {
            background-color: var(--color-card-bg);
            border-radius: 12px; /* Slightly more rounded */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--color-card-border);
        }

        /* Button styles */
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.2s ease;
            font-weight: 600;
            border: none;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
            box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
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

        /* Progress bars */
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-progress-fill);
            transition: width 0.3s ease;
        }
        
        /* Metric card hover effect (more professional interaction) */
        .metric-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Module Performance Bars */
        .result-progress-fill {
            background: linear-gradient(90deg, var(--color-heading) 0%, #34d399 100%);
            height: 8px;
            border-radius: 4px;
        }

        .result-progress-fill-warning {
            background: linear-gradient(90deg, #f97316 0%, #f59e0b 100%);
        }
        
        /* Analysis Cards */
        .improvement-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfccb 100%);
            border-left: 5px solid var(--color-heading); /* Thicker border */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .strength-card {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-left: 5px solid var(--color-icon); /* Thicker border */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* --- Media Queries for Responsiveness --- */
        @media (max-width: 1023px) {
            /* Tablet and smaller adjustments */
            .container {
                padding: 0 1rem;
            }

            /* Header adjustments */
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-info-badges {
                margin-top: 1rem;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .header-info-badges > div {
                margin-right: 0.75rem;
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 640px) {
            /* Mobile specific adjustments */
            
            /* Smaller score circle for mobile space */
            .score-circle {
                width: 90px;
                height: 90px;
            }
            .score-circle .text-3xl {
                font-size: 1.75rem;
            }
            .score-circle .text-sm {
                font-size: 0.65rem;
            }

            /* Stack assessment badges on very small screens */
            .header-info-badges {
                flex-direction: column;
                align-items: stretch;
                margin-top: 0.75rem;
            }
            .header-info-badges > div {
                width: 100%;
                margin-right: 0;
                margin-bottom: 0.5rem;
                text-align: center;
            }

            /* Ensure the two-column action/analysis section stacks */
            .lg\:grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header py-4">
        <div class="container">
            <!-- Added header-content class for easier mobile targeting -->
            <div class="header-content flex justify-between items-center">
                
                <!-- Logo and Title Block -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full object-contain flex items-center justify-center shadow-md" style="background-color: var(--color-heading);">
                        <i class="fas fa-database text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold" style="color: var(--color-heading);">ISUtoLearn Training Results</h1>
                        <p class="text-sm font-medium" style="color: var(--color-text-secondary);">Oracle SQL Certification</p>
                    </div>
                </div>
                
                <!-- Date and User Info Badges -->
                <div class="header-info-badges flex items-center space-x-3">
                    <div class="flex items-center space-x-2 bg-[var(--color-card-bg)] px-3 py-2 rounded-full text-sm font-medium shadow-sm border border-gray-100">
                        <i class="fas fa-calendar-alt text-xs" style="color: var(--color-heading);"></i>
                        <span style="color: var(--color-text-secondary);">Completed: Oct 6, 2025</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-full text-sm shadow-sm font-semibold" style="background-color: var(--color-user-bg); border: 1px solid var(--color-card-border);">
                        <i class="fas fa-user-circle text-base" style="color: var(--color-heading);"></i>
                        <span style="color: var(--color-user-text);">Learner: Juan</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-10">
        <div class="container">
            
            <!-- 1. Summary Metrics Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
                
                <!-- Overall Score Card -->
                <div class="training-card p-6 flex flex-col items-center justify-center metric-card text-center">
                    <h3 class="font-semibold text-lg mb-4 text-gray-700">Overall Performance</h3>
                    <div class="relative score-circle flex items-center justify-center mb-4">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Circle -->
                            <path
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="var(--color-progress-bg)"
                                stroke-width="3"
                            />
                            <!-- Progress Arc (88%) -->
                            <path
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="var(--color-correct)"
                                stroke-width="3"
                                stroke-dasharray="88, 100"
                            />
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center">
                            <span class="text-4xl font-extrabold">88%</span>
                            <span class="text-sm font-medium text-gray-500">Total Score</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 p-2 px-4 rounded-full font-bold text-sm" 
                        style="background-color: var(--color-green-button); color: white;">
                        <i class="fas fa-check-circle"></i>
                        <span>PASSED</span>
                    </div>
                </div>

                <!-- Assessment Details -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-5 border-b pb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Assessment Details</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Test Name:</span>
                            <span class="font-semibold text-right">Oracle SQL Certification Prep</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Duration:</span>
                            <span class="font-semibold text-right">1 hour, 45 minutes</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Correct Answers:</span>
                            <span class="font-semibold text-right text-green-600">44 / 50 Questions</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Passing Threshold:</span>
                            <span class="font-semibold text-right text-orange-500">75% (37.5 Questions)</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-5 border-b pb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Long-term Metrics</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600 font-medium">Highest Attempt Score:</span>
                                <span class="font-extrabold" style="color: var(--color-heading);">92%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 92%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600 font-medium">Average Attempt Score:</span>
                                <span class="font-extrabold" style="color: var(--color-heading);">85.5%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85.5%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600 font-medium">Total Attempts Taken:</span>
                                <span class="font-extrabold" style="color: var(--color-heading-secondary);">4</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Module Performance Section -->
            <div class="training-card p-6 mb-10">
                <div class="flex justify-between items-center mb-6 border-b pb-4" style="border-color: var(--color-card-border);">
                    <h2 class="section-title text-2xl font-bold" style="border: none;">Performance by Topic Module</h2>
                    <button class="btn-secondary px-5 py-2 rounded-full text-sm font-semibold flex items-center hover:shadow-lg">
                        <i class="fas fa-chart-bar mr-2"></i>
                        View Detailed Report
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Database Design -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg">1. Database Design Fundamentals</h3>
                            <div class="result-progress-bar mt-3">
                                <div class="result-progress-fill" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4">95%</span>
                            <span class="performance-indicator performance-excellent rounded-full text-sm">Excellent</span>
                        </div>
                    </div>

                    <!-- DDL -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg">2. Database Object Management (DDL)</h3>
                            <div class="result-progress-bar mt-3">
                                <div class="result-progress-fill" style="width: 82%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4">82%</span>
                            <span class="performance-indicator performance-good rounded-full text-sm">Good</span>
                        </div>
                    </div>

                    <!-- Data Retrieval -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg">3. Data Retrieval (SELECT & Joins)</h3>
                            <div class="result-progress-bar mt-3">
                                <div class="result-progress-fill" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4">90%</span>
                            <span class="performance-indicator performance-excellent rounded-full text-sm">Excellent</span>
                        </div>
                    </div>

                    <!-- DML (Critical) -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150 border-orange-300">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg" style="color: var(--color-heading-secondary);">4. Data Manipulation (DML)</h3>
                            <div class="result-progress-bar mt-3">
                                <div class="result-progress-fill result-progress-fill-warning" style="width: 78%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4" style="color: var(--color-heading-secondary);">78%</span>
                            <span class="performance-indicator performance-fair rounded-full text-sm">Needs Focus</span>
                        </div>
                    </div>

                    <!-- Troubleshooting -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg">5. Troubleshooting & Error Handling</h3>
                            <div class="result-progress-bar mt-3">
                                <div class="result-progress-fill" style="width: 88%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4">88%</span>
                            <span class="performance-indicator performance-good rounded-full text-sm">Good</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Performance Analysis and Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Performance Analysis -->
                <div class="training-card p-6">
                    <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-card-border);">Learner Insights & Analysis</h2>
                    
                    <div class="space-y-5">
                        <div class="improvement-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-heading);">Core Strengths</h3>
                                    <p class="text-sm text-gray-700">You've demonstrated **excellent mastery** in **Database Design (95%)** and **Data Retrieval (90%)**. You are proficient in writing complex `SELECT` queries and understanding relational structures.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="strength-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-yellow-100 text-yellow-700 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-lightbulb text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-heading-secondary);">Focus Area</h3>
                                    <p class="text-sm text-gray-700">**Data Manipulation (DML)** is your lowest score at **78%**. Focus on advanced scenarios involving `INSERT`, `UPDATE`, and `DELETE` with subqueries and transaction control (`COMMIT`/`ROLLBACK`).</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border border-blue-200 rounded-xl bg-blue-50">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-trophy text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-text);">Certification Readiness</h3>
                                    <p class="text-sm text-gray-700">Your 88% overall score places you **above the passing requirement**. We recommend a targeted review of DML before attempting the official certification.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="training-card p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-card-border);">Next Steps & Resources</h2>
                        <p class="text-gray-600 mb-8">Utilize these resources to address your focus area (DML) and solidify your preparation for certification.</p>
                    </div>
                    
                    <div class="space-y-4">
                        <button class="btn-primary w-full py-4 rounded-xl font-bold text-lg flex items-center justify-center hover:scale-[1.01] transition duration-200">
                            <i class="fas fa-redo mr-3"></i>
                            Retake Assessment (Focus Mode)
                        </button>
                        <button class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100">
                            <i class="fas fa-certificate mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Begin Practice Certification Exam
                        </button>
                        <button class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100">
                            <i class="fas fa-download mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Export Detailed Results to PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-12 text-center text-sm p-6 text-gray-500 border-t" style="border-color: var(--color-card-border);">
        <p class="mb-1">ISU Learning Platform • Oracle SQL Certification Prep</p>
        <p>&copy; 2025 ISUtoLearn. All rights reserved.</p>
    </footer>
</body>
</html>
