<?php
session_start();
include '../pdoconfig.php';
include 'functions/adaptive_algorithms.php';

// Initialize training progress session if not set
if (!isset($_SESSION['training_progress'])) {
    $_SESSION['training_progress'] = [];
}

if (!isset($_SESSION['student_id'])) {
    $_SESSION['student_id'] = 1; // For demo
}

// Initialize adaptive algorithm session variables
if (!isset($_SESSION['topics_id'])) {
    // Get topics from adaptive algorithm
    $stmt = $pdo->prepare("SELECT topic_id FROM student_performance WHERE user_id = :student_id GROUP BY topic_id ORDER BY AVG(result) ASC");
    $stmt->execute([":student_id" => $_SESSION['student_id']]);
    $topic_ids = $stmt->fetchAll();
    $_SESSION['topics_id'] = array_column($topic_ids, 'topic_id');
}

// Initialize adaptive algorithm session variables
if (!isset($_SESSION['adaptive_current_topic_index'])) {
    $_SESSION['adaptive_current_topic_index'] = 0;
}

if (!isset($_SESSION['topic_index'])) {
    $_SESSION['topic_index'] = 0;
}

if (!isset($_SESSION['used_id'])) {
    $_SESSION['used_id'] = [];
}

if (!isset($_SESSION['mastery_each_topic'])) {
    $_SESSION['mastery_each_topic'] = [];
}

if (!isset($_SESSION['answer_result_tracker'])) {
    $_SESSION['answer_result_tracker'] = [];
}

if (!isset($_SESSION['adaptive_question_history'])) {
    $_SESSION['adaptive_question_history'] = [];
}

if (!isset($_SESSION['total_questions_limit'])) {
    $_SESSION['total_questions_limit'] = 30; // Limit total questions to 30
}

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

// ===== ADAPTIVE ALGORITHM QUESTION SELECTION =====

// If no performance history, get all topics for this assessment
if (empty($_SESSION['topics_id'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT topic_id FROM questions WHERE assessment_id = :assessment_id");
    $stmt->execute([':assessment_id' => $final_assessment_id]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['topics_id'] = array_column($topics, 'topic_id');
}

// Initialize session variables for adaptive testing
if (!isset($_SESSION['adaptive_questions_by_topic'][$course_id])) {
    $_SESSION['adaptive_questions_by_topic'][$course_id] = [];
    $_SESSION['adaptive_question_index_by_topic'][$course_id] = [];
    $_SESSION['adaptive_answered_by_topic'][$course_id] = [];
}

// Get current topic based on algorithm progression
$current_topic_index = $_SESSION['adaptive_current_topic_index'];
$current_topic_id = $_SESSION['topics_id'][$current_topic_index] ?? null;

// Get questions for current topic (with limit)
if ($current_topic_id && !isset($_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id])) {
    // Calculate how many questions we can take from this topic (max 5 per topic)
    $questions_per_topic = 5;
    
    // Get questions for this topic from the database
    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_name FROM questions q 
                          JOIN topics t ON q.topic_id = t.id 
                          WHERE q.assessment_id = :assessment_id AND q.topic_id = :topic_id 
                          ORDER BY RAND() LIMIT :limit");
    $stmt->bindValue(':assessment_id', $final_assessment_id, PDO::PARAM_INT);
    $stmt->bindValue(':topic_id', $current_topic_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $questions_per_topic, PDO::PARAM_INT);
    $stmt->execute();
    $topic_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Store in session
    $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id] = $topic_questions;
    $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = 0;
    $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id] = [];
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
    $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
    $stmt->execute([':question_id' => $current_question['id']]);
    $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
$mastery_update_info = null;

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
    
    // Update adaptive algorithm (with increased mastery gains)
    $mastery_update_info = check_student_mastery($current_question['topic_id'], $user_choice_id);
    
    // Store answer for this topic's question
    $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index] = [
        'user_choice_id' => $user_choice_id,
        'is_correct' => $is_correct,
        'answered_at' => time(),
        'question_id' => $current_question['id']
    ];
    
    // Store in training progress as well
    $_SESSION['training_progress'][$current_question['id']] = [
        'user_choice_id' => $user_choice_id,
        'is_correct' => $is_correct,
        'answered_at' => time()
    ];
    
    // Store in question history for sidebar display
    if (!isset($_SESSION['adaptive_question_history'][$course_id])) {
        $_SESSION['adaptive_question_history'][$course_id] = [];
    }
    
    // Add to history if not already there
    $question_data = [
        'question_id' => $current_question['id'],
        'topic_id' => $current_topic_id,
        'topic_index' => $current_topic_index,
        'question_index' => $current_question_index,
        'question_position' => count($_SESSION['adaptive_question_history'][$course_id]) + 1,
        'is_correct' => $is_correct,
        'answered_at' => time()
    ];
    
    // Check if this question is already in history
    $found_index = false;
    foreach ($_SESSION['adaptive_question_history'][$course_id] as $index => $history_item) {
        if ($history_item['question_id'] == $current_question['id']) {
            $found_index = $index;
            break;
        }
    }
    
    if ($found_index !== false) {
        // Update existing entry
        $_SESSION['adaptive_question_history'][$course_id][$found_index] = $question_data;
    } else {
        // Add new entry
        $_SESSION['adaptive_question_history'][$course_id][] = $question_data;
    }
    
    // Don't auto-proceed to next question - let user click "Next Question"
    
} else {
    // Check if this question was already answered to show explanation
    if ($current_question && isset($_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index])) {
        $progress = $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id][$current_question_index];
        $user_answer = $progress['user_choice_id'];
        $show_explanation = true;
        $is_correct = $progress['is_correct'];
        
        // Also check if there's mastery update info for this question
        // (we need to find it in the history)
        if (isset($_SESSION['adaptive_question_history'][$course_id])) {
            foreach ($_SESSION['adaptive_question_history'][$course_id] as $history_item) {
                if ($history_item['question_id'] == $current_question['id']) {
                    // We can't get the original mastery update info, but we can at least show the answer
                    break;
                }
            }
        }
    }
}

// Handle manual navigation to next question
if (isset($_GET['next_question'])) {
    $next_question_index = $current_question_index + 1;
    
    // Check if we need to move to next topic
    $mastery = $_SESSION['mastery_each_topic'][$current_topic_id] ?? 0.3;
    $answered_in_topic = isset($_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id]) ? 
        count($_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id]) : 0;
    
    // If no more questions in current topic OR mastery is high enough, consider moving to next topic
    if ($next_question_index >= count($topic_questions) || $mastery >= 0.9) {
        // Move to next topic
        if (isset($_SESSION['topics_id'][$current_topic_index + 1])) {
            $_SESSION['adaptive_current_topic_index']++;
            $current_topic_index = $_SESSION['adaptive_current_topic_index'];
            $current_topic_id = $_SESSION['topics_id'][$current_topic_index];
            
            // Initialize new topic questions if not exists
            if (!isset($_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id])) {
                // Calculate how many questions we can take from this topic
                $questions_per_topic = 5;
                
                $stmt = $pdo->prepare("SELECT q.*, t.title as topic_name FROM questions q 
                                      JOIN topics t ON q.topic_id = t.id 
                                      WHERE q.assessment_id = :assessment_id AND q.topic_id = :topic_id 
                                      ORDER BY RAND() LIMIT :limit");
                $stmt->bindValue(':assessment_id', $final_assessment_id, PDO::PARAM_INT);
                $stmt->bindValue(':topic_id', $current_topic_id, PDO::PARAM_INT);
                $stmt->bindValue(':limit', $questions_per_topic, PDO::PARAM_INT);
                $stmt->execute();
                $new_topic_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id] = $new_topic_questions;
                $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = 0;
                $_SESSION['adaptive_answered_by_topic'][$course_id][$current_topic_id] = [];
            }
            
            $topic_questions = $_SESSION['adaptive_questions_by_topic'][$course_id][$current_topic_id];
            $current_question_index = 0;
        } else {
            // No more topics, redirect to completion
            header("Location: training_assessment_result.php?course_id=$course_id");
            exit();
        }
    } else {
        // Move to next question in same topic
        $_SESSION['adaptive_question_index_by_topic'][$course_id][$current_topic_id] = $next_question_index;
        $current_question_index = $next_question_index;
    }
    
    // Check if we've reached the total question limit
    $total_answered = 0;
    if (isset($_SESSION['adaptive_answered_by_topic'][$course_id])) {
        foreach ($_SESSION['adaptive_answered_by_topic'][$course_id] as $topic_answers) {
            $total_answered += count($topic_answers);
        }
    }
    
    if ($total_answered >= $_SESSION['total_questions_limit']) {
        header("Location: training_assessment_result.php?course_id=$course_id");
        exit();
    }
    
    // Update current question
    $current_question = $topic_questions[$current_question_index] ?? null;
    
    // If no more questions, redirect to completion
    if (!$current_question) {
        header("Location: training_assessment_result.php?course_id=$course_id");
        exit();
    }
    
    // Get new choices
    if ($current_question) {
        $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
        $stmt->execute([':question_id' => $current_question['id']]);
        $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get correct choice for new question
    $correct_choice = null;
    foreach ($current_choices as $choice) {
        if ($choice['is_correct']) {
            $correct_choice = $choice;
            break;
        }
    }
    
    // Reset answer state for new question
    $user_answer = null;
    $show_explanation = false;
    $is_correct = false;
    $mastery_update_info = null;
    
    // Redirect to avoid form resubmission
    header("Location: training_assessment_mode.php?course_id=$course_id&question_index=$current_question_index");
    exit();
}

// Calculate total questions across all topics (for display)
$total_questions = 0;
$max_questions = $_SESSION['total_questions_limit'];
if (isset($_SESSION['adaptive_questions_by_topic'][$course_id])) {
    foreach ($_SESSION['adaptive_questions_by_topic'][$course_id] as $topic_id => $questions) {
        $total_questions += count($questions);
    }
}
$total_questions = min($total_questions, $max_questions);

// Calculate progress
$answered_count = 0;
$correct_count = 0;

if (isset($_SESSION['adaptive_answered_by_topic'][$course_id])) {
    foreach ($_SESSION['adaptive_answered_by_topic'][$course_id] as $topic_id => $answers) {
        foreach ($answers as $answer) {
            $answered_count++;
            if ($answer['is_correct']) {
                $correct_count++;
            }
        }
    }
}

$accuracy = $answered_count > 0 ? round(($correct_count / $answered_count) * 100) : 0;
$progress_percentage = $max_questions > 0 ? round(($answered_count / $max_questions) * 100) : 0;

// Get all answered questions for sidebar display
$answered_questions_list = [];
if (isset($_SESSION['adaptive_question_history'][$course_id])) {
    $answered_questions_list = $_SESSION['adaptive_question_history'][$course_id];
    
    // Sort by answered time (or question position)
    usort($answered_questions_list, function($a, $b) {
        if (isset($a['question_position']) && isset($b['question_position'])) {
            return $a['question_position'] <=> $b['question_position'];
        }
        return $a['answered_at'] <=> $b['answered_at'];
    });
}

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
    <title>Adaptive Training Mode | ISU Learning Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-correct: #2ECC71;     /* soft academic green */
            --color-incorrect: #E74C3C; 
        }
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
            max-height: 250px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .module-nav-content::-webkit-scrollbar {
            width: 6px;
        }

        .module-nav-content::-webkit-scrollbar-thumb {
            background-color: var(--color-card-border);
            border-radius: 3px;
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

        .topic-mastery-bar {
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .topic-mastery-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
            transition: width 0.5s ease;
        }

        .mastery-indicator {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.25rem;
            display: flex;
            justify-content: space-between;
        }

        .streak-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: rgba(249, 115, 22, 0.1);
            border-radius: 5px;
            font-size: 0.75rem;
        }

        .streak-fire {
            color: #f97316;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
                        Adaptive Training
                    </h1>
                    <p class="text-xs" style="color: var(--color-text-secondary);"><?= htmlspecialchars($course_name) ?></p>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center flex-wrap justify-end gap-2 md:gap-3 shrink-0">
                <div class="flex items-center gap-1 md:gap-2 bg-[var(--color-card-bg)] px-2 py-1 rounded text-xs md:text-sm">
                    <i class="fas fa-brain text-xs" style="color: var(--color-heading);"></i>
                    <span style="color: var(--color-text-secondary);">Adaptive Training Mode</span>
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
                        <h3 class="font-semibold text-sm md:text-base mb-3" style="color: var(--color-text);">Adaptive Training Progress</h3>
                        
                        <!-- Show all answered questions in sidebar -->
                        <?php if (!empty($answered_questions_list)): ?>
                        <div class="mb-3">
                            <div class="text-xs mb-1" style="color: var(--color-text-secondary);">Answered Questions:</div>
                            <div class="module-nav-content">
                                <?php 
                                $question_number = 1;
                                foreach ($answered_questions_list as $history_item): 
                                    $is_current = ($history_item['question_id'] == ($current_question['id'] ?? null));
                                    $is_correct_history = $history_item['is_correct'];
                                    
                                    $nav_class = 'module-nav';
                                    if ($is_current) {
                                        $nav_class .= ' current';
                                    } else if ($is_correct_history) {
                                        $nav_class .= ' correct';
                                    } else {
                                        $nav_class .= ' incorrect';
                                    }
                                ?>
                                    <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $history_item['question_index'] ?>" 
                                       class="<?= $nav_class ?>">
                                        <?= $question_number++ ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Current question indicator -->
                        <div class="mb-3">
                            <div class="text-xs mb-1" style="color: var(--color-text-secondary);">Current Question:</div>
                            <div class="flex justify-center">
                                <div class="module-nav current" style="width: 50px; height: 50px; font-size: 1.2rem; background-color: var(--color-heading);">
                                    <?= $answered_count + 1 ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Navigation buttons -->
                        <div class="flex justify-between items-center mb-3">
                            <?php if ($current_question_index > 0): ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_question_index - 1 ?>" 
                                   class="pagination-control text-xs md:text-sm" style="border-color: var(--color-card-border);">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            <?php else: ?>
                                <span class="pagination-control disabled text-xs md:text-sm">Prev</span>
                            <?php endif; ?>
                            
                            <span id="page-info" class="text-xs md:text-sm font-medium" style="color: var(--color-text-secondary);">
                                <?= $answered_count + 1 ?> of <?= $max_questions ?>
                            </span>
                            
                            <?php if (!$show_explanation): ?>
                                <span class="pagination-control disabled text-xs md:text-sm">Next</span>
                            <?php else: ?>
                                <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&next_question=1&question_index=<?= $current_question_index ?>" 
                                   class="pagination-control text-xs md:text-sm" style="border-color: var(--color-card-border);">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Current topic mastery -->
                        <?php if ($current_topic_id && isset($_SESSION['mastery_each_topic'][$current_topic_id])): 
                            $mastery_percentage = round($_SESSION['mastery_each_topic'][$current_topic_id] * 100);
                            $correct_streak = 0;
                            if (isset($_SESSION['answer_result_tracker'])) {
                                for ($i = count($_SESSION['answer_result_tracker']) - 1; $i >= 0; $i--) {
                                    if ($_SESSION['answer_result_tracker'][$i]) {
                                        $correct_streak++;
                                    } else {
                                        break;
                                    }
                                }
                            }
                        ?>
                        <div class="space-y-2 text-xs md:text-sm pt-3 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Current Topic:</span>
                                <span class="font-semibold" style="color: var(--color-heading);"><?= $current_question['topic_name'] ?? 'Unknown' ?></span>
                            </div>
                            
                            <?php if ($correct_streak > 0): ?>
                            <div class="streak-indicator">
                                <i class="fas fa-fire streak-fire"></i>
                                <span>Correct Streak: <strong><?= $correct_streak ?></strong></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mastery-indicator">
                                <span style="color: var(--color-text-secondary);">Mastery:</span>
                                <span class="font-semibold" style="color: var(--color-heading);"><?= $mastery_percentage ?>%</span>
                            </div>
                            <div class="topic-mastery-bar">
                                <div class="topic-mastery-fill" style="width: <?= $mastery_percentage ?>%"></div>
                            </div>
                            <div class="text-xs text-center" style="color: var(--color-text-secondary);">
                                Need 90% mastery to advance to next topic
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="space-y-2 text-xs md:text-sm pt-3 border-t" style="border-color: var(--color-card-border);">
                            <div class="flex justify-between">
                                <span style="color: var(--color-text-secondary);">Progress:</span>
                                <span class="font-semibold" style="color: var(--color-heading);"><?= $answered_count ?>/<?= $max_questions ?></span>
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
                        
                        <!-- Adaptive algorithm info -->
                        <div class="mt-3 pt-3 border-t" style="border-color: var(--color-card-border);">
                            <h5 class="font-semibold text-xs mb-1" style="color: var(--color-text);">Adaptive Algorithm</h5>
                            <ul class="text-xs space-y-1" style="color: var(--color-text-secondary);">
                                <li>• 3-4 correct streaks can pass mastery</li>
                                <li>• Move to next topic at 90% mastery</li>
                                <li>• Max 5 questions per topic</li>
                                <li>• Total limit: <?= $max_questions ?> questions</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-3 md:space-y-4 order-1 lg:order-2">
                    <?php if ($current_question && $answered_count < $max_questions): ?>
                    <div class="training-card p-3 md:p-4 lg:p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-3 md:gap-0 mb-3 md:mb-4 pb-3 border-b" style="border-color: var(--color-card-border);">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="status-badge" style="background-color: var(--color-heading); color: white;">
                                        Adaptive Training
                                    </span>
                                    <span class="status-badge" style="background-color: rgba(234, 179, 8, 0.1); color: var(--color-icon);">
                                        <i class="fas fa-brain mr-1"></i> Topic <?= $current_topic_index + 1 ?>
                                    </span>
                                    <?php if ($current_topic_id && isset($_SESSION['mastery_each_topic'][$current_topic_id])): ?>
                                    <span class="status-badge" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-correct);">
                                        <i class="fas fa-chart-line mr-1"></i> <?= round($_SESSION['mastery_each_topic'][$current_topic_id] * 100) ?>% Mastery
                                    </span>
                                    <?php endif; ?>
                                    <?php 
                                    if (isset($_SESSION['answer_result_tracker'])) {
                                        $current_streak = 0;
                                        for ($i = count($_SESSION['answer_result_tracker']) - 1; $i >= 0; $i--) {
                                            if ($_SESSION['answer_result_tracker'][$i]) {
                                                $current_streak++;
                                            } else {
                                                break;
                                            }
                                        }
                                        if ($current_streak >= 2): ?>
                                    <span class="status-badge" style="background-color: rgba(249, 115, 22, 0.1); color: #f97316;">
                                        <i class="fas fa-fire mr-1"></i> <?= $current_streak ?> Streak
                                    </span>
                                    <?php   endif;
                                    } ?>
                                </div>
                                <h2 class="text-lg md:text-xl lg:text-2xl font-semibold" style="color: var(--color-text);">
                                    <?= htmlspecialchars($current_question['question']) ?>
                                </h2>
                            </div>
                            <div class="text-right text-xs" style="color: var(--color-text-secondary);">
                                <div>Adaptive Mode</div>
                                <div>Immediate Feedback</div>
                                <div>Topic: <?= htmlspecialchars($current_question['topic_name']) ?></div>
                                <div>Question: <?= $answered_count + 1 ?>/<?= $max_questions ?></div>
                            </div>
                        </div>

                        <form method="POST" action="training_assessment_mode.php?course_id=<?= $course_id ?>&question_index=<?= $current_question_index ?>">
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
                                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&next_question=1&question_index=<?= $current_question_index ?>" 
                                           class="btn-primary px-3 md:px-4 py-1 md:py-2 rounded text-sm md:text-base font-medium">
                                            <i class="fas fa-arrow-right mr-1"></i> Next Question
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <input type="hidden" name="choice_id" id="selected_choice" value="<?= $user_answer ?>">
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
                        
                        <!-- Mastery update information -->
                        <?php if ($current_topic_id && isset($_SESSION['mastery_each_topic'][$current_topic_id])): 
                            $new_mastery = round($_SESSION['mastery_each_topic'][$current_topic_id] * 100);
                            
                            // Calculate streak
                            $current_streak = 0;
                            if (isset($_SESSION['answer_result_tracker'])) {
                                for ($i = count($_SESSION['answer_result_tracker']) - 1; $i >= 0; $i--) {
                                    if ($_SESSION['answer_result_tracker'][$i]) {
                                        $current_streak++;
                                    } else {
                                        break;
                                    }
                                }
                            }
                            
                            // Calculate mastery change (approximate)
                            $mastery_change = $is_correct ? 25 : -15;
                            if ($is_correct && $current_streak > 1) {
                                $mastery_change *= min($current_streak, 3); // Max 3x bonus
                            }
                        ?>
                        <div class="mt-4 pt-4 border-t" style="border-color: var(--color-card-border);">
                            <h5 class="font-semibold text-sm mb-2" style="color: var(--color-heading);">Mastery Update</h5>
                            <div class="flex items-center justify-between">
                                <div class="text-xs" style="color: var(--color-text-secondary);">
                                    Topic mastery <?= $is_correct ? 'increased' : 'decreased' ?> based on your answer
                                    <?php if ($is_correct && $current_streak > 1): ?>
                                    <br><span class="text-orange-500">(Streak bonus: <?= $current_streak ?>x!)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm font-semibold" style="color: <?= $is_correct ? 'var(--color-correct)' : 'var(--color-incorrect)' ?>;">
                                    <?= $is_correct ? '+' : '' ?><?= $mastery_change ?>%
                                </div>
                            </div>
                            <div class="topic-mastery-bar mt-2">
                                <div class="topic-mastery-fill" style="width: <?= $new_mastery ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span style="color: var(--color-text-secondary);">Current: <?= $new_mastery ?>%</span>
                                <span style="color: var(--color-text-secondary);">Target: 90%</span>
                            </div>
                            <?php if ($new_mastery >= 90): ?>
                            <div class="mt-2 p-2 bg-green-100 border border-green-200 rounded text-xs text-green-800">
                                <i class="fas fa-trophy mr-1"></i> Topic Mastery Achieved! You can now advance to the next topic.
                            </div>
                            <?php endif; ?>
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
                        <h3 class="text-xl font-semibold mb-4" style="color: var(--color-text);">Adaptive Training Complete!</h3>
                        <p class="mb-4" style="color: var(--color-text-secondary);">
                            You have completed <?= $answered_count ?> questions in this adaptive training module.
                            <?php if ($answered_count >= $max_questions): ?>
                            <br>You have reached the maximum of <?= $max_questions ?> questions.
                            <?php endif; ?>
                        </p>
                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&reset=1" 
                           class="btn-primary px-4 py-2 rounded mr-2">Restart Training</a>
                        <a href="training_assessment_result.php?course_id=<?= $course_id ?>" 
                           class="btn-secondary px-4 py-2 rounded">View Results</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function selectOption(choiceId) {
            // Only allow selection if explanation is not shown
            if (document.getElementById('explanation-panel')) {
                return;
            }
            
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