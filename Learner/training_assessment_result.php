<?php
session_start();
include '../pdoconfig.php';

if (!isset($_GET['course_id'])) {
    die("Course ID not specified.");
}

$course_id = (int)$_GET['course_id'];

// Get course details
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmt->execute([':course_id' => $course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found.");
}

$course_name = $course['title'];

// Get final assessment for this course
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'final' AND (course_id = :course_id OR course_id IS NULL) LIMIT 1");
$stmt->execute([':course_id' => $course_id]);
$final_assessment = $stmt->fetch();

if (!$final_assessment) {
    die("No final assessment found for this course.");
}

$final_assessment_id = $final_assessment['id'];

// Get ALL topics from the course
$stmt = $pdo->prepare('SELECT DISTINCT t.id, t.title FROM topics t 
                      JOIN questions q ON t.id = q.topic_id 
                      WHERE q.assessment_id = :assessment_id 
                      ORDER BY t.title');
$stmt->execute([':assessment_id' => $final_assessment_id]);
$all_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get questions for the final assessment with topics
$stmt = $pdo->prepare('SELECT q.*, t.title as topic_title, t.id as topic_id 
                      FROM questions q 
                      JOIN topics t ON q.topic_id = t.id 
                      WHERE q.assessment_id = :assessment_id 
                      ORDER BY t.id, q.id');
$stmt->execute([':assessment_id' => $final_assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_questions = count($questions);

if ($total_questions === 0) {
    die("No questions found for the final assessment.");
}

// Calculate results from session data
$correct_count = 0;
$answered_count = 0;
$topic_performance = [];
$covered_topics = [];
$uncovered_topics = [];

// First, get all topics from the assessment
$assessment_topics = [];
foreach ($questions as $question) {
    if (!in_array($question['topic_id'], $assessment_topics)) {
        $assessment_topics[] = $question['topic_id'];
    }
}

// Check if we have adaptive session data
$is_adaptive_mode = false;
if (isset($_SESSION['adaptive_question_history'][$course_id])) {
    $is_adaptive_mode = true;
    
    // Get covered topics from adaptive session
    $covered_topic_ids = [];
    foreach ($_SESSION['adaptive_question_history'][$course_id] as $history_item) {
        if (!in_array($history_item['topic_id'], $covered_topic_ids)) {
            $covered_topic_ids[] = $history_item['topic_id'];
        }
    }
    
    // Get uncovered topics (topics in assessment but not covered in adaptive mode)
    $uncovered_topic_ids = array_diff($assessment_topics, $covered_topic_ids);
    
    // Initialize all topics
    foreach ($all_topics as $topic) {
        $topic_id = $topic['id'];
        $topic_title = $topic['title'];
        
        if (in_array($topic_id, $covered_topic_ids)) {
            // This topic was covered in adaptive mode
            $topic_performance[$topic_id] = [
                'title' => $topic_title,
                'total' => 0,
                'correct' => 0,
                'percentage' => 0,
                'covered' => true,
                'questions_answered' => 0
            ];
            $covered_topics[$topic_id] = $topic_title;
        } else if (in_array($topic_id, $assessment_topics)) {
            // This topic is in assessment but wasn't covered in adaptive mode
            $uncovered_topics[$topic_id] = [
                'title' => $topic_title,
                'reason' => 'Not covered in adaptive assessment'
            ];
        }
    }
    
    // Calculate performance for covered topics
    foreach ($_SESSION['adaptive_question_history'][$course_id] as $history_item) {
        $question_id = $history_item['question_id'];
        $topic_id = $history_item['topic_id'];
        
        if (isset($topic_performance[$topic_id])) {
            $topic_performance[$topic_id]['total']++;
            $topic_performance[$topic_id]['questions_answered']++;
            
            if ($history_item['is_correct']) {
                $correct_count++;
                $topic_performance[$topic_id]['correct']++;
            }
            $answered_count++;
        }
    }
    
} else {
    // Regular training mode (non-adaptive)
    $is_adaptive_mode = false;
    
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $topic_id = $question['topic_id'];
        $topic_title = $question['topic_title'];
        
        // Initialize topic in performance array
        if (!isset($topic_performance[$topic_id])) {
            $topic_performance[$topic_id] = [
                'title' => $topic_title,
                'total' => 0,
                'correct' => 0,
                'percentage' => 0,
                'covered' => true,
                'questions_answered' => 0
            ];
            $covered_topics[$topic_id] = $topic_title;
        }
        
        $topic_performance[$topic_id]['total']++;
        
        if (isset($_SESSION['training_progress'][$question_id])) {
            $topic_performance[$topic_id]['questions_answered']++;
            $answered_count++;
            if ($_SESSION['training_progress'][$question_id]['is_correct']) {
                $correct_count++;
                $topic_performance[$topic_id]['correct']++;
            }
        }
    }
    
    // In regular mode, all assessment topics are considered covered
    foreach ($assessment_topics as $topic_id) {
        if (!isset($topic_performance[$topic_id])) {
            // Find topic info
            foreach ($all_topics as $topic) {
                if ($topic['id'] == $topic_id) {
                    $topic_performance[$topic_id] = [
                        'title' => $topic['title'],
                        'total' => 0,
                        'correct' => 0,
                        'percentage' => 0,
                        'covered' => true,
                        'questions_answered' => 0
                    ];
                    $covered_topics[$topic_id] = $topic['title'];
                    break;
                }
            }
        }
    }
}

// Calculate percentages for covered topics
foreach ($topic_performance as $topic_id => $topic) {
    $topic_performance[$topic_id]['percentage'] = $topic['questions_answered'] > 0 ? 
        round(($topic['correct'] / $topic['questions_answered']) * 100) : 0;
}

// Calculate overall score based on answered questions
$overall_score = $answered_count > 0 ? round(($correct_count / $answered_count) * 100) : 0;
$progress_percentage = $total_questions > 0 ? round(($answered_count / $total_questions) * 100) : 0;

// Determine performance indicators
function getPerformanceIndicator($percentage) {
    if ($percentage >= 90) return ['label' => 'Excellent', 'class' => 'performance-excellent', 'icon' => 'fa-trophy'];
    if ($percentage >= 80) return ['label' => 'Good', 'class' => 'performance-good', 'icon' => 'fa-check-circle'];
    if ($percentage >= 70) return ['label' => 'Fair', 'class' => 'performance-fair', 'icon' => 'fa-chart-line'];
    return ['label' => 'Needs Focus', 'class' => 'performance-poor', 'icon' => 'fa-exclamation-triangle'];
}

// Get worst performing covered topic for focus area
$worst_topic = null;
$worst_score = 100;
foreach ($topic_performance as $topic) {
    if ($topic['covered'] && $topic['questions_answered'] > 0 && $topic['percentage'] < $worst_score) {
        $worst_score = $topic['percentage'];
        $worst_topic = $topic;
    }
}

// Determine pass/fail status
$passing_threshold = 75;
$passed = $overall_score >= $passing_threshold;

// Get learning objectives for the course
$learning_objectives = [
    "Understand database design principles and normalization",
    "Master SQL query writing and optimization", 
    "Learn database security and administration",
    "Develop troubleshooting skills for database issues",
    "Prepare for professional database certification"
];

// Calculate coverage statistics
$total_assessment_topics = count($assessment_topics);
$covered_topics_count = count($covered_topics);
$uncovered_topics_count = $total_assessment_topics - $covered_topics_count;
$coverage_percentage = $total_assessment_topics > 0 ? round(($covered_topics_count / $total_assessment_topics) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Results | ISU Learning Platform</title>

    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Base Styles --- */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            background-color: var(--color-header-bg);
            border-bottom: 1px solid var(--color-card-border);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .training-card {
            background-color: var(--color-card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--color-card-border);
        }

        .btn-primary {
            background-color: var(--color-button-primary);
            color: white;
            transition: all 0.2s ease;
            font-weight: 600;
            border: none;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--color-button-primary-hover);
            box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--color-text);
            border: 1.5px solid var(--color-card-border);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: var(--color-card-border);
        }

        .progress-bar {
            height: 6px;
            border-radius: 3px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-progress-fill);
            transition: width 0.3s ease;
        }
        
        .metric-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Module Performance Bars */
        .result-progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
            overflow: hidden;
        }

        .result-progress-fill {
            background: linear-gradient(90deg, var(--color-heading) 0%, #34d399 100%);
            height: 100%;
            border-radius: 4px;
        }

        .result-progress-fill-warning {
            background: linear-gradient(90deg, #f97316 0%, #f59e0b 100%);
        }
        
        .result-progress-fill-poor {
            background: linear-gradient(90deg, #ef4444 0%, #f97316 100%);
        }
        
        /* Performance Indicators */
        .performance-indicator {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .performance-excellent {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .performance-good {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .performance-fair {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .performance-poor {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        /* Topic Status */
        .topic-status-covered {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
        }
        
        .topic-status-uncovered {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
        
        .topic-status-not-in-assessment {
            background-color: #f8fafc;
            border-left: 4px solid #94a3b8;
        }
        
        /* Analysis Cards */
        .improvement-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfccb 100%);
            border-left: 5px solid var(--color-heading);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .focus-card {
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
            border-left: 5px solid #f97316;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Score Circle */
        .score-circle {
            width: 120px;
            height: 120px;
        }

        /* Uncovered Topics */
        .uncovered-topic-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .uncovered-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .uncovered-icon.warning {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        /* Toggle Styles */
        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-radius: 10px;
            border: 2px solid #fbbf24;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .toggle-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
        }
        
        .toggle-container.active {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }
        
        .toggle-content {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .toggle-content.expanded {
            max-height: 1000px;
            opacity: 1;
            margin-top: 1rem;
        }
        
        .toggle-arrow {
            transition: transform 0.3s ease;
        }
        
        .toggle-arrow.expanded {
            transform: rotate(180deg);
        }
        
        /* Improved Layout Spacing */
        .section-spacing {
            margin-bottom: 2.5rem;
        }
        
        .card-header-spacing {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid;
        }
        
        .topic-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .topic-card {
            padding: 1.25rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .topic-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .topic-card.uncovered {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fbbf24;
        }
        
        .topic-card.covered {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #34d399;
        }
        
        .topic-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .badge-uncovered {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-covered {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        /* --- Media Queries for Responsiveness --- */
        @media (max-width: 1023px) {
            .container {
                padding: 0 1rem;
            }

            .header-content {
                flex-direction: row; 
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .header-info-badges {
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.5rem;
            }

            .header-info-badges > div {
                margin: 0;
            }
            
            .topic-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .score-circle {
                width: 100px;
                height: 100px;
            }
            
            .topic-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .score-circle {
                width: 90px;
                height: 90px;
            }

            .header-info-badges {
                flex-direction: column;
                align-items: stretch;
                margin-top: 0.75rem;
            }
            .header-info-badges > div {
                width: 100%;
                margin-right: 0;
                margin-bottom: 0.5rem;
                text-align: center;
            }

            .lg\:grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            .uncovered-topic-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .toggle-container {
                padding: 0.75rem 1rem;
            }
        }

        .section-title {
            border-bottom: 2px solid;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .coverage-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .coverage-good {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .coverage-partial {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .coverage-poor {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #bfdbfe;
            margin-top: 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <header class="header py-2">
        <div class="container">
            <div class="header-content flex justify-between items-center">
                
                <!-- Logo and Title Block -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full object-contain flex items-center justify-center shadow-md" style="background-color: var(--color-heading);">
                        <img src="../images/isu-logo.png" alt="ISU Logo">
                    </div>
                    <div>
                       <h1 class="text-base sm:text-lg font-extrabold tracking-wider truncate text-[var(--color-heading)] leading-none">
                            ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
                        </h1>
                        <p class="text-sm font-medium" style="color: var(--color-text-secondary);"><?= htmlspecialchars($course_name) ?> - Training Results</p>
                    </div>
                </div>
                
                <!-- Mode Indicator -->
                <div class="header-info-badges flex items-center space-x-3">
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-full text-sm shadow-sm font-semibold" 
                         style="background-color: <?= $is_adaptive_mode ? '#f0f9ff' : '#f0fdf4' ?>; 
                                border: 1px solid <?= $is_adaptive_mode ? '#bae6fd' : '#bbf7d0' ?>;">
                        <i class="fas <?= $is_adaptive_mode ? 'fa-brain' : 'fa-graduation-cap' ?> text-base" 
                           style="color: <?= $is_adaptive_mode ? '#0ea5e9' : '#16a34a' ?>;"></i>
                        <span style="color: <?= $is_adaptive_mode ? '#0369a1' : '#166534' ?>;">
                            <?= $is_adaptive_mode ? 'Adaptive Mode' : 'Training Mode' ?>
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-full text-sm shadow-sm font-semibold" 
                         style="background-color: var(--color-user-bg); border: 1px solid var(--color-card-border);">
                        <i class="fas fa-user-circle text-base" style="color: var(--color-heading);"></i>
                        <span style="color: var(--color-user-text);">Learner</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-10">
        <div class="container">
            
            <!-- 1. Summary Metrics Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
                
                <!-- Overall Score Card -->
                <div class="training-card p-6 flex flex-col items-center justify-center metric-card text-center">
                    <h3 class="font-semibold text-lg mb-4" style="color: var(--color-text);">Overall Performance</h3>
                    <div class="relative score-circle flex items-center justify-center mb-4">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Circle -->
                            <path
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="var(--color-progress-bg)"
                                stroke-width="3"
                            />
                            <!-- Progress Arc -->
                            <path
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="var(--color-progress)"
                                stroke-width="3"
                                stroke-dasharray="<?= $overall_score ?>, 100"
                            />
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center">
                            <span class="text-xl font-extrabold"><?= $overall_score ?>%</span>
                            <span class="text-sm font-medium" style="color: var(--color-text-secondary);">Total Score</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 p-2 px-4 rounded-full font-bold text-sm" 
                        style="background-color: <?= $passed ? 'var(--color-green-button)' : '#ef4444' ?>; color: white;">
                        <i class="fas <?= $passed ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                        <span><?= $passed ? 'PASSED' : 'FAILED' ?></span>
                    </div>
                </div>

                <!-- Assessment Details -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-5 border-b pb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Assessment Details</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Mode:</span>
                            <span class="font-semibold text-right"><?= $is_adaptive_mode ? 'Adaptive Training' : 'Full Training' ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Questions Answered:</span>
                            <span class="font-semibold text-right"><?= $answered_count ?> / <?= $total_questions ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Correct Answers:</span>
                            <span class="font-semibold text-right" style="color: var(--color-green-button);"><?= $correct_count ?> / <?= $answered_count ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Topics Covered:</span>
                            <span class="font-semibold text-right">
                                <?= $covered_topics_count ?> / <?= $total_assessment_topics ?>
                                <span class="coverage-badge <?= $coverage_percentage >= 90 ? 'coverage-good' : ($coverage_percentage >= 70 ? 'coverage-partial' : 'coverage-poor') ?> ml-2">
                                    <?= $coverage_percentage ?>%
                                </span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Date Completed:</span>
                            <span class="font-semibold text-right"><?= date('M. j, Y') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Topic Coverage -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-5 border-b pb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Topic Coverage</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-lg topic-status-covered">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                <div>
                                    <div class="font-semibold">Covered Topics</div>
                                    <div class="text-sm" style="color: var(--color-text-secondary);">Questions answered in assessment</div>
                                </div>
                            </div>
                            <span class="font-bold text-lg"><?= $covered_topics_count ?></span>
                        </div>
                        
                        <?php if ($is_adaptive_mode && $uncovered_topics_count > 0): ?>
                        <div class="flex items-center justify-between p-3 rounded-lg topic-status-uncovered">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-amber-600 mr-3"></i>
                                <div>
                                    <div class="font-semibold">Uncovered Topics</div>
                                    <div class="text-sm" style="color: var(--color-text-secondary);">Not assessed in adaptive mode</div>
                                </div>
                            </div>
                            <span class="font-bold text-lg"><?= $uncovered_topics_count ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-sm" style="color: var(--color-text-secondary);">
                            <i class="fas fa-info-circle mr-2"></i>
                            <?php if ($is_adaptive_mode): ?>
                                Adaptive mode focuses on your weakest areas first. Some topics may not be covered.
                            <?php else: ?>
                                Full training mode covers all available topics in the assessment.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Covered Topics Performance Section -->
            <div class="training-card p-6 mb-10">
                <div class="flex justify-between items-center card-header-spacing" style="border-color: var(--color-card-border);">
                    <div>
                        <h2 class="section-title text-2xl font-bold" style="border-color: var(--color-heading);">Performance on Covered Topics</h2>
                        <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">
                            These are the topics you actually encountered during the <?= $is_adaptive_mode ? 'adaptive' : '' ?> assessment.
                        </p>
                    </div>
                    <a href="bridge.php?course_id=<?= $course_id ?>&location=training_assessment" class="btn-secondary px-5 py-2 rounded-full text-sm font-semibold flex items-center hover:shadow-lg">
                        <i class="fas fa-redo mr-2"></i>
                        Retake Training
                    </a>
                </div>
                
                <?php if (count($topic_performance) > 0): ?>
                <div class="space-y-4">
                    <?php 
                    $topic_counter = 0;
                    foreach ($topic_performance as $topic_id => $topic): 
                        if (!$topic['covered']) continue;
                        $topic_counter++;
                        $performance = getPerformanceIndicator($topic['percentage']);
                        $is_worst_topic = ($worst_topic && $worst_topic['title'] === $topic['title'] && $worst_topic['percentage'] === $topic['percentage']);
                    ?>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150 <?= $is_worst_topic ? 'border-orange-300' : '' ?>">
                        <div class="flex-1 w-full sm:w-auto">
                            <div class="flex items-center mb-2">
                                <i class="fas <?= $performance['icon'] ?> mr-3 <?= $performance['class'] ?>"></i>
                                <h3 class="font-semibold text-lg"><?= $topic_counter ?>. <?= htmlspecialchars($topic['title']) ?></h3>
                            </div>
                            <div class="text-sm mb-3" style="color: var(--color-text-secondary);">
                                <?= $topic['questions_answered'] ?> question<?= $topic['questions_answered'] != 1 ? 's' : '' ?> answered
                                <?php if ($topic['questions_answered'] > 0): ?>
                                • <?= $topic['correct'] ?> correct • <?= ($topic['questions_answered'] - $topic['correct']) ?> incorrect
                                <?php endif; ?>
                            </div>
                            <div class="result-progress-bar">
                                <div class="result-progress-fill <?= $is_worst_topic ? 'result-progress-fill-warning' : '' ?>" style="width: <?= $topic['percentage'] ?>%"></div>
                            </div>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-0 sm:ml-4 min-w-max">
                            <span class="font-bold text-xl mr-4 <?= $is_worst_topic ? 'text-orange-600' : '' ?>"><?= $topic['percentage'] ?>%</span>
                            <span class="performance-indicator <?= $performance['class'] ?> rounded-full text-sm"><?= $performance['label'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-10">
                    <i class="fas fa-chart-bar text-4xl mb-4" style="color: var(--color-text-secondary);"></i>
                    <h3 class="text-xl font-semibold mb-2">No Topics Covered Yet</h3>
                    <p class="text-gray-600">Complete the training assessment to see your performance on covered topics.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- 3. Uncovered Topics Section (Only for Adaptive Mode) -->
            <?php if ($is_adaptive_mode && $uncovered_topics_count > 0): ?>
            <div class="training-card p-6 mb-10">
                <div class="flex justify-between items-center card-header-spacing" style="border-color: var(--color-card-border);">
                    <div>
                        <h2 class="section-title text-2xl font-bold" style="border-color: #f97316;">Topics Not Covered in Assessment</h2>
                        <p class="mt-2 text-sm" style="color: var(--color-text-secondary);">
                            These topics are part of the course but weren't assessed in the adaptive mode. 
                            <span class="font-medium">Consider reviewing them to ensure comprehensive knowledge.</span>
                        </p>
                    </div>
                </div>
                
                <!-- Toggle for Uncovered Topics -->
                <div class="toggle-container" onclick="toggleUncoveredTopics()">
                    <div class="flex items-center">
                        <i class="fas fa-book-open text-xl mr-3" style="color: #d97706;"></i>
                        <div>
                            <h3 class="font-bold text-lg">Show Uncovered Topics</h3>
                            <p class="text-sm" style="color: var(--color-text-secondary);">
                                Click to view <?= $uncovered_topics_count ?> topic<?= $uncovered_topics_count != 1 ? 's' : '' ?> that were not assessed
                            </p>
                        </div>
                    </div>
                    <div class="toggle-arrow" id="toggleArrow">
                        <i class="fas fa-chevron-down text-xl" style="color: #d97706;"></i>
                    </div>
                </div>
                
                <!-- Hidden Content -->
                <div class="toggle-content" id="uncoveredTopicsContent">
                    <div class="topic-grid">
                        <?php 
                        $uncovered_counter = 0;
                        foreach ($uncovered_topics as $topic_id => $topic): 
                            $uncovered_counter++;
                        ?>
                        <div class="topic-card uncovered">
                            <span class="topic-badge badge-uncovered">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Uncovered
                            </span>
                            <h4 class="font-semibold text-lg mb-2"><?= $uncovered_counter ?>. <?= htmlspecialchars($topic['title']) ?></h4>
                            <p class="text-sm mb-3" style="color: var(--color-text-secondary);">
                                <i class="fas fa-info-circle mr-1"></i>
                                Not assessed in adaptive mode
                            </p>
                            <div class="text-sm font-medium text-amber-700">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Recommended for review
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="info-box mt-6">
                        <div class="flex items-start">
                            <i class="fas fa-lightbulb text-xl mr-3 mt-1" style="color: #3b82f6;"></i>
                            <div>
                                <h4 class="font-semibold text-lg mb-2">Why Some Topics Weren't Covered</h4>
                                <p class="text-sm mb-3" style="color: var(--color-text);">
                                    The adaptive algorithm prioritizes topics where you need the most improvement. 
                                    If you quickly achieved mastery in certain areas, the system may not have presented questions from all topics.
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                    <div class="text-center">
                                        <div class="stat-number" style="color: #3b82f6;"><?= $covered_topics_count ?></div>
                                        <div class="stat-label">Covered Topics</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="stat-number" style="color: #f97316;"><?= $uncovered_topics_count ?></div>
                                        <div class="stat-label">Uncovered Topics</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="stat-number" style="color: #10b981;"><?= $total_assessment_topics ?></div>
                                        <div class="stat-label">Total Topics</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="stat-number" style="color: #8b5cf6;"><?= $coverage_percentage ?>%</div>
                                        <div class="stat-label">Coverage Rate</div>
                                    </div>
                                </div>
                                <p class="text-sm mt-4 font-medium" style="color: #3b82f6;">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Recommendation:</strong> For complete preparation, consider taking the full training mode.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4. Performance Analysis and Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 section-spacing">
                <!-- Performance Analysis -->
                <div class="training-card p-6">
                    <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-heading);">Assessment Analysis & Insights</h2>
                    
                    <div class="space-y-5">
                        <div class="improvement-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-heading);">Coverage Analysis</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        <?php if ($is_adaptive_mode): ?>
                                            You covered <strong><?= $covered_topics_count ?> out of <?= $total_assessment_topics ?> topics</strong> 
                                            (<?= $coverage_percentage ?>% coverage) in adaptive mode.
                                            <?php if ($uncovered_topics_count > 0): ?>
                                                <br><br>
                                                <strong><?= $uncovered_topics_count ?> topic<?= $uncovered_topics_count != 1 ? 's were' : ' was' ?> not assessed:</strong> 
                                                The adaptive algorithm focused on your areas needing improvement.
                                            <?php endif; ?>
                                        <?php else: ?>
                                            You completed the full training assessment covering all <strong><?= $total_assessment_topics ?> topics</strong>.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($worst_topic): ?>
                        <div class="focus-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-orange-100 text-orange-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-bullseye text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: #c2410c;">Primary Focus Area</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        Among covered topics, <strong><?= htmlspecialchars($worst_topic['title']) ?></strong> 
                                        showed the lowest performance at <strong><?= $worst_topic['percentage'] ?>%</strong>.
                                        <br><br>
                                        <strong>Recommendation:</strong> Focus your review efforts on this topic to improve your overall mastery.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="p-4 border border-blue-200 rounded-xl bg-blue-50">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-blue-100 text-blue-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-trophy text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-text);">Next Steps for Improvement</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        <?php if ($passed): ?>
                                        Congratulations! Your <strong><?= $overall_score ?>%</strong> score indicates strong understanding of covered topics.
                                        <?php else: ?>
                                        Your <strong><?= $overall_score ?>%</strong> score suggests areas for improvement in covered topics.
                                        <?php endif; ?>
                                        
                                        <br><br>
                                        <strong>To achieve comprehensive mastery:</strong>
                                        <ol class="list-decimal pl-5 mt-2 space-y-1">
                                            <li>Review performance on covered topics above</li>
                                            <?php if ($is_adaptive_mode && $uncovered_topics_count > 0): ?>
                                            <li>Practice uncovered topics for complete knowledge</li>
                                            <?php endif; ?>
                                            <li>Retake training with different modes</li>
                                            <li>Focus on weakest performing areas</li>
                                        </ol>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="training-card p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-heading);">Continue Your Learning Journey</h2>
                        <p class="mb-8" style="color: var(--color-text-secondary);">
                            Choose your next step based on your assessment results and learning goals.
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <a href="bridge.php?course_id=<?= $course_id ?>&location=training_assessment" class="btn-primary w-full py-4 rounded-xl font-bold text-lg flex items-center justify-center hover:scale-[1.01] transition duration-200 no-underline">
                            <i class="fas fa-redo mr-3"></i>
                            Retake Training Assessment
                        </a>
                        
                        <?php if ($worst_topic): ?>
                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&focus_topic=<?= array_search($worst_topic, $topic_performance) ?>" 
                           class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100 no-underline">
                            <i class="fas fa-bullseye mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Focus on <?= htmlspecialchars($worst_topic['title']) ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($is_adaptive_mode && $uncovered_topics_count > 0): ?>
                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&mode=full" 
                           class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100 no-underline">
                            <i class="fas fa-book-open mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Take Full Assessment (All Topics)
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100">
                            <i class="fas fa-download mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Export Results Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-12 text-center text-sm p-6" style="color: var(--color-text-secondary); border-top: 1px solid var(--color-card-border);">
        <p class="mb-1">ISU Learning Platform • <?= htmlspecialchars($course_name) ?> Training Results</p>
        <p>&copy; <?= date('Y') ?> ISUtoLearn. All rights reserved.</p>
    </footer>

    <script>
        function toggleUncoveredTopics() {
            const content = document.getElementById('uncoveredTopicsContent');
            const arrow = document.getElementById('toggleArrow');
            const container = document.querySelector('.toggle-container');
            
            content.classList.toggle('expanded');
            arrow.classList.toggle('expanded');
            container.classList.toggle('active');
        }
        
        // Initialize the toggle state
        document.addEventListener('DOMContentLoaded', function() {
            // You can set initial state here if needed
        });
    </script>
</body>
</html>