<?php
include "../pdoconfig.php";
$_SESSION['current_page'] = "confirmation";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['topic_id']) && isset($_GET['module_id'])) {
        $topic_id = $_GET['topic_id'];
        $module_id = $_GET['module_id'];
        $course_id = $_GET['course_id'];
    }
}

$stmt = $pdo->prepare("SELECT a.*, t.title AS topic_name, t.total_exp, m.title AS module_name FROM assessments a
            JOIN topics t ON a.topic_id = t.id
            JOIN modules m ON t.module_id = m.id
        WHERE a.topic_id = :topic_id AND a.module_id = :module_id");
$stmt->execute([":topic_id" => $topic_id, ":module_id" => $module_id]);
$assessment = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
    /* Interactive Button Base Style */
    .interactive-button {
        font-weight: bold;
        border-width: 2px;
        cursor: pointer;
        transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
        text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
        box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3); /* The "push" shadow */
    }
    .interactive-button:active {
        transform: translateY(2px); /* Moves down on click */
        box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
    }
    /* Primary button style using the green color */
    .primary-action-button {
        background-color: var(--color-green-button);
        color: white;
        border-color: var(--color-green-button-hover); 
        box-shadow: 0 6px 0 var(--color-green-button-dark); /* Deeper shadow for punch */
    }

    /* Secondary button cleanup */
    .secondary-action-button {
        background-color: var(--color-button-secondary);
        color: var(--color-button-secondary-text);
        border: 1px solid var(--color-button-secondary-text);
    }

    /* Assessment Card Border Effect */
    .assessment-frame {
        border: 3px solid var(--color-heading);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4), 0 0 0 5px var(--color-heading-secondary); 
    }
    
    /* Stat grid style */
    .stat-grid-item {
        background-color: var(--color-card-section-bg);
        border-left: 5px solid var(--color-heading-secondary); /* Highlight border */
    }
</style>
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">Start Assessment</h1>
                <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);"><?= $assessment['module_name'] ?> > <?= $assessment['topic_name'] ?></h6>
            </div>
            <a href="topicContent.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>&topic_id=<?= $topic_id ?>" class="secondary-action-button px-4 py-2 rounded-full transition-all text-sm font-semibold">
                <i class="fas fa-arrow-left mr-2"></i> Exit
            </a>
        </header>

        <main class="p-8 flex-1 flex flex-col items-center justify-center">

            <div class="assessment-frame p-10 rounded-2xl w-full max-w-2xl text-center space-y-8" 
            style="background-color: var(--color-card-bg);">
                
                <i class="fas fa-brain text-7xl" style="color: var(--color-button-primary);"></i>
                
                <h2 class="text-4xl font-extrabold" style="color: var(--color-heading);"><?= $assessment['topic_name'] ?></h2>
                
                <p class="text-lg font-medium" style="color: var(--color-text);">
                    Prepare to demonstrate your mastery! This assessment covers <?= $assessment['name'] ?>
                </p>

                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl stat-grid-item">
                        <i class="fas fa-question-circle text-2xl mb-1" style="color: var(--color-icon);"></i>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Questions</p>
                        <p class="text-2xl font-extrabold" style="color: var(--color-heading);"><?= $assessment['total_items'] ?></p>
                    </div>

                    <!-- Difficulty Level Card -->
                    <div class="p-4 rounded-xl stat-grid-item">
                        <i class="fas fa-chart-line text-2xl mb-1" style="color: var(--color-heading-secondary);"></i>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Difficulty Level</p>
                        <p class="text-2xl font-extrabold" style="color: var(--color-heading);"><?= $assessment['difficulty'] ?></p>
                    </div>

                    <!-- Reward XP Card -->
                    <div class="p-4 rounded-xl stat-grid-item">
                        <i class="fas fa-star text-2xl mb-1" style="color: var(--color-heading-secondary);"></i>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);">Reward XP</p>
                        <p class="text-2xl font-extrabold status-completed">+<?= $assessment['total_exp'] ?></p>
                    </div>
                </div>

                <div class="p-4 rounded-xl" style="background-color: var(--color-card-section-bg); border: 1px dashed var(--color-green-button);">
                    <p class="text-lg font-bold" style="color: var(--color-text);">Minimum Score for Module Completion</p>
                    <p class="text-5xl font-extrabold mt-2" style="color: var(--color-green-button);">60%</p>
                </div>

                <a href="assessmentTopic.php" class="interactive-button primary-action-button inline-flex items-center justify-center w-full max-w-sm px-8 py-4 text-xl font-extrabold rounded-full">
                    Begin Test <i class="fas fa-arrow-circle-right ml-4"></i>
                </a>

                <div class="w-full text-center pt-4">
                    <a href="topicContent.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>&topic_id=<?= $topic_id ?>" class="secondary-action-button inline-flex items-center justify-center px-6 py-2 rounded-full transition text-sm">
                        <i class="fas fa-list-ul mr-2"></i> Return to Lesson Overview
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
    </script>
</body>
</html>
