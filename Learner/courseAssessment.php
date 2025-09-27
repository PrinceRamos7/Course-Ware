<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GMetrix-Style Assessment</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Firebase SDKs -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import { getFirestore, collection, doc, setDoc, query, orderBy, onSnapshot, serverTimestamp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // Global variables for Firebase access
        window.initializeApp = initializeApp;
        window.getAuth = getAuth;
        window.signInAnonymously = signInAnonymously;
        window.signInWithCustomToken = signInWithCustomToken;
        window.onAuthStateChanged = onAuthStateChanged;
        window.getFirestore = getFirestore;
        window.collection = collection;
        window.doc = doc;
        window.setDoc = setDoc;
        window.query = query;
        window.orderBy = orderBy;
        window.onSnapshot = onSnapshot;
        window.serverTimestamp = serverTimestamp;
    </script>
    <style>
        /* Define custom color variables for the theme */
        :root {
            --color-main-bg: #f8f8f8;
            --color-heading: #1f2937; /* Dark Gray */
            --color-heading-secondary: #0f172a; /* Slate 900 */
            --color-text: #374151; /* Medium Gray */
            --color-text-secondary: #6b7280; /* Light Gray */
            --color-card-bg: #ffffff;
            --color-card-border: #e5e7eb;
            --color-card-section-bg: #f3f4f6;
            --color-green-button: #10b981; /* Emerald */
            --color-button-primary: #3b82f6; /* Blue */
            --color-button-primary-hover: #1d4ed8;
            --color-icon: #f59e0b; /* Amber */
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
        }
        
        .assessment-card {
            border: 3px solid var(--color-card-border);
            background-color: var(--color-card-bg);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .mode-card {
            border: 2px solid var(--color-card-border);
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .mode-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
        }
        
        .training-color { color: var(--color-green-button); }
        .testing-color { color: var(--color-heading-secondary); }

        .interactive-button {
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease;
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
            text-transform: uppercase;
        }
        .interactive-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
        }
        
        .feedback-panel-correct { background-color: var(--color-green-button); color: white; }
        .feedback-panel-wrong { background-color: #ef4444; color: white; } /* Tailwind red-500 */
        .training-tip {
             background-color: var(--color-card-section-bg);
             border-left: 5px solid var(--color-icon);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start py-12 px-4">

    <!-- Icon Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <!-- ####################################### -->
    <!-- MAIN APP CONTAINER -->
    <!-- ####################################### -->
    <main id="app-container" class="w-full flex justify-center"></main>

    <!-- ####################################### -->
    <!-- TEMPLATES (HTML is injected here by JS) -->
    <!-- ####################################### -->
    
    <template id="mode-selector-template">
        <div class="max-w-4xl w-full p-8">
            <header class="text-center mb-10">
                <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">GMetrix-Style Assessment</h1>
                <p class="text-xl font-semibold" style="color: var(--color-text-secondary);">Select Your Quest Mode</p>
                <div id="user-id-display" class="mt-4 text-xs font-mono" style="color: var(--color-text-secondary);"></div>
            </header>

            <div class="flex flex-col md:flex-row gap-8">
                
                <!-- Training Mode Card -->
                <div id="mode-training" class="mode-card flex-1 p-8 rounded-xl text-center bg-[var(--color-card-bg)] hover:border-[var(--color-green-button)]">
                    <i class="fas fa-graduation-cap text-6xl mb-4 training-color"></i>
                    <h2 class="text-2xl font-bold mb-3 training-color">Training Mode (Learning Quest)</h2>
                    <p class="text-sm mb-4" style="color: var(--color-text-secondary);">
                        **Immediate Feedback:** Get detailed explanations and definitions after every question submission. Learn from your mistakes in real-time.
                    </p>
                    <button class="w-full py-3 rounded-lg text-white font-bold interactive-button" 
                            style="background-color: var(--color-green-button); box-shadow: 0 4px 0 var(--color-button-primary-hover);">
                        Start Training
                    </button>
                </div>

                <!-- Testing Mode Card -->
                <div id="mode-testing" class="mode-card flex-1 p-8 rounded-xl text-center bg-[var(--color-card-bg)] hover:border-[var(--color-heading-secondary)]">
                    <i class="fas fa-fist-raised text-6xl mb-4 testing-color"></i>
                    <h2 class="text-2xl font-bold mb-3 testing-color">Testing Mode (Exam Quest)</h2>
                    <p class="text-sm mb-4" style="color: var(--color-text-secondary);">
                        **Standard Exam:** No feedback or hints until the assessment is 100% complete. Designed to measure true knowledge under exam conditions.
                    </p>
                    <button class="w-full py-3 rounded-lg text-white font-bold interactive-button" 
                            style="background-color: var(--color-heading-secondary); box-shadow: 0 4px 0 var(--color-heading-secondary);">
                        Start Exam
                    </button>
                </div>
            </div>

            <!-- Past Attempts Table -->
            <div class="mt-12 p-6 rounded-xl assessment-card">
                <h3 class="text-xl font-bold mb-4" style="color: var(--color-heading);">
                    <i class="fas fa-chart-bar mr-2"></i> Past Attempts
                </h3>
                <div id="attempts-table-container" class="overflow-x-auto">
                    <!-- Attempts will be injected here -->
                </div>
            </div>
        </div>
    </template>
    
    <template id="assessment-template">
        <div class="max-w-xl w-full p-4">
            <header class="text-center mb-6">
                <h1 id="mode-title" class="text-3xl font-extrabold"></h1>
                <p id="mode-subtitle" class="text-md" style="color: var(--color-heading-secondary);"></p>
            </header>

            <div class="assessment-card p-6 rounded-xl">
                
                <!-- Question Content -->
                <div class="mb-6">
                    <p id="question-text" class="text-xl font-semibold mb-4" style="color: var(--color-text);"></p>
                    <div id="options-container" class="space-y-3">
                        <!-- Options injected here -->
                    </div>
                </div>
                
                <!-- Action Button -->
                <button id="submit-btn" class="w-full py-3 rounded-lg text-white font-extrabold interactive-button" 
                        style="background-color: var(--color-button-primary); box-shadow: 0 4px 0 var(--color-button-primary-hover);">
                    Submit Answer
                </button>
                
                <!-- TRAINING MODE FEEDBACK PANEL -->
                <div id="feedback-panel" class="hidden mt-6">
                    
                    <!-- Dynamic feedback banner (Correct/Wrong) -->
                    <div id="feedback-banner" class="p-4 rounded-t-lg text-center font-bold text-lg"></div>

                    <!-- Detailed Explanation (Definition) -->
                    <div class="p-4 rounded-b-lg training-tip text-left" style="color: var(--color-text);">
                        <h4 class="text-md font-extrabold mb-2" style="color: var(--color-heading-secondary);">
                            <i class="fas fa-book-open mr-1"></i> Definition & Explanation
                        </h4>
                        <p id="explanation-text" class="text-sm"></p>
                    </div>
                </div>
                
            </div>
        </div>
    </template>

    <template id="results-template">
        <div class="max-w-xl w-full p-4">
            <div class="assessment-card p-8 rounded-xl text-center">
                <h1 id="results-title" class="text-4xl font-extrabold mb-4">
                    <i class="fas fa-medal mr-2"></i> Quest Complete!
                </h1>
                <p id="results-message" class="text-xl mb-6" style="color: var(--color-text)"></p>
                
                <div class="py-4 border-y-2 mb-6" style="border-color: var(--color-card-border)">
                    <p id="results-score" class="text-5xl font-black"></p>
                    <p class="text-lg font-semibold" style="color: var(--color-text-secondary)">Final Score</p>
                </div>
                
                <p id="results-summary" class="text-lg font-semibold mb-6" style="color: var(--color-text)"></p>

                <button id="restart-btn"
                        class="w-full py-3 rounded-lg text-white font-extrabold interactive-button" 
                        style="background-color: var(--color-icon); box-shadow: 0 4px 0 var(--color-heading-secondary)">
                    Return to Mode Selection
                </button>
            </div>
        </div>
    </template>
    
    <template id="attempts-table-template">
        <table class="min-w-full text-sm rounded-lg overflow-hidden">
            <thead class="text-left font-semibold" style="background-color: var(--color-card-section-bg); color: var(--color-text-secondary);">
                <tr>
                    <th class="p-3">Mode</th>
                    <th class="p-3">Score</th>
                    <th class="p-3">Correct</th>
                    <th class="p-3">Date</th>
                </tr>
            </thead>
            <tbody id="attempts-tbody" style="color: var(--color-text);">
                <!-- Rows injected here -->
            </tbody>
        </table>
    </template>

    <!-- ####################################### -->
    <!-- JAVASCRIPT LOGIC -->
    <!-- ####################################### -->
    <script type="module">
        // --- Global Data: Sample Questions ---
        const QUESTIONS = [
            {
                id: 1,
                question: "What is the primary purpose of the `def` keyword in Python?",
                options: { A: "To define a class object.", B: "To declare a variable.", C: "To define a new function.", D: "To start a loop structure." },
                correctAnswer: "C",
                explanation: "The **`def`** keyword in Python is specifically used to **define a function or method**. This creates a reusable block of code. Classes are defined using `class`.",
                topic: "Python",
            },
            {
                id: 2,
                question: "In HTML, which tag is used to define an internal style sheet?",
                options: { A: "<script>", B: "<css>", C: "<style>", D: "<link>" },
                correctAnswer: "C",
                explanation: "The **`<style>`** tag is used to define CSS styles directly within the HTML document. The `<link>` tag is used to reference external style sheets.",
                topic: "Web Development",
            },
            {
                id: 3,
                question: "Which of the following data structures operates on a Last-In, First-Out (LIFO) principle?",
                options: { A: "Queue", B: "Linked List", C: "Stack", D: "Tree" },
                correctAnswer: "C",
                explanation: "A **Stack** follows the LIFO principle (Last-In, First-Out). The last element added to the stack is the first one removed.",
                topic: "Data Structures",
            },
        ];

        // --- State Management ---
        const state = {
            mode: null, // 'training' or 'testing'
            currentQuestionIndex: 0,
            userAnswers: {}, // {0: 'A', 1: 'C', ...}
            isSubmitted: false, // For training mode feedback
            selectedOption: null,
            isFinished: false,
            userId: null,
            db: null,
            auth: null,
            appId: typeof __app_id !== 'undefined' ? __app_id : 'default-app-id',
            pastAttempts: [],
        };
        
        // --- Firebase/Firestore Handlers ---
        
        async function initializeFirebase() {
            try {
                const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
                const app = window.initializeApp(firebaseConfig);
                state.db = window.getFirestore(app);
                state.auth = window.getAuth(app);

                const initialToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;
                
                if (initialToken) {
                    await window.signInWithCustomToken(state.auth, initialToken);
                } else {
                    await window.signInAnonymously(state.auth);
                }

                window.onAuthStateChanged(state.auth, (user) => {
                    if (user) {
                        state.userId = user.uid;
                    } else {
                        state.userId = crypto.randomUUID();
                    }
                    render();
                    setupAttemptsListener();
                });
            } catch (error) {
                console.error("Firebase initialization failed:", error);
                // Fallback for UI if Firebase fails
                state.userId = 'Error: Check Console';
                render(); 
            }
        }

        function setupAttemptsListener() {
            if (!state.db || !state.userId) return;

            const attemptsRef = window.collection(state.db, `/artifacts/${state.appId}/users/${state.userId}/assessment_attempts`);
            const q = window.query(attemptsRef, window.orderBy("timestamp", "desc"));

            window.onSnapshot(q, (snapshot) => {
                state.pastAttempts = snapshot.docs.map(doc => ({
                    id: doc.id,
                    ...doc.data()
                }));
                renderPastAttempts();
            }, (error) => {
                console.error("Error fetching attempts:", error);
            });
        }
        
        async function saveAttempt(score, correctCount) {
            if (!state.db || !state.userId) {
                console.error("Firestore not initialized or user ID missing. Cannot save attempt.");
                return;
            }

            const attemptDocRef = window.doc(window.collection(state.db, `/artifacts/${state.appId}/users/${state.userId}/assessment_attempts`));

            try {
                await window.setDoc(attemptDocRef, {
                    mode: state.mode,
                    score: score,
                    correctCount: correctCount,
                    totalQuestions: QUESTIONS.length,
                    timestamp: window.serverTimestamp(),
                    answers: state.userAnswers,
                });
                console.log("Attempt saved successfully!");
            } catch (error) {
                console.error("Error saving attempt to Firestore:", error);
            }
        }

        // --- Assessment Logic ---

        function calculateScore() {
            let correctCount = 0;
            QUESTIONS.forEach((q, index) => {
                if (state.userAnswers[index] === q.correctAnswer) {
                    correctCount++;
                }
            });
            const score = (correctCount / QUESTIONS.length) * 100;
            return { correctCount, score };
        }

        function handleNext() {
            // Record the answer
            state.userAnswers[state.currentQuestionIndex] = state.selectedOption;
            state.selectedOption = null;
            state.isSubmitted = false;

            if (state.currentQuestionIndex < QUESTIONS.length - 1) {
                state.currentQuestionIndex++;
                render();
            } else {
                // End of Assessment
                const { score, correctCount } = calculateScore();
                saveAttempt(score, correctCount);
                state.isFinished = true;
                render();
            }
        }

        function handleSubmit() {
            if (!state.selectedOption) {
                // Use custom message box instead of alert
                showCustomMessage("Validation Error", "Please select an answer before proceeding.");
                return;
            }

            if (state.mode === 'training') {
                if (state.isSubmitted) {
                    // Already submitted, move to next question
                    handleNext();
                } else {
                    // First submission in training mode: show feedback
                    state.isSubmitted = true;
                    render();
                }
            } else if (state.mode === 'testing') {
                // Testing mode: record answer and move to the next question immediately
                handleNext();
            }
        }

        function handleOptionChange(key) {
            if (state.mode === 'training' && state.isSubmitted) return;
            state.selectedOption = key;
            render();
        }

        // --- Rendering Functions ---

        const appContainer = document.getElementById('app-container');

        function render() {
            appContainer.innerHTML = '';
            
            if (!state.mode) {
                renderModeSelection();
            } else if (state.isFinished) {
                renderResults();
            } else {
                renderAssessment();
            }
        }

        function renderModeSelection() {
            const template = document.getElementById('mode-selector-template');
            const clone = document.importNode(template.content, true);
            
            clone.querySelector('#mode-training').addEventListener('click', () => {
                state.mode = 'training';
                state.currentQuestionIndex = 0;
                state.isFinished = false;
                state.userAnswers = {};
                render();
            });
            
            clone.querySelector('#mode-testing').addEventListener('click', () => {
                state.mode = 'testing';
                state.currentQuestionIndex = 0;
                state.isFinished = false;
                state.userAnswers = {};
                render();
            });
            
            const userIdDisplay = clone.querySelector('#user-id-display');
            userIdDisplay.textContent = `User ID: ${state.userId || 'Authenticating...'}`;

            appContainer.appendChild(clone);
            renderPastAttempts();
        }
        
        function renderPastAttempts() {
            const tableContainer = document.getElementById('attempts-table-container');
            if (!tableContainer) return; // Not on the mode selection screen

            tableContainer.innerHTML = '';

            if (state.pastAttempts.length === 0) {
                tableContainer.innerHTML = `<p class="text-center italic text-sm p-4" style="color: var(--color-text-secondary)">
                    No past attempts found. Start an assessment to track your progress!
                </p>`;
                return;
            }

            const template = document.getElementById('attempts-table-template');
            const clone = document.importNode(template.content, true);
            const tbody = clone.querySelector('#attempts-tbody');

            state.pastAttempts.forEach(attempt => {
                const tr = document.createElement('tr');
                tr.className = "border-t";
                tr.style.borderColor = 'var(--color-card-border)';
                
                const modeClass = attempt.mode === 'training' ? 'training-color' : 'testing-color';
                const date = attempt.timestamp?.toDate ? attempt.timestamp.toDate().toLocaleDateString() : 'N/A';
                
                tr.innerHTML = `
                    <td class="p-3 font-semibold ${modeClass}">${attempt.mode}</td>
                    <td class="p-3">${attempt.score.toFixed(1)}%</td>
                    <td class="p-3">${attempt.correctCount}/${attempt.totalQuestions}</td>
                    <td class="p-3 text-sm" style="color: var(--color-text-secondary);">${date}</td>
                `;
                tbody.appendChild(tr);
            });

            tableContainer.appendChild(clone);
        }

        function renderAssessment() {
            const q = QUESTIONS[state.currentQuestionIndex];
            const template = document.getElementById('assessment-template');
            const clone = document.importNode(template.content, true);
            
            // Header
            clone.querySelector('#mode-title').textContent = state.mode === 'training' ? 'Training Mode: Learn & Assess' : 'Testing Mode: Exam Quest';
            clone.querySelector('#mode-title').style.color = state.mode === 'training' ? 'var(--color-green-button)' : 'var(--color-heading-secondary)';
            clone.querySelector('#mode-subtitle').textContent = `Question ${state.currentQuestionIndex + 1} of ${QUESTIONS.length}`;
            
            // Question Text
            clone.querySelector('#question-text').textContent = q.question;

            // Options
            const optionsContainer = clone.querySelector('#options-container');
            optionsContainer.innerHTML = '';
            
            Object.entries(q.options).forEach(([key, value]) => {
                const isSelected = state.selectedOption === key;
                const isDisabled = state.mode === 'training' && state.isSubmitted;
                const isCorrectOption = isDisabled && key === q.correctAnswer;
                const isWrongSelection = isDisabled && isSelected && key !== q.correctAnswer;

                const label = document.createElement('label');
                label.className = `block p-3 rounded-lg border-2 transition-all cursor-pointer bg-white ${isDisabled ? 'opacity-80' : 'hover:border-[var(--color-heading-secondary)]'}`;
                label.style.borderColor = isCorrectOption || isWrongSelection ? '' : 'var(--color-card-border)';
                label.style.borderWidth = (isCorrectOption || isWrongSelection) ? '3px' : '2px';
                label.style.backgroundColor = 'var(--color-card-bg)';
                
                if (isCorrectOption) {
                    label.classList.add('border-[var(--color-green-button)]', 'font-bold');
                } else if (isWrongSelection) {
                    label.classList.add('border-[#ef4444]', 'font-bold');
                }

                label.innerHTML = `
                    <input 
                        type="radio" 
                        name="answer" 
                        value="${key}" 
                        ${isSelected ? 'checked' : ''}
                        ${isDisabled ? 'disabled' : ''}
                        class="mr-3 align-middle" 
                        style="color: var(--color-heading);"
                    />
                    <span class="font-semibold">${key}:</span> ${value}
                `;
                label.addEventListener('click', (e) => {
                    // Prevent changing selection if already submitted in training mode
                    if (!isDisabled) {
                        // Check if the click originated from the label, not the radio input itself
                        const target = e.target.closest('label');
                        if(target) handleOptionChange(key);
                    }
                });
                
                optionsContainer.appendChild(label);
            });

            // Feedback Panel
            const feedbackPanel = clone.querySelector('#feedback-panel');
            const feedbackBanner = clone.querySelector('#feedback-banner');
            const explanationText = clone.querySelector('#explanation-text');
            
            if (state.mode === 'training' && state.isSubmitted) {
                const isCorrect = state.selectedOption === q.correctAnswer;
                feedbackPanel.classList.remove('hidden');
                
                feedbackBanner.className = 'p-4 rounded-t-lg text-center font-bold text-lg ' + (isCorrect ? 'feedback-panel-correct' : 'feedback-panel-wrong');
                feedbackBanner.innerHTML = isCorrect 
                    ? '<i class="fas fa-check-circle mr-2"></i> CORRECT! Excellent work.'
                    : '<i class="fas fa-times-circle mr-2"></i> INCORRECT. Review the definition below.';
                
                explanationText.textContent = q.explanation;
            } else {
                feedbackPanel.classList.add('hidden');
            }

            // Submit Button
            const submitBtn = clone.querySelector('#submit-btn');
            submitBtn.textContent = (state.mode === 'training' && state.isSubmitted) ? "Next Question >>" : "Submit Answer";
            submitBtn.style.backgroundColor = 'var(--color-button-primary)';
            submitBtn.style.boxShadow = '0 4px 0 var(--color-button-primary-hover)';
            
            submitBtn.addEventListener('click', handleSubmit);

            appContainer.appendChild(clone);
        }

        function renderResults() {
            const { score, correctCount } = calculateScore();
            const template = document.getElementById('results-template');
            const clone = document.importNode(template.content, true);

            const title = clone.querySelector('#results-title');
            const message = clone.querySelector('#results-message');
            const scoreDisplay = clone.querySelector('#results-score');
            const summary = clone.querySelector('#results-summary');
            
            const isPassing = score >= 70;
            const color = isPassing ? 'var(--color-green-button)' : 'var(--color-heading-secondary)';

            title.style.color = color;
            title.querySelector('i').className = `fas ${isPassing ? 'fa-medal' : 'fa-brain'} mr-2`;
            
            message.textContent = isPassing ? "Congratulations! You passed this quest." : "Great effort! Review your development areas and try again.";
            scoreDisplay.textContent = score.toFixed(1) + '%';
            scoreDisplay.style.color = 'var(--color-heading)';
            summary.innerHTML = `You answered <strong>${correctCount}</strong> out of ${QUESTIONS.length} questions correctly.`;

            clone.querySelector('#restart-btn').addEventListener('click', () => {
                state.mode = null;
                state.isFinished = false;
                render();
            });

            appContainer.appendChild(clone);
        }
        
        // --- Custom Message Box (Replaces alert()) ---
        function showCustomMessage(title, text) {
            // Check if a modal already exists to prevent stacking
            if (document.getElementById('custom-modal')) return;

            const modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full" style="color: var(--color-heading);">
                    <h4 class="text-xl font-bold mb-3">${title}</h4>
                    <p class="text-gray-600 mb-6">${text}</p>
                    <button id="modal-close-btn" class="w-full py-2 rounded-lg text-white font-bold interactive-button"
                            style="background-color: var(--color-button-primary); box-shadow: 0 4px 0 var(--color-button-primary-hover);">
                        OK
                    </button>
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('#modal-close-btn').addEventListener('click', () => {
                document.body.removeChild(modal);
            });
        }


        // --- Initialization ---
        window.addEventListener('load', initializeFirebase);
    </script>
</body>
</html>
