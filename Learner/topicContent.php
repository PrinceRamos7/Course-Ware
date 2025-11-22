<?php
include "../pdoconfig.php";
$_SESSION['current_page'] = "content";
$course_id = 1;

// Initialize variables
$topic_id = $module_id = $course_id = null;
$topic_data = [];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['topic_id']) && isset($_GET['module_id']) && isset($_GET['course_id'])) {
        $topic_id = $_GET['topic_id'];
        $module_id = $_GET['module_id'];
        $course_id = $_GET['course_id'];
        
        // Fetch topic data from topics table
        try {
            $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = ?");
            $stmt->execute([$topic_id]);
            $topic_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic_data) {
                // If topic not found, redirect back
                header("Location: topicCard.php?course_id=" . $course_id . "&module_id=" . $module_id);
                exit();
            }
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            // Fallback to default content if DB fails
            $topic_data = [
                'title' => 'Designing Database Tables',
                'description' => 'Structuring information for efficient storage.',
                'content' => 'A database table is where all your data lives, organized in a structured, grid-like format.'
            ];
        }
    } else {
        // Redirect if required parameters are missing
        header("Location: topicCard.php");
        exit();
    }
}

unset($_SESSION['progress']);
unset($_SESSION['topic_answer_details']);
unset($_SESSION['answeredCount']);

// Parse the content from topics table into slides
$content_slides = [];
if (!empty($topic_data['content'])) {
    // Split content by sections (assuming content is structured with separators)
    $sections = preg_split('/\n---\n|\n#|\n\*\*\*|\n###/', $topic_data['content']);
    
    foreach ($sections as $section) {
        $section = trim($section);
        if (!empty($section)) {
            $content_slides[] = $section;
        }
    }
}

// If no content sections found, use the description as a fallback
if (empty($content_slides) && !empty($topic_data['description'])) {
    $content_slides[] = $topic_data['description'];
}

// Add a final completion slide
$content_slides[] = "COMPLETION_SLIDE";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lesson - <?php echo htmlspecialchars($topic_data['title']); ?></title>
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

.content-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px dashed var(--color-card-border);
}

.content-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.slide-content {
    max-height: 400px;
    overflow-y: auto;
    padding: 1rem;
}
    </style>
</head>
<body class="min-h-screen flex font-sans" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'?>

    <div class="flex-1 flex flex-col ml-0 md:ml-16 min-h-screen overflow-x-hidden">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-4 sm:px-6 md:px-8 py-3 flex flex-col md:flex-row md:justify-between md:items-center items-start gap-4 md:gap-0 sticky top-0 z-10"
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">

                <div class="flex gap-2">
                    <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)] rounded-lg p-2 text-[var(--color-text)]">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <div class="flex flex-col">
                        <h1 class="text-xl md:text-2xl font-bold" style="color: var(--color-heading);">
                            <?php echo htmlspecialchars($topic_data['title']); ?>
                        </h1>
                        <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">
                            IT-Specialist Database > Database Design > <?php echo htmlspecialchars($topic_data['title']); ?>
                        </h6>
                    </div>
                </div>

            <a href="topicCard.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>" class="px-3 md:px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold w-full md:w-auto text-center">
                <i class="fas fa-list-ul mr-2"></i> Back to Topics
            </a>
        </header>

        <main class="p-4 sm:p-6 md:p-8 mx-auto flex-1 flex flex-col w-full overflow-y-auto md:max-w-4xl">

            <div class="mb-6 md:mb-8 border-b pb-4" style="border-color: var(--color-heading-secondary);">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-2" style="color: var(--color-heading);">
                    <?php echo htmlspecialchars($topic_data['title']); ?>
                </h1>
                <?php if (!empty($topic_data['description'])): ?>
                    <h2 class="text-lg sm:text-xl md:text-xl font-bold" style="color: var(--color-heading-secondary);">
                        <?php echo htmlspecialchars($topic_data['description']); ?>
                    </h2>
                <?php endif; ?>
            </div>

            <div class="lesson-frame flex-1 flex flex-col p-4 md:p-6 rounded-xl shadow-2xl w-full">

                <div id="lesson-carousel" class="relative overflow-hidden flex-1 flex flex-col w-full">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full">
                        
                        <?php foreach ($content_slides as $index => $slide_content): ?>
                            <?php if ($slide_content === "COMPLETION_SLIDE"): ?>
                                <!-- Final completion slide -->
                                <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                                    <div class="p-4 sm:p-6 rounded-xl highlight-box w-full max-w-lg text-center"
                                            style="border: 2px solid var(--color-heading-secondary);">
                                        <h3 class="text-xl sm:text-2xl font-extrabold mb-3" style="color: var(--color-heading);">Challenge Complete!</h3>
                                        <p class="text-lg sm:text-xl leading-relaxed" style="color: var(--color-text-on-section);">
                                            You've mastered the fundamentals. Proceed to the assessment to finish the lesson and earn your rewards!
                                        </p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Content slides from topics table -->
                                <div class="carousel-item min-w-full p-2 h-full flex items-center justify-center">
                                    <div class="slide-content w-full">
                                        <div class="text-base sm:text-lg leading-relaxed" style="color: var(--color-text);">
                                            <?php echo nl2br(htmlspecialchars($slide_content)); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

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