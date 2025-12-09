<?php
session_start();
include '../pdoconfig.php';

// Initialize adaptive algorithm session variables
if (!isset($_SESSION['student_id'])) {
    $_SESSION['student_id'] = 1; // For demo
}

// Include adaptive algorithm
include 'functions/adaptive_algorithms.php';

// Get course_id from URL parameter
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;

// Get assessment for this course
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'final' AND (course_id = :course_id OR course_id IS NULL) LIMIT 1");
$stmt->execute([':course_id' => $course_id]);
$final_assessment = $stmt->fetch();

if (!$final_assessment) {
    die("No final assessment found for this course.");
}

$final_assessment_id = $final_assessment['id'];

// Get course details
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course = $stmt->fetch();
$course_name = $course['title'];

// ===== ADAPTIVE ALGORITHM QUESTION SELECTION =====

// Initialize or get topics from adaptive algorithm
// This comes from the adaptive_algorithms.php which sets $_SESSION['topics_id']
$stmt = $pdo->prepare("SELECT topic_id FROM student_performance WHERE user_id = :student_id GROUP BY topic_id ORDER BY AVG(result) ASC");
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$topic_ids = $stmt->fetchAll();
$_SESSION['topics_id'] = array_column($topic_ids, 'topic_id');

// If no performance history, get all topics for this assessment
if (empty($_SESSION['topics_id'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT topic_id FROM questions WHERE assessment_id = :assessment_id");
    $stmt->execute([':assessment_id' => $final_assessment_id]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['topics_id'] = array_column($topics, 'topic_id');
}

// Initialize session variables for adaptive testing
if (!isset($_SESSION['adaptive_current_topic_index'])) {
    $_SESSION['adaptive_current_topic_index'] = 0;
}

if (!isset($_SESSION['adaptive_questions_by_topic'][$course_id])) {
    $_SESSION['adaptive_questions_by_topic'][$course_id] = [];
    $_SESSION['adaptive_question_index_by_topic'][$course_id] = [];
    $_SESSION['adaptive_answered_by_topic'][$course_id] = [];
    $_SESSION['adaptive_flagged_by_topic'][$course_id] = [];
}

// Get current topic based on algorithm progression
$current_topic_index = $_SESSION['adaptive_current_topic_index'];
$current_topic_id = $_SESSION['topics_id'][$current_topic_index] ?? null;

// Get questions for current topic
if ($current_topic_id && !isset($_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id])) {
    // Get questions for this topic from the database
    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_name FROM questions q 
                          JOIN topics t ON q.topic_id = t.id 
                          WHERE q.assessment_id = :assessment_id AND q.topic_id = :topic_id 
                          ORDER BY RAND()");
    $stmt->execute([
        ':assessment_id' => $final_assessment_id,
        ':topic_id' => $current_topic_id
    ]);
    $topic_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Store in session
    $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id] = $topic_questions;
    $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = 0;
    $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id] = [];
    $_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id] = [];
}

// Get current question for the current topic
if ($current_topic_id && isset($_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id])) {
    $topic_questions = $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id];
    $current_question_index = $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id];
    $current_question = $topic_questions[$current_question_index] ?? null;
} else {
    $current_question = null;
}

// Handle question navigation
if (isset($_GET['question_index'])) {
    $question_index = (int)$_GET['question_index'];
    if ($current_topic_id && isset($_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id])) {
        $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = $question_index;
        $current_question_index = $question_index;
        $current_question = $topic_questions[$current_question_index] ?? null;
    }
}

// Get choices for current question
$current_choices = [];
if ($current_question) {
    $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id ORDER BY id');
    $stmt->execute([':question_id' => $current_question['id']]);
    $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice_id'])) {
    $user_choice_id = (int)$_POST['choice_id'];
    $question_id = (int)$_POST['question_id'];
    
    // Get the choice details
    $stmt = $pdo->prepare("SELECT * FROM choices WHERE id = :choice_id");
    $stmt->execute([':choice_id' => $user_choice_id]);
    $choice = $stmt->fetch();
    
    if ($choice && $current_topic_id && $current_question) {
        // Update adaptive algorithm
        check_student_mastery($current_question['topic_id'], $user_choice_id);
        
        // Store answer for this topic's question
        $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index] = $user_choice_id;
        
        // Check mastery to see if we should move to next topic
        $mastery = $_SESSION['mastery_each_topic'][$current_topic_id] ?? 0.3;
        
        // Move to next question in current topic
        $next_question_index = $current_question_index + 1;
        
        // If no more questions in current topic OR mastery is high enough, consider moving to next topic
        if ($next_question_index >= count($topic_questions) || $mastery >= 0.9) {
            // Move to next topic
            if (isset($_SESSION['topics_id'][$current_topic_index + 1])) {
                $_SESSION['adaptive_current_topic_index']++;
                $current_topic_index = $_SESSION['adaptive_current_topic_index'];
                $current_topic_id = $_SESSION['topics_id'][$current_topic_index];
                
                // Initialize new topic questions if not exists
                if (!isset($_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id])) {
                    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_name FROM questions q 
                                          JOIN topics t ON q.topic_id = t.id 
                                          WHERE q.assessment_id = :assessment_id AND q.topic_id = :topic_id 
                                          ORDER BY RAND()");
                    $stmt->execute([
                        ':assessment_id' => $final_assessment_id,
                        ':topic_id' => $current_topic_id
                    ]);
                    $new_topic_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id] = $new_topic_questions;
                    $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = 0;
                    $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id] = [];
                    $_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id] = [];
                }
                
                $topic_questions = $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id];
                $current_question_index = 0;
            } else {
                // No more topics, redirect to completion
                header("Location: assessment_result.php?course_id=$course_id");
                exit();
            }
        } else {
            // Move to next question in same topic
            $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = $next_question_index;
            $current_question_index = $next_question_index;
        }
        
        // Update current question
        $current_question = $topic_questions[$current_question_index] ?? null;
        
        // Get new choices
        if ($current_question) {
            $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id ORDER BY id');
            $stmt->execute([':question_id' => $current_question['id']]);
            $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Redirect to avoid form resubmission
        header("Location: testing_assessment_mode.php?course_id=$course_id");
        exit();
    }
}

// Handle flag toggling
if (isset($_GET['toggle_flag'])) {
    if ($current_topic_id) {
        $_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id][$current_question_index] = 
            !($_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id][$current_question_index] ?? false);
    }
    header("Location: testing_assessment_mode.php?course_id=$course_id");
    exit();
}

// Calculate progress across all topics
$total_questions_count = 0;
$answered_count = 0;
$flagged_count = 0;

if (isset($_SESSION['adaptive_questions_by_topic'][$course_id])) {
    foreach ($_SESSION['adaptive_questions_by_topic'][$course_id] as $topic_id => $questions) {
        $total_questions_count += count($questions);
        if (isset($_SESSION['adaptive_answered_by_topic'][$course_id][$topic_id])) {
            $answered_count += count($_SESSION['adaptive_answered_by_topic'][$course_id][$topic_id]);
        }
        if (isset($_SESSION['adaptive_flagged_by_topic'][$course_id][$topic_id])) {
            foreach ($_SESSION['adaptive_flagged_by_topic'][$course_id][$topic_id] as $flagged) {
                if ($flagged) $flagged_count++;
            }
        }
    }
}

$progress_percentage = $total_questions_count > 0 ? round(($answered_count / $total_questions_count) * 100) : 0;

// Check if all questions answered
$all_answered = ($answered_count == $total_questions_count);

// Get all questions for navigation (simplified view)
$all_questions_for_nav = [];
$question_counter = 0;
if (isset($_SESSION['adaptive_questions_by_topic'][$course_id])) {
    foreach ($_SESSION['adaptive_questions_by_topic'][$course_id] as $topic_id => $questions) {
        foreach ($questions as $index => $question) {
            $all_questions_for_nav[] = [
                'topic_id' => $topic_id,
                'question_index' => $index,
                'global_index' => $question_counter,
                'answered' => isset($_SESSION['adaptive_answered_by_topic'][$course_id][$topic_id][$index]),
                'flagged' => $_SESSION['adaptive_flagged_by_topic'][$course_id][$topic_id][$index] ?? false,
                'is_current' => ($topic_id == $current_topic_id && $index == $current_question_index)
            ];
            $question_counter++;
        }
    }
}

$total_questions = count($all_questions_for_nav);

// Calculate current global index for navigation
$current_global_index = -1;
foreach ($all_questions_for_nav as $index => $q) {
    if ($q['is_current']) {
        $current_global_index = $index;
        break;
    }
}
if ($current_global_index === -1 && !empty($all_questions_for_nav)) {
    $current_global_index = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Assessment | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Your existing CSS styles remain exactly the same */
        html, body {
            font-family: 'bungee', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.5;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .exam-container {
            background-color: var(--color-card-bg);
            border-radius: 0.5rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-card-border);
            overflow: hidden;
        }

        .option-item {
            border: 2px solid var(--color-card-border);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .option-item:hover {
            border-color: var(--color-heading-secondary);
            box-shadow: 0 0 0 1px var(--color-heading-secondary);
        }
        /* Selected option uses Primary Green for confirmation */
        .option-item.selected {
            border-color: var(--color-heading); 
            background-color: rgba(21, 128, 61, 0.08); 
        }
        .option-item.answered {
             cursor: default;
        }

        .option-indicator {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid var(--color-card-border);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        /* Selected indicator uses Primary Green for confirmation */
        .option-item.selected .option-indicator {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }

        .question-nav {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid var(--color-card-border);
            background-color: var(--color-card-bg);
            color: var(--color-text);
        }
        .question-nav:hover { border-color: var(--color-heading-secondary); }
        /* Current question uses Primary Green (heading) */
        .question-nav.current { background-color: var(--color-heading); color: white; border-color: var(--color-heading); }
        /* Answered question uses Secondary Orange (heading-secondary) */
        .question-nav.answered { background-color: var(--color-heading-secondary); color: white; border-color: var(--color-heading-secondary); }
        /* Flagged question uses Warning Yellow */
        .question-nav.flagged { background-color: var(--color-warning); color: var(--color-text); border-color: var(--color-warning); }
        .question-nav.answered.flagged { background-color: var(--color-warning); color: var(--color-text); border-color: var(--color-warning); }

        .btn-base { 
            padding: 0.75rem 1.5rem; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            text-align: center;
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 500;
        }
        /* Primary button uses Green button variables */
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: background-color 0.2s ease, transform 0.1s;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-1px);
        }
        /* Secondary button uses pale yellow background and golden brown text */
        .btn-secondary {
            background-color: var(--color-button-secondary);
            border: 1px solid var(--color-card-border);
            color: var(--color-button-secondary-text); 
            transition: all 0.2s ease;
        }
        .btn-secondary:hover:not(:disabled) {
            border-color: var(--color-heading-secondary); 
            color: var(--color-heading-secondary); 
        }
        .btn-base:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-bar { 
            height: 6px; 
            border-radius: 3px; 
            background-color: var(--color-progress-bg); 
        }
        /* Progress fill uses the XP gradient */
        .progress-fill { 
            height: 100%; 
            background: var(--color-progress-fill); 
            transition: width 0.5s ease; 
        }
        .timer-critical { 
            color: var(--color-time-critical) !important; 
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .compact-grid {
            display: grid;
            gap: 0.5rem;
            grid-template-columns: repeat(5, 1fr);
        }
        
        .scrollable-map-container {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        .scrollable-map-container::-webkit-scrollbar {
            width: 8px;
        }
        .scrollable-map-container::-webkit-scrollbar-thumb {
            background: var(--color-card-border);
            border-radius: 10px;
        }
        .scrollable-map-container::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-secondary);
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--color-card-border);
        }
        
        .pagination-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid var(--color-card-border);
            background-color: var(--color-card-bg);
            color: var(--color-text);
        }
        
        .pagination-btn:hover {
            border-color: var(--color-heading-secondary);
        }
        
        .pagination-btn.active {
            background-color: var(--color-heading);
            color: white;
            border-color: var(--color-heading);
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-info {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            margin: 0 1rem;
        }

        /* Responsive adjustments - keep as is */
        @media (max-width: 639px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .header-user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .question-panel {
                margin-top: 1rem;
            }
            
            .compact-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .question-nav {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 0.875rem;
            }
            
            .exam-container.p-6 {
                padding: 1rem;
            }
            
            .option-item {
                padding: 0.75rem;
            }
            
            .option-indicator {
                width: 1.75rem;
                height: 1.75rem;
                font-size: 0.875rem;
            }
            
            .btn-base {
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
            }
            
            .footer-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .footer-actions > div {
                width: 100%;
            }
            
            .footer-actions .btn-base {
                width: 100%;
                justify-content: center;
            }
            
            .pagination-btn {
                width: 2.25rem;
                height: 2.25rem;
                font-size: 0.75rem;
            }
            
            .pagination-info {
                font-size: 0.75rem;
                margin: 0 0.5rem;
            }
        }

        @media (min-width: 640px) and (max-width: 767px) {
            .compact-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .header-content {
                flex-direction: row;
                justify-content: space-between;
            }
            
            .header-user-info {
                flex-direction: row;
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .main-content-grid {
                grid-template-areas: "sidebar main";
                grid-template-columns: 1fr 2fr;
                gap: 1.5rem;
            }
            
            .sidebar-panel { 
                grid-area: sidebar; 
            }
            
            .question-panel { 
                grid-area: main; 
            }
            
            .compact-grid { 
                grid-template-columns: repeat(5, 1fr); 
            }
        }

        @media (min-width: 1024px) {
            .main-content-grid {
                grid-template-areas: "sidebar main";
                grid-template-columns: 1fr 3fr;
            }
            .sidebar-panel { grid-area: sidebar; }
            .question-panel { grid-area: main; }
            .compact-grid { grid-template-columns: repeat(5, 1fr); }
        }
        
        @media (min-width: 1280px) {
            .compact-grid { grid-template-columns: repeat(6, 1fr); }
            .main-content-grid { grid-template-columns: 300px 1fr; }
        }
    </style>
</head>
<body>
    <header class="top-0 right-0 left-0 fixed z-10 shadow-md py-4" style="background-color: var(--color-header-bg);">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center header-content">
                <div class="flex items-center space-x-3">
                    <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>" class="w-8 h-8 rounded-full flex object-contain items-center justify-center" style="background-color: var(--color-heading);">
                       <img src="../images/isu-logo.png" alt="ISU Logo">
                    </a>
                    <div>
                        <h1 class="text-base sm:text-lg font-extrabold tracking-wider truncate text-[var(--color-heading)] leading-none">
                            ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
                            Testing
                        </h1>
                        <p class="text-xs font-medium" style="color: var(--color-text-secondary);"><?= htmlspecialchars($course_name) ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 header-user-info">
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-lg transition duration-300" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-clock text-base" style="color: var(--color-heading);"></i>
                        <span id="timer" class="font-mono text-lg font-bold" style="color: var(--color-heading);">45:00</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm" 
                         style="background-color: var(--color-user-bg);">
                        <i class="fas fa-user-circle text-lg" style="color: var(--color-icon);"></i>
                        <span class="font-semibold hidden md:inline" style="color: var(--color-text);">Student</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-[80px]">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 main-content-grid">
                <div class="lg:col-span-1 space-y-4 sidebar-panel">
                    <div class="exam-container p-4">
                        <h3 class="font-bold text-base mb-3 uppercase tracking-wider" style="color: var(--color-heading-secondary);">Question Map</h3>
                        
                        <div class="scrollable-map-container">
                            <div id="question-map" class="compact-grid">
                                <?php if (!empty($all_questions_for_nav)): ?>
                                    <?php foreach ($all_questions_for_nav as $index => $question_data): 
                                        $question_number = $index + 1;
                                        $is_answered = $question_data['answered'];
                                        $is_flagged = $question_data['flagged'];
                                        $is_current = $question_data['is_current'];
                                        
                                        $nav_class = 'question-nav';
                                        if ($is_current) $nav_class .= ' current';
                                        if ($is_answered) $nav_class .= ' answered';
                                        if ($is_flagged) $nav_class .= ' flagged';
                                    ?>
                                        <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $question_data['question_index'] ?>&topic_id=<?= $question_data['topic_id'] ?>" 
                                           class="<?= $nav_class ?>" <?= $is_answered ? 'style="cursor: not-allowed;"' : '' ?>>
                                            <?= $question_number ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-span-5 text-center py-4" style="color: var(--color-text-secondary);">
                                        No questions available
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Pagination Controls -->
                        <?php if ($total_questions > 0): ?>
                        <div class="pagination-container">
                            <span class="pagination-info">Total Questions: <?= $total_questions ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-5 space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="font-medium" style="color: var(--color-text-secondary);">Completion:</span>
                                <span id="progress-text" class="font-bold" style="color: var(--color-heading);"><?= $answered_count ?> / <?= $total_questions_count ?></span>
                            </div>
                            <div class="progress-bar">
                                <div id="progress-fill" class="progress-fill" style="width: <?= $progress_percentage ?>%"></div>
                            </div>
                            <?php if ($current_topic_id && isset($_SESSION['mastery_each_topic'][$current_topic_id])): ?>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-medium" style="color: var(--color-text-secondary);">Current Topic Mastery:</span>
                                <span class="font-bold" style="color: var(--color-heading-secondary);"><?= round($_SESSION['mastery_each_topic'][$current_topic_id] * 100) ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading-secondary);"></div><span style="color: var(--color-text-secondary);">Answered (<span id="answered-count-legend"><?= $answered_count ?></span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading);"></div><span style="color: var(--color-text-secondary);">Current (<span id="current-count-legend">1</span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-warning);"></div><span style="color: var(--color-text-secondary);">Flagged (<span id="flagged-count-legend"><?= $flagged_count ?></span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-card-border);"></div><span style="color: var(--color-text-secondary);">Unanswered (<span id="unanswered-count-legend"><?= $total_questions_count - $answered_count ?></span>)</span></div>
                        </div>

                        <div class="mt-5">
                            <?php if ($all_answered): ?>
                                <a href="assessment_result.php?course_id=<?= $course_id ?>" id="final-submit-btn" class="btn-base btn-primary w-full py-3 rounded text-base font-bold uppercase tracking-wider text-center">
                                    <i class="fas fa-paper-plane mr-2"></i> Final Submission
                                </a>
                            <?php else: ?>
                                <button id="final-submit-btn" class="btn-base btn-primary w-full py-3 rounded text-base font-bold uppercase tracking-wider" disabled>
                                    <i class="fas fa-paper-plane mr-2"></i> Answer All Questions First
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="exam-container p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-lg mt-0.5" style="color: var(--color-heading);"></i>
                            <div>
                                <h4 class="font-bold text-sm mb-1" style="color: var(--color-text);">Adaptive Testing</h4>
                                <ul class="list-disc ml-4 text-xs space-y-1" style="color: var(--color-text-secondary);">
                                    <li>Questions adapt based on your performance</li>
                                    <li>Topics are ordered from lowest to highest mastery</li>
                                    <li>Move to next topic at 90% mastery</li>
                                    <li>No backtracking to previous questions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3 question-panel">
                    <div id="question-panel" class="exam-container p-6">
                        <?php if ($current_question): ?>
                        <div class="mb-6 border-b pb-4" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center space-x-3">
                                    <span id="q-status-badge" class="px-3 py-1 rounded-full text-sm font-bold" 
                                          style="background-color: var(--color-heading); color: white;">
                                        Topic <?= $current_topic_index + 1 ?> of <?= count($_SESSION['topics_id'] ?? []) ?>
                                    </span>
                                    <?php if ($_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id][$current_question_index] ?? false): ?>
                                        <span id="q-flag-badge" class="px-3 py-1 rounded-full text-xs font-medium" 
                                              style="background-color: rgba(249, 115, 22, 0.1); color: var(--color-heading-secondary);">
                                             <i class="fas fa-flag mr-1"></i> Flagged
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold" style="color: var(--color-heading-secondary);">1 Point</div>
                                    <div class="text-xs" style="color: var(--color-text-secondary);">
                                        Topic: <?= htmlspecialchars($current_question['topic_name']) ?>
                                    </div>
                                </div>
                            </div>
                            <h2 class="text-xl font-bold" style="color: var(--color-text);">
                                <?= htmlspecialchars($current_question['question']) ?>
                            </h2>
                        </div>

                        <?php 
                        $is_answered = isset($_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index]);
                        $selected_answer = $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index] ?? null;
                        ?>
                        
                        <form method="POST" action="testing_assessment_mode.php?course_id=<?= $course_id ?>" id="answer-form">
                            <input type="hidden" name="question_id" value="<?= $current_question['id'] ?>">
                            
                            <div class="mb-8">
                                <div id="options-list" class="space-y-4">
                                    <?php foreach ($current_choices as $index => $choice): 
                                        $letter = chr(65 + $index);
                                        $is_selected = ($selected_answer == $choice['id']);
                                    ?>
                                        <div class="option-item p-4 <?= $is_selected ? 'selected' : '' ?> <?= $is_answered ? 'answered' : '' ?>" 
                                             data-option="<?= $letter ?>" 
                                             data-choice-id="<?= $choice['id'] ?>"
                                             <?php if (!$is_answered): ?>onclick="selectOption(this)"<?php endif; ?>>
                                            <div class="flex items-start space-x-4">
                                                <div class="option-indicator"><?= $letter ?></div>
                                                <p class="text-base pt-0.5 font-medium" style="color: var(--color-text);">
                                                    <?= htmlspecialchars($choice['choice']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t footer-actions" style="border-color: var(--color-card-border);">
                                <div class="flex space-x-3 mb-3 sm:mb-0">
                                    <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&toggle_flag=1" 
                                       class="btn-base btn-secondary px-4 py-2 text-sm font-medium">
                                        <i class="fas fa-flag mr-1"></i> 
                                        <?= ($_SESSION['adaptive_flagged_by_topic'][$course_id][$current_topic_id][$current_question_index] ?? false) ? 'Unflag Question' : 'Flag Question' ?>
                                    </a>
                                </div>
                                
                                <div class="flex space-x-3 w-full sm:w-auto">
                                    <!-- No previous button in adaptive mode (no backtracking) -->
                                    <button class="btn-base btn-secondary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium" disabled>
                                        <i class="fas fa-arrow-left"></i> Previous
                                    </button>
                                    
                                    <?php if (!$is_answered): ?>
                                        <button type="submit" 
                                                id="submit-answer" 
                                                class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium"
                                                disabled>
                                            Submit Answer
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium"
                                                disabled>
                                            Already Answered
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Next button only if current question is answered -->
                                    <?php if ($is_answered && $current_question_index < count($topic_questions) - 1): ?>
                                        <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_question_index + 1 ?>" 
                                           class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium">
                                            Next <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-base btn-primary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium" disabled>
                                            Next <i class="fas fa-arrow-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <input type="hidden" name="choice_id" id="selected-choice" value="<?= $selected_answer ?>">
                        </form>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <h3 class="text-xl font-bold mb-4" style="color: var(--color-text);">Assessment Complete</h3>
                                <p style="color: var(--color-text-secondary);">You have completed all available questions.</p>
                                <a href="assessment_result.php?course_id=<?= $course_id ?>" class="btn-base btn-primary mt-4">
                                    View Results
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function selectOption(element) {
            // Remove selected class from all options
            document.querySelectorAll('.option-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update hidden input with selected choice ID
            const choiceId = element.getAttribute('data-choice-id');
            document.getElementById('selected-choice').value = choiceId;
            
            // Enable submit button
            document.getElementById('submit-answer').disabled = false;
        }
        
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        // Apply theme on page load
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        
        document.addEventListener('DOMContentLoaded', function() {
            const totalQuestions = <?= $total_questions_count ?>;
            const answeredCount = <?= $answered_count ?>;
            const examTime = 45 * 60;
            let timerInterval;

            const elements = {
                timer: document.getElementById('timer'),
                progressFill: document.getElementById('progress-fill'),
                progressText: document.getElementById('progress-text'),
                answeredLegend: document.getElementById('answered-count-legend'),
                flaggedLegend: document.getElementById('flagged-count-legend'),
                unansweredLegend: document.getElementById('unanswered-count-legend'),
                finalSubmitBtn: document.getElementById('final-submit-btn')
            };

            function updateTimerDisplay() {
                const minutes = Math.floor(examTime / 60);
                const seconds = examTime % 60;
                elements.timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (examTime <= 300) {
                    elements.timer.classList.add('timer-critical');
                } else {
                    elements.timer.classList.remove('timer-critical');
                }
            }

            function startTimer() {
                updateTimerDisplay();
                let remainingTime = examTime;
                timerInterval = setInterval(() => {
                    remainingTime--;
                    const minutes = Math.floor(remainingTime / 60);
                    const seconds = remainingTime % 60;
                    elements.timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (remainingTime <= 300) {
                        elements.timer.classList.add('timer-critical');
                    }
                    if (remainingTime <= 0) {
                        clearInterval(timerInterval);
                        alert('Time is up! Your exam has been automatically submitted.');
                        window.location.href = 'assessment_result.php?course_id=<?= $course_id ?>';
                    }
                }, 1000);
            }

            function updateProgress() {
                const percent = Math.floor((answeredCount / totalQuestions) * 100);
                elements.progressFill.style.width = `${percent}%`;
                elements.progressText.textContent = `${answeredCount} / ${totalQuestions}`;
                
                elements.answeredLegend.textContent = answeredCount;
                elements.flaggedLegend.textContent = <?= $flagged_count ?>;
                elements.unansweredLegend.textContent = totalQuestions - answeredCount;
                
                // Enable/disable final submit button
                if (answeredCount === totalQuestions) {
                    if (elements.finalSubmitBtn) {
                        elements.finalSubmitBtn.disabled = false;
                        elements.finalSubmitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Final Submission';
                    }
                }
            }

            // Initialize progress
            updateProgress();
            
            // Start timer
            startTimer();
            
            // Auto-select previously selected option if question was answered
            const selectedChoice = document.getElementById('selected-choice').value;
            if (selectedChoice) {
                const optionItem = document.querySelector(`.option-item[data-choice-id="${selectedChoice}"]`);
                if (optionItem) {
                    optionItem.classList.add('selected');
                    // Disable submit button if already answered
                    const submitBtn = document.getElementById('submit-answer');
                    if (submitBtn && optionItem.classList.contains('answered')) {
                        submitBtn.disabled = true;
                    }
                }
            }
        });
    </script>
</body>
</html>