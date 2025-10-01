<?php
include "../pdoconfig.php";
$_SESSION['current_page'] = "content";
$course_id = 1;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['topic_id']) && isset($_GET['module_id']) && isset($_GET['course_id'])) {
        $topic_id = $_GET['topic_id'];
        $module_id = $_GET['module_id'];
        $course_id = $_GET['course_id'];
    }
}

unset($_SESSION['progress']);
unset($_SESSION['topic_answer_details']);
unset($_SESSION['answeredCount']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lesson - Variables Quest</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <style>
/* --- Component Styles using CSS Variables --- */

.lesson-frame {
    border: 3px solid var(--color-heading);
    /* Creates a subtle 3D lift/depth for the main lesson block */
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3), 0 0 0 5px var(--color-heading-secondary); 
    background-color: var(--color-card-bg);
}

/* Style for the Highlight Box (Quick Fact) */
.highlight-box {
    box-shadow: inset 0 0 15px rgba(75, 37, 130, 0.5); /* Inner shadow for depth */
    border-radius: 1rem;
    background-color: var(--color-highlight-bg); /* Uses the added orange accent */
}

/* Style for the Code Blocks in the table */
.code-block {
    font-family: 'Consolas', 'Courier New', monospace;
    font-weight: bold;
    color: var(--color-heading-secondary); /* A bright, distinct color */
    background-color: var(--color-main-bg); /* Use main bg color for contrast */
    padding: 2px 4px;
    border-radius: 4px;
}

/* --- Interactive Button Styles (Navigation & Actions) --- */

.interactive-button {
    font-weight: bold;
    border-width: 2px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
}

/* Base Shadow/Lift */
.interactive-button:not([disabled]) {
    box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
}

/* Press/Click Effect: Simulates button being pushed down */
.interactive-button:active {
    transform: translateY(2px);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
}

/* Primary Action (Next/Previous/Continue) */
.primary-action {
    background-color: var(--color-button-primary);
    color: white;
    border-color: var(--color-button-primary);
    box-shadow: 0 4px 0 var(--color-button-primary-hover); /* Use hover color for shadow */
}
.primary-action:active {
    box-shadow: 0 2px 0 var(--color-button-primary-hover);
}

/* Secondary Action (Back to Topics/Close) */
.secondary-action {
    background-color: var(--color-button-secondary);
    color: var(--color-button-secondary-text);
    border-color: var(--color-button-secondary-text);
    box-shadow: 0 4px 0 var(--color-button-secondary-text); /* Use text color for shadow */
}
.secondary-action:active {
    box-shadow: 0 2px 0 var(--color-button-secondary-text);
}

/* Success Action (Take Assessment) */
.success-action {
    background-color: var(--color-green-button);
    color: var(--color-card-bg); /* White or light text on the button color */
    border-color: var(--color-green-button-hover);
    box-shadow: 0 4px 0 var(--color-green-button-hover);
}
.success-action:hover {
    filter: brightness(1.1);
}
.success-action:active {
    box-shadow: 0 2px 0 var(--color-green-button-hover);
}


/* --- Quiz Popup Styles --- */

.quiz-popup {
    box-shadow: 0 0 20px 5px rgba(0, 0, 0, 0.5);
    background-color: var(--color-card-bg); 
    border: 4px solid var(--color-heading);
}

/* Quiz Option Button Base Style */
.quiz-option {
    background-color: var(--color-main-bg); /* Use main bg for interactive look */
    border: 2px solid var(--color-card-border);
    color: var(--color-text);
    font-weight: 600;
}
.quiz-option:hover {
    border-color: var(--color-heading-secondary);
    background-color: var(--color-toggle-bg); /* Subtle green hover */
    transform: translateY(-2px);
}

/* Correct Answer (Feedback) */
.correct-answer {
    background-color: var(--color-green-button) !important;
    color: white !important;
    border-color: var(--color-green-button-hover) !important;
    box-shadow: 0 4px 0 var(--color-green-button-hover);
}

/* Incorrect Answer (Feedback) */
.incorrect-answer {
    background-color: var(--color-red-button) !important;
    color: white !important;
    border-color: var(--color-red-button-hover) !important;
    box-shadow: 0 4px 0 var(--color-red-button-hover);
}

/* Hint for Correct Answer after an incorrect attempt */
.correct-answer-hint {
    /* Uses the RGB helper variable for transparency effect */
    background-color: rgba(var(--color-green-button-rgb), 0.3) !important; 
    border-color: var(--color-green-button) !important;
    color: var(--color-text) !important;
}

/* Carousel Indicator Active State */
.active-indicator {
    background-color: var(--color-heading-secondary) !important; 
}
    </style>
</head>
<body class="min-h-screen flex font-sans" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">What is a Variable?</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Introduction to Python > Variables > What is a Variable?</h6>
            </div>
            <a href="topicCard.php" class="px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold">
                 <i class="fas fa-list-ul mr-2"></i> Back to Topics
            </a>
        </header>

        <main class="p-8 max-w-4xl mx-auto flex-1 flex flex-col w-full">

            <div class="mb-8 border-b pb-4" style="border-color: var(--color-heading-secondary);">
                <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Variables and Data Types</h1>
                <h2 class="text-xl font-bold" style="color: var(--color-heading-secondary);">The building blocks of every program.</h2>
            </div>

            <div class="lesson-frame flex-1 flex flex-col p-6 rounded-xl shadow-2xl">
            
                <div id="lesson-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <p class="text-lg leading-relaxed text-center p-8" style="color: var(--color-text);">
                                A **variable** is like a **container** or a box that holds information. When you write code, you often need to store pieces of data, like numbers, text, or true/false values. Variables give you a way to label and store this data so you can use it later. Think of it as a labeled box you can put things into and take things out of. The label is the **variable's name**, and the thing inside is the **value**.
                            </p>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="p-6 rounded-xl highlight-box w-full max-w-xl" 
                                    style="border: 2px solid var(--color-heading-secondary);">
                                <p class="text-xl font-extrabold text-center" style="color: var(--color-text-on-section);">
                                    <i class="fas fa-star mr-2"></i> **Quick Fact:** In Python, you don't need to explicitly declare the data type. Python is **dynamically typed** and figures it out for you automatically!
                                </p>
                                <p class="mt-4 text-center font-bold" style="color: var(--color-text-on-section);">
                                    👉 Get ready! A Quick Checkpoint will pop up on the next screen.
                                </p>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-lg mx-auto">
                                <h3 class="text-2xl font-bold mb-4" style="color: var(--color-heading);">Key Characteristics:</h3>
                                <ul class="list-none space-y-4 text-lg">
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-code mr-3 mt-1" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Dynamic Typing:** You don't have to declare a variable's type.
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-redo-alt mr-3 mt-1" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Mutable:** Variables can be changed to hold new values, making them flexible.
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-rocket mr-3 mt-1" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Reusability:** The same variable name can be efficiently used throughout your code.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="flex justify-center w-full max-w-xl mx-auto">
                                <img src="https://placehold.co/800x450/22c55e/fefce8?text=Variable+as+a+Box" alt="An illustration of a variable as a labeled storage container." 
                                        class="rounded-xl shadow-lg w-full ring-2" style="border-color: var(--color-heading);">
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-xl mx-auto">
                                <h3 class="text-2xl font-bold mb-4" style="color: var(--color-heading);">Watch This to Level Up:</h3>
                                <div class="relative w-full rounded-xl overflow-hidden shadow-2xl" style="padding-top: 56.25%; border: 4px solid var(--color-heading-secondary);">
                                    <iframe class="absolute top-0 left-0 w-full h-full"
                                        src="https://www.youtube.com/embed/videoseries?list=PL-osiE80TeTsqnO9w-s-MZMjgFk_R6JzB" 
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex flex-col items-center justify-center">
                            <div class="w-full max-w-2xl mx-auto">
                                <h3 class="text-2xl font-bold mb-4 text-center" style="color: var(--color-heading);">Common Data Types (Inventory)</h3>
                                <div class="overflow-x-auto rounded-lg border-2 shadow-lg" style="border-color: var(--color-heading);">
                                    <table class="w-full text-sm" style="color: var(--color-text);">
                                        <thead class="text-xs uppercase" style="background-color: var(--color-heading);">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 font-extrabold text-white">Type</th>
                                                <th scope="col" class="px-6 py-3 font-extrabold text-white">Example</th>
                                                <th scope="col" class="px-6 py-3 font-extrabold text-white">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-6 py-4 font-bold" style="color: var(--color-heading-secondary);">Integer</td>
                                                <td class="px-6 py-4 code-block">`age = 25`</td>
                                                <td class="px-6 py-4">A whole number (no decimals).</td>
                                            </tr>
                                            <tr class="border-b" style="border-color: var(--color-card-border);">
                                                <td class="px-6 py-4 font-bold" style="color: var(--color-heading-secondary);">Float</td>
                                                <td class="px-6 py-4 code-block">`price = 9.99`</td>
                                                <td class="px-6 py-4">A number with a decimal point.</td>
                                            </tr>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-6 py-4 font-bold" style="color: var(--color-heading-secondary);">String</td>
                                                <td class="px-6 py-4 code-block">`name = "Alice"`</td>
                                                <td class="px-6 py-4">Text, enclosed in quotes.</td>
                                            </tr>
                                            <tr>
                                                <td class="px-6 py-4 font-bold" style="color: var(--color-heading-secondary);">Boolean</td>
                                                <td class="px-6 py-4 code-block">`is_valid = True`</td>
                                                <td class="px-6 py-4">A true or false value.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                             <div class="p-6 rounded-xl highlight-box w-full max-w-lg text-center" 
                                 style="border: 2px solid var(--color-heading-secondary);">
                                <h3 class="text-2xl font-extrabold mb-3" style="color: var(--color-heading);">Challenge Complete!</h3>
                                <p class="text-xl leading-relaxed" style="color: var(--color-text-on-section);">
                                    You've mastered the fundamentals. Proceed to the assessment to finish the lesson and earn your rewards!
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 p-4 border-t" style="border-color: var(--color-card-border);">
                    <button id="prev-button" class="px-6 py-2 rounded-full transition interactive-button primary-action flex items-center invisible">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    
                    <div id="carousel-indicators" class="flex space-x-3">
                        </div>

                    <button id="next-button" class="px-6 py-2 rounded-full transition interactive-button primary-action flex items-center">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <a href="assessmentTopicConfirmation.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&topic_id=<?=$topic_id?>" id="assessment-button" 
                        class="px-6 py-2 rounded-full transition interactive-button success-action flex items-center font-extrabold" 
                        style="display: none;">
                        Complete Quest <i class="fas fa-check-circle ml-2"></i>
                    </a>
                </div>
            </div>
        </main>
        
        <div id="popup-question" class="fixed inset-0 flex items-center justify-center z-50 hidden" 
            style="background-color: var(--color-popup-bg);">
            <div class="w-11/12 max-w-md p-8 rounded-xl shadow-2xl space-y-6 quiz-popup">
                <h3 class="text-3xl font-extrabold text-center flex items-center justify-center" style="color: var(--color-heading);">
                    <i class="fas fa-dungeon mr-3"></i> Quick Checkpoint
                </h3>
                <p class="text-lg text-center" style="color: var(--color-text);">
                    In Python, what is the main purpose of a variable?
                </p>
                <div class="space-y-3">
                    <button class="w-full px-4 py-3 rounded-full text-left transition answer-button quiz-option" data-answer="1">
                        A) To make code more complicated.
                    </button>
                    <button class="w-full px-4 py-3 rounded-full text-left transition answer-button quiz-option" data-answer="2">
                        B) To hold and label data for later use.
                    </button>
                    <button class="w-full px-4 py-3 rounded-full text-left transition answer-button quiz-option" data-answer="3">
                        C) To generate random numbers.
                    </button>
                </div>
                <div id="feedback" class="text-center text-xl font-extrabold"></div>
                <div class="w-full text-center pt-2">
                    <button id="close-popup" class="px-6 py-2 rounded-full transition interactive-button secondary-action" disabled>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function applyThemeFromLocalStorage() {
            // Placeholder for theme application
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

        // --- Carousel and Popup Logic ---
        const carouselInner = document.getElementById('carousel-inner');
        const nextButton = document.getElementById('next-button');
        const prevButton = document.getElementById('prev-button');
        const assessmentButton = document.getElementById('assessment-button');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const items = document.querySelectorAll('.carousel-item');
        
        const popup = document.getElementById('popup-question');
        const closePopup = document.getElementById('close-popup');
        const feedbackDiv = document.getElementById('feedback');
        const answerButtons = document.querySelectorAll('.answer-button');

        let currentIndex = 0;
        const totalItems = items.length;
        // Checkpoint triggers when navigating from index 1 to index 2 (Slide 3)
        const popUpTriggerIndex = 2; 
        let hasAnsweredCorrectly = localStorage.getItem('questionAnswered') === 'true';

        // Initialize carousel indicators
        function createIndicators() {
            indicatorsContainer.innerHTML = ''; 
            for (let i = 0; i < totalItems; i++) {
                const dot = document.createElement('div');
                dot.classList.add('w-3', 'h-3', 'rounded-full', 'cursor-pointer', 'transition-all');
                dot.style.backgroundColor = 'var(--color-card-border)';
                dot.dataset.index = i;
                dot.addEventListener('click', () => showSlide(i));
                indicatorsContainer.appendChild(dot);
            }
        }

        // Show a specific slide
        function showSlide(index) {
            currentIndex = Math.max(0, Math.min(index, totalItems - 1));
            carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateIndicators();
            updateNavigationButtons();
        }
        
        // Function to handle the quiz checkpoint trigger
        function checkCheckpoint() {
            // Trigger popup if we land on the checkpoint slide (index 2) AND haven't answered correctly yet.
            if (currentIndex === popUpTriggerIndex && !hasAnsweredCorrectly) {
                // Prevent further navigation until the quiz is addressed
                nextButton.disabled = true; 
                nextButton.classList.add('opacity-50');
                popup.classList.remove('hidden');
                // Ensure close button starts disabled until an answer is selected
                closePopup.disabled = true; 
            } else {
                nextButton.disabled = false;
                nextButton.classList.remove('opacity-50');
            }
        }

        // Event listener for the Next button
        nextButton.addEventListener('click', () => {
            if (currentIndex < totalItems - 1) {
                currentIndex++;
                showSlide(currentIndex);
                // Checkpoint is checked AFTER the slide transition
                checkCheckpoint(); 
            }
        });

        // Event listener for the Previous button
        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                showSlide(currentIndex);
                // Re-enable Next button if user navigates back from the checkpoint slide
                if (currentIndex < popUpTriggerIndex) {
                    nextButton.disabled = false;
                    nextButton.classList.remove('opacity-50');
                }
            }
        });

        // Update indicator styles
        function updateIndicators() {
            const indicators = indicatorsContainer.querySelectorAll('div');
            indicators.forEach((dot, i) => {
                dot.classList.remove('active-indicator');
                dot.style.backgroundColor = 'var(--color-card-border)'; // Default color
                if (i === currentIndex) {
                    dot.classList.add('active-indicator');
                }
            });
        }
        
        // Update button visibility based on slide index
        function updateNavigationButtons() {
             if (currentIndex === 0) {
                 prevButton.classList.add('invisible');
             } else {
                 prevButton.classList.remove('invisible');
             }

             if (currentIndex === totalItems - 1) {
                 nextButton.style.display = 'none';
                 assessmentButton.style.display = 'flex';
             } else {
                 nextButton.style.display = 'flex';
                 assessmentButton.style.display = 'none';
                 // Re-check the checkpoint state to ensure the Next button is disabled if required
                 if (currentIndex === popUpTriggerIndex && !hasAnsweredCorrectly) {
                     nextButton.disabled = true;
                     nextButton.classList.add('opacity-50');
                 } else {
                     nextButton.disabled = false;
                     nextButton.classList.remove('opacity-50');
                 }
             }
        }

        // Popup question logic
        closePopup.addEventListener('click', () => {
            popup.classList.add('hidden');
            feedbackDiv.innerHTML = '';
            
            // Reset answer button state
            answerButtons.forEach(btn => {
                btn.classList.remove('correct-answer', 'incorrect-answer', 'correct-answer-hint');
                btn.classList.add('quiz-option'); // Restore base class
                btn.disabled = false; // Re-enable for the next time it appears
            });
            
            closePopup.disabled = true; // Re-disable for future use
            
            // If answered correctly, enable navigation
            if (hasAnsweredCorrectly) {
                nextButton.disabled = false; 
                nextButton.classList.remove('opacity-50');
            } else {
                // If incorrect, show the current slide again and keep next button disabled
                showSlide(currentIndex);
            }
        });

        answerButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                const selectedAnswer = event.target.dataset.answer;
                const correctAnswer = '2'; 
                
                answerButtons.forEach(btn => btn.disabled = true); // Disable all buttons after selection

                if (selectedAnswer === correctAnswer) {
                    feedbackDiv.innerHTML = '<i class="fas fa-trophy mr-2"></i> Quest objective complete! You earned 5 XP!';
                    feedbackDiv.style.color = 'var(--color-green-button)';
                    event.target.classList.add('correct-answer');
                    localStorage.setItem('questionAnswered', 'true');
                    hasAnsweredCorrectly = true;
                } else {
                    feedbackDiv.innerHTML = '<i class="fas fa-bomb mr-2"></i> Incorrect. Review and try again next time!';
                    feedbackDiv.style.color = 'var(--color-red-button)';
                    event.target.classList.add('incorrect-answer');
                    
                    // Highlight the correct answer hint
                    document.querySelector(`[data-answer="${correctAnswer}"]`).classList.add('correct-answer-hint');
                }
                
                event.target.classList.remove('quiz-option');
                closePopup.disabled = false; // Enable the close button after the quiz is attempted
            });
        });

        // Initial setup
        document.addEventListener('DOMContentLoaded', () => {
            createIndicators();
            // Start at slide 0, or skip checkpoint if already cleared
            if (hasAnsweredCorrectly) {
                 showSlide(0); 
            } else {
                showSlide(0);
            }
            checkCheckpoint(); // Ensures checkpoint logic runs on page load if user is on the trigger slide.
        });

    </script>
</body>
</html>