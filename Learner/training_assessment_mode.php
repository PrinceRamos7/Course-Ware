<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Assessment Mode - ISU</title>
    <link rel="stylesheet" href="../output.css">
    
    <style>
        /* Specific state colors for feedback - using direct hex codes */
        .bg-correct { background-color: #d1fae5; border-color: #10b981; } /* Light Emerald Green */
        .text-correct { color: #047857; }
        .bg-wrong { background-color: #fee2e2; border-color: #ef4444; } /* Light Red */
        .text-wrong { color: #b91c1c; }
        .border-selected { border-color: #15803d; border-width: 3px; } 

        .progress-fill { 
            background: linear-gradient(to right, #22c55e, #facc15, #f97316); /* Your custom progress fill gradient */
            transition: width 0.7s ease-out; /* Smooth progress */
        }
        .combo-pulse {
            animation: pulse-combo 0.5s infinite alternate;
        }
        @keyframes pulse-combo {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(1.05); opacity: 0.9; }
        }
        .choice-item:hover {
            transform: scale(1.015); /* More impactful hover */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .correct-animation {
            animation: shake-correct 0.3s ease-in-out;
        }
        @keyframes shake-correct {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-[#fefce8] min-h-screen p-4 md:p-8 font-inter antialiased">

    <div class="max-w-4xl mx-auto">
        
        <header class="mb-8 p-4 rounded-xl shadow-2xl border border-[#e5e7eb] bg-white sticky top-0 z-10">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-extrabold text-[#15803d]">
                    <i class="fas fa-rocket text-[#eab308] mr-2"></i> Training Challenge
                </h1>
                <div id="combo-tracker" class="flex items-center text-xl font-black text-[#f97316] bg-[#fde68a] p-2 rounded-full shadow-lg border-2 border-[#f59e0b] min-w-[120px] justify-center transition-all duration-300">
                    <i class="fas fa-fire mr-2"></i> Combo: <span id="current-streak">0</span>
                </div>
            </div>

            <div>
                <p class="text-sm font-bold text-[#475569] mb-1">Mission Progress: <span id="current-question-index">0</span> / <span id="total-questions">0</span></p>
                <div class="h-4 w-full bg-[#e5e7eb] rounded-full overflow-hidden shadow-inner">
                    <div id="xp-bar" class="h-4 progress-fill rounded-full" style="width: 0%;"></div>
                </div>
            </div>
        </header>

        <div id="quiz-content-container">
            <div id="quiz-card" class="bg-white p-6 rounded-xl shadow-xl border border-[#e5e7eb] transition-all duration-300">
                <div id="question-container" class="mb-6">
                    <div class="bg-[#ecfccb] p-4 rounded-lg border border-[#d1d5db] mb-4">
                        <p class="text-lg font-semibold text-[#14532d]">CHALLENGE <span id="q-number">1</span>:</p>
                        <p id="question-text" class="text-xl font-bold text-[#0f172a] mt-2">Loading question...</p>
                    </div>
                </div>

                <div id="choices-container" class="space-y-3 mb-8">
                    </div>
            </div>

            <div id="feedback-card" class="mt-6 hidden">
                <h2 id="feedback-title" class="text-3xl font-extrabold mb-4 text-[#f97316] flex items-center"></h2>
                <div class="bg-white p-5 rounded-xl shadow-xl border border-[#e5e7eb]">
                    <p id="feedback-message" class="text-lg font-semibold mb-4 text-[#0f172a]"></p>
                    
                    <div id="explanation-details" class="space-y-4">
                        <h3 class="text-xl font-bold text-[#15803d] border-b border-[#e5e7eb] pb-2 mb-4">Mastery Breakdown:</h3>
                        </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex justify-end">
            <button id="action-button" onclick="handleNext()" disabled
                class="w-full sm:w-auto bg-[#f97316] text-white font-black py-4 px-10 rounded-full shadow-2xl shadow-[#f97316]/50 transition-all duration-300 transform hover:scale-[1.05] hover:bg-[#c2410c] disabled:opacity-50 disabled:cursor-not-allowed text-lg uppercase tracking-wider">
                Select Answer
            </button>
        </div>


        <div id="completion-screen" class="bg-white p-10 rounded-xl shadow-2xl border border-[#e5e7eb] text-center hidden transform scale-0 transition-transform duration-500 ease-out">
            <i class="fas fa-medal text-8xl text-[#eab308] mb-6 animate-pulse"></i>
            <h2 class="text-4xl font-extrabold text-[#15803d] mb-4">MISSION ACCOMPLISHED!</h2>
            <p class="text-xl text-[#475569] mb-6">Your training level is complete. You've earned your stars!</p>
            <p class="text-3xl font-bold text-[#f97316] mt-4 p-4 bg-[#fef08a] rounded-lg inline-block">
                Final Score: <span id="final-score">0</span> / <span id="total-final-questions">0</span>
            </p>
            <button onclick="window.location.reload()"
                class="mt-8 bg-[#22c55e] text-white font-bold py-3 px-8 rounded-lg shadow-xl hover:bg-[#16a34a] transition-colors duration-200 uppercase">
                Start New Campaign
            </button>
        </div>
    </div>
    
    <script >
    
const quizQuestions = [
 ..
    {
        question: "Which of the following is the primary purpose of a database 'Primary Key'?",
        choices: [
            { text: "To link tables together in a Many-to-Many relationship.", explanation: "Primary keys are used to link tables, but their *primary* purpose is ensuring uniqueness and identification, not specifically defining the type of relationship." },
            { text: "To uniquely identify each record in a table.", explanation: "Correct! The Primary Key constraint ensures that all values in a column are unique, providing a stable identifier for every row (record)." },
            { text: "To index the column for faster searching on non-unique values.", explanation: "Primary keys are indexed for speed, but they *must* contain unique values. A non-unique index is typically a 'Secondary Index'." },
            { text: "To prevent NULL values from being inserted into the column.", explanation: "This is the function of a 'NOT NULL' constraint, which is often applied to Primary Keys, but is not the key purpose of the Primary Key itself." }
        ],
        correctAnswerIndex: 1
    },
    {
        question: "In object-oriented programming, what is 'Encapsulation'?",
        choices: [
            { text: "The ability of an object to take on many forms.", explanation: "This concept refers to Polymorphism, not Encapsulation." },
            { text: "The process of hiding the internal state and requiring all interaction to be done through the object's methods.", explanation: "Correct! Encapsulation bundles data (attributes) and methods that operate on the data, and restricts direct access to some of the object's components." },
            { text: "Creating a new class based on an existing class.", explanation: "This describes Inheritance, where a new class (subclass) inherits properties and methods from an existing class (superclass)." },
            { text: "Breaking a complex system into smaller, manageable sub-systems.", explanation: "This describes Modularization or Decomposition, a general software design principle, not the specific OOP concept of Encapsulation." }
        ],
        correctAnswerIndex: 1
    },
    {
        question: "What is the result of '2' + 2 in JavaScript?",
        choices: [
            { text: "4", explanation: "While this is true in strongly typed languages like Python or Java (after conversion), in JavaScript, the string concatenation rule takes precedence." },
            { text: "22", explanation: "Correct! Due to type coercion, when the '+' operator is used with a string and a number, the number is converted to a string, and concatenation occurs." },
            { text: "Error: Type Mismatch", explanation: "JavaScript is loosely typed and attempts to coerce types instead of throwing a type mismatch error for the '+' operator." },
            { text: "NaN", explanation: "NaN (Not a Number) usually results from illegal mathematical operations, but string concatenation is a valid operation here." }
        ],
        correctAnswerIndex: 1
    },
    {
        question: "Which HTML tag is used to define an internal style sheet?",
        choices: [
            { text: "<css>", explanation: "There is no `<css>` tag in standard HTML." },
            { text: "<style>", explanation: "Correct! The `<style>` tag is used in the `<head>` section of an HTML document to define CSS styles for the page." },
            { text: "<script>", explanation: "The `<script>` tag is used for embedding or linking JavaScript code." },
            { text: "<link>", explanation: "The `<link>` tag is used to link to external resources, such as an external CSS file." }
        ],
        correctAnswerIndex: 1
    },
    {
        question: "What is the concept of 'Cloud Computing' that allows users to rent fully managed hardware and software environments, ready for applications?",
        choices: [
            { text: "Infrastructure as a Service (IaaS)", explanation: "IaaS provides access to computing resources (VMs, storage, networks), but the user manages the OS and application." },
            { text: "Software as a Service (SaaS)", explanation: "SaaS provides a complete, finished application (like Gmail or Salesforce), not an environment for building your own." },
            { text: "Platform as a Service (PaaS)", explanation: "Correct! PaaS provides a platform (OS, runtime, database, middleware) for developers to deploy, manage, and run applications without managing the underlying infrastructure." },
            { text: "Function as a Service (FaaS)", explanation: "FaaS is a subset of serverless computing, focused on running small, event-triggered blocks of code." }
        ],
        correctAnswerIndex: 2
    }
];

// --- State Variables (Kept streak) ---
let currentQuestionIndex = 0;
let selectedAnswerIndex = -1;
let score = 0;
let isAnswerChecked = false;
let currentStreak = 0; 

// --- DOM Elements (Updated/Added) ---
const qIndexSpan = document.getElementById('current-question-index');
const totalQuestionsSpan = document.getElementById('total-questions');
const qNumberSpan = document.getElementById('q-number');
const questionText = document.getElementById('question-text');
const choicesContainer = document.getElementById('choices-container');
const feedbackCard = document.getElementById('feedback-card');
const feedbackTitle = document.getElementById('feedback-title');
const feedbackMessage = document.getElementById('feedback-message');
const explanationDetails = document.getElementById('explanation-details');
const quizCard = document.getElementById('quiz-card');
const completionScreen = document.getElementById('completion-screen');
const finalScoreSpan = document.getElementById('final-score');
const totalFinalQuestionsSpan = document.getElementById('total-final-questions');
const actionButton = document.getElementById('action-button'); 

// Gamified Elements
const xpBar = document.getElementById('xp-bar'); // Renamed ID from the HTML, but kept logic here.
const comboTracker = document.getElementById('combo-tracker');
const currentStreakSpan = document.getElementById('current-streak');


// --- Core Functions ---

/**
 * Updates the Progress bar width.
 */
function updateProgress() {
    const progressPercent = ((currentQuestionIndex) / quizQuestions.length) * 100;
    xpBar.style.width = `${progressPercent}%`;
    qIndexSpan.textContent = currentQuestionIndex; // Show completed questions
}

/**
 * Updates the Combo/Streak visual.
 */
function updateStreakDisplay(isCorrect) {
    currentStreakSpan.textContent = currentStreak;
    comboTracker.classList.remove('combo-pulse', 'bg-red-200'); // Reset classes

    if (currentStreak >= 2) {
        // Apply pulsing effect for a high streak
        comboTracker.classList.add('combo-pulse');
        comboTracker.classList.add('bg-[#fde68a]');
        comboTracker.classList.remove('bg-gray-100');
    } else if (!isCorrect && currentStreak === 0) {
        // Show a reset/missed visual
        comboTracker.classList.remove('bg-[#fde68a]');
        comboTracker.classList.add('bg-red-200');
    } else {
         comboTracker.classList.remove('bg-red-200');
         comboTracker.classList.add('bg-[#fde68a]');
    }
}

/**
 * Renders the current question and its choices.
 */
function renderQuestion() {
    if (currentQuestionIndex >= quizQuestions.length) {
        showCompletionScreen();
        return;
    }

    const currentQ = quizQuestions[currentQuestionIndex];
    
    // Update Header and Question text
    qIndexSpan.textContent = currentQuestionIndex + 1; // Show current question number
    qNumberSpan.textContent = currentQuestionIndex + 1;
    totalQuestionsSpan.textContent = quizQuestions.length;
    questionText.textContent = currentQ.question;
    
    // Reset controls and state
    isAnswerChecked = false;
    selectedAnswerIndex = -1;
    actionButton.textContent = 'Commit Answer'; // Gamified button text
    actionButton.disabled = true;
    
    // Switch views
    feedbackCard.classList.add('hidden');
    quizCard.classList.remove('hidden');

    // Render choices
    choicesContainer.innerHTML = '';
    currentQ.choices.forEach((choice, index) => {
        const choiceElement = document.createElement('div');
        choiceElement.className = 'choice-item relative p-4 rounded-xl bg-white border border-[#e5e7eb] cursor-pointer transition-all duration-200 shadow-md hover:shadow-xl hover:bg-yellow-50 transform';
        
        choiceElement.innerHTML = `<span class="font-bold text-[#14532d] mr-3">${String.fromCharCode(65 + index)}.</span><span class="text-[#0f172a] font-medium">${choice.text}</span>`;
        choiceElement.setAttribute('data-index', index);
        
        // Add click listener
        choiceElement.onclick = (e) => selectAnswer(index, e.currentTarget);
        
        choicesContainer.appendChild(choiceElement);
    });
    
    updateProgress();
}

/**
 * Handles the selection of a choice.
 */
function selectAnswer(index, element) {
    if (isAnswerChecked) return; 
    
    // Remove selection border/scale from all choices
    document.querySelectorAll('.choice-item').forEach(item => {
        item.classList.remove('border-selected', 'scale-[1.015]');
    });

    // Add selection border/scale to the current choice for visual feedback
    element.classList.add('border-selected', 'scale-[1.015]');
    selectedAnswerIndex = index;
    actionButton.disabled = false;
    actionButton.textContent = 'Check Answer'; 
}

/**
 * Combined function to handle both checking the answer and moving to the next question.
 */
function handleNext() {
    if (!isAnswerChecked) {
        // --- Step 1: Check Answer Logic (First Click) ---
        if (selectedAnswerIndex === -1) return; 
        
        isAnswerChecked = true;

        const currentQ = quizQuestions[currentQuestionIndex];
        const isCorrect = selectedAnswerIndex === currentQ.correctAnswerIndex;

        if (isCorrect) {
            score++;
            currentStreak++; // Increment streak
            showFeedback(true);
        } else {
            currentStreak = 0; // Reset streak
            showFeedback(false);
        }

        updateStreakDisplay(isCorrect); // Update streak visual

        // Lock all choices visually and mark results
        document.querySelectorAll('.choice-item').forEach((item, index) => {
            item.classList.remove('border-selected', 'cursor-pointer', 'hover:shadow-xl', 'hover:bg-yellow-50', 'transform');
            item.onclick = null; // Disable further clicks
            
            const isCorrectChoice = index === currentQ.correctAnswerIndex;
            const isSelectedChoice = index === selectedAnswerIndex;

            // Apply visual feedback based on correctness
            if (isCorrectChoice) {
                // Add a cool animation for the correct answer
                item.classList.add('bg-correct', 'border-green-500', 'shadow-2xl', 'shadow-green-500/50', 'ring-4', 'ring-green-200', 'correct-animation');
                item.querySelector('span:first-child').classList.add('text-correct');
            } else if (isSelectedChoice) {
                // Strong visual for the wrong answer
                item.classList.add('bg-wrong', 'border-red-500', 'shadow-2xl', 'shadow-red-500/50', 'ring-4', 'ring-red-200');
                item.querySelector('span:first-child').classList.add('text-wrong');
            }
        });

        // Update button for next action
        actionButton.textContent = (currentQuestionIndex + 1) < quizQuestions.length ? 'Continue Mission 🚀' : 'Finalize Score';
        actionButton.disabled = false;
        
        updateProgress();

    } else {
        // --- Step 2: Next Question Logic (Second Click) ---
        currentQuestionIndex++;
        renderQuestion();
    }
}

/**
 * Displays the feedback and explanation card.
 */
function showFeedback(isCorrect) {
    quizCard.classList.add('hidden'); 
    feedbackCard.classList.remove('hidden');

    if (isCorrect) {
        // Removed XP Multiplier text
        feedbackTitle.innerHTML = `<i class="fas fa-star mr-2"></i> LEVEL UP! (+1 Score)`;
        feedbackTitle.classList.remove('text-[#f97316]');
        feedbackTitle.classList.add('text-[#15803d]');
        feedbackMessage.textContent = currentStreak > 1 ? `Amazing! Your ${currentStreak}-Question Combo is active. Keep the streak alive!` : "Target acquired! You chose the correct answer. Review the explanation to solidify your mastery.";
    } else {
        feedbackTitle.innerHTML = `<i class="fas fa-times-circle mr-2"></i> STREAK BROKEN! (Combo Reset)`;
        feedbackTitle.classList.remove('text-[#15803d]');
        feedbackTitle.classList.add('text-[#f97316]');
        feedbackMessage.textContent = "Error. The selected answer was incorrect. Read the detailed breakdown below to understand the reasoning and prevent future mistakes.";
    }

    // Populate Detailed Explanation (Logic is mostly fine)
    const currentQ = quizQuestions[currentQuestionIndex];
    explanationDetails.innerHTML = '<h3 class="text-xl font-bold text-[#15803d] border-b border-[#e5e7eb] pb-2 mb-4">Mastery Breakdown:</h3>'; 
    
    currentQ.choices.forEach((choice, index) => {
        const isCorrectChoice = index === currentQ.correctAnswerIndex;
        const isUserChoice = index === selectedAnswerIndex;

        let statusClass = 'bg-gray-100 border-gray-300';
        let statusIcon = 'ⓘ';
        
        if (isCorrectChoice) {
            statusClass = 'bg-correct border-green-400';
            statusIcon = '✅ Correct Answer';
        } else if (isUserChoice && !isCorrect) {
            statusClass = 'bg-wrong border-red-400';
            statusIcon = '❌ Your Failure Point (Incorrect)';
        } else if (!isCorrectChoice) {
            statusClass = 'bg-yellow-50 border-yellow-200';
            statusIcon = '💡 Alternative Path';
        }

        const detailElement = document.createElement('div');
        detailElement.className = `${statusClass} p-4 rounded-lg border shadow-sm transition-all duration-300`;
        detailElement.innerHTML = `
            <p class="font-bold text-lg text-[#0f172a] mb-1">${String.fromCharCode(65 + index)}. ${choice.text}</p>
            <p class="text-sm font-semibold ${isCorrectChoice ? 'text-correct' : (isUserChoice && !isCorrect ? 'text-wrong' : 'text-[#475569]')}">${statusIcon}</p>
            <p class="text-[#475569] mt-2">${choice.explanation}</p>
        `;
        explanationDetails.appendChild(detailElement);
    });
}

/**
 * Shows the final completion screen.
 */
function showCompletionScreen() {
    document.getElementById('quiz-content-container').classList.add('hidden'); // Hide the main content
    actionButton.classList.add('hidden'); // Hide action button on final screen
    completionScreen.classList.remove('hidden');
    
    finalScoreSpan.textContent = score;
    totalFinalQuestionsSpan.textContent = quizQuestions.length;
    
    // Add a scaling animation for impact
    setTimeout(() => {
        completionScreen.classList.remove('scale-0');
        completionScreen.classList.add('scale-100');
    }, 50);
}

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    totalQuestionsSpan.textContent = quizQuestions.length;
    totalFinalQuestionsSpan.textContent = quizQuestions.length;
    renderQuestion();
    updateStreakDisplay(true); // Initialize streak to 0
});
    </script>
</body>
</html>