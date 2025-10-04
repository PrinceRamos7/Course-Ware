<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Assessment | ISU Learning Platform</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">   
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.5;
            min-height: 100vh;
            padding:0;
        }

        /* Custom Component Styles */
        .exam-container {
            background-color: var(--color-card-bg);
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-card-border);
            overflow: hidden; /* For progress bar rounding */
        }

        .option-item {
            border: 2px solid var(--color-card-border);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .option-item:hover {
            border-color: var(--color-heading-secondary);
            box-shadow: 0 0 0 1px var(--color-heading-secondary);
        }
        .option-item.selected {
            border-color: var(--color-heading);
            background-color: rgba(34, 197, 94, 0.08); /* light green background */
        }

        .option-indicator {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid var(--color-card-border);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .option-item.selected .option-indicator {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        /* Question Navigation Grid */
        .question-nav {
            width: 2.25rem; /* w-9 */
            height: 2.25rem; /* h-9 */
            border-radius: 0.375rem; /* rounded-md */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid var(--color-card-border);
        }
        .question-nav:hover { border-color: var(--color-heading-secondary); }
        .question-nav.current { background-color: var(--color-heading); color: white; border-color: var(--color-heading); }
        .question-nav.answered { background-color: var(--color-button-primary); color: white; border-color: var(--color-button-primary); }
        .question-nav.flagged { background-color: var(--color-warning); color: white; border-color: var(--color-warning); }

        /* Button Styling */
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: background-color 0.2s ease, transform 0.1s;
        }
        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-1px);
        }
        .btn-secondary {
            border: 1px solid var(--color-card-border);
            color: var(--color-text-secondary);
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            border-color: var(--color-text);
            color: var(--color-text);
        }
        
        /* Progress & Timer */
        .progress-bar { height: 6px; border-radius: 3px; background-color: var(--color-progress-bg); }
        .progress-fill { height: 100%; background: var(--color-progress-fill); transition: width 0.5s ease; }
        .timer-critical { 
            color: var(--color-time-critical) !important; 
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        /* Tailwind overrides for clean layout */
        @media (min-width: 1024px) {
            .compact-grid {
                grid-template-columns: repeat(6, 1fr); /* Maximize width usage on large screens */
                gap: 0.75rem;
            }
        }
        @media (max-width: 1023px) {
            .compact-grid {
                grid-template-columns: repeat(8, 1fr); /* Better fit on tablets */
            }
        }
        @media (max-width: 640px) {
            .compact-grid {
                grid-template-columns: repeat(5, 1fr); /* Original design for mobile */
            }
        }
    </style>
</head>
<body class="pt-2">
    <header class="header sticky top-0 z-10 py-3 shadow-md" style="background-color: var(--color-header-bg);">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded flex items-center justify-center" style="background-color: var(--color-heading);">
                        <i class="fas fa-microchip text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold" style="color: var(--color-heading);">ISU Certification Assessment</h1>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Database Security Specialist: Section 2 (Questions 1-20)</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-lg transition duration-300" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-clock text-base" style="color: var(--color-heading);"></i>
                        <span id="timer" class="font-mono text-lg font-bold" style="color: var(--color-heading);">45:00</span>
                    </div>
                    
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-2 rounded-lg text-sm" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-lg" style="color: var(--color-icon);"></i>
                        <span class="font-semibold" style="color: var(--color-text);">Ryuta (Candidate ID: 9001)</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3">
                    <div class="exam-container p-6">
                        <div class="mb-6 border-b pb-4" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center space-x-3">
                                    <span id="q-status-badge" class="px-3 py-1 rounded-full text-sm font-bold" 
                                          style="background-color: var(--color-heading); color: white;">Question 3</span>
                                    <span id="q-flag-badge" class="px-3 py-1 rounded-full text-xs font-medium hidden" 
                                          style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                        <i class="fas fa-flag mr-1"></i> Flagged
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold" style="color: var(--color-heading-secondary);">1 Point</div>
                                    <div class="text-xs" style="color: var(--color-text-secondary);">Difficulty: Advanced</div>
                                </div>
                            </div>
                            <h2 class="text-xl font-bold" style="color: var(--color-text);">
                                Multi-tenant data isolation security assessment
                            </h2>
                        </div>

                        <div class="mb-8">
                            <p class="text-base mb-6 font-medium" style="color: var(--color-text);">
                                In a multi-tenant SaaS application handling sensitive financial data, which approach provides the optimal balance between security isolation and operational scalability while maintaining compliance with data protection regulations?
                            </p>

                            <div id="options-list" class="space-y-4">
                                <div class="option-item p-4" data-option="A">
                                    <div class="flex items-start space-x-4">
                                        <div class="option-indicator">A</div>
                                        <p class="text-base pt-0.5 font-medium" style="color: var(--color-text);">Row-level security with tenant\_id predicates</p>
                                    </div>
                                </div>

                                <div class="option-item p-4" data-option="B">
                                    <div class="flex items-start space-x-4">
                                        <div class="option-indicator">B</div>
                                        <p class="text-base pt-0.5 font-medium" style="color: var(--color-text);">Separate database instances per tenant</p>
                                    </div>
                                </div>

                                <div class="option-item p-4" data-option="C">
                                    <div class="flex items-start space-x-4">
                                        <div class="option-indicator">C</div>
                                        <p class="text-base pt-0.5 font-medium" style="color: var(--color-text);">Schema-level separation within shared database</p>
                                    </div>
                                </div>

                                <div class="option-item p-4" data-option="D">
                                    <div class="flex items-start space-x-4">
                                        <div class="option-indicator">D</div>
                                        <p class="text-base pt-0.5 font-medium" style="color: var(--color-text);">Application-level filtering with audit logging</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex space-x-3 mb-3 sm:mb-0">
                                <button id="flag-btn" class="btn-secondary px-4 py-2 rounded text-sm font-medium">
                                    <i class="fas fa-flag mr-1"></i> Flag Question
                                </button>
                                <button class="btn-secondary px-4 py-2 rounded text-sm font-medium hidden sm:inline-flex">
                                    <i class="fas fa-comment-dots mr-1"></i> Add Note
                                </button>
                            </div>
                            
                            <div class="flex space-x-3 w-full sm:w-auto">
                                <button id="prev-btn" class="btn-secondary w-1/3 sm:w-auto px-4 py-2 rounded text-sm font-medium">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button id="submit-answer" class="btn-primary w-1/3 sm:w-auto px-4 py-2 rounded text-sm font-medium">
                                    Submit Answer
                                </button>
                                <button id="next-btn" class="btn-primary w-1/3 sm:w-auto px-4 py-2 rounded text-sm font-medium">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-4">
                    <div class="exam-container p-4">
                        <h3 class="font-bold text-base mb-3 uppercase tracking-wider" style="color: var(--color-heading-secondary);">Question Map</h3>
                        <div id="question-map" class="compact-grid">
                            </div>
                        
                        <div class="mt-5 space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="font-medium" style="color: var(--color-text-secondary);">Completion:</span>
                                <span id="progress-text" class="font-bold" style="color: var(--color-heading);">3 / 20</span>
                            </div>
                            <div class="progress-bar">
                                <div id="progress-fill" class="progress-fill" style="width: 15%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full bg-green-500"></div><span style="color: var(--color-text-secondary);">Answered (2)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading);"></div><span style="color: var(--color-text-secondary);">Current (1)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-warning);"></div><span style="color: var(--color-text-secondary);">Flagged (1)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-card-border);"></div><span style="color: var(--color-text-secondary);">Unanswered (16)</span></div>
                        </div>

                        <div class="mt-5">
                            <button class="btn-primary w-full py-3 rounded text-base font-bold uppercase tracking-wider">
                                <i class="fas fa-paper-plane mr-2"></i> Final Submission
                            </button>
                        </div>
                    </div>

                    <div class="exam-container p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-lg mt-0.5" style="color: var(--color-heading);"></i>
                            <div>
                                <h4 class="font-bold text-sm mb-1" style="color: var(--color-text);">Assessment Guidelines</h4>
                                <ul class="list-disc ml-4 text-xs space-y-1" style="color: var(--color-text-secondary);">
                                    <li>Time Limit: **45 minutes** total.</li>
                                    <li>**No backtracking** is permitted after submitting an answer.</li>
                                    <li>Results are available immediately post-submission.</li>
                                    <li>Passing Score: **75%** or higher.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalQuestions = 20;
            let currentQuestion = 3; 
            let examTime = 45 * 60; 
            let timerInterval;
            
            // Mock data for question states (1: Current, 2: Answered, 3: Flagged)
            const questionStates = Array(totalQuestions).fill(0);
            questionStates[1] = 2; // Q2 is answered
            questionStates[2] = 3; // Q3 is flagged (Current question)
            let answeredCount = 1;
            let flaggedCount = 1;

            const elements = {
                timer: document.getElementById('timer'),
                questionMap: document.getElementById('question-map'),
                optionsList: document.getElementById('options-list'),
                flagBtn: document.getElementById('flag-btn'),
                submitAnswer: document.getElementById('submit-answer'),
                prevBtn: document.getElementById('prev-btn'),
                nextBtn: document.getElementById('next-btn'),
                qFlagBadge: document.getElementById('q-flag-badge'),
                progressFill: document.getElementById('progress-fill'),
                progressText: document.getElementById('progress-text'),
                qStatusBadge: document.getElementById('q-status-badge')
            };

            // --- Timer Functions ---
            function updateTimerDisplay() {
                const minutes = Math.floor(examTime / 60);
                const seconds = examTime % 60;
                elements.timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Critical time warning (5 minutes)
                if (examTime <= 300) {
                    elements.timer.classList.add('timer-critical');
                } else {
                    elements.timer.classList.remove('timer-critical');
                }
            }

            function startTimer() {
                updateTimerDisplay();
                timerInterval = setInterval(() => {
                    examTime--;
                    updateTimerDisplay();
                    if (examTime <= 0) {
                        clearInterval(timerInterval);
                        alert('Time is up! Your exam has been automatically submitted.');
                        // Add code for final submission logic here
                    }
                }, 1000);
            }

            // --- UI Rendering Functions ---

            // Function to render the Question Navigation Map
            function renderQuestionMap() {
                elements.questionMap.innerHTML = '';
                questionStates.forEach((state, index) => {
                    const qNum = index + 1;
                    let classes = 'question-nav';
                    if (qNum === currentQuestion) classes += ' current';
                    else if (state === 2) classes += ' answered';
                    else if (state === 3) classes += ' flagged';
                    
                    const div = document.createElement('div');
                    div.className = classes;
                    div.textContent = qNum;
                    div.setAttribute('data-q-num', qNum);
                    div.addEventListener('click', () => changeQuestion(qNum));
                    elements.questionMap.appendChild(div);
                });
            }

            // Function to update main progress bar
            function updateProgress() {
                const percent = Math.floor((answeredCount / totalQuestions) * 100);
                elements.progressFill.style.width = `${percent}%`;
                elements.progressText.textContent = `${answeredCount} / ${totalQuestions}`;
            }

            // --- Interaction Logic ---

            // Question change simulator
            function changeQuestion(qNum) {
                if (qNum < 1 || qNum > totalQuestions) return;
                currentQuestion = qNum;
                
                // Update question header info
                elements.qStatusBadge.textContent = `Question ${qNum}`;
                elements.qFlagBadge.classList.toggle('hidden', questionStates[qNum - 1] !== 3);
                elements.flagBtn.textContent = questionStates[qNum - 1] === 3 ? 'Unflag Question' : 'Flag Question';

                // Simulate new question content/options state loading (reset selection)
                elements.optionsList.querySelectorAll('.option-item').forEach(item => {
                    item.classList.remove('selected');
                    item.querySelector('.option-indicator').classList.remove('selected');
                });

                // Disable previous/next buttons at limits
                elements.prevBtn.disabled = qNum === 1;
                elements.nextBtn.disabled = qNum === totalQuestions;

                renderQuestionMap();
            }

            // Option selection logic
            elements.optionsList.addEventListener('click', (e) => {
                const item = e.target.closest('.option-item');
                if (!item) return;

                elements.optionsList.querySelectorAll('.option-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                
                // You'd typically store the selection here (e.g., questionAnswers[currentQuestion] = item.dataset.option;)
            });

            // Flag Button toggle
            elements.flagBtn.addEventListener('click', () => {
                const index = currentQuestion - 1;
                if (questionStates[index] === 3) {
                    questionStates[index] = questionStates[index] === 2 ? 2 : 0; // Unflag: revert to answered or unanswered
                    flaggedCount--;
                } else {
                    questionStates[index] = 3; // Flag
                    flaggedCount++;
                }
                changeQuestion(currentQuestion); // Re-render to update map/badge
            });

            // Submit Answer button
            elements.submitAnswer.addEventListener('click', () => {
                const selectedOption = elements.optionsList.querySelector('.option-item.selected');
                if (!selectedOption) {
                    alert('Please select an option before submitting your answer.');
                    return;
                }

                if (questionStates[currentQuestion - 1] !== 2) {
                    answeredCount++; // Only increment if not already answered
                }
                questionStates[currentQuestion - 1] = 2; // Mark as answered

                updateProgress();
                changeQuestion(currentQuestion + 1); // Move to next question

                // NOTE: No option to go back (No backtracking allowed)
            });

            // Navigation Buttons
            elements.prevBtn.addEventListener('click', () => {
                 // For this professional assessment, since "No backtracking allowed" is an instruction, 
                 // the previous button should ideally be disabled or removed after submitting the current question.
                 // We will keep it disabled for this demo based on the mock data.
                 // In a real system, you'd only allow navigation if the question hasn't been submitted yet.
                // For this code, we'll implement a strict policy:
                if (questionStates[currentQuestion - 2] === 2) {
                    alert('No backtracking is allowed after an answer is submitted.');
                } else {
                    changeQuestion(currentQuestion - 1);
                }
            });
            elements.nextBtn.addEventListener('click', () => changeQuestion(currentQuestion + 1));
            

            // --- Initialization ---
            startTimer();
            renderQuestionMap();
            updateProgress();
            changeQuestion(currentQuestion);
        });
    </script>
</body>
</html>