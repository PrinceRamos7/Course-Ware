<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn - Quest Log</title>
    <link rel="stylesheet" href="../output.css"> 
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>

        body {
            padding: 0;
            min-height: 100vh;
            background-color: var(--color-main-bg); 
            color: var(--color-text); 
        }

        /* Header Optimization */
        .main-header {
            padding: 0.75rem 1.5rem; 
            background-color: var(--color-header-bg); 
            border-bottom: 3px solid var(--color-heading); 
            backdrop-filter: blur(5px);
        }

        /* Main Content Optimization */
        main {
            padding: 1.5rem; 
            flex-grow: 1; 
        }

        /* Styling for the central quiz frame */
        .lesson-frame {
            border: 4px solid var(--color-heading); 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15), 0 0 0 5px var(--color-xp-bg); 
            height: 100%;
            min-height: 55vh;
            background-color: var(--color-card-bg); 
            padding: 1rem;
        }
        
        /* Question Card Professional Styling - "Quest Details" */
        .question-card {
            background-color: var(--color-user-bg); 
            border: 2px solid var(--color-heading); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            position: relative; 
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Quiz Option Styling - "Action Buttons" */
        .quiz-option {
            background-color: var(--color-card-bg); 
            border: 2px solid var(--color-card-border); 
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--color-text);
            box-shadow: 0 3px 0 var(--color-card-border); 
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .quiz-option:hover {
            border-color: var(--color-heading-secondary); 
            transform: translateY(-1px); 
            box-shadow: 0 4px 0 var(--color-heading-secondary); 
            background-color: var(--color-sidebar-link-hover);
        }
        
        /* Temporary Selection State */
        .quiz-option.selected {
            border-color: var(--color-heading); 
            background-color: var(--color-sidebar-link-active); 
            box-shadow: 0 3px 0 var(--color-heading); 
            transform: translateY(0); 
        }

        /* Navigation Buttons */
        #next-button, #prev-button {
            transition: background-color 0.2s, transform 0.1s;
            background-color: var(--color-button-primary);
            color: white;
            box-shadow: 0 4px 0 var(--color-button-primary-hover);
            border-radius: 9999px;
            padding: 0.6rem 1.25rem;
            font-size: 0.95rem;
        }

        #next-button:hover:not(:disabled), #prev-button:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(1px);
            box-shadow: 0 2px 0 var(--color-button-primary-hover);
        }
        
        /* Submit Link Styling (Final Action Button) */
        #submit-link {
            background-color: var(--color-heading) !important;
            box-shadow: 0 4px 0 #14532d !important; /* Hardcode a darker shade of heading for 3D effect */
            padding: 0.75rem 1.5rem;
        }
        #submit-link:hover {
            background-color: #14532d !important;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #14532d !important;
        }

        /* XP/Points Display Header Styling */
        .xp-display {
            background-color: var(--color-xp-bg); 
            color: var(--color-xp-text); 
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        /* Progress Bar Styling */
        #progress-bar-fill {
            background: var(--color-progress-fill); /* Use the gradient fill */
            border-radius: 9999px;
            height: 100%;
            transition: width 0.5s ease-out;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        /* Question point badge */
        .point-badge {
            position: absolute;
            top: -10px; 
            right: 10px;
            background-color: var(--color-heading-secondary); 
            color: white;
            padding: 0.4rem 0.8rem;
            box-shadow: 0 4px 0 #c2410c; 
            border: 2px solid white;
            animation: pulse-shadow 2s infinite alternate;
        }
        
        /* Ensure progress bar track uses the correct background and border */
        .w-full.h-4.mb-6 {
            background-color: var(--color-progress-bg);
            border-color: var(--color-heading);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans">

    <header class="main-header shadow-xl px-8 flex justify-between items-center sticky top-0 z-10">
        
        <div class="flex flex-col">
            <h1 class="text-2xl font-extrabold" style="color: var(--color-heading);">🎯 Python Quest Log</h1>
            <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Module Assessment: Focused Review</h6>
        </div>

        <div id="total-points-display" class="xp-display flex items-center">
            <i class="fas fa-coins mr-2"></i> Total Points: <span class="ml-1 font-extrabold" id="quiz-total-points">0 / 650</span>
        </div> 
    </header>

    <main class="max-w-6xl mx-auto flex-1 flex flex-col w-full min-h-full"> 

        <div class="mb-4 text-center">
            <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Module Assessment</h1>
            <h2 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Section 1: Variables & Data Types</h2>
        </div>
        
        <div class="w-full h-4 mb-6 rounded-full border-2 border-green-700">
            <div id="progress-bar-fill" style="width: 0%;"></div>
        </div>

        <div class="lesson-frame flex-1 flex flex-col rounded-xl shadow-2xl">
            
            <form id="module-assessment-form" class="flex-1 flex flex-col">
                
                <div id="assessment-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">
                        </div>
                </div>

                <div class="flex justify-between items-center mt-2 p-3 border-t" style="border-color: var(--color-card-border);">
                    
                    <button type="button" id="prev-button" class="transition font-semibold flex items-center invisible">
                        <i class="fas fa-arrow-left mr-2"></i> Previous Quest
                    </button>
                    
                    <div id="progress-text" class="text-sm font-semibold" style="color: var(--color-text-secondary);">
                        Loading...
                    </div>

                    <a href="assessmentModuleResult.php" id="submit-link" 
                        class="rounded-full transition font-extrabold text-lg flex items-center justify-center" 
                        style="display: none; color: white; text-decoration: none;"
                        aria-label="Finish and Continue to Submit Assessment">
                        <i class="fas fa-gavel mr-3"></i> Finish & Continue
                    </a>

                    <button type="button" id="next-button" disabled class="transition font-semibold flex items-center opacity-50">
                        Next Quest <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    
                </div>
            </form>
        </div>
    </main>

    <script>
        function applyThemeFromLocalStorage() {
            // This function is still good to keep for theme toggling logic
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

        // --- Quiz Data (Unchanged) ---
        const questions = [
            { 
                question: "1. Which of the following is an example of a **Boolean** data type?", 
                options: ["'99'", "is_complete = True", "5.0"], 
                correctAnswerIndex: 1, 
                basePoints: 100
            },
            { 
                question: "2. What operator is used for **exponentiation** (raising to a power) in Python?", 
                options: ["^", "**", "//"], 
                correctAnswerIndex: 1, 
                basePoints: 150 
            },
            { 
                question: "3. Which data structure is ordered, mutable, and allows duplicate members?", 
                options: ["Tuple", "List", "Set", "Dictionary"], 
                correctAnswerIndex: 1, 
                basePoints: 200 
            },
            { 
                question: "4. In Python, which keyword is used to define a function?", 
                options: ["function", "def", "func", "define"], 
                correctAnswerIndex: 1, 
                basePoints: 120 
            },
            { 
                question: "5. What is the result of `10 % 3`?", 
                options: ["3", "1", "3.33", "0"], 
                correctAnswerIndex: 1, 
                basePoints: 80 
            }
        ];

        const totalPossiblePoints = questions.reduce((sum, q) => sum + q.basePoints, 0);

        // --- State ---
        let currentIndex = 0;
        const totalQuestions = questions.length;
        const answers = new Array(totalQuestions).fill(null); 
        const totalItems = totalQuestions + 1; 

        // --- DOM Elements ---
        const carouselInner = document.getElementById('carousel-inner');
        const nextButton = document.getElementById('next-button');
        const prevButton = document.getElementById('prev-button');
        const submitLink = document.getElementById('submit-link'); 
        const progressBarFill = document.getElementById('progress-bar-fill');
        const progressText = document.getElementById('progress-text');
        const totalPointsDisplay = document.getElementById('quiz-total-points');
        
        // --- HTML Generation Functions ---

        function generateQuestionHTML(qData, index) {
            const optionsHTML = qData.options.map((option, optIndex) => {
                const content = option.includes('`') ? `<code class="font-mono text-lg">${option}</code>` : `<p class="text-lg">${option}</p>`;

                return `
                    <div class="quiz-option p-4 rounded-lg flex items-center" data-q-index="${index}" data-opt-index="${optIndex}">
                        <span class="text-lg font-extrabold mr-4" style="color: var(--color-heading-secondary);">${String.fromCharCode(65 + optIndex)}.</span> 
                        ${content}
                        <input type="radio" name="q_${index}_answer" value="${optIndex}" class="hidden">
                    </div>
                `;
            }).join('');

            return `
                <div class="carousel-item min-w-full h-full flex flex-col justify-between p-1">
                    <div class="question-card flex-1 flex flex-col justify-center"> 
                        
                        <div class="point-badge">
                            <i class="fas fa-star mr-1"></i> ${qData.basePoints} XP
                        </div>

                        <p class="text-sm font-bold mb-3" style="color: var(--color-heading-secondary);">
                            QUEST ${index + 1} / ${totalQuestions}
                        </p>
                        <h4 class="text-xl font-extrabold mb-6" style="color: var(--color-text);">
                            ${qData.question}
                        </h4>
                        <div class="space-y-3 option-group" data-q-index="${index}">
                            ${optionsHTML}
                        </div>
                    </div>
                </div>
            `;
        }

        function generateCompletionSlideHTML() {
              return `
                <div class="carousel-item min-w-full p-1 h-full flex flex-col justify-center items-center">
                    <div class="question-card p-6 text-center" style="max-width: 500px; border-top: 4px solid var(--color-heading-secondary); transform: scale(1.02);">
                        <i class="fas fa-scroll text-5xl mb-3" style="color: var(--color-heading);"></i>
                        <h3 class="text-2xl font-extrabold mb-2" style="color: var(--color-heading);">Quest Log Complete!</h3>
                        <p class="text-lg leading-relaxed font-bold mb-3" style="color: var(--color-heading-secondary);">
                            You've faced all ${totalQuestions} challenges.
                        </p>
                        <p class="text-base leading-relaxed" style="color: var(--color-text);">
                            Proceed to the **Finish & Continue** button to finalize your submission and claim your rewards!
                        </p>
                    </div>
                </div>
            `;
        }

        // --- Core Quiz Logic (Unchanged for interactivity) ---

        function initializeQuiz() {
            questions.forEach((q, index) => {
                carouselInner.innerHTML += generateQuestionHTML(q, index);
            });
            carouselInner.innerHTML += generateCompletionSlideHTML();
            
            initializeOptionListeners();
            
            totalPointsDisplay.textContent = `0 / ${totalPossiblePoints}`;
            showSlide(0);
        }

        function initializeOptionListeners() {
            document.querySelectorAll('.quiz-option').forEach(option => {
                option.addEventListener('click', function() {
                    const qIndex = parseInt(this.dataset.qIndex);
                    selectAnswer(qIndex, this);
                });
            });
        }
        
        function selectAnswer(qIndex, selectedOptionElement) {
            const selectedOptIndex = parseInt(selectedOptionElement.dataset.optIndex);
            
            answers[qIndex] = selectedOptIndex;

            const optionGroup = selectedOptionElement.closest('.option-group');
            const allOptions = optionGroup.querySelectorAll('.quiz-option');

            allOptions.forEach((opt) => {
                opt.classList.remove('selected', 'correct', 'incorrect');
            });
            
            selectedOptionElement.classList.add('selected');

            if (qIndex < totalQuestions - 1 || answers.every(ans => ans !== null)) {
                nextButton.disabled = false;
                nextButton.classList.remove('opacity-50');
            }
        }
        
        function showSlide(index) {
            currentIndex = Math.max(0, Math.min(index, totalItems - 1));
            carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateNavigation();
            updateProgress();
            
            const isQuestionSlide = currentIndex < totalQuestions;

            if (isQuestionSlide) {
                const currentAnswer = answers[currentIndex];
                const currentSlide = carouselInner.children[currentIndex].querySelector('.question-card');
                const allOptions = currentSlide.querySelectorAll('.quiz-option');
                
                allOptions.forEach((opt, optIndex) => {
                    opt.classList.remove('selected', 'correct', 'incorrect');
                    if (currentAnswer !== null && optIndex === currentAnswer) {
                        opt.classList.add('selected');
                    }
                });
                
                if (currentAnswer === null) { 
                    nextButton.disabled = true;
                    nextButton.classList.add('opacity-50');
                } else {
                    nextButton.disabled = false;
                    nextButton.classList.remove('opacity-50');
                }
            }
        }
        
        function updateProgress() {
            const answeredCount = answers.filter(ans => ans !== null).length;
            const progress = currentIndex < totalQuestions ? answeredCount : totalQuestions;
            const percentage = Math.round((progress / totalQuestions) * 100);
            
            progressBarFill.style.width = `${percentage}%`;
            
            if (currentIndex < totalQuestions) {
                progressText.textContent = `Progress: ${answeredCount} of ${totalQuestions} Quests Completed`;
            } else {
                progressText.textContent = `Assessment Complete - Ready for Submission!`;
            }

            // Simplified point calculation for display
            totalPointsDisplay.textContent = `${answeredCount * 100} / ${totalPossiblePoints}`; 
        }
        
        function updateNavigation() {
            prevButton.classList.toggle('invisible', currentIndex === 0);
            
            if (currentIndex === totalQuestions) {
                nextButton.style.display = 'none';
                submitLink.style.display = 'flex';
            } else {
                nextButton.style.display = 'flex';
                submitLink.style.display = 'none';
                
                if (currentIndex === totalQuestions - 1 && answers[currentIndex] !== null) {
                    nextButton.textContent = 'Review & Finish 📜';
                } else {
                    nextButton.textContent = 'Next Quest';
                }
            }
        }

        // --- Event Listeners ---
        nextButton.addEventListener('click', () => {
            if (currentIndex < totalItems - 1) {
                showSlide(currentIndex + 1);
            }
        });

        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                showSlide(currentIndex - 1);
            }
        });

        document.addEventListener('DOMContentLoaded', initializeQuiz);
    </script>
</body>
</html>