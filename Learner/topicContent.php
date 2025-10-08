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
    <title>Lesson - Database Tables</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <style>
.lesson-frame {
    border: 3px solid var(--color-heading);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3), 0 0 0 5px var(--color-heading-secondary);
    background-color: var(--color-card-bg);
    min-height: 0;
}

.highlight-box {
    box-shadow: inset 0 0 15px rgba(75, 37, 130, 0.5);
    border-radius: 1rem;
    background-color: var(--color-highlight-bg);
}

.code-block {
    font-family: 'Consolas', 'Courier New', monospace;
    font-weight: bold;
    color: var(--color-heading-secondary);
    background-color: var(--color-main-bg);
    padding: 2px 4px;
    border-radius: 4px;
    word-break: break-all;
    white-space: normal;
}

.interactive-button {
    font-weight: bold;
    border-width: 2px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
}

.interactive-button:not([disabled]) {
    box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
}

.interactive-button:active {
    transform: translateY(2px);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
}

.primary-action {
    background-color: var(--color-button-primary);
    color: white;
    border-color: var(--color-button-primary);
    box-shadow: 0 4px 0 var(--color-button-primary-hover);
}
.primary-action:active {
    box-shadow: 0 2px 0 var(--color-button-primary-hover);
}

.secondary-action {
    background-color: var(--color-button-secondary);
    color: var(--color-button-secondary-text);
    border-color: var(--color-button-secondary-text);
    box-shadow: 0 4px 0 var(--color-button-secondary-text);
}
.secondary-action:active {
    box-shadow: 0 2px 0 var(--color-button-secondary-text);
}

.success-action {
    background-color: var(--color-green-button);
    color: var(--color-card-bg);
    border-color: var(--color-green-button-hover);
    box-shadow: 0 4px 0 var(--color-green-button-hover);
}
.success-action:hover {
    filter: brightness(1.1);
}
.success-action:active {
    box-shadow: 0 2px 0 var(--color-green-button-hover);
}

.active-indicator {
    background-color: var(--color-heading-secondary) !important;
}
    </style>
</head>
<body class="min-h-screen flex font-sans" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>

    <div class="flex-1 flex flex-col ml-0 md:ml-16 min-h-screen overflow-x-hidden">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-4 sm:px-6 md:px-8 py-3 flex flex-col md:flex-row md:justify-between md:items-center items-start gap-4 md:gap-0 sticky top-0 z-10"
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">

                <div class="flex gap-2">
                    <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)]  rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
            </button>
            <div class="flex flex-col">
                <h1 class="text-xl md:text-2xl font-bold" style="color: var(--color-heading);">Designing Database Tables</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">IT-Specialist Database > Database Design > Design Tables for storing data</h6>
            </div>
                </div>

            <a href="topicCard.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>" class="px-3 md:px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold w-full md:w-auto text-center">
                <i class="fas fa-list-ul mr-2"></i> Back to Topics
            </a>
        </header>

        <main class="p-4 sm:p-6 md:p-8 mx-auto flex-1 flex flex-col w-full overflow-y-auto md:max-w-4xl">

            <div class="mb-6 md:mb-8 border-b pb-4" style="border-color: var(--color-heading-secondary);">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Tables, Columns, and Data Types</h1>
                <h2 class="text-lg sm:text-xl md:text-xl font-bold" style="color: var(--color-heading-secondary);">Structuring information for efficient storage.</h2>
            </div>

            <div class="lesson-frame flex-1 flex flex-col p-4 md:p-6 rounded-xl shadow-2xl w-full">

                <div id="lesson-carousel" class="relative overflow-hidden flex-1 flex flex-col w-full">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <p class="text-base sm:text-lg leading-relaxed text-center px-1 py-8 w-full" style="color: var(--color-text);">
                                A **database table** is where all your data lives, organized in a structured, grid-like format. Each **row** represents a single record (like a user or a product), and each **column** represents a specific attribute (like a name or a price). Good table design is essential for speed, integrity, and scalability.
                            </p>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="p-4 sm:p-6 rounded-xl highlight-box w-full max-w-xl"
                                        style="border: 2px solid var(--color-heading-secondary);">
                                <p class="text-lg sm:text-xl font-extrabold text-center" style="color: var(--color-text-on-section);">
                                    <i class="fas fa-star mr-2"></i> **Quick Fact:** Every table should have a **Primary Key**—a column (or set of columns) with a unique value for each row, ensuring no two records are identical!
                                </p>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-lg mx-auto px-2 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4" style="color: var(--color-heading);">Key Design Principles:</h3>
                                <ul class="list-none space-y-3 sm:space-y-4 text-base sm:text-lg">
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-key mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Atomicity:** Data should be broken down into the smallest possible meaningful parts (e.g., separate columns for first\_name and last\_name).
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-link mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Normalization:** Structure tables to eliminate redundant data and ensure data dependencies are logical (usually involves creating multiple related tables).
                                        </div>
                                    </li>
                                    <li class="flex items-start" style="color: var(--color-text);">
                                        <i class="fas fa-balance-scale mr-3 mt-1 text-sm sm:text-base" style="color: var(--color-heading-secondary);"></i>
                                        <div>
                                            **Consistency:** Apply appropriate data types and constraints (NOT NULL, UNIQUE) to maintain data integrity across the entire database.
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="flex justify-center w-full max-w-xl mx-auto px-4 sm:px-0">
                                <img src="../images/table_design.png" alt="An illustration of a database table with columns, rows, and key relationships."
                                            class="rounded-xl shadow-lg w-full ring-2" style="border-color: var(--color-heading);">
                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                            <div class="w-full max-w-xl mx-auto px-4 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4" style="color: var(--color-heading);">Watch This to Level Up:</h3>
                               <div class="relative w-full rounded-xl overflow-hidden shadow-2xl" 
     style="padding-top: 56.25%; border: 4px solid var(--color-heading-secondary);">

  <iframe class="absolute top-0 left-0 w-full h-full"
      src="https://www.youtube.com/embed/XfrgCK6BX5w"
      title="YouTube video player"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
      allowfullscreen>
  </iframe>
</div>


                            </div>
                        </div>

                        <div class="carousel-item min-w-full p-2 h-full flex flex-col items-center justify-center">
                            <div class="w-full max-w-2xl mx-auto px-2 sm:px-0">
                                <h3 class="text-xl sm:text-2xl font-bold mb-4 text-center" style="color: var(--color-heading);">Common MySQL Data Types (Inventory)</h3>
                                <div class="overflow-x-auto rounded-lg border-2 shadow-lg w-full" style="border-color: var(--color-heading);">
                                    <table class="w-full text-xs sm:text-sm" style="color: var(--color-text);">
                                        <thead class="text-xs uppercase" style="background-color: var(--color-heading);">
                                            <tr>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Type</th>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Example</th>
                                                <th scope="col" class="px-2 sm:px-3 py-2 sm:py-3 font-extrabold text-white">Use Case</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">INT</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`user_id INT`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">Whole numbers (e.g., ID numbers, counts).</td>
                                            </tr>
                                            <tr class="border-b" style="border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">VARCHAR(255)</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`name VARCHAR(100)`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">Variable length text (e.g., names, short descriptions).</td>
                                            </tr>
                                            <tr class="border-b" style="background-color: var(--color-card-section-bg); border-color: var(--color-card-border);">
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">DECIMAL(5,2)</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`price DECIMAL(8,2)`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">Precise decimal numbers (e.g., currency).</td>
                                            </tr>
                                            <tr>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 font-bold" style="color: var(--color-heading-secondary);">DATETIME</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4 code-block">`created_at DATETIME`</td>
                                                <td class="px-2 sm:px-3 py-3 sm:py-4">Date and time combination.</td>
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

        const carouselInner = document.getElementById('carousel-inner');
        const nextButton = document.getElementById('next-button');
        const prevButton = document.getElementById('prev-button');
        const assessmentButton = document.getElementById('assessment-button');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const items = document.querySelectorAll('.carousel-item');

        let currentIndex = 0;
        const totalItems = items.length;

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

        function showSlide(index) {
            currentIndex = Math.max(0, Math.min(index, totalItems - 1));
            carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateIndicators();
            updateNavigationButtons();
        }

        nextButton.addEventListener('click', () => {
            if (currentIndex < totalItems - 1) {
                currentIndex++;
                showSlide(currentIndex);
            }
        });

        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                showSlide(currentIndex);
            }
        });

        function updateIndicators() {
            const indicators = indicatorsContainer.querySelectorAll('div');
            indicators.forEach((dot, i) => {
                dot.classList.remove('active-indicator');
                dot.style.backgroundColor = 'var(--color-card-border)';
                if (i === currentIndex) {
                    dot.classList.add('active-indicator');
                }
            });
        }

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

        document.addEventListener('DOMContentLoaded', () => {
            createIndicators();
            showSlide(0);
        });

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