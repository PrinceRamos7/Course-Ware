<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>
        /* Styling for the central quiz frame */
        .lesson-frame {
            border: 3px solid var(--color-heading);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1), 0 0 0 5px var(--color-user-bg); 
            height: 100%;
            background-color: var(--color-card-bg); 
        }
        
        /* Question Card Professional Styling */
        .question-card {
            background-color: var(--color-user-bg); 
            border-radius: 0.75rem;
            border: 1px solid var(--color-card-border);
            position: relative; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        /* Quiz Option Styling */
        .quiz-option {
            background-color: var(--color-card-bg); 
            border: 2px solid var(--color-card-border);
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--color-text);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .quiz-option:hover {
            border-color: var(--color-heading); 
            transform: scale(1.005);
            background-color: var(--color-sidebar-link-hover);
        }
        
        /* Temporary Selection State */
        .quiz-option.selected {
            border-color: var(--color-heading-secondary); /* Accent border for selection */
            background-color: var(--color-sidebar-link-active); 
            box-shadow: 0 0 5px rgba(245, 158, 11, 0.7);
        }

        /* Navigation Buttons (for consistency) */
        #next-button, #prev-button {
            transition: background-color 0.2s, transform 0.1s;
            background-color: var(--color-button-primary);
            color: white;
            box-shadow: 0 4px 0 var(--color-button-primary-hover);
        }

        #next-button:hover:not(:disabled), #prev-button:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(2px);
            box-shadow: 0 2px 0 var(--color-button-primary-hover);
        }
        
        /* Disabled state for opacity */
        #next-button.opacity-50, #prev-button.opacity-50 {
            box-shadow: none;
            transform: none;
        }

        /* Submit Link Styling (to mimic a button) */
        #submit-link:hover {
            background-color: #14532d !important;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #14532d !important;
        }
        body{
            padding:0;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans" style="background-color: var(--color-main-bg); color: var(--color-text); ">

    <!-- Header (Gamification removed) -->
    <header class="main-header p-4 shadow-xl px-8 py-3 flex justify-between items-center sticky top-0 z-10" 
        style="background-color: var(--color-header-bg); border-bottom: 2px solid var(--color-card-border); backdrop-filter: blur(5px);">
        
        <div class="flex flex-col">
            <h1 class="text-2xl font-bold" style="color: var(--color-heading);">Python Fundamentals Module</h1>
            <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Module Assessment: Focused Review</h6>
        </div>

        <!-- Space holder for alignment -->
        <div class="w-24"></div> 
    </header>

    <main class="p-8 max-w-6xl mx-auto flex-1 flex flex-col w-full min-h-full"> 

        <div class="mb-6 text-center">
            <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Module Assessment</h1>
            <h2 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Section 1: Variables & Data Types</h2>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full h-3 mb-6 rounded-full" style="background-color: var(--color-progress-bg);">
            <div id="progress-bar-fill" style="width: 0%;"></div>
        </div>

        <div class="lesson-frame flex-1 flex flex-col p-6 rounded-xl shadow-2xl">
            
            <!-- Note: Form submission is now handled by the final anchor tag link -->
            <form id="module-assessment-form" class="flex-1 flex flex-col">
                
                <div id="assessment-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">
                        <!-- Content goes here -->
                    </div>
                </div>

                <!-- Navigation and Submit Controls -->
                <div class="flex justify-between items-center mt-6 p-4 border-t" style="border-color: var(--color-card-border);">
                    
                    <button type="button" id="prev-button" class="px-6 py-2 rounded-full transition font-semibold flex items-center invisible">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    
                    <div id="progress-text" class="text-sm font-semibold" style="color: var(--color-text-secondary);">
                        Loading...
                    </div>

                    <!-- REPLACED BUTTON WITH ANCHOR TAG FOR SUBMISSION -->
                    <a href="submitAssessment.php" id="submit-link" 
                        class="px-8 py-2 rounded-full transition font-extrabold text-lg flex items-center justify-center" 
                        style="display: none; background-color: var(--color-heading); color: white; box-shadow: 0 4px 0 #14532d; text-decoration: none;"
                        aria-label="Finish and Continue to Submit Assessment">
                        <i class="fas fa-gavel mr-3"></i> Finish & Continue
                    </a>

                    <button type="button" id="next-button" disabled class="px-6 py-2 rounded-full transition font-semibold flex items-center opacity-50">
                        Next Question <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    
                </div>
            </form>
        </div>
    </main>

    <script>
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

        // --- Quiz Data (Easily expandable) ---

        const questions = [
            { 
                question: "1. Which of the following is an example of a **Boolean** data type?", 
                options: ["'99'", "is_complete = True", "5.0"], 
                correctAnswerIndex: 1, 
                basePoints: 100 // Base points kept for structure, but not used in this file's logic
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

        // --- State ---
        let currentIndex = 0;
        const totalQuestions = questions.length;
        // Stores selected answer index for each question
        const answers = new Array(totalQuestions).fill(null); 
        const totalItems = totalQuestions + 1; // +1 for the final completion slide

        // --- DOM Elements ---
        const carouselInner = document.getElementById('carousel-inner');
        const nextButton = document.getElementById('next-button');
        const prevButton = document.getElementById('prev-button');
        // Reference the new link element
        const submitLink = document.getElementById('submit-link'); 
        const progressBarFill = document.getElementById('progress-bar-fill');
        const progressText = document.getElementById('progress-text');
        
        // --- HTML Generation Functions ---

        function generateQuestionHTML(qData, index) {
            const optionsHTML = qData.options.map((option, optIndex) => `
                <div class="quiz-option p-4 rounded-lg flex items-center" data-q-index="${index}" data-opt-index="${optIndex}">
                    <span class="text-lg font-semibold mr-3">${String.fromCharCode(65 + optIndex)}.</span> 
                    ${option.includes('`') ? option : `<p>${option}</p>`}
                    <input type="radio" name="q_${index}_answer" value="${optIndex}" class="hidden">
                </div>
            `).join('');

            return `
                <div class="carousel-item min-w-full p-2 h-full flex flex-col justify-between">
                    <div class="question-card p-6 flex-1 flex flex-col justify-center"> 
                        <p class="text-sm font-bold mb-4" style="color: var(--color-heading-secondary);">
                            Question ${index + 1} / ${totalQuestions}
                        </p>
                        <h4 class="text-2xl font-semibold mb-6" style="color: var(--color-text);">
                            ${qData.question}
                        </h4>
                        <div class="space-y-4 option-group" data-q-index="${index}">
                            ${optionsHTML}
                        </div>
                    </div>
                </div>
            `;
        }

        function generateCompletionSlideHTML() {
             return `
                <div class="carousel-item min-w-full p-2 h-full flex flex-col justify-center items-center">
                    <div class="question-card p-8 text-center" style="max-width: 500px; border-top: 4px solid var(--color-heading);">
                        <i class="fas fa-check-circle text-6xl mb-4" style="color: var(--color-heading);"></i>
                        <h3 class="text-3xl font-extrabold mb-3" style="color: var(--color-heading);">Assessment Review Complete!</h3>
                        <p class="text-xl leading-relaxed font-bold mb-3" style="color: var(--color-heading-secondary);">
                            You have answered all ${totalQuestions} questions.
                        </p>
                        <p class="text-lg leading-relaxed" style="color: var(--color-text);">
                            Click the **Finish & Continue** button below to formally submit your assessment on the next page.
                        </p>
                    </div>
                </div>
            `;
        }

        // --- Core Quiz Logic ---

        function initializeQuiz() {
            // 1. Generate all question slides and the completion slide
            questions.forEach((q, index) => {
                carouselInner.innerHTML += generateQuestionHTML(q, index);
            });
            carouselInner.innerHTML += generateCompletionSlideHTML();
            
            // 2. Attach listeners
            initializeOptionListeners();
            
            // 3. Initial UI update
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
        
        // Handles selection UI and recording the answer (no grading)
        function selectAnswer(qIndex, selectedOptionElement) {
            const selectedOptIndex = parseInt(selectedOptionElement.dataset.optIndex);
            
            // 1. Store the selected answer
            answers[qIndex] = selectedOptIndex;

            // 2. Apply visual feedback for selection 
            const optionGroup = selectedOptionElement.closest('.option-group');
            const allOptions = optionGroup.querySelectorAll('.quiz-option');

            allOptions.forEach((opt) => {
                // Clear any previous selection or grading feedback (since grading is now external)
                opt.classList.remove('selected', 'correct', 'incorrect');
            });
            
            selectedOptionElement.classList.add('selected');

            // 3. Enable Next button if an answer is selected
            nextButton.disabled = false;
            nextButton.classList.remove('opacity-50');
        }
        
        // --- Navigation and UI Updates ---

        function showSlide(index) {
            currentIndex = Math.max(0, Math.min(index, totalItems - 1));
            carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateNavigation();
            updateProgress();
            
            const isQuestionSlide = currentIndex < totalQuestions;

            // Restore selection state for the current slide if it's a question
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
                
                // Disable Next button if no answer is selected
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
            // Progress tracks answered questions + the final slide transition
            const progress = currentIndex; 
            const percentage = Math.round((progress / totalQuestions) * 100);
            
            if (currentIndex < totalQuestions) {
                progressBarFill.style.width = `${percentage}%`;
                progressText.textContent = `Question ${currentIndex + 1} of ${totalQuestions}`;
            } else {
                progressBarFill.style.width = `100%`;
                progressText.textContent = `Assessment Complete`;
            }
        }
        
        function updateNavigation() {
            // Previous button visibility
            prevButton.classList.toggle('invisible', currentIndex === 0);
            
            // Next vs. Submit Link visibility
            if (currentIndex === totalQuestions) {
                nextButton.style.display = 'none';
                submitLink.style.display = 'flex'; // Show the submission link
            } else {
                nextButton.style.display = 'flex';
                submitLink.style.display = 'none'; // Hide the submission link
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
        
        // Note: The original form submission logic has been removed as per request.
        // The final action is now a simple navigation via the anchor tag.

        // Initial setup
        document.addEventListener('DOMContentLoaded', initializeQuiz);

    </script>
</body>
</html>
