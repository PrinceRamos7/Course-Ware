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

// Get testing assessment for this course
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'final' AND (course_id = :course_id OR course_id IS NULL) LIMIT 1");
$stmt->execute([':course_id' => $course_id]);
$testing_assessment = $stmt->fetch();

if (!$testing_assessment) {
    die("No testing assessment found for this course.");
}

$testing_assessment_id = $testing_assessment['id'];

// Get ALL topics from the course
$stmt = $pdo->prepare('SELECT DISTINCT t.id, t.title FROM topics t 
                      JOIN questions q ON t.id = q.topic_id 
                      WHERE q.assessment_id = :assessment_id 
                      ORDER BY t.title');
$stmt->execute([':assessment_id' => $testing_assessment_id]);
$all_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get questions for the testing assessment with topics
$stmt = $pdo->prepare('SELECT q.*, t.title as topic_title, t.id as topic_id 
                      FROM questions q 
                      JOIN topics t ON q.topic_id = t.id 
                      WHERE q.assessment_id = :assessment_id 
                      ORDER BY t.id, q.id');
$stmt->execute([':assessment_id' => $testing_assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_questions = count($questions);

if ($total_questions === 0) {
    die("No questions found for the testing assessment.");
}

// Calculate results from session data (based on training_assessment_mode.php logic)
$correct_count = 0;
$answered_count = 0;
$topic_performance = [];
$covered_topics = [];

// Check if we have testing session data (similar to training mode)
if (isset($_SESSION['testing_progress'][$course_id])) {
    // Testing mode - all questions should be answered
    $testing_progress = $_SESSION['testing_progress'][$course_id];
    
    // Initialize topic performance tracking
    foreach ($all_topics as $topic) {
        $topic_id = $topic['id'];
        $topic_title = $topic['title'];
        
        $topic_performance[$topic_id] = [
            'title' => $topic_title,
            'total' => 0,
            'correct' => 0,
            'percentage' => 0,
            'covered' => false,
            'questions_answered' => 0
        ];
    }
    
    // Count questions per topic and track performance
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $topic_id = $question['topic_id'];
        
        $topic_performance[$topic_id]['total']++;
        
        if (isset($testing_progress[$question_id])) {
            $topic_performance[$topic_id]['questions_answered']++;
            $answered_count++;
            
            if ($testing_progress[$question_id]['is_correct']) {
                $correct_count++;
                $topic_performance[$topic_id]['correct']++;
            }
            
            // Mark topic as covered
            $topic_performance[$topic_id]['covered'] = true;
            if (!in_array($topic_id, array_keys($covered_topics))) {
                $covered_topics[$topic_id] = $topic_performance[$topic_id]['title'];
            }
        }
    }
} else {
    // No testing data found
    die("No testing data found. Please complete the testing assessment first.");
}

// Calculate percentages for covered topics
foreach ($topic_performance as $topic_id => $topic) {
    if ($topic['questions_answered'] > 0) {
        $topic_performance[$topic_id]['percentage'] = round(($topic['correct'] / $topic['questions_answered']) * 100);
    }
}

// Calculate overall score
$overall_score = $answered_count > 0 ? round(($correct_count / $answered_count) * 100) : 0;
$progress_percentage = $total_questions > 0 ? round(($answered_count / $total_questions) * 100) : 0;

// Determine performance indicators with testing-specific thresholds
function getTestingPerformanceIndicator($percentage) {
    if ($percentage >= 85) return ['label' => 'Mastery', 'class' => 'performance-mastery', 'icon' => 'fa-medal', 'color' => '#10b981'];
    if ($percentage >= 70) return ['label' => 'Proficient', 'class' => 'performance-proficient', 'icon' => 'fa-check-circle', 'color' => '#3b82f6'];
    if ($percentage >= 60) return ['label' => 'Developing', 'class' => 'performance-developing', 'icon' => 'fa-chart-line', 'color' => '#f59e0b'];
    return ['label' => 'Beginning', 'class' => 'performance-beginning', 'icon' => 'fa-seedling', 'color' => '#ef4444'];
}

// Get worst performing topic for focus area
$worst_topic = null;
$worst_score = 100;
$best_topic = null;
$best_score = 0;

foreach ($topic_performance as $topic) {
    if ($topic['questions_answered'] > 0) {
        if ($topic['percentage'] < $worst_score) {
            $worst_score = $topic['percentage'];
            $worst_topic = $topic;
        }
        if ($topic['percentage'] > $best_score) {
            $best_score = $topic['percentage'];
            $best_topic = $topic;
        }
    }
}

// Determine pass/fail status with testing thresholds
$passing_threshold = 70; // Higher threshold for testing
$passed = $overall_score >= $passing_threshold;

// Calculate coverage statistics
$total_assessment_topics = 0;
$covered_topics_count = 0;

foreach ($topic_performance as $topic) {
    if ($topic['total'] > 0) {
        $total_assessment_topics++;
        if ($topic['questions_answered'] > 0) {
            $covered_topics_count++;
        }
    }
}

$coverage_percentage = $total_assessment_topics > 0 ? round(($covered_topics_count / $total_assessment_topics) * 100) : 0;

// Get time data if available
$completion_time = null;
if (isset($_SESSION['testing_start_time'][$course_id]) && isset($_SESSION['testing_end_time'][$course_id])) {
    $time_taken = $_SESSION['testing_end_time'][$course_id] - $_SESSION['testing_start_time'][$course_id];
    $minutes = floor($time_taken / 60);
    $seconds = $time_taken % 60;
    $completion_time = sprintf("%d:%02d", $minutes, $seconds);
}

// Calculate accuracy per topic type (for insights)
$topic_types = [];
foreach ($topic_performance as $topic_id => $topic) {
    if ($topic['questions_answered'] > 0) {
        $topic_name = $topic['title'];
        $topic_types[$topic_name] = $topic['percentage'];
    }
}

// Sort topics by performance
uasort($topic_performance, function($a, $b) {
    return $b['percentage'] <=> $a['percentage'];
});

// Get learning objectives for the course
$learning_objectives = [
    "Demonstrate comprehensive knowledge of database concepts",
    "Apply SQL skills to solve complex problems",
    "Show proficiency in database security measures",
    "Exhibit troubleshooting expertise",
    "Validate readiness for professional certification"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Results | ISU Learning Platform</title>

    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Modern Testing Theme --- */
        :root {
            --testing-primary: #4f46e5;
            --testing-secondary: #7c3aed;
            --testing-accent: #8b5cf6;
            --testing-success: #10b981;
            --testing-warning: #f59e0b;
            --testing-danger: #ef4444;
            --testing-info: #3b82f6;
            --testing-dark: #1e1b4b;
            --testing-light: #e0e7ff;
            --testing-card-bg: #ffffff;
            --testing-card-border: #e5e7eb;
            --testing-header-bg: #ffffff;
            --testing-text: #1f2937;
            --testing-text-secondary: #6b7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            color: var(--testing-text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            background: var(--testing-header-bg);
            border-bottom: 2px solid var(--testing-primary);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .testing-card {
            background: var(--testing-card-bg);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.12);
            border: 1px solid var(--testing-card-border);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .testing-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(79, 70, 229, 0.2);
        }

        .btn-testing {
            background: linear-gradient(135deg, var(--testing-primary) 0%, var(--testing-secondary) 100%);
            color: white;
            transition: all 0.3s ease;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-testing:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--testing-primary);
            border: 2px solid var(--testing-primary);
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-secondary:hover {
            background: var(--testing-primary);
            color: white;
        }

        .score-circle {
            width: 180px;
            height: 180px;
            position: relative;
        }

        .score-circle svg {
            transform: rotate(-90deg);
        }

        .score-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Performance Indicators */
        .performance-indicator {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .performance-mastery {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .performance-proficient {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .performance-developing {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .performance-beginning {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Topic Performance Bars */
        .topic-progress-bar {
            height: 10px;
            border-radius: 5px;
            background: #e5e7eb;
            overflow: hidden;
            position: relative;
        }

        .topic-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--testing-primary), var(--testing-accent));
            border-radius: 5px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .topic-progress-fill.mastery {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .topic-progress-fill.proficient {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
        }

        .topic-progress-fill.developing {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .topic-progress-fill.beginning {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        /* Topic Cards */
        .topic-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            border: 2px solid transparent;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .topic-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--testing-primary);
            transition: all 0.3s ease;
        }

        .topic-card:hover::before {
            width: 6px;
            background: var(--testing-secondary);
        }

        .topic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.15);
            border-color: var(--testing-light);
        }

        .topic-card.mastery {
            border-color: #10b981;
        }

        .topic-card.mastery::before {
            background: #10b981;
        }

        .topic-card.proficient {
            border-color: #3b82f6;
        }

        .topic-card.proficient::before {
            background: #3b82f6;
        }

        .topic-card.developing {
            border-color: #f59e0b;
        }

        .topic-card.developing::before {
            background: #f59e0b;
        }

        .topic-card.beginning {
            border-color: #ef4444;
        }

        .topic-card.beginning::before {
            background: #ef4444;
        }

        /* Stats Cards */
        .stat-card {
            background: linear-gradient(135deg, var(--testing-card-bg) 0%, #f8fafc 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid var(--testing-light);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .stat-icon.score {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: var(--testing-primary);
        }

        .stat-icon.accuracy {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: var(--testing-success);
        }

        .stat-icon.time {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: var(--testing-warning);
        }

        .stat-icon.coverage {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: var(--testing-info);
        }

        /* Section Headers */
        .section-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--testing-primary), var(--testing-secondary));
            border-radius: 2px;
        }

        .section-title {
            font-size: 1.875rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Result Badge */
        .result-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .result-pass {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
        }

        .result-fail {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
        }

        /* Insights Cards */
        .insight-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid var(--testing-light);
            position: relative;
            overflow: hidden;
        }

        .insight-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--testing-primary), var(--testing-secondary));
        }

        .insight-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .insight-icon.strength {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #10b981;
        }

        .insight-icon.weakness {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #ef4444;
        }

        .insight-icon.recommendation {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #3b82f6;
        }

        /* Animation for score reveal */
        @keyframes scoreReveal {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .score-reveal {
            animation: scoreReveal 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 0 1rem;
            }
            
            .score-circle {
                width: 150px;
                height: 150px;
            }
            
            .score-number {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .score-circle {
                width: 120px;
                height: 120px;
            }
            
            .score-number {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: 0 0.75rem;
            }
            
            .score-circle {
                width: 100px;
                height: 100px;
            }
            
            .score-number {
                font-size: 1.75rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
        }

        /* Print Styles */
        @media print {
            .btn-testing, .btn-secondary, .result-badge {
                display: none !important;
            }
            
            .testing-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            .score-circle svg {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="header py-4">
        <div class="container">
            <div class="flex justify-between items-center">
                <!-- Logo and Title -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary));">
                        <img src="../images/isu-logo.png" alt="ISU Logo" class="w-6 h-6">
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold tracking-wider truncate">
                            <span style="background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                ISU<span style="color: var(--testing-accent);">to</span>Learn
                            </span>
                        </h1>
                        <p class="text-sm font-medium" style="color: var(--testing-text-secondary);">Testing Results • <?= htmlspecialchars($course_name) ?></p>
                    </div>
                </div>
                
                <!-- Mode Indicator -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 px-4 py-2 rounded-full text-sm shadow-lg font-semibold" 
                         style="background: linear-gradient(135deg, var(--testing-primary), var(--testing-secondary)); color: white;">
                        <i class="fas fa-clipboard-check text-base"></i>
                        <span>Testing Mode</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 px-4 py-2 rounded-full text-sm shadow font-semibold" 
                         style="background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); border: 2px solid var(--testing-light);">
                        <i class="fas fa-user-graduate text-base" style="color: var(--testing-primary);"></i>
                        <span style="color: var(--testing-text);">Candidate</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-8">
        <div class="container">
            
            <!-- 1. Score Overview Section -->
            <div class="testing-card p-8 mb-8">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                    <!-- Score Visualization -->
                    <div class="flex flex-col items-center">
                        <div class="score-circle score-reveal mb-4">
                            <svg class="w-full h-full" viewBox="0 0 36 36">
                                <!-- Background Circle -->
                                <path
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="#e5e7eb"
                                    stroke-width="3"
                                />
                                <!-- Score Arc -->
                                <path
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="url(#scoreGradient)"
                                    stroke-width="3"
                                    stroke-dasharray="<?= $overall_score ?>, 100"
                                />
                                <defs>
                                    <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:var(--testing-primary);stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:var(--testing-secondary);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="score-number mb-1"><?= $overall_score ?>%</span>
                                <span class="text-sm font-medium" style="color: var(--testing-text-secondary);">Overall Score</span>
                            </div>
                        </div>
                        
                        <!-- Pass/Fail Badge -->
                        <div class="result-badge <?= $passed ? 'result-pass' : 'result-fail' ?>">
                            <i class="fas <?= $passed ? 'fa-trophy' : 'fa-redo' ?> mr-2"></i>
                            <?= $passed ? 'PASSED' : 'NOT PASSED' ?>
                        </div>
                    </div>
                    
                    <!-- Score Details -->
                    <div class="flex-1 max-w-2xl">
                        <div class="section-header">
                            <h2 class="section-title">Assessment Summary</h2>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="stat-card">
                                <div class="stat-icon score">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="stat-number"><?= $overall_score ?>%</div>
                                <div class="stat-label" style="color: var(--testing-text-secondary);">Score</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon accuracy">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div class="stat-number"><?= $accuracy = $answered_count > 0 ? round(($correct_count / $answered_count) * 100) : 0 ?>%</div>
                                <div class="stat-label" style="color: var(--testing-text-secondary);">Accuracy</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon time">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-number">
                                    <?= $completion_time ?: 'N/A' ?>
                                </div>
                                <div class="stat-label" style="color: var(--testing-text-secondary);">Time</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon coverage">
                                    <i class="fas fa-coverage"></i>
                                </div>
                                <div class="stat-number"><?= $coverage_percentage ?>%</div>
                                <div class="stat-label" style="color: var(--testing-text-secondary);">Coverage</div>
                            </div>
                        </div>
                        
                        <!-- Performance Indicator -->
                        <div class="flex items-center justify-between p-4 rounded-xl" 
                             style="background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); border: 2px solid var(--testing-light);">
                            <div>
                                <h3 class="font-bold text-lg mb-1" style="color: var(--testing-text);">Performance Level</h3>
                                <p class="text-sm" style="color: var(--testing-text-secondary);">
                                    Based on your overall score of <?= $overall_score ?>%
                                </p>
                            </div>
                            <?php 
                            $performance = getTestingPerformanceIndicator($overall_score);
                            ?>
                            <span class="performance-indicator <?= $performance['class'] ?>">
                                <i class="fas <?= $performance['icon'] ?> mr-2"></i>
                                <?= $performance['label'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 2. Topic Performance Grid -->
            <div class="testing-card p-8 mb-8">
                <div class="section-header">
                    <h2 class="section-title">Topic Performance Analysis</h2>
                    <p class="mt-2 text-sm" style="color: var(--testing-text-secondary);">
                        Detailed breakdown of your performance across all assessed topics
                    </p>
                </div>
                
                <?php if (count($topic_performance) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $topic_counter = 0;
                    foreach ($topic_performance as $topic_id => $topic): 
                        if ($topic['questions_answered'] === 0) continue;
                        $topic_counter++;
                        $performance = getTestingPerformanceIndicator($topic['percentage']);
                        $performance_class = strtolower($performance['label']);
                    ?>
                    <div class="topic-card <?= $performance_class ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-xs font-semibold px-3 py-1 rounded-full" 
                                      style="background: <?= $performance['color'] ?>20; color: <?= $performance['color'] ?>;">
                                    Topic <?= $topic_counter ?>
                                </span>
                                <h3 class="font-bold text-lg mt-2" style="color: var(--testing-text);">
                                    <?= htmlspecialchars($topic['title']) ?>
                                </h3>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold" style="color: <?= $performance['color'] ?>;">
                                    <?= $topic['percentage'] ?>%
                                </span>
                                <div class="text-xs" style="color: var(--testing-text-secondary);">
                                    Score
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span style="color: var(--testing-text-secondary);">
                                    <?= $topic['questions_answered'] ?> questions
                                </span>
                                <span style="color: <?= $performance['color'] ?>;">
                                    <?= $topic['correct'] ?> correct
                                </span>
                            </div>
                            <div class="topic-progress-bar">
                                <div class="topic-progress-fill <?= $performance_class ?>" 
                                     style="width: <?= $topic['percentage'] ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="performance-indicator <?= $performance['class'] ?> text-xs">
                                <i class="fas <?= $performance['icon'] ?> mr-1"></i>
                                <?= $performance['label'] ?>
                            </span>
                            <div class="text-xs" style="color: var(--testing-text-secondary);">
                                <?= $topic['questions_answered'] - $topic['correct'] ?> incorrect
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-10">
                    <i class="fas fa-chart-bar text-4xl mb-4" style="color: var(--testing-text-secondary);"></i>
                    <h3 class="text-xl font-semibold mb-2">No Performance Data</h3>
                    <p class="text-gray-600">Complete the testing assessment to see your topic performance.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- 3. Detailed Insights -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Strength Analysis -->
                <div class="insight-card">
                    <div class="insight-icon strength">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3" style="color: var(--testing-text);">Key Strengths</h3>
                    <p class="text-sm mb-4" style="color: var(--testing-text-secondary);">
                        <?php if ($best_topic): ?>
                        Your strongest area was <strong><?= htmlspecialchars($best_topic['title']) ?></strong> 
                        with a score of <strong><?= $best_topic['percentage'] ?>%</strong>.
                        <?php else: ?>
                        Complete more questions to identify your strengths.
                        <?php endif; ?>
                    </p>
                    <ul class="text-sm space-y-2" style="color: var(--testing-text-secondary);">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2" style="color: #10b981;"></i>
                            Overall accuracy: <?= $accuracy ?>%
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2" style="color: #10b981;"></i>
                            Topics covered: <?= $covered_topics_count ?> of <?= $total_assessment_topics ?>
                        </li>
                        <?php if ($passed): ?>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle mr-2" style="color: #10b981;"></i>
                            Passed the assessment threshold
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Weakness Analysis -->
                <div class="insight-card">
                    <div class="insight-icon weakness">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3" style="color: var(--testing-text);">Areas for Improvement</h3>
                    <p class="text-sm mb-4" style="color: var(--testing-text-secondary);">
                        <?php if ($worst_topic): ?>
                        Focus on improving <strong><?= htmlspecialchars($worst_topic['title']) ?></strong> 
                        (<?= $worst_topic['percentage'] ?>% score).
                        <?php else: ?>
                        All topics performed equally well.
                        <?php endif; ?>
                    </p>
                    <ul class="text-sm space-y-2" style="color: var(--testing-text-secondary);">
                        <li class="flex items-center">
                            <i class="fas fa-times-circle mr-2" style="color: #ef4444;"></i>
                            Questions missed: <?= $answered_count - $correct_count ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-times-circle mr-2" style="color: #ef4444;"></i>
                            Completion rate: <?= $progress_percentage ?>%
                        </li>
                        <?php if (!$passed): ?>
                        <li class="flex items-center">
                            <i class="fas fa-times-circle mr-2" style="color: #ef4444;"></i>
                            Below passing threshold (<?= $passing_threshold ?>%)
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Recommendations -->
                <div class="insight-card">
                    <div class="insight-icon recommendation">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3" style="color: var(--testing-text);">Next Steps</h3>
                    <p class="text-sm mb-4" style="color: var(--testing-text-secondary);">
                        Based on your performance, here are recommended actions:
                    </p>
                    <ul class="text-sm space-y-2" style="color: var(--testing-text-secondary);">
                        <li class="flex items-center">
                            <i class="fas fa-arrow-right mr-2" style="color: #3b82f6;"></i>
                            <?php if ($worst_topic): ?>
                            Focus review on <?= htmlspecialchars($worst_topic['title']) ?>
                            <?php else: ?>
                            Review all topics for comprehensive mastery
                            <?php endif; ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-arrow-right mr-2" style="color: #3b82f6;"></i>
                            Retake assessment in 2-3 days
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-arrow-right mr-2" style="color: #3b82f6;"></i>
                            Practice with timed mock tests
                        </li>
                        <?php if (!$passed): ?>
                        <li class="flex items-center">
                            <i class="fas fa-arrow-right mr-2" style="color: #3b82f6;"></i>
                            Target <?= $passing_threshold - $overall_score ?>% improvement
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- 4. Action Section -->
            <div class="testing-card p-8">
                <div class="section-header">
                    <h2 class="section-title">Continue Your Learning Journey</h2>
                    <p class="mt-2 text-sm" style="color: var(--testing-text-secondary);">
                        Choose your next step based on your testing results
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="testing_assessment_mode.php?course_id=<?= $course_id ?>" 
                       class="btn-testing py-4 rounded-xl font-bold text-lg flex flex-col items-center justify-center hover:scale-[1.02] transition duration-200 no-underline">
                        <i class="fas fa-redo text-2xl mb-2"></i>
                        Retake Test
                    </a>
                    
                    <?php if ($worst_topic): ?>
                    <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&focus_topic=<?= array_search($worst_topic, $topic_performance) ?>" 
                       class="btn-secondary py-4 rounded-xl font-bold text-lg flex flex-col items-center justify-center hover:scale-[1.02] transition duration-200 no-underline">
                        <i class="fas fa-bullseye text-2xl mb-2" style="color: var(--testing-primary);"></i>
                        Focus Practice
                    </a>
                    <?php else: ?>
                    <button class="btn-secondary py-4 rounded-xl font-bold text-lg flex flex-col items-center justify-center hover:scale-[1.02] transition duration-200">
                        <i class="fas fa-book-open text-2xl mb-2" style="color: var(--testing-primary);"></i>
                        Review Topics
                    </button>
                    <?php endif; ?>
                    
                    <button class="btn-testing py-4 rounded-xl font-bold text-lg flex flex-col items-center justify-center hover:scale-[1.02] transition duration-200"
                            onclick="window.print()">
                        <i class="fas fa-print text-2xl mb-2"></i>
                        Print Results
                    </button>
                    
                    <button class="btn-secondary py-4 rounded-xl font-bold text-lg flex flex-col items-center justify-center hover:scale-[1.02] transition duration-200">
                        <i class="fas fa-download text-2xl mb-2" style="color: var(--testing-primary);"></i>
                        Export PDF
                    </button>
                </div>
                
                <!-- Learning Objectives -->
                <div class="mt-8 pt-8 border-t" style="border-color: var(--testing-light);">
                    <h3 class="font-bold text-lg mb-4" style="color: var(--testing-text);">Assessment Objectives</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($learning_objectives as $objective): ?>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mt-1 mr-3" style="color: var(--testing-success);"></i>
                            <span class="text-sm" style="color: var(--testing-text);"><?= htmlspecialchars($objective) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-12 text-center p-6" style="color: var(--testing-text-secondary); border-top: 2px solid var(--testing-light);">
        <p class="mb-1 font-semibold">ISU Testing Platform • <?= htmlspecialchars($course_name) ?> Assessment Results</p>
        <p>Completed on <?= date('F j, Y \a\t g:i A') ?> • &copy; <?= date('Y') ?> ISUtoLearn. All rights reserved.</p>
    </footer>

    <script>
        // Add animation to score elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            const progressBars = document.querySelectorAll('.topic-progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
            
            // Add confetti effect for passing score
            <?php if ($passed): ?>
            setTimeout(() => {
                createConfetti();
            }, 1000);
            <?php endif; ?>
        });
        
        function createConfetti() {
            const colors = ['#4f46e5', '#7c3aed', '#8b5cf6', '#10b981', '#3b82f6'];
            const confettiCount = 100;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-20px';
                confetti.style.zIndex = '9999';
                confetti.style.pointerEvents = 'none';
                
                document.body.appendChild(confetti);
                
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight + 20}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: 3000 + Math.random() * 2000,
                    easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                });
                
                animation.onfinish = () => confetti.remove();
            }
        }
        
        // Print functionality
        function printResults() {
            window.print();
        }
    </script>
</body>
</html>