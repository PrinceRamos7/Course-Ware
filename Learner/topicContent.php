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
/* --- Component Styles using CSS Variables (No changes needed here) --- */

.lesson-frame {
    border: 3px solid var(--color-heading);
    /* Creates a subtle 3D lift/depth for the main lesson block */
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3), 0 0 0 5px var(--color-heading-secondary);
    background-color: var(--color-card-bg);
    /* Crucial for flex items to shrink in height */
    min-height: 0;
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
    /* Crucial for responsiveness in tables/narrow containers */
    word-break: break-all; /* Allows long words/strings to break */
    white-space: normal;  /* Ensures text wraps within the cell */
}

/* --- Interactive Button Styles (No changes needed here) --- */

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

/* Carousel Indicator Active State */
.active-indicator {
    background-color: var(--color-heading-secondary) !important;
}
    </style>
</head>
<body class="min-h-screen flex font-sans" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>

    <button class="mobile-menu-button md:hidden fixed top-4 left-4 z-50 bg-[var(--color-card-bg)] border border-[var(--color-card-border)] rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <div class="sidebar-overlay md:hidden"></div>

    <div class="flex-1 flex flex-col ml-0 md:ml-16 min-h-screen overflow-x-hidden">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-4 sm:px-6 md:px-8 py-3 flex flex-col md:flex-row md:justify-between md:items-center items-start gap-4 md:gap-0 sticky top-0 z-10"
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-xl md:text-2xl font-bold" style="color: var(--color-text);">What is a Variable?</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Introduction to Python > Variables > What is a Variable?</h6>
            </div>
            <a href="topicCard.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>" class="px-3 md:px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold w-full md:w-auto text-center">
                <i class="fas fa-list-ul mr-2"></i> Back to Topics
            </a>
        </header>

        <main class="p-4 sm:p-6 md:p-8 mx-auto flex-1 flex flex-col w-full overflow-y-auto md:max-w-4xl">

            <div class="mb-6 md:mb-8 border-b pb-4" style="border-color: var(--color-heading-secondary);">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Variables and Data Types</h1>
                <h2 class="text-lg sm:text-xl md:text-xl font-bold" style="color: var(--color-heading-secondary);">The building blocks of every program.</h2>
            </div>

            <div class="lesson-frame flex-1 flex flex-col p-4 md:p-6 rounded-xl shadow-2xl w-full">

                <div id="lesson-carousel" class="relative overflow-hidden flex-1 flex flex-col w-full">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <p class="text-base sm:text-lg leading-relaxed text-center px-1 py-8 w-full" style="color: var(--color-text);">
                                A **variable** is like a **container** or a box that holds information. When you write code, you often need to store pieces of data, like numbers, text, or true/false values. Variables give you a way to label and store this data so you can use it later. Think of it as a labeled box you can put things into and take things out of. The label is the **variable's name**, and the thing inside is the **value**.
                            </p>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="p-4 sm:p-6 rounded-xl highlight-box w-full max-w-xl"
                                    style="border: 2px solid var(--color-heading-secondary);">
                                <p class="text-lg sm:text-xl font-extrabold text-center" style="color: var(--color-text-on-section);">
                                    <i class="fas fa-star mr-2"></i> **Quick Fact:** In Python, you don't need to explicitly declare the data type. Python is **dynamically typed** and figures it out for you automatically!
                                </p>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-lg mx-auto px-2 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4" style="color: var(--color-heading);">Key Characteristics:</h3>
                                <ul class="list-none space-y-3 sm:space-y-4 text-base sm:text-lg">
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-code mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Dynamic Typing:** You don't have to declare a variable's type.
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-redo-alt mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Mutable:** Variables can be changed to hold new values, making them flexible.
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-rocket mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Reusability:** The same variable name can be efficiently used throughout your code.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="flex justify-center w-full max-w-xl mx-auto px-4 sm:px-0">
                                <img src="../images/table_design.png" alt="An illustration of a variable as a labeled storage container."
                                        class="rounded-xl shadow-lg w-full ring-2" style="border-color: var(--color-heading);">
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-xl mx-auto px-4 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4" style="color: var(--color-heading);">Watch This to Level Up:</h3>
                                <div class="relative w-full rounded-xl overflow-hidden shadow-2xl" style="padding-top: 56.25%; border: 4px solid var(--color-heading-secondary);">
                                    <iframe class="absolute top-0 left-0 w-full h-full"
                                        src="https://www.youtube.com/embed/videoseries?list=PL-osiE80TeTsqnO9w-s-MZMjgFk_R6JzB"
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex flex-col items-center justify-center">
                            <div class="w-full max-w-2xl mx-auto px-2 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4 text-center" style="color: var(--color-heading);">Common Data Types (Inventory)</h3>
                                <div class="overflow-x-auto rounded-lg border-2 shadow-lg w-full" style="border-color: var(--color-heading);">
                                    <table class="w-full text-xs sm:text-sm" style="color: var(--color-text);">
                                        <thead class="text-xs uppercase" style="background-color: var(--color-heading);">
                                            <tr>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Type</th>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Example</th>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">Integer</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`age = 25`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">A whole number (no decimals).</td>
                                            </tr>
                                            <tr class="border-b" style="border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">Float</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`price = 9.99`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">A number with a decimal point.</td>
                                            </tr>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">String</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`name = "Alice"`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">Text, enclosed in quotes.</td>
                                            </tr>
                                            <tr>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">Boolean</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`is_valid = True`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">A true or false value.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                             <div class="p-4 sm:p-6 rounded-xl highlight-box w-full max-w-lg text-center"
                                  style="border: 2px solid var(--color-heading-secondary);">
                                 <h3 class="text-xl sm:text-2xl font-extrabold mb-3" style="color: var(--color-heading);">Challenge Complete!</h3>
                                 <p class="text-lg sm:text-xl leading-relaxed" style="color: var(--color-text-on-section);">
                                     You've mastered the fundamentals. Proceed to the assessment to finish the lesson and earn your rewards!
                                 </p>
                             </div>
                        </div>

                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4 md:gap-0 mt-6 p-4 border-t" style="border-color: var(--color-card-border);">
                    <button id="prev-button" class="px-4 md:px-6 py-2 rounded-full transition interactive-button primary-action flex items-center justify-center invisible w-full md:w-auto order-2 md:order-1">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>

                    <div id="carousel-indicators" class="flex space-x-2 md:space-x-3 order-1 md:order-2">
                    </div>

                    <button id="next-button" class="px-4 md:px-6 py-2 rounded-full transition interactive-button primary-action flex items-center justify-center w-full md:w-auto order-3">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>

                    <a href="assessmentTopicConfirmation.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&topic_id=<?=$topic_id?>" id="assessment-button"
                        class="px-4 md:px-6 py-2 rounded-full transition interactive-button success-action flex items-center justify-center font-extrabold w-full md:w-auto order-4"
                        style="display: none;">
                        Complete Quest <i class="fas fa-check-circle ml-2"></i>
                    </a>
                </div>
            </div>
        </main>
    </div>

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

        // --- Carousel Logic (No changes needed here) ---
        const carouselInner = document.getElementById('carousel-inner');
        const nextButton = document.getElementById('next-button');
        const prevButton = document.getElementById('prev-button');
        const assessmentButton = document.getElementById('assessment-button');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const items = document.querySelectorAll('.carousel-item');

        let currentIndex = 0;
        const totalItems = items.length;

        // Initialize carousel indicators
        function createIndicators() {
            indicatorsContainer.innerHTML = '';
            for (let i = 0; i < totalItems; i++) {
                const dot = document.createElement('div');
                dot.classList.add('w-2', 'h-2', 'md:w-3', 'md:h-3', 'rounded-full', 'cursor-pointer', 'transition-all');
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

        // Event listener for the Next button
        nextButton.addEventListener('click', () => {
            if (currentIndex < totalItems - 1) {
                currentIndex++;
                showSlide(currentIndex);
            }
        });

        // Event listener for the Previous button
        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                showSlide(currentIndex);
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
             }
        }

        // Initial setup
        document.addEventListener('DOMContentLoaded', () => {
            createIndicators();
            showSlide(0); // Always start at slide 0
        });

        // --- Sidebar/Mobile Menu Logic (No changes needed here) ---
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const body = document.body;
        
        mobileMenuButton.addEventListener('click', () => {
            document.querySelector('#sidebar')?.classList.toggle('-translate-x-full');
            document.querySelector('.sidebar-overlay')?.classList.toggle('hidden');
            body.classList.toggle('overflow-hidden');
        });

        document.querySelector('.sidebar-overlay')?.addEventListener('click', () => {
            document.querySelector('#sidebar')?.classList.add('-translate-x-full');
            document.querySelector('.sidebar-overlay')?.classList.add('hidden');
            body.classList.remove('overflow-hidden');
        });

    </script>
</body>
</html>