<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css"> 
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<style>

body {
    background-color: var(--color-main-bg);
    color: var(--color-text);
    font-family: ui-sans-serif, system-ui, sans-serif;
    transition: background 0.5s ease, color 0.5s ease;
    padding: 0;
}

.custom-radio input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    display: inline-block;
    position: relative;
    height: 1.25rem; 
    width: 1.25rem;
    border-radius: 50%; 
    border: 3px solid var(--color-radio-border); /* Thicker border */
    background-color: var(--color-radio-bg);
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease-in-out;
    flex-shrink: 0;
}
.custom-radio input[type="radio"]:checked {
    border-color: var(--color-radio-checked);
    background-color: var(--color-radio-checked);
    box-shadow: 0 0 0 2px var(--color-card-bg) inset; /* Inner white ring */
}
.custom-radio input[type="radio"]:checked::after {
    content: '';
    display: block;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 0.35rem; 
    height: 0.35rem; 
    border-radius: 50%; 
    background-color: var(--color-card-bg); /* Center dot */
    opacity: 0; /* Hidden by default, only checked state matters */
    transition: opacity 0.2s;
}

.choice-card {
    transition: all 0.2s ease-in-out, transform 0.1s ease;
    cursor: pointer;
    border: 3px solid var(--color-card-border);
    box-shadow: 0 4px 0 var(--color-card-border); /* 3D effect */
    background-color: var(--color-card-bg);
}
.choice-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-heading);
    box-shadow: 0 6px 0 var(--color-heading);
}
.choice-card.selected {
    transform: translateY(2px); /* Clicked/selected "presses down" */
    border-color: var(--color-heading-secondary) !important;
    background-color: var(--color-card-section-bg) !important;
    box-shadow: 0 2px 0 var(--color-heading-secondary) !important; /* Smaller 3D shadow */
}
body.dark-mode .choice-card.selected {
    background-color: var(--color-card-section-bg) !important;
    box-shadow: 0 2px 0 var(--color-heading-secondary) !important; 
}

/* --- 5. NAVIGATION BUTTONS (Gamified Look) --- */
.q-nav-button {
    background-color: var(--color-button-secondary);
    color: var(--color-button-secondary-text);
    box-shadow: 0 3px 0px var(--color-button-secondary-text);
}
.q-nav-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 0px var(--color-button-secondary-text);
}
.q-nav-button.answered {
    background-color: var(--color-button-primary);
    color: var(--color-button-secondary-text);
    box-shadow: 0 3px 0px var(--color-button-primary-hover);
}
.q-nav-button.answered:hover {
    box-shadow: 0 4px 0px var(--color-button-primary-hover);
}
.q-nav-button.current {
    transform: scale(1.1);
    border: 3px solid var(--color-heading-secondary);
    box-shadow: 0 4px 0px var(--color-heading-secondary) !important;
}

.nav-btn {
    box-shadow: 0 4px 0 var(--color-button-primary-hover);
    transition: all 0.1s;
}
.nav-btn:active {
    transform: translateY(4px);
    box-shadow: none;
}
#nextBtn.submit-btn {
    box-shadow: 0 4px 0 var(--color-green-button-hover);
}
#nextBtn.submit-btn:active {
    transform: translateY(4px);
    box-shadow: none;
}
</style>
</head>
<body class="min-h-screen flex flex-col" style="background-color: var(--color-main-bg);">

    <header class="backdrop-blur-sm p-4 shadow-lg flex justify-between items-center z-20 sticky top-0" style="background-color: var(--color-header-bg); border-bottom: 2px solid var(--color-heading);">
        <div class="flex items-center space-x-4">
            <img src="../images/isu-logo.png" alt="ISU Logo" class="w-10 h-10 object-contain">
            <h1 class="text-lg font-extrabold font-bungee tracking-wider text-[var(--color-heading)]">
                ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
            </h1>
            <span id="headerQuestionCount" class="text-sm font-semibold p-1 px-3 rounded-full" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                Question 1 of 5
            </span>
        </div>
        <div class="flex items-center space-x-4">
            <div class="flex items-center rounded-full px-3 py-1 text-sm font-extrabold border-2" style="background-color: var(--color-card-bg); color: var(--color-text); border-color: var(--color-heading-secondary);">
                <i class="far fa-clock mr-2" style="color: var(--color-heading-secondary);"></i>
                <span id="timer">30:00</span>
            </div>
            <button id="themeToggleBtn" class="py-2 px-3 rounded-full transition-colors duration-200 shadow-md" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                <i class="fas fa-moon"></i>
            </button>
            <button id="headerSubmitBtn" class="font-bold py-2 px-5 rounded-lg transition-colors nav-btn" style="background-color: var(--color-green-button); color: white;">
                Submit Final
            </button>
        </div>
    </header>

    <div class="flex flex-1 p-8 space-x-8"> 

        <aside class="w-72 rounded-2xl p-6 shadow-2xl flex flex-col justify-between h-[80vh] sticky top-24 border-4" 
            style="background-color: var(--color-sidebar-bg); color: var(--color-text); border-color: var(--color-sidebar-border);">
            <div>
                <h2 class="text-xl font-extrabold mb-4 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-scroll mr-2" style="color: var(--color-heading-secondary);"></i> Quest Map
                </h2>
                <div id="questionNavGrid" class="grid grid-cols-5 gap-3 mb-8">
                    </div>

                <h2 class="text-lg font-bold mb-3 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-info-circle mr-2" style="color: var(--color-heading-secondary);"></i> Legend
                </h2>
                <div class="space-y-2 text-sm" style="color: var(--color-text-secondary);">
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full mr-2" style="background-color: var(--color-button-primary);"></span> Completed
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full border-2 mr-2" style="border-color: var(--color-heading-secondary); background-color: var(--color-heading);"></span> Active Quest
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full mr-2" style="background-color: var(--color-button-secondary);"></span> Unexplored
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t-2" style="border-color: var(--color-card-section-border);">
                <h3 class="text-lg font-bold mb-2 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-level-up-alt mr-2" style="color: var(--color-heading-secondary);"></i>Progress
                </h3>
                <div class="w-full rounded-full h-3 mb-2 border-2" style="background-color: var(--color-progress-bg); border-color: var(--color-progress-bg);">
                    <div id="progressFill" class="h-full rounded-full transition-all duration-300" style="background: var(--color-progress-fill); width: 0%;"></div>
                </div>
                <p id="progressText" class="text-sm font-semibold" style="color: var(--color-text-secondary);">0 / 5 Quests Completed</p>
            </div>
        </aside>

        <main class="flex-1 backdrop-blur-md rounded-2xl p-8 shadow-2xl flex flex-col relative overflow-hidden border-4" 
            style="background-color: var(--color-card-bg); border-color: var(--color-sidebar-border);">
            <div id="quizContent" class="flex-1 flex flex-col">
                </div>

            <div class="mt-8 pt-6 border-t-2 flex justify-between items-center" style="border-color: var(--color-card-section-border);">
                <button id="prevBtn" class="font-bold py-2 px-5 rounded-lg transition-colors nav-btn" 
                    style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                    <i class="fas fa-arrow-left mr-2"></i> Previous Quest
                </button>
                <span id="selectAnswerHint" class="text-sm italic font-semibold flex items-center" style="color: var(--color-heading-secondary);">
                    <i class="fas fa-bolt mr-2" style="color: var(--color-heading);"></i> Answer Required
                </span>
                <button id="nextBtn" class="font-bold py-2 px-5 rounded-lg transition-colors nav-btn" 
                    style="background-color: var(--color-button-primary); color: white;">
                    Next Quest <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <div id="completionPopup" class="hidden absolute inset-0 backdrop-blur-lg flex items-center justify-center z-30 opacity-0 rounded-2xl" style="background-color: var(--color-popup-bg);">
                <div class="text-center p-8 rounded-xl shadow-2xl border-4" 
                    style="background-color: var(--color-popup-content-bg); border-color: var(--color-heading);">
                    <i class="fas fa-medal text-yellow-500 text-6xl mb-4 drop-shadow"></i>
                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Challenge Completed!</h2>
                    <p id="finalScore" class="text-xl mt-4 font-semibold" style="color: var(--color-text);">You earned 0 XP!</p>
                    <button onclick="window.location.reload()" class="mt-8 font-bold py-3 px-8 rounded-lg transition-transform hover:scale-105 nav-btn" style="background-color: var(--color-green-button); color: white;">
                        Replay Mission
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        const DUMMY_QUESTIONS = [
            { id: 1, question_text: "What is the correct way to declare a variable in JavaScript ES6?", type: "Multiple Choice", choices: [{ text: "A. var myVariable = 'value';", is_correct: false }, { text: "B. let myVariable = 'value';", is_correct: false }, { text: "C. const myVariable = 'value';", is_correct: false }, { text: "D. Both let and const are correct", is_correct: true }], user_answer_index: null },
            { id: 2, question_text: "Which keyword is used to define a function in JavaScript?", type: "Multiple Choice", choices: [{ text: "A. method", is_correct: false }, { text: "B. func", is_correct: false }, { text: "C. function", is_correct: true }, { text: "D. define", is_correct: false }], user_answer_index: null },
            { id: 3, question_text: "How do you add a single-line comment in JavaScript?", type: "Multiple Choice", choices: [{ text: "A. ", is_correct: false }, { text: "B. // This is a comment", is_correct: true }, { text: "C. /* This is a comment */", is_correct: false }, { text: "D. # This is a comment", is_correct: false }], user_answer_index: null },
            { id: 4, question_text: "Which operator is used for strict equality (value and type) in JavaScript?", type: "Multiple Choice", choices: [{ text: "A. ==", is_correct: false }, { text: "B. =", is_correct: false }, { text: "C. ===", is_correct: true }, { text: "D. !==", is_correct: false }], user_answer_index: null },
            { id: 5, question_text: "What will 'typeof null' return in JavaScript?", type: "Multiple Choice", choices: [{ text: "A. 'null'", is_correct: false }, { text: "B. 'undefined'", is_correct: false }, { text: "C. 'object'", is_correct: true }, { text: "D. 'number'", is_correct: false }], user_answer_index: null }
        ];

        let currentQuestionIndex = 0;
        const totalQuestions = DUMMY_QUESTIONS.length;
        const quizDurationMinutes = 30;

        // --- DOM Elements ---
        const $ = id => document.getElementById(id);
        const headerQuestionCount = $('headerQuestionCount');
        const timerDisplay = $('timer');
        const questionNavGrid = $('questionNavGrid');
        const quizContent = $('quizContent');
        const prevBtn = $('prevBtn');
        const nextBtn = $('nextBtn');
        const headerSubmitBtn = $('headerSubmitBtn');
        const selectAnswerHint = $('selectAnswerHint');
        const progressFill = $('progressFill');
        const progressText = $('progressText');
        const completionPopup = $('completionPopup');
        const finalScore = $('finalScore');
        const themeToggleBtn = $('themeToggleBtn');
        
        const sunIcon = '<i class="fas fa-sun"></i>';
        const moonIcon = '<i class="fas fa-moon"></i>';

        // --- Theme Logic ---
        function toggleTheme() {
            const isDarkMode = document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            themeToggleBtn.innerHTML = isDarkMode ? sunIcon : moonIcon;
            themeToggleBtn.style.backgroundColor = isDarkMode ? 'var(--color-button-secondary)' : 'var(--color-button-secondary)';
            themeToggleBtn.style.color = isDarkMode ? 'var(--color-button-secondary-text)' : 'var(--color-button-secondary-text)';
        }

        function applySavedTheme() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                themeToggleBtn.innerHTML = sunIcon;
            } else {
                document.body.classList.remove('dark-mode');
                themeToggleBtn.innerHTML = moonIcon;
            }
        }

        // --- Timer Logic ---
        let timeRemaining = quizDurationMinutes * 60; 
        let timerInterval;

        function startTimer() {
            timerInterval = setInterval(() => {
                timeRemaining--;
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (timeRemaining <= 300) { // 5 minutes remaining
                     timerDisplay.style.color = 'var(--color-heading-secondary)';
                     gsap.to(timerDisplay, { scale: 1.1, repeat: 1, yoyo: true, duration: 0.5 });
                }

                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.textContent = "00:00";
                    showCompletion(true);
                }
            }, 1000);
        }

        // --- Navigation Grid Logic ---
        function renderQuestionNavGrid() {
            questionNavGrid.innerHTML = '';
            DUMMY_QUESTIONS.forEach((q, index) => {
                const button = document.createElement('button');
                button.textContent = index + 1;
                button.className = `q-nav-button w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold transition-all duration-200`;
                button.onclick = () => jumpToQuestion(index);
                questionNavGrid.appendChild(button);
            });
            updateQuestionNavGridStates();
        }

        function updateQuestionNavGridStates() {
            const buttons = questionNavGrid.querySelectorAll('.q-nav-button');
            buttons.forEach((button, index) => {
                button.classList.remove('answered', 'current');
                if (DUMMY_QUESTIONS[index].user_answer_index !== null) {
                    button.classList.add('answered');
                }
                if (index === currentQuestionIndex) {
                    button.classList.add('current');
                }
            });
        }

        function jumpToQuestion(index) {
            if (index < 0 || index >= totalQuestions || index === currentQuestionIndex) return;
            currentQuestionIndex = index;
            renderQuestion();
        }

        // --- Main Quiz Content Render & Transition ---
        function renderQuestion() {
            const currentQuestion = DUMMY_QUESTIONS[currentQuestionIndex];
            headerQuestionCount.textContent = `Question ${currentQuestionIndex + 1} of ${totalQuestions}`;
            updateQuestionNavGridStates();
            updateProgress();
            updateNavigationButtons();
            
            // GSAP fade-out transition (Your required fade-in style)
            gsap.to(quizContent, { opacity: 0, y: -20, duration: 0.2, onComplete: () => {
                
                // Construct new question HTML
                quizContent.innerHTML = `
                    <div class="mb-4 p-3 rounded-lg border-l-4" style="background-color: var(--color-card-section-bg); border-color: var(--color-heading);">
                        <span class="text-sm font-bold" style="color: var(--color-text-on-section);">
                             Level: ${currentQuestion.id} / ${totalQuestions} - ${currentQuestion.type}
                        </span>
                    </div>
                    <h3 class="text-2xl font-extrabold mb-8 leading-relaxed" style="color: var(--color-text);">${currentQuestion.question_text}</h3>
                    <div id="choicesContainer" class="space-y-4 flex-1">
                        ${currentQuestion.choices.map((choice, index) => `
                            <label class="choice-card p-4 rounded-xl flex items-center group cursor-pointer"
                                data-index="${index}">
                                <input type="radio" name="question-${currentQuestion.id}" value="${index}"
                                    class="custom-radio mr-4"
                                    ${currentQuestion.user_answer_index === index ? 'checked' : ''}
                                    onchange="handleAnswer(${index})">
                                <span class="text-lg font-semibold" style="color: var(--color-text);">${choice.text}</span>
                            </label>
                        `).join('')}
                    </div>
                `;
                
                // GSAP fade-in transition
                gsap.fromTo(quizContent, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.3 });
            }});
        }

        function handleAnswer(choiceIndex) {
            DUMMY_QUESTIONS[currentQuestionIndex].user_answer_index = choiceIndex;
            
            const choicesContainer = $('choicesContainer');
            const choiceLabels = choicesContainer.querySelectorAll('.choice-card');
            
            // Update classes for visual feedback
            choiceLabels.forEach((label, index) => {
                label.classList.remove('selected');
                if (index === choiceIndex) {
                    label.classList.add('selected');
                    // Gamified feedback animation
                    gsap.fromTo(label, { scale: 1.01 }, { scale: 1, duration: 0.1, ease: "power1.out" });
                }
            });

            updateQuestionNavGridStates();
            updateProgress();
            updateNavigationButtons();
        }

        // --- Navigation Button Logic ---
        function updateNavigationButtons() {
            prevBtn.disabled = currentQuestionIndex === 0;
            prevBtn.classList.toggle('opacity-50', currentQuestionIndex === 0);
            prevBtn.classList.toggle('cursor-not-allowed', currentQuestionIndex === 0);

            const hasAnsweredCurrent = DUMMY_QUESTIONS[currentQuestionIndex].user_answer_index !== null;
            selectAnswerHint.classList.toggle('hidden', hasAnsweredCurrent);
            
            const isLastQuestion = currentQuestionIndex === totalQuestions - 1;

            if (isLastQuestion) {
                nextBtn.innerHTML = `Submit Final <i class="fas fa-check-circle ml-2"></i>`;
                nextBtn.style.backgroundColor = `var(--color-green-button)`;
                nextBtn.classList.add('submit-btn');
                headerSubmitBtn.style.backgroundColor = `var(--color-green-button)`;
            } else {
                nextBtn.innerHTML = `Next Quest <i class="fas fa-arrow-right ml-2"></i>`;
                nextBtn.style.backgroundColor = `var(--color-button-primary)`;
                nextBtn.classList.remove('submit-btn');
                headerSubmitBtn.style.backgroundColor = `var(--color-green-button)`; 
            }
        }

        // --- Event Listeners for Navigation ---
        prevBtn.addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                renderQuestion();
            }
        });

        const submitQuizHandler = () => {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                renderQuestion();
            } else {
                showCompletion();
            }
        };
        
        nextBtn.addEventListener('click', submitQuizHandler);
        headerSubmitBtn.addEventListener('click', showCompletion);


        // --- Progress Logic ---
        function updateProgress() {
            let answeredCount = DUMMY_QUESTIONS.filter(q => q.user_answer_index !== null).length;
            const progressPercentage = (answeredCount / totalQuestions) * 100;
            
            gsap.to(progressFill, { width: `${progressPercentage}%`, duration: 0.5, ease: "power1.out" });
            progressText.textContent = `${answeredCount} / ${totalQuestions} Quests Completed`;
        }

        // --- Completion / Results Logic ---
        function showCompletion(timeUp = false) {
            clearInterval(timerInterval); 

            let correctCount = 0;
            DUMMY_QUESTIONS.forEach(q => {
                if (q.user_answer_index !== null && q.choices[q.user_answer_index].is_correct) {
                    correctCount++;
                }
            });
            const scorePercentage = Math.round((correctCount / totalQuestions) * 100);

            finalScore.innerHTML = `You answered <span class="font-bold text-2xl" style="color: var(--color-heading);">${correctCount}</span> out of <span class="font-bold">${totalQuestions}</span> correctly.`;
            
            if (timeUp) {
                finalScore.innerHTML = `<p class="text-xl font-extrabold mb-2" style="color: var(--color-heading-secondary);">Time Limit Reached!</p>` + finalScore.innerHTML;
            } else {
                finalScore.innerHTML = `<p class="text-2xl font-extrabold mb-2" style="color: var(--color-heading-secondary);">Score: ${scorePercentage}%</p>` + finalScore.innerHTML;
            }
            
            completionPopup.classList.remove('hidden');
            gsap.fromTo(completionPopup, { opacity: 0 }, { opacity: 1, duration: 0.5 });
            gsap.fromTo(completionPopup.querySelector('div'), { scale: 0.8 }, { scale: 1, duration: 0.5, ease: "back.out(1.7)" });
        }

        // --- Initialization ---
        function initQuiz() {
            applySavedTheme();
            themeToggleBtn.addEventListener('click', toggleTheme);
            
            headerQuestionCount.textContent = `Question 1 of ${totalQuestions}`;
            progressText.textContent = `0 / ${totalQuestions} Quests Completed`;

            if (DUMMY_QUESTIONS.length === 0) {
                quizContent.innerHTML = `<p class='text-center text-lg mt-8' style="color: var(--color-text-secondary);">No quests available for this challenge yet!</p>`;
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                selectAnswerHint.style.display = 'none';
                return;
            }
            renderQuestionNavGrid();
            renderQuestion();
            startTimer();
        }

        initQuiz();
    </script>
</body>
</html>