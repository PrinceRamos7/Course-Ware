<?php
session_start();
include '../pdoconfig.php';

// Initialize training progress session if not set
if (!isset($_SESSION['training_progress'])) {
    $_SESSION['training_progress'] = [];
}

// Get page parameter for pagination
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$questions_per_page = 10;

if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
    $question_id = $_GET['question_id'] ?? null;
}

// Get course details
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course = $stmt->fetch();
$course_name = $course['title'];

// Get final assessment for this course
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'final' AND (course_id = :course_id OR course_id IS NULL) LIMIT 1");
$stmt->execute([':course_id' => $course_id]);
$final_assessment = $stmt->fetch();

if (!$final_assessment) {
    die("No final assessment found for this course.");
}

$final_assessment_id = $final_assessment['id'];

// Get questions for the final assessment
$stmt = $pdo->prepare('SELECT q.*, t.title FROM questions q 
                      JOIN topics t ON q.topic_id = t.id 
                      WHERE q.assessment_id = :assessment_id ORDER BY q.id ASC');
$stmt->execute([':assessment_id' => $final_assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_questions = count($questions);

if ($total_questions === 0) {
    die("No questions found for the final assessment.");
}

// Store shuffled questions order in session to maintain consistency
if (!isset($_SESSION['shuffled_questions_order'][$course_id])) {
    // Create an array of indices and shuffle them
    $indices = range(0, $total_questions - 1);
    shuffle($indices);
    $_SESSION['shuffled_questions_order'][$course_id] = $indices;
    
    // Also store the original questions for reference
    $_SESSION['original_questions'][$course_id] = $questions;
}

// Get the shuffled order for this course
$shuffled_indices = $_SESSION['shuffled_questions_order'][$course_id];
$original_questions = $_SESSION['original_questions'][$course_id];

// Build questions arrays for navigation using shuffled order
$questions_id = [];
$questions_text = [];
$question_topic_name = [];

foreach ($shuffled_indices as $new_index => $original_index) {
    $question = $original_questions[$original_index];
    $questions_id[$new_index] = $question['id'];
    $questions_text[$new_index] = $question['question'];
    $question_topic_name[$new_index] = $question['title'];
}

// Get the actual questions array in shuffled order
$shuffled_questions = [];
foreach ($shuffled_indices as $new_index => $original_index) {
    $shuffled_questions[$new_index] = $original_questions[$original_index];
}

// FIXED: Handle question navigation with proper current index determination
if (!$question_id) {
    // If no question_id provided, use the page to determine which question to show
    // Default to first question on the current page
    $start_index = ($current_page - 1) * $questions_per_page;
    $current_index = $start_index;
    if ($current_index >= $total_questions) {
        $current_index = 0;
        $current_page = 1;
    }
    $current_question_id = $questions_id[$current_index];
} else {
    // Find the current index based on provided question_id in our shuffled order
    $current_index = array_search($question_id, $questions_id);
    if ($current_index === false) {
        // If question_id not found in shuffled order, default to first question
        $current_index = 0;
        $current_question_id = $questions_id[$current_index];
    } else {
        $current_question_id = $questions_id[$current_index];
    }
}

// Get the current question from shuffled questions
if (isset($questions_id[$current_index]) && isset($shuffled_questions[$current_index])) {
    $current_question = $shuffled_questions[$current_index];
    $current_question_id = $current_question['id'];
} else {
    // Fallback: use first question
    $current_index = 0;
    $current_question = $shuffled_questions[$current_index] ?? null;
    $current_question_id = $current_question['id'] ?? null;
}

// If still not found, use first question
if (!$current_question && count($shuffled_questions) > 0) {
    $current_question = $shuffled_questions[0];
    $current_question_id = $current_question['id'];
    $current_index = 0;
}

// FIXED: Calculate the correct page based on current index
$current_page = floor($current_index / $questions_per_page) + 1;
$total_pages = ceil($total_questions / $questions_per_page);

// FIXED: Ensure current_page is within bounds
$current_page = min(max(1, $current_page), $total_pages);

// FIXED: Calculate indices for the current page
$start_index_for_page = ($current_page - 1) * $questions_per_page;
$end_index_for_page = min($start_index_for_page + $questions_per_page, $total_questions) - 1;

// Navigation indices
$next_index = $current_index + 1;
$prev_index = $current_index - 1;

// FIXED: Calculate which page the next/previous questions are on
$next_page = $current_page;
$prev_page = $current_page;

if ($next_index >= $total_questions) {
    $next_index = null; // No next question
} else {
    $next_page = floor($next_index / $questions_per_page) + 1;
}

if ($prev_index < 0) {
    $prev_index = null; // No previous question
} else {
    $prev_page = floor($prev_index / $questions_per_page) + 1;
}

$is_final_question = $current_index == $total_questions - 1;

// Get choices for current question
$current_choices = [];
if ($current_question_id) {
    $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
    $stmt->execute([':question_id' => $current_question_id]);
    $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Choices are in their original order
}

// Get correct choice
$correct_choice = null;
foreach ($current_choices as $choice) {
    if ($choice['is_correct']) {
        $correct_choice = $choice;
        break;
    }
}

// Handle answer submission
$user_answer = null;
$show_explanation = false;
$is_correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice_id'])) {
    $user_choice_id = (int)$_POST['choice_id'];
    $user_answer = $user_choice_id;
    $show_explanation = true;
    
    // Check if answer is correct
    foreach ($current_choices as $choice) {
        if ($choice['id'] == $user_choice_id) {
            $is_correct = $choice['is_correct'];
            break;
        }
    }
    
    // Store user progress in session with question_id as key
    $_SESSION['training_progress'][$current_question_id] = [
        'user_choice_id' => $user_choice_id,
        'is_correct' => $is_correct,
        'answered_at' => time()
    ];
} else {
    // Check if this question was already answered to show explanation
    if (isset($_SESSION['training_progress'][$current_question_id])) {
        $progress = $_SESSION['training_progress'][$current_question_id];
        $user_answer = $progress['user_choice_id'];
        $show_explanation = true;
        $is_correct = $progress['is_correct'];
    }
}

// Calculate progress
$answered_count = 0;
$correct_count = 0;

foreach ($shuffled_questions as $question) {
    if (isset($_SESSION['training_progress'][$question['id']])) {
        $answered_count++;
        if ($_SESSION['training_progress'][$question['id']]['is_correct']) {
            $correct_count++;
        }
    }
}

$accuracy = $answered_count > 0 ? round(($correct_count / $answered_count) * 100) : 0;
$progress_percentage = $total_questions > 0 ? round(($answered_count / $total_questions) * 100) : 0;

// Get explanation and learning details
$user_explanation = null;
$correct_explanation = null;
$learning_details = null;

if ($show_explanation && $user_answer) {
    // Get explanation for user's choice (whether correct or incorrect)
    $stmt = $pdo->prepare('SELECT * FROM choice_explanations WHERE choice_id = :choice_id');
    $stmt->execute([':choice_id' => $user_answer]);
    $user_explanation = $stmt->fetch();
    
    // Get explanation for correct choice
    if ($correct_choice) {
        $stmt = $pdo->prepare('SELECT * FROM choice_explanations WHERE choice_id = :choice_id AND shown_when = "correct"');
        $stmt->execute([':choice_id' => $correct_choice['id']]);
        $correct_explanation = $stmt->fetch();
        
        // If no correct explanation found, try without the shown_when filter
        if (!$correct_explanation) {
            $stmt = $pdo->prepare('SELECT * FROM choice_explanations WHERE choice_id = :choice_id LIMIT 1');
            $stmt->execute([':choice_id' => $correct_choice['id']]);
            $correct_explanation = $stmt->fetch();
        }
        
        // Get learning details for correct choice
        $stmt = $pdo->prepare('SELECT * FROM choice_learning_details WHERE choice_id = :choice_id');
        $stmt->execute([':choice_id' => $correct_choice['id']]);
        $learning_details = $stmt->fetch();
    }
}

// Get learning objectives for the course/module
$learning_objectives = [
    "Understand database design principles and normalization",
    "Master SQL query writing and optimization",
    "Learn database security and administration",
    "Develop troubleshooting skills for database issues",
    "Prepare for professional database certification"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Training Mode | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-correct: #2ECC71;     /* soft academic green */
            --color-incorrect: #E74C3C; 
        }
        /* Your existing CSS styles remain the same */
        body {
            font-family: 'Inter', sans-serif;
            padding: 0;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.4;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header {
            background-color: var(--color-header-bg);
            border-bottom: 1px solid var(--color-card-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .training-card {
            background-color: var(--color-card-bg);
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--color-card-border);
        }

        .option-item {
            border: 1.5px solid var(--color-card-border);
            border-radius: 5px;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .option-item:hover {
            border-color: var(--color-heading-secondary);
            background-color: rgba(249, 115, 22, 0.02);
        }

        .option-item.selected {
            border-color: var(--color-heading);
            background-color: rgba(34, 197, 94, 0.05);
        }

        .option-item.correct {
            border-color: var(--color-correct);
            background-color: rgba(34, 197, 94, 0.1);
        }

        .option-item.incorrect {
            border-color: var(--color-incorrect);
            background-color: rgba(220, 38, 38, 0.05);
        }

        .option-indicator {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1.5px solid var(--color-card-border);
            flex-shrink: 0;
        }

        .option-indicator.default {
            background-color: transparent;
            color: var(--color-text);
        }

        .option-indicator.selected {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .option-indicator.correct {
            background-color: var(--color-correct);
            color: white;
            border-color: var(--color-correct);
        }

        .option-indicator.incorrect {
            background-color: var(--color-incorrect);
            color: white;
            border-color: var(--color-incorrect);
        }

        .explanation-panel {
            background-color: var(--color-explanation-bg);
            border-radius: 5px;
            border-left: 3px solid var(--color-heading);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.2s ease;
            font-weight: 600;
            border: none;
            font-size: 0.875rem;
        }

        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--color-text);
            border: 1.5px solid var(--color-card-border);
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .btn-secondary:hover {
            background-color: var(--color-card-border);
        }

        .progress-bar {
            height: 4px;
            border-radius: 2px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-progress-fill);
            transition: width 0.3s ease;
        }

        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--color-card-bg) 0%, #f8fafc 100%);
            border: 1px solid var(--color-card-border);
            border-radius: 5px;
            padding: 0.75rem;
        }

        .sidebar-sticky-wrapper {
            position: sticky;
            top: 70px;
            align-self: flex-start;
        }

        .module-nav-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .module-nav {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1.5px solid var(--color-card-border);
            text-decoration: none;
            color: var(--color-text);
        }

        .module-nav:hover {
            border-color: var(--color-heading);
            color: var(--color-text);
        }

        .module-nav.current {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .module-nav.answered {
            background-color: var(--color-button-primary);
            color: white;
            border-color: var(--color-button-primary);
        }

        .module-nav.correct {
            background-color: var(--color-correct);
            color: white;
            border-color: var(--color-correct);
        }

        .module-nav.incorrect {
            background-color: var(--color-incorrect);
            color: white;
            border-color: var(--color-incorrect);
        }

        .pagination-control {
            padding: 0.5rem 0.75rem;
            border-radius: 5px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            border: 1px solid var(--color-card-border);
            text-decoration: none;
            display: inline-block;
            color: var(--color-text);
        }

        .pagination-control:hover:not(.disabled) {
            background-color: var(--color-card-border);
            color: var(--color-text);
        }

        .pagination-control.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            color: var(--color-text-secondary);
        }

        .pagination-number {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-weight: bold;
            transition: background-color 0.2s;
            text-decoration: none;
            color: var(--color-text);
        }
        
        .pagination-number.active {
            background-color: var(--color-heading-secondary);
            color: white;
            box-shadow: 0 2px 0 #ea580c;
        }
        
        .pagination-number:not(.active) {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        /* FIXED: Pagination container styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .page-info {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            margin: 0 0.5rem;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .sidebar-sticky-wrapper {
                position: static;
                order: 2;
            }
            
            .module-nav-content {
                grid-template-columns: repeat(auto-fill, minmax(35px, 1fr));
                gap: 0.4rem;
            }
            
            .module-nav {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.75rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.4rem;
            }
            
            .stat-card {
                padding: 0.5rem;
            }
            
            .module-nav-content {
                grid-template-columns: repeat(auto-fill, minmax(32px, 1fr));
                gap: 0.3rem;
            }
            
            .module-nav {
                width: 32px;
                height: 32px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 0.5rem;
            }
            
            .training-card {
                padding: 0.75rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.3rem;
            }
            
            .stat-card {
                padding: 0.4rem;
            }
            
            .module-nav-content {
                grid-template-columns: repeat(auto-fill, minmax(28px, 1fr));
                gap: 0.25rem;
            }
            
            .module-nav {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }
            
            .option-indicator {
                width: 22px;
                height: 22px;
                font-size: 0.7rem;
            }
        }

        header .container {
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
    </style>
</head>
<body>
    <header class="top-0 sticky z-10 shadow-md py-3 md:py-4" style="background-color: var(--color-header-bg);">
        <div class="container">
            <!-- Left Section -->
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-6 h-6 md:w-8 md:h-8 rounded-full flex items-center justify-center shrink-0" style="background-color: var(--color-heading);">
                    <img src="../images/isu-logo.png" alt="ISU Logo">
                </div>
                <div class="truncate">
                    <h1 class="text-base sm:text-lg font-extrabold tracking-wider truncate text-[var(--color-heading)] leading-none">
                        ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
                        Final Training
                    </h1>
                    <p class="text-xs" style="color: var(--color-text-secondary);"><?= htmlspecialchars($course_name) ?></p>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center flex-wrap justify-end gap-2 md:gap-3 shrink-0">
                <div class="flex items-center gap-1 md:gap-2 bg-[var(--color-card-bg)] px-2 py-1 rounded text-xs md:text-sm">
                    <i class="fas fa-graduation-cap text-xs" style="color: var(--color-heading);"></i>
                    <span style="color: var(--color-text-secondary);">Final Assessment Training</span>
                </div>

                <div class="flex items-center gap-1 md:gap-2 px-2 py-1 rounded text-xs md:text-sm"
                    style="background-color: var(--color-user-bg);">
                    <i class="fas fa-user-circle text-xs" style="color: var(--color-icon);"></i>
                    <span class="font-medium" style="color: var(--color-user-text);">Learner</span>
                </div>
            </div>
        </div>
    </header>

    <main class="py-3 md:py-4">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 md:gap-4">
                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-3 sidebar-sticky-wrapper order-2 lg:order-1">
                    <div class="training-card p-3 md:p-4">
                        <h3 class="font-semibold text-sm md:text-base mb-3" style="color: var(--color-text);">Training Progress</h3>
                        
                        <div class="flex justify-between items-center mb-2">
                            <?php if ($prev_index !== null && $prev_index >= 0): ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_id=<?= $questions_id[$prev_index] ?>&page=<?= $prev_page ?>" 
                                   class="pagination-control text-xs md:text-sm" style="border-color: var(--color-card-border);">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            <?php else: ?>
                                <span class="pagination-control disabled text-xs md:text-sm">Prev</span>
                            <?php endif; ?>
                            
                            <span id="page-info" class="text-xs md:text-sm font-medium" style="color: var(--color-text-secondary);">
                                <?= $current_index + 1 ?> of <?= $total_questions ?>
                            </span>
                            
                            <?php if ($next_index !== null && $next_index < $total_questions): ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_id=<?= $questions_id[$next_index] ?>&page=<?= $next_page ?>" 
                                   class="pagination-control text-xs md:text-sm" style="border-color: var(--color-card-border);">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="pagination-control disabled text-xs md:text-sm">Next</span>
                            <?php endif; ?>
                        </div>

                        <div id="module-nav-container" class="module-nav-content">
                            <?php 
                            for ($i = $start_index_for_page; $i < min($start_index_for_page + $questions_per_page, $total_questions); $i++) {
                                $question_id = $questions_id[$i];
                                $is_answered = isset($_SESSION['training_progress'][$question_id]);
                                $is_correct_answered = $is_answered && $_SESSION['training_progress'][$question_id]['is_correct'];
                                $is_current = $question_id == $current_question_id;
                                
                                // Proper class assignment logic
                                $nav_class = 'module-nav';
                                if ($is_current) {
                                    $nav_class .= ' current';
                                } else if ($is_answered) {
                                    if ($is_correct_answered) {
                                        $nav_class .= ' correct';
                                    } else {
                                        $nav_class .= ' incorrect';
                                    }
                                }
                            ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_id=<?= $question_id ?>&page=<?= $current_page ?>" 
                                   class="<?= $nav_class ?>">
                                    <?= $i + 1 ?>
                                </a>
                            <?php } ?>
                        </div>
                        
                        <!-- Added pagination controls -->
                        <div class="pagination-container">
                            <?php if ($current_page > 1): ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&page=<?= $current_page - 1 ?>" 
                                   class="pagination-control">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="pagination-control disabled">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            <?php endif; ?>
                            
                            <span class="page-info">Page <?= $current_page ?> of <?= $total_pages ?></span>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&page=<?= $current_page + 1 ?>" 
                                   class="pagination-control">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="pagination-control disabled">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-2 text-xs md:text-sm pt-3 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Progress:</span>
                                <span class="font-semibold" style="color: var(--color-heading);"><?= $answered_count ?>/<?= $total_questions ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress_percentage ?>%"></div>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Accuracy:</span>
                                <span class="font-semibold" style="color: var(--color-heading);"><?= $accuracy ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="training-card p-3 md:p-4">
                        <h4 class="font-semibold text-xs md:text-sm mb-2" style="color: var(--color-text);">Learning Metrics</h4>
                        <div class="stats-grid">
                            <div class="stat-card text-center">
                                <div class="text-sm md:text-base font-bold mb-1" style="color: var(--color-heading);"><?= $accuracy ?>%</div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Accuracy</div>
                            </div>
                            <div class="stat-card text-center">
                                <div class="text-sm md:text-base font-bold mb-1" style="color: var(--color-heading-secondary);"><?= $answered_count ?></div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Completed</div>
                            </div>
                            <div class="stat-card text-center">
                                <div class="text-sm md:text-base font-bold mb-1" style="color: var(--color-icon);"><?= $correct_count ?></div>
                                <div class="text-xs" style="color: var(--color-text-secondary);">Correct</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-3 md:space-y-4 order-1 lg:order-2">
                    <?php if ($current_question): ?>
                    <div class="training-card p-3 md:p-4 lg:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-3 md:gap-0 mb-3 md:mb-4 pb-3 border-b" style="border-color: var(--color-card-border);">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="status-badge" style="background-color: var(--color-heading); color: white;">
                                        Final Assessment
                                    </span>
                                    <span class="status-badge" style="background-color: rgba(234, 179, 8, 0.1); color: var(--color-icon);">
                                        <i class="fas fa-database mr-1"></i> Training Mode
                                    </span>
                                </div>
                                <h2 class="text-lg md:text-xl lg:text-2xl font-semibold" style="color: var(--color-text);">
                                    <?= htmlspecialchars($current_question['question']) ?>
                                </h2>
                            </div>
                            <div class="text-right text-xs" style="color: var(--color-text-secondary);">
                                <div>Training Mode</div>
                                <div>Immediate Feedback</div>
                            </div>
                        </div>

                        <form method="POST" action="training_assessment_mode.php?course_id=<?= $course_id ?>&question_id=<?= $current_question_id ?>&page=<?= $current_page ?>">
                            <div class="mb-3 md:mb-4">
                                <div class="space-y-2 md:space-y-3">
                                    <?php foreach ($current_choices as $index => $choice): 
                                        $letter = chr(65 + $index);
                                        $is_selected = $user_answer == $choice['id'];
                                        $is_correct_choice = $choice['is_correct'];
                                        
                                        $option_class = 'option-item p-2 md:p-3';
                                        $indicator_class = 'option-indicator';
                                        
                                        if ($show_explanation) {
                                            if ($is_correct_choice) {
                                                $option_class .= ' correct';
                                                $indicator_class .= ' correct';
                                            } elseif ($is_selected && !$is_correct_choice) {
                                                $option_class .= ' incorrect';
                                                $indicator_class .= ' incorrect';
                                            }
                                        } elseif ($is_selected) {
                                            $option_class .= ' selected';
                                            $indicator_class .= ' selected';
                                        } else {
                                            $indicator_class .= ' default';
                                        }
                                    ?>
                                        <div class="<?= $option_class ?>" onclick="selectOption(<?= $choice['id'] ?>)">
                                            <div class="flex items-center space-x-2 md:space-x-3">
                                                <div class="<?= $indicator_class ?>"><?= $letter ?></div>
                                                <div class="flex-1">
                                                    <p class="text-sm md:text-base" style="color: var(--color-text);">
                                                        <?= htmlspecialchars($choice['choice']) ?>
                                                    </p>
                                                    
                                                    <!-- Show explanation for user's selected choice (whether correct or wrong) -->
                                                    <?php if ($show_explanation && $is_selected && $user_explanation): ?>
                                                        <div class="explanation-content mt-2 p-2 rounded text-xs md:text-sm" 
                                                             style="background-color: rgba(0,0,0,0.03); border-left: 3px solid <?= $is_correct ? 'var(--color-correct)' : 'var(--color-incorrect)' ?>;">
                                                            <strong>Analysis:</strong> <?= htmlspecialchars($user_explanation['explanation']) ?>
                                                        </div>
                                                    <?php elseif ($show_explanation && $is_correct_choice && $correct_explanation): ?>
                                                        <div class="explanation-content mt-2 p-2 rounded text-xs md:text-sm" 
                                                             style="background-color: rgba(0,0,0,0.03); border-left: 3px solid <?= $is_correct ? 'var(--color-correct)' : 'var(--color-incorrect)' ?>;">
                                                            <strong>Analysis:</strong> <?= htmlspecialchars($correct_explanation['explanation']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 md:gap-0 pt-3 border-t" style="border-color: var(--color-card-border);">
                                <div class="text-xs" style="color: var(--color-text-secondary);">
                                    <i class="fas fa-info-circle mr-1"></i> 
                                    <?= $show_explanation ? 'Review the explanation below' : 'Select an option to check your understanding' ?>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <?php if (!$show_explanation): ?>
                                        <button type="submit" id="submit-btn" class="btn-primary px-3 md:px-4 py-1 md:py-2 rounded text-sm md:text-base font-medium" disabled>
                                            <i class="fas fa-check mr-1"></i> Check Answer
                                        </button>
                                    <?php else: ?>
                                        <?php if ($next_index !== null && $next_index < $total_questions): ?>
                                            <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_id=<?= $questions_id[$next_index] ?>&page=<?= $next_page ?>" 
                                               class="btn-primary px-3 md:px-4 py-1 md:py-2 rounded text-sm md:text-base font-medium">
                                                <i class="fas fa-arrow-right mr-1"></i> Next Question
                                            </a>
                                        <?php else: ?>
                                            <a href="training_assessment_result.php?course_id=<?= $course_id ?>" 
                                               class="btn-primary px-3 md:px-4 py-1 md:py-2 rounded text-sm md:text-base font-medium">
                                                <i class="fas fa-check-circle mr-1"></i> Complete Training
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <input type="hidden" name="choice_id" id="selected_choice" value="">
                        </form>
                    </div>

                    <?php if ($show_explanation && $correct_choice && $correct_explanation): ?>
                    <div id="explanation-panel" class="explanation-panel p-3 md:p-4 border-2 border-[var(--color-card-border)]">
                        <div class="flex items-start space-x-2 md:space-x-3 mb-3">
                            <i class="fas fa-check-circle mt-0.5 text-lg md:text-xl" style="color: var(--color-correct);"></i>
                            <div>
                                <h4 class="font-semibold text-base md:text-lg mb-1" style="color: var(--color-correct);">
                                    <?= $is_correct ? 'Correct! ' : 'Correct Answer Analysis' ?>
                                </h4>
                                <p class="text-xs md:text-sm" style="color: var(--color-text);">
                                    <?= htmlspecialchars($correct_explanation['explanation']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($learning_details): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3 text-xs md:text-sm">
                            <div class="p-2 md:p-3 rounded" style="background-color: rgba(34, 197, 94, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-correct);">Advantages</h5>
                                <pre style="color: var(--color-text);"><?= htmlspecialchars($learning_details['advantage']) ?></pre>
                            </div>
                            <div class="p-2 md:p-3 rounded" style="background-color: rgba(249, 115, 22, 0.1);">
                                <h5 class="font-semibold mb-1" style="color: var(--color-heading-secondary);">Considerations</h5>
                                <pre style="color: var(--color-text);"><?= htmlspecialchars($learning_details['consideration']) ?></pre>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Learning Objectives Section -->
                    <div class="training-card p-3 md:p-4">
                        <h4 class="font-semibold text-sm md:text-base mb-2 md:mb-3" style="color: var(--color-text);">Learning Objectives</h4>
                        <ul class="text-xs md:text-sm space-y-1 md:space-y-2" style="color: var(--color-text-secondary);">
                            <?php foreach ($learning_objectives as $objective): ?>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 md:mr-3 mt-0.5 md:mt-1 text-xs md:text-sm" style="color: var(--color-correct);"></i>
                                <span><?= htmlspecialchars($objective) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php else: ?>
                    <div class="training-card p-6 text-center">
                        <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Training Complete!</h3>
                        <p class="mb-4" style="color: var(--color-text-secondary);">
                            You have completed all questions in this training module.
                        </p>
                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>" 
                           class="btn-primary px-4 py-2 rounded">Restart Training</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function selectOption(choiceId) {
            document.getElementById('selected_choice').value = choiceId;
            document.getElementById('submit-btn').disabled = false;
            
            // Update UI to show selected option
            document.querySelectorAll('.option-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.querySelectorAll('.option-indicator').forEach(indicator => {
                indicator.classList.remove('selected');
                indicator.classList.add('default');
            });
            
            event.currentTarget.classList.add('selected');
            event.currentTarget.querySelector('.option-indicator').classList.remove('default');
            event.currentTarget.querySelector('.option-indicator').classList.add('selected');
        }

        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        
        // Auto-select previously selected option if question was answered
        document.addEventListener('DOMContentLoaded', function() {
            const selectedChoice = document.getElementById('selected_choice').value;
            if (selectedChoice) {
                const optionItem = document.querySelector(`.option-item[onclick*="${selectedChoice}"]`);
                if (optionItem) {
                    optionItem.click();
                }
            }
        });
    </script>
</body>
</html>