<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Assessment | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">   

    <style>

         html, body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.5;
            min-height: 100vh;
            padding:0;
            margin:0;
        }

        .exam-container {
            background-color: var(--color-card-bg);
            border-radius: 0.5rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-card-border);
            overflow: hidden;
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
        /* Selected option uses Primary Green for confirmation */
        .option-item.selected {
            border-color: var(--color-heading); 
            background-color: rgba(21, 128, 61, 0.08); 
        }
        .option-item.answered {
             cursor: default;
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
        /* Selected indicator uses Primary Green for confirmation */
        .option-item.selected .option-indicator {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .question-nav {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid var(--color-card-border);
            background-color: var(--color-card-bg);
            color: var(--color-text);
        }
        .question-nav:hover { border-color: var(--color-heading-secondary); }
        /* Current question uses Primary Green (heading) */
        .question-nav.current { background-color: var(--color-heading); color: white; border-color: var(--color-heading); }
        /* Answered question uses Secondary Orange (heading-secondary) */
        .question-nav.answered { background-color: var(--color-heading-secondary); color: white; border-color: var(--color-heading-secondary); }
        /* Flagged question uses Warning Yellow */
        .question-nav.flagged { background-color: var(--color-warning); color: var(--color-text); border-color: var(--color-warning); }
        .question-nav.answered.flagged { background-color: var(--color-warning); color: var(--color-text); border-color: var(--color-warning); }


        .btn-base { 
            padding: 0.75rem 1.5rem; border-radius: 0.375rem; cursor: pointer; text-align: center;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 500;
        }
        /* Primary button uses Green button variables */
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: background-color 0.2s ease, transform 0.1s;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-1px);
        }
        /* Secondary button uses pale yellow background and golden brown text */
        .btn-secondary {
            background-color: var(--color-button-secondary);
            border: 1px solid var(--color-card-border);
            color: var(--color-button-secondary-text); 
            transition: all 0.2s ease;
        }
        .btn-secondary:hover:not(:disabled) {
            border-color: var(--color-heading-secondary); 
            color: var(--color-heading-secondary); 
        }
        .btn-base:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-bar { height: 6px; border-radius: 3px; background-color: var(--color-progress-bg); }
        /* Progress fill uses the XP gradient */
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

        .compact-grid {
            display: grid;
            gap: 0.5rem;
            grid-template-columns: repeat(5, 1fr);
        }
        
        .scrollable-map-container {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        .scrollable-map-container::-webkit-scrollbar {
            width: 8px;
        }
        .scrollable-map-container::-webkit-scrollbar-thumb {
            background: var(--color-card-border);
            border-radius: 10px;
        }
        .scrollable-map-container::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-secondary);
        }

        @media (min-width: 640px) {
             .compact-grid { grid-template-columns: repeat(6, 1fr); }
        }
        @media (min-width: 1024px) {
            .main-content-grid {
                grid-template-areas: "sidebar main";
                grid-template-columns: 1fr 3fr;
            }
            .sidebar-panel { grid-area: sidebar; }
            .question-panel { grid-area: main; }
            .compact-grid { grid-template-columns: repeat(5, 1fr); }
        }
        @media (min-width: 1280px) {
            .compact-grid { grid-template-columns: repeat(6, 1fr); }
            .main-content-grid { grid-template-columns: 300px 1fr; }
        }
    </style>
</head>
<body>
    <header class="top-0 right-0 left-0 fixed z-10 shadow-md py-4" style="background-color: var(--color-header-bg);">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex object-contain items-center justify-center" style="background-color: var(--color-heading);">
                       <img src="../images/isu-logo.png" alt="">
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold hidden sm:inline" style="color: var(--color-heading);">ISU Course Assessment</h1>
                        <h1 class="text-xl font-extrabold sm:hidden" style="color: var(--color-heading);">ISU CA</h1>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Database Specialists</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-lg transition duration-300" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-clock text-base" style="color: var(--color-heading);"></i>
                        <span id="timer" class="font-mono text-lg font-bold" style="color: var(--color-heading);">45:00</span>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-2 px-3 py-2 rounded-lg text-sm" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-lg" style="color: var(--color-icon);"></i>
                        <span class="font-semibold" style="color: var(--color-text);">Ryuta</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-[80px]">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 main-content-grid">
                <div class="lg:col-span-1 space-y-4 sidebar-panel">
                    <div class="exam-container p-4">
                        <h3 class="font-bold text-base mb-3 uppercase tracking-wider" style="color: var(--color-heading-secondary);">Question Map</h3>
                        
                        <div class="scrollable-map-container">
                            <div id="question-map" class="compact-grid">
                            </div>
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
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading-secondary);"></div><span style="color: var(--color-text-secondary);">Answered (<span id="answered-count-legend">2</span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading);"></div><span style="color: var(--color-text-secondary);">Current (<span id="current-count-legend">1</span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-warning);"></div><span style="color: var(--color-text-secondary);">Flagged (<span id="flagged-count-legend">1</span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-card-border);"></div><span style="color: var(--color-text-secondary);">Unanswered (<span id="unanswered-count-legend">16</span>)</span></div>
                        </div>

                        <div class="mt-5">
                            <button id="final-submit-btn" class="btn-base btn-primary w-full py-3 rounded text-base font-bold uppercase tracking-wider">
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
                                    <li class="font-bold text-red-600">**No backtracking** permitted after submitting an answer.</li>
                                    <li>Results are available immediately post-submission.</li>
                                    <li>Passing Score: **75%** or higher.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3 question-panel">
                    <div id="question-panel" class="exam-container p-6">
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
                            <p id="q-text" class="text-base mb-6 font-medium" style="color: var(--color-text);">
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
                                <button id="flag-btn" class="btn-base btn-secondary px-4 py-2 text-sm font-medium">
                                    <i class="fas fa-flag mr-1"></i> Flag Question
                                </button>
                                <button class="btn-base btn-secondary px-4 py-2 text-sm font-medium hidden sm:inline-flex" disabled>
                                    <i class="fas fa-comment-dots mr-1"></i> Add Note
                                </button>
                            </div>
                            
                            <div class="flex space-x-3 w-full sm:w-auto">
                                <button id="prev-btn" class="btn-base btn-secondary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium" disabled>
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                
                                <button id="submit-answer" class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium">
                                    Submit Answer
                                </button>
                                
                                <button id="next-btn" class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
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
            const totalQuestions = 20;
            let currentQuestion = 3; 
            let examTime = 45 * 60; 
            let timerInterval;
            
            const questionStates = Array(totalQuestions).fill(0);
            questionStates[0] = 2; // Answered
            questionStates[1] = 4; // Answered and Flagged
            questionStates[2] = 1; // Current (or Unattempted/Active)

            let answeredCount = 2;
            let flaggedCount = 1;
            let currentAnswer = null;

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
                qStatusBadge: document.getElementById('q-status-badge'),
                answeredLegend: document.getElementById('answered-count-legend'),
                flaggedLegend: document.getElementById('flagged-count-legend'),
                unansweredLegend: document.getElementById('unanswered-count-legend'),
                scrollableMapContainer: document.querySelector('.scrollable-map-container')
            };

            function updateTimerDisplay() {
                const minutes = Math.floor(examTime / 60);
                const seconds = examTime % 60;
                elements.timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
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
                        document.getElementById('final-submit-btn').click(); 
                    }
                }, 1000);
            }

            function getQuestionClasses(qNum, state) {
                let classes = 'question-nav';
                if (qNum === currentQuestion) classes += ' current';
                else if (state === 2 || state === 4) classes += ' answered';
                if (state === 3 || state === 4) classes += ' flagged';
                return classes;
            }

            function renderQuestionMap() {
                elements.questionMap.innerHTML = '';
                let currentElement = null;
                questionStates.forEach((state, index) => {
                    const qNum = index + 1;
                    
                    const div = document.createElement('div');
                    div.className = getQuestionClasses(qNum, state);
                    div.textContent = qNum;
                    div.setAttribute('data-q-num', qNum);

                    if (state < 2 || qNum === currentQuestion) {
                        div.addEventListener('click', () => changeQuestion(qNum));
                    } else {
                        div.style.cursor = 'not-allowed';
                    }

                    if (qNum === currentQuestion) {
                        currentElement = div;
                    }

                    elements.questionMap.appendChild(div);
                });

                if (currentElement) {
                    const container = elements.scrollableMapContainer;
                    const containerRect = container.getBoundingClientRect();
                    const elementRect = currentElement.getBoundingClientRect();

                    if (elementRect.bottom > containerRect.bottom || elementRect.top < containerRect.top) {
                        container.scrollTop = currentElement.offsetTop - container.offsetTop - (containerRect.height / 2) + (elementRect.height / 2);
                    }
                }
            }

            function updateProgress() {
                const percent = Math.floor((answeredCount / totalQuestions) * 100);
                elements.progressFill.style.width = `${percent}%`;
                elements.progressText.textContent = `${answeredCount} / ${totalQuestions}`;

                const currentIsAnswered = questionStates[currentQuestion - 1] > 1;
                
                elements.answeredLegend.textContent = answeredCount;
                elements.flaggedLegend.textContent = flaggedCount;
                
                let currentlyUnattempted = 0;
                questionStates.forEach(state => {
                    if (state === 0 || state === 1 || state === 3) {
                        currentlyUnattempted++;
                    }
                });
                elements.unansweredLegend.textContent = currentlyUnattempted;
            }
            
            function updateQuestionPanelState() {
                const qIndex = currentQuestion - 1;
                const state = questionStates[qIndex];
                const isAnswered = state === 2 || state === 4;

                elements.optionsList.querySelectorAll('.option-item').forEach(item => {
                    item.classList.remove('selected', 'answered');
                    item.style.pointerEvents = isAnswered ? 'none' : 'auto';
                    if (isAnswered) item.classList.add('answered');
                });

                elements.submitAnswer.disabled = isAnswered;
                elements.nextBtn.disabled = !isAnswered && currentQuestion < totalQuestions;
                elements.prevBtn.disabled = true;
                
                const isFlagged = state === 3 || state === 4;
                elements.qFlagBadge.classList.toggle('hidden', !isFlagged);
                elements.flagBtn.textContent = isFlagged ? 'Unflag Question' : 'Flag Question';
            }

            function changeQuestion(qNum) {
                if (qNum < 1 || qNum > totalQuestions) return;

                if (currentQuestion !== qNum) {
                    const oldIndex = currentQuestion - 1;
                    if (questionStates[oldIndex] === 1) questionStates[oldIndex] = 0; 
                }

                currentQuestion = qNum;
                questionStates[currentQuestion - 1] = questionStates[currentQuestion - 1] < 2 ? 1 : questionStates[currentQuestion - 1];
                
                elements.qStatusBadge.textContent = `Question ${qNum}`;
                
                updateQuestionPanelState();
                renderQuestionMap();
            }

            elements.optionsList.addEventListener('click', (e) => {
                const item = e.target.closest('.option-item');
                if (!item || item.classList.contains('answered')) return;

                elements.optionsList.querySelectorAll('.option-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                currentAnswer = item.dataset.option;
                elements.nextBtn.disabled = false;
            });

            elements.flagBtn.addEventListener('click', () => {
                const index = currentQuestion - 1;
                const state = questionStates[index];
                
                const wasFlagged = state === 3 || state === 4;

                if (wasFlagged) {
                    questionStates[index] = state === 3 ? 1 : 2; 
                    flaggedCount--;
                } else {
                    questionStates[index] = state === 1 ? 3 : 4; 
                    flaggedCount++;
                }
                updateProgress();
                updateQuestionPanelState();
                renderQuestionMap();
            });

            elements.submitAnswer.addEventListener('click', () => {
                const selectedOption = elements.optionsList.querySelector('.option-item.selected');
                if (!selectedOption) {
                    alert('Please select an option before submitting your answer.');
                    return;
                }

                const index = currentQuestion - 1;
                const wasAnswered = questionStates[index] === 2 || questionStates[index] === 4;
                
                if (questionStates[index] === 1) {
                    questionStates[index] = 2;
                } else if (questionStates[index] === 3) {
                    questionStates[index] = 4;
                }
                
                if (!wasAnswered) { answeredCount++; }

                updateProgress();
                
                if (currentQuestion < totalQuestions) {
                    changeQuestion(currentQuestion + 1);
                } else {
                    updateQuestionPanelState();
                    alert('You have answered the final question. Please use the Final Submission button when ready.');
                }
            });

            elements.nextBtn.addEventListener('click', () => {
                if (currentQuestion < totalQuestions) {
                    changeQuestion(currentQuestion + 1);
                }
            });
            
            document.getElementById('final-submit-btn').addEventListener('click', () => {
                let unattempted = 0;
                questionStates.forEach(state => {
                    if (state < 2) {
                        unattempted++;
                    }
                });

                if (unattempted > 0) {
                    if (!confirm(`You have ${unattempted} question(s) unattempted. Are you sure you want to finalize your submission?`)) {
                        return;
                    }
                }
                clearInterval(timerInterval);
                alert('Assessment submitted! Thank you.');
            });
            

            startTimer();
            renderQuestionMap();
            updateProgress();
            changeQuestion(currentQuestion);
        });
    </script>
</body>
</html>