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
            'percentage' => 0
        ];
    }
    
    $topic_performance[$topic_id]['total']++;
    
    if (isset($_SESSION['training_progress'][$question_id])) {
        $answered_count++;
        if ($_SESSION['training_progress'][$question_id]['is_correct']) {
            $correct_count++;
            $topic_performance[$topic_id]['correct']++;
        }
    }
}

// Calculate percentages
$overall_score = $total_questions > 0 ? round(($correct_count / $total_questions) * 100) : 0;
$progress_percentage = $total_questions > 0 ? round(($answered_count / $total_questions) * 100) : 0;

// Calculate topic percentages
foreach ($topic_performance as $topic_id => $topic) {
    $topic_performance[$topic_id]['percentage'] = $topic['total'] > 0 ? 
        round(($topic['correct'] / $topic['total']) * 100) : 0;
}

// Determine performance indicators
function getPerformanceIndicator($percentage) {
    if ($percentage >= 90) return ['label' => 'Excellent', 'class' => 'performance-excellent'];
    if ($percentage >= 80) return ['label' => 'Good', 'class' => 'performance-good'];
    if ($percentage >= 70) return ['label' => 'Fair', 'class' => 'performance-fair'];
    return ['label' => 'Needs Focus', 'class' => 'performance-poor'];
}

// Get worst performing topic for focus area
$worst_topic = null;
$worst_score = 100;
foreach ($topic_performance as $topic) {
    if ($topic['percentage'] < $worst_score) {
        $worst_score = $topic['percentage'];
        $worst_topic = $topic;
    }
}

// Mock long-term metrics (in a real application, these would come from a database)
$highest_attempt_score = max(92, $overall_score); // Example logic
$average_attempt_score = round(($highest_attempt_score + $overall_score) / 2);
$total_attempts = 4; // Example

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
        
        /* Analysis Cards */
        .improvement-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfccb 100%);
            border-left: 5px solid var(--color-heading);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .strength-card {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-left: 5px solid var(--color-icon);
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
        }

        @media (max-width: 768px) {
            .score-circle {
                width: 100px;
                height: 100px;
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
        }

        .section-title {
            border-bottom: 2px solid;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
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
                
                <!-- Date and User Info Badges -->
                <div class="header-info-badges flex items-center space-x-3">
                    <div class="flex items-center space-x-2 px-3 py-2 rounded-full text-sm shadow-sm font-semibold" style="background-color: var(--color-user-bg); border: 1px solid var(--color-card-border);">
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
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Test Name:</span>
                            <span class="font-semibold text-right"><?= htmlspecialchars($course_name) ?> Final Assessment</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Questions Answered:</span>
                            <span class="font-semibold text-right"><?= $answered_count ?> / <?= $total_questions ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Correct Answers:</span>
                            <span class="font-semibold text-right" style="color: var(--color-green-button);"><?= $correct_count ?> / <?= $total_questions ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Passing Threshold:</span>
                            <span class="font-semibold text-right" style="color: var(--color-heading-secondary);"><?= $passing_threshold ?>%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--color-text-secondary); font-weight: 500;">Date Completed:</span>
                            <span class="font-semibold text-right"><?= date('M. j, Y') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="training-card p-6 metric-card">
                    <h3 class="font-semibold text-lg mb-5 border-b pb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Long-term Metrics</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span style="color: var(--color-text-secondary); font-weight: 500;">Highest Attempt Score:</span>
                                <span class="font-extrabold" style="color: var(--color-heading);"><?= $highest_attempt_score ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $highest_attempt_score ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span style="color: var(--color-text-secondary); font-weight: 500;">Average Attempt Score:</span>
                                <span class="font-extrabold" style="color: var(--color-heading);"><?= $average_attempt_score ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $average_attempt_score ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span style="color: var(--color-text-secondary); font-weight: 500;">Total Attempts Taken:</span>
                                <span class="font-extrabold" style="color: var(--color-heading-secondary);"><?= $total_attempts ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Module Performance Section -->
            <div class="training-card p-6 mb-10">
                <div class="flex justify-between items-center mb-6 border-b pb-4" style="border-color: var(--color-card-border);">
                    <h2 class="section-title text-2xl font-bold" style="border-color: var(--color-heading);">Performance by Topic Module</h2>
                    <a href="training_assessment_mode.php?course_id=<?= $course_id ?>" class="btn-secondary px-5 py-2 rounded-full text-sm font-semibold flex items-center hover:shadow-lg">
                        <i class="fas fa-redo mr-2"></i>
                        Retake Training
                    </a>
                </div>
                
                <div class="space-y-4">
                    <?php 
                    $topic_counter = 0;
                    foreach ($topic_performance as $topic_id => $topic): 
                        $topic_counter++;
                        $performance = getPerformanceIndicator($topic['percentage']);
                        $is_worst_topic = ($worst_topic && $worst_topic['title'] === $topic['title'] && $worst_topic['percentage'] === $topic['percentage']);
                    ?>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition duration-150 <?= $is_worst_topic ? 'border-orange-300' : '' ?>">
                        <div class="flex-1 w-full sm:w-auto">
                            <h3 class="font-semibold text-lg"><?= $topic_counter ?>. <?= htmlspecialchars($topic['title']) ?></h3>
                            <div class="result-progress-bar mt-3">
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
            </div>

            <!-- 3. Performance Analysis and Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Performance Analysis -->
                <div class="training-card p-6">
                    <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-heading);">Learner Insights & Analysis</h2>
                    
                    <div class="space-y-5">
                        <div class="improvement-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-green-100 text-green-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-heading);">Core Strengths</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        <?php
                                        $strong_topics = array_filter($topic_performance, function($topic) {
                                            return $topic['percentage'] >= 85;
                                        });
                                        
                                        if (count($strong_topics) > 0) {
                                            $strong_topic_names = array_map(function($topic) {
                                                return $topic['title'] . " (" . $topic['percentage'] . "%)";
                                            }, $strong_topics);
                                            echo "You've demonstrated <strong>strong performance</strong> in <strong>" . implode(', ', $strong_topic_names) . "</strong>. Continue building on these solid foundations.";
                                        } else {
                                            echo "Focus on developing core competencies across all topics. Consistent practice will help build your strengths.";
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($worst_topic): ?>
                        <div class="focus-card p-4 rounded-xl">
                            <div class="flex items-start">
                                <div class="p-3 rounded-xl bg-orange-100 text-orange-600 mr-4 mt-1 flex-shrink-0">
                                    <i class="fas fa-lightbulb text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1" style="color: #c2410c;">Focus Area</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        <strong><?= htmlspecialchars($worst_topic['title']) ?></strong> is your lowest scoring area at <strong><?= $worst_topic['percentage'] ?>%</strong>. 
                                        Consider reviewing the fundamental concepts and practicing more questions in this topic area.
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
                                    <h3 class="font-bold text-lg mb-1" style="color: var(--color-text);">Certification Readiness</h3>
                                    <p class="text-sm" style="color: var(--color-text);">
                                        <?php if ($passed): ?>
                                        Your <strong><?= $overall_score ?>%</strong> overall score places you <strong>above the passing requirement</strong>. 
                                        You're well prepared for certification. Consider targeted review of weaker areas before the official exam.
                                        <?php else: ?>
                                        Your <strong><?= $overall_score ?>%</strong> overall score is <strong>below the passing threshold</strong>. 
                                        Focus on improving your understanding of key concepts, especially in lower-performing topics.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="training-card p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="section-title text-2xl font-bold mb-6" style="border-color: var(--color-heading);">Next Steps & Resources</h2>
                        <p class="mb-8" style="color: var(--color-text-secondary);">
                            Utilize these resources to address your focus areas and solidify your preparation.
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <a href="bridge.php?course_id=<?= $course_id ?>&location=training_assessment" class="btn-primary w-full py-4 rounded-xl font-bold text-lg flex items-center justify-center hover:scale-[1.01] transition duration-200 no-underline">
                            <i class="fas fa-redo mr-3"></i>
                            Retake Training Assessment
                        </a>
                        
                        <?php if ($worst_topic): ?>
                        <a href="training_assessment_mode.php?course_id=<?= $course_id ?>&topic_id=<?= array_keys($topic_performance)[array_search($worst_topic, $topic_performance)] ?>" 
                           class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100 no-underline">
                            <i class="fas fa-bullseye mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Focus on <?= htmlspecialchars($worst_topic['title']) ?>
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn-secondary w-full py-4 rounded-xl font-semibold flex items-center justify-center hover:bg-gray-100">
                            <i class="fas fa-download mr-3 text-lg" style="color: var(--color-heading);"></i>
                            Export Detailed Results to PDF
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
</body>
</html>