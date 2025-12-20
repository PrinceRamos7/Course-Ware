<?php
session_start();
include '../pdoconfig.php';

// Get course_id from URL parameter
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 1;

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

// ===== TIMER MANAGEMENT =====
$time_limit = 45 * 60; // 45 minutes in seconds

// Initialize test timer if not set
if (!isset($_SESSION['testing_start_time'][$course_id])) {
    $_SESSION['testing_start_time'][$course_id] = time();
    $_SESSION['testing_time_limit'][$course_id] = $time_limit;
}

// Calculate remaining time
$start_time = $_SESSION['testing_start_time'][$course_id];
$time_limit = $_SESSION['testing_time_limit'][$course_id];
$elapsed_time = time() - $start_time;
$remaining_time = max(0, $time_limit - $elapsed_time);
$is_time_up = ($remaining_time <= 0);

// Auto-submit if time is up
if ($is_time_up && !isset($_GET['final_submit'])) {
    header("Location: testing_assessment_mode.php?course_id=$course_id&final_submit=1");
    exit();
}

// Format time for display
$minutes_remaining = floor($remaining_time / 60);
$seconds_remaining = $remaining_time % 60;
$time_display = sprintf('%02d:%02d', $minutes_remaining, $seconds_remaining);

// Get all questions from the assessment
$stmt = $pdo->prepare("SELECT q.*, t.title as topic_name FROM questions q 
                      JOIN topics t ON q.topic_id = t.id 
                      WHERE q.assessment_id = :assessment_id");
$stmt->execute([':assessment_id' => $final_assessment_id]);
$all_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== ENHANCED QUESTION SELECTION ALGORITHM =====
// 1. Group questions by topic
// 2. Randomly select 0-5 questions from each topic
// 3. Ensure total questions = 30 (or max available)

// Initialize testing session if not set
if (!isset($_SESSION['testing_progress'][$course_id])) {
    $_SESSION['testing_progress'][$course_id] = [];
}

if (!isset($_SESSION['testing_questions'][$course_id])) {
    // Group questions by topic
    $questions_by_topic = [];
    foreach ($all_questions as $question) {
        $topic_id = $question['topic_id'];
        if (!isset($questions_by_topic[$topic_id])) {
            $questions_by_topic[$topic_id] = [];
        }
        $questions_by_topic[$topic_id][] = $question;
    }
    
    // Shuffle questions within each topic
    foreach ($questions_by_topic as &$topic_questions) {
        shuffle($topic_questions);
    }
    
    // Distribute questions - start with 1 question from each topic
    $selected_questions = [];
    $max_per_topic = 5;
    $total_target = 30;
    $topics = array_keys($questions_by_topic);
    $topic_count = count($topics);
    
    // First pass: get 1 question from each topic
    foreach ($topics as $topic_id) {
        if (!empty($questions_by_topic[$topic_id])) {
            $selected_questions[] = array_shift($questions_by_topic[$topic_id]);
        }
    }
    
    // Second pass: randomly distribute remaining questions
    $remaining_slots = $total_target - count($selected_questions);
    $iteration = 0;
    $max_iterations = 50; // Safety limit
    
    while ($remaining_slots > 0 && $iteration < $max_iterations) {
        $iteration++;
        
        // Shuffle topics for random distribution
        shuffle($topics);
        
        foreach ($topics as $topic_id) {
            if ($remaining_slots <= 0) break;
            
            // Count how many questions we already have from this topic
            $current_from_topic = array_filter($selected_questions, function($q) use ($topic_id) {
                return $q['topic_id'] == $topic_id;
            });
            $count_from_topic = count($current_from_topic);
            
            // If we have less than max_per_topic and there are more questions available
            if ($count_from_topic < $max_per_topic && 
                !empty($questions_by_topic[$topic_id]) && 
                $remaining_slots > 0) {
                
                $selected_questions[] = array_shift($questions_by_topic[$topic_id]);
                $remaining_slots--;
                
                // 30% chance to skip to next topic (for more random distribution)
                if (mt_rand(1, 100) <= 30) {
                    continue;
                }
            }
        }
        
        // Check if we've run out of questions
        $all_empty = true;
        foreach ($questions_by_topic as $topic_questions) {
            if (!empty($topic_questions)) {
                $all_empty = false;
                break;
            }
        }
        if ($all_empty) break;
    }
    
    // Shuffle the final selected questions
    shuffle($selected_questions);
    
    // If we have more than 30 questions (shouldn't happen, but just in case)
    if (count($selected_questions) > $total_target) {
        $selected_questions = array_slice($selected_questions, 0, $total_target);
    }
    
    // Store in session
    $_SESSION['testing_questions'][$course_id] = $selected_questions;
    $_SESSION['testing_question_index'][$course_id] = 0;
    $_SESSION['testing_answered'][$course_id] = [];
    $_SESSION['testing_flagged'][$course_id] = [];
    
    // Also store topic distribution for display
    $topic_distribution = [];
    foreach ($selected_questions as $question) {
        $topic_name = $question['topic_name'];
        if (!isset($topic_distribution[$topic_name])) {
            $topic_distribution[$topic_name] = 0;
        }
        $topic_distribution[$topic_name]++;
    }
    $_SESSION['topic_distribution'][$course_id] = $topic_distribution;
}

// Get questions from session
$questions = $_SESSION['testing_questions'][$course_id];
$total_questions = count($questions);

// Get topic distribution for display
$topic_distribution = $_SESSION['topic_distribution'][$course_id] ?? [];

// Get current question index
$current_index = $_SESSION['testing_question_index'][$course_id] ?? 0;

// Handle question navigation
if (isset($_GET['question_index'])) {
    $new_index = (int)$_GET['question_index'];
    if ($new_index >= 0 && $new_index < $total_questions) {
        $current_index = $new_index;
        $_SESSION['testing_question_index'][$course_id] = $current_index;
    }
}

// Get current question
$current_question = $questions[$current_index] ?? null;

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
    
    // Store answer
    $_SESSION['testing_answered'][$course_id][$current_index] = $user_choice_id;
    
    // Move to next question automatically
    $next_index = $current_index + 1;
    if ($next_index < $total_questions) {
        $_SESSION['testing_question_index'][$course_id] = $next_index;
        $current_index = $next_index;
        $current_question = $questions[$current_index] ?? null;
        
        // Get new choices
        if ($current_question) {
            $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id ORDER BY id');
            $stmt->execute([':question_id' => $current_question['id']]);
            $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: testing_assessment_mode.php?course_id=$course_id&question_index=$current_index");
    exit();
}

// Handle flag toggling
if (isset($_GET['toggle_flag'])) {
    $_SESSION['testing_flagged'][$course_id][$current_index] = 
        !($_SESSION['testing_flagged'][$course_id][$current_index] ?? false);
    header("Location: testing_assessment_mode.php?course_id=$course_id&question_index=$current_index");
    exit();
}

// Handle final submission
if (isset($_GET['final_submit'])) {
    // Clear timer session
    unset($_SESSION['testing_start_time'][$course_id]);
    unset($_SESSION['testing_time_limit'][$course_id]);
    
    // Redirect to results
    header("Location: testing_assessment_result.php?course_id=$course_id");
    exit();
}

// Calculate progress
$answered_count = 0;
$correct_count = 0;
$flagged_count = 0;

foreach ($questions as $index => $question) {
    if (isset($_SESSION['testing_answered'][$course_id][$index])) {
        $answered_count++;
    }
    if (isset($_SESSION['testing_flagged'][$course_id][$index]) && $_SESSION['testing_flagged'][$course_id][$index]) {
        $flagged_count++;
    }
}

$progress_percentage = $total_questions > 0 ? round(($answered_count / $total_questions) * 100) : 0;

// Check if all questions answered
$all_answered = ($answered_count == $total_questions);
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
        .question-nav.current { background-color: var(--color-heading); color: white; border-color: var(--color-heading); }
        .question-nav.answered { background-color: var(--color-heading-secondary); color: white; border-color: var(--color-heading-secondary); }
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
        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: background-color 0.2s ease, transform 0.1s;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(-1px);
        }
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

        /* Responsive adjustments */
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
                        <span id="timer" class="font-mono text-lg font-bold" style="color: var(--color-heading);">
                            <?= $time_display ?>
                        </span>
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
                                <?php if ($total_questions > 0): ?>
                                    <?php for ($i = 0; $i < $total_questions; $i++): 
                                        $is_answered = isset($_SESSION['testing_answered'][$course_id][$i]);
                                        $is_flagged = $_SESSION['testing_flagged'][$course_id][$i] ?? false;
                                        $is_current = ($i == $current_index);
                                        
                                        $nav_class = 'question-nav';
                                        if ($is_current) $nav_class .= ' current';
                                        if ($is_answered) $nav_class .= ' answered';
                                        if ($is_flagged) $nav_class .= ' flagged';
                                    ?>
                                        <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $i ?>" 
                                           class="<?= $nav_class ?>" <?= $is_answered ? 'style="cursor: default;"' : '' ?>>
                                            <?= $i + 1 ?>
                                        </a>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    <div class="col-span-5 text-center py-4" style="color: var(--color-text-secondary);">
                                        No questions available
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-5 space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="font-medium" style="color: var(--color-text-secondary);">Completion:</span>
                                <span id="progress-text" class="font-bold" style="color: var(--color-heading);"><?= $answered_count ?> / <?= $total_questions ?></span>
                            </div>
                            <div class="progress-bar">
                                <div id="progress-fill" class="progress-fill" style="width: <?= $progress_percentage ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-medium" style="color: var(--color-text-secondary);">Time Remaining:</span>
                                <span id="time-remaining" class="font-bold" style="color: var(--color-heading-secondary);">
                                    <?= $time_display ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading-secondary);"></div><span style="color: var(--color-text-secondary);">Answered (<span id="answered-count-legend"><?= $answered_count ?></span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-heading);"></div><span style="color: var(--color-text-secondary);">Current (<span id="current-count-legend"><?= $current_index + 1 ?></span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-warning);"></div><span style="color: var(--color-text-secondary);">Flagged (<span id="flagged-count-legend"><?= $flagged_count ?></span>)</span></div>
                            <div class="flex items-center space-x-1"><div class="w-2.5 h-2.5 rounded-full" style="background-color: var(--color-card-border);"></div><span style="color: var(--color-text-secondary);">Unanswered (<span id="unanswered-count-legend"><?= $total_questions - $answered_count ?></span>)</span></div>
                        </div>

                        <div class="mt-5">
                            <?php if ($all_answered): ?>
                                <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&final_submit=1" 
                                   id="final-submit-btn" 
                                   class="btn-base btn-primary w-full py-3 rounded text-base font-bold uppercase tracking-wider text-center">
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
                                <h4 class="font-bold text-sm mb-1" style="color: var(--color-text);">Testing Information</h4>
                                <ul class="list-disc ml-4 text-xs space-y-1" style="color: var(--color-text-secondary);">
                                    <li>Total Questions: <?= $total_questions ?> (max 30)</li>
                                    <li>Up to 5 questions per topic</li>
                                    <li>45-minute time limit</li>
                                    <li>No immediate feedback</li>
                                    <li>Flag questions for review</li>
                                    <?php if (!empty($topic_distribution)): ?>
                                    <li>Topic distribution:
                                        <?php 
                                        $dist_texts = [];
                                        foreach ($topic_distribution as $topic => $count) {
                                            $dist_texts[] = "$topic: $count";
                                        }
                                        echo implode(', ', $dist_texts);
                                        ?>
                                    </li>
                                    <?php endif; ?>
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
                                        Question <?= $current_index + 1 ?> of <?= $total_questions ?>
                                    </span>
                                    <?php if ($_SESSION['testing_flagged'][$course_id][$current_index] ?? false): ?>
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
                        $is_answered = isset($_SESSION['testing_answered'][$course_id][$current_index]);
                        $selected_answer = $_SESSION['testing_answered'][$course_id][$current_index] ?? null;
                        ?>
                        
                        <form method="POST" action="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_index ?>" id="answer-form">
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
                                    <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&toggle_flag=1&question_index=<?= $current_index ?>" 
                                       class="btn-base btn-secondary px-4 py-2 text-sm font-medium">
                                        <i class="fas fa-flag mr-1"></i> 
                                        <?= ($_SESSION['testing_flagged'][$course_id][$current_index] ?? false) ? 'Unflag Question' : 'Flag Question' ?>
                                    </a>
                                </div>
                                
                                <div class="flex space-x-3 w-full sm:w-auto">
                                    <!-- Previous button -->
                                    <?php if ($current_index > 0): ?>
                                        <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_index - 1 ?>" 
                                           class="btn-base btn-secondary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium">
                                            <i class="fas fa-arrow-left"></i> Previous
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-base btn-secondary w-1/3 sm:w-auto px-4 py-2 text-sm font-medium" disabled>
                                            <i class="fas fa-arrow-left"></i> Previous
                                        </button>
                                    <?php endif; ?>
                                    
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
                                    
                                    <!-- Next button -->
                                    <?php if ($current_index < $total_questions - 1): ?>
                                        <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_index + 1 ?>" 
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
                                <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>&final_submit=1" class="btn-base btn-primary mt-4">
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
            const totalQuestions = <?= $total_questions ?>;
            const answeredCount = <?= $answered_count ?>;
            
            // Get initial remaining time from server (in seconds)
            let remainingTime = <?= $remaining_time ?>;
            
            // Check if time is already up
            if (remainingTime <= 0) {
                alert('Time is up! Your exam has been automatically submitted.');
                window.location.href = 'testing_assessment_mode.php?course_id=<?= $course_id ?>&final_submit=1';
                return;
            }
            
            let timerInterval;
            let isTimeUp = false;

            const elements = {
                timer: document.getElementById('timer'),
                timeRemaining: document.getElementById('time-remaining'),
                progressFill: document.getElementById('progress-fill'),
                progressText: document.getElementById('progress-text'),
                answeredLegend: document.getElementById('answered-count-legend'),
                flaggedLegend: document.getElementById('flagged-count-legend'),
                unansweredLegend: document.getElementById('unanswered-count-legend'),
                currentLegend: document.getElementById('current-count-legend'),
                finalSubmitBtn: document.getElementById('final-submit-btn')
            };

            function updateTimerDisplay() {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                elements.timer.textContent = timeString;
                elements.timeRemaining.textContent = timeString;
                
                if (remainingTime <= 300) { // 5 minutes or less
                    elements.timer.classList.add('timer-critical');
                    elements.timeRemaining.classList.add('timer-critical');
                } else {
                    elements.timer.classList.remove('timer-critical');
                    elements.timeRemaining.classList.remove('timer-critical');
                }
            }

            function startTimer() {
                updateTimerDisplay();
                
                timerInterval = setInterval(() => {
                    remainingTime--;
                    updateTimerDisplay();
                    
                    if (remainingTime <= 0) {
                        clearInterval(timerInterval);
                        isTimeUp = true;
                        alert('Time is up! Your exam has been automatically submitted.');
                        window.location.href = 'testing_assessment_mode.php?course_id=<?= $course_id ?>&final_submit=1';
                    }
                    
                    // Save time every 30 seconds to prevent cheating
                    if (remainingTime % 30 === 0) {
                        saveElapsedTime();
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
                elements.currentLegend.textContent = <?= $current_index + 1 ?>;
                
                // Enable/disable final submit button
                if (answeredCount === totalQuestions) {
                    if (elements.finalSubmitBtn) {
                        elements.finalSubmitBtn.disabled = false;
                        elements.finalSubmitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Final Submission';
                    }
                }
            }

            // Function to save elapsed time to server
            function saveElapsedTime() {
                if (typeof navigator.sendBeacon === 'function') {
                    const elapsedTime = <?= $time_limit ?> - remainingTime;
                    const data = new FormData();
                    data.append('course_id', <?= $course_id ?>);
                    data.append('elapsed_time', elapsedTime);
                    data.append('action', 'save_time');
                    
                    // Send beacon to save time
                    navigator.sendBeacon('save_testing_time.php', data);
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
            
            // Save time when user navigates away
            window.addEventListener('beforeunload', saveElapsedTime);
            
            // Save time when form is submitted
            const answerForm = document.getElementById('answer-form');
            if (answerForm) {
                answerForm.addEventListener('submit', saveElapsedTime);
            }
        });
    </script>
</body>
</html>