<?php
require_once '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_estimated_time.php';
include_once 'functions/count_total_exp.php';

$student_id = $_SESSION['student_id'];
$total_questions = $_SESSION['total_questions'];

if (isset($_GET['course_id']) && isset($_GET['module_id']) && isset($_GET['topic_id']) && isset($_GET['assessment_id'])) {
    $course_id = (int) $_GET['course_id'];
    $module_id = (int) $_GET['module_id'];
    $topic_id = (int) $_GET['topic_id'];
    $assessment_id = (int) $_GET['assessment_id'];
}

$stmt = $pdo->prepare("SELECT * FROM assessments WHERE id = :assessment_id");
$stmt->execute([':assessment_id' => $assessment_id]);
$assessment = $stmt->fetch();

$questions_id = [];
$choice_id = [];
$time_spent = [];
if (!empty($_SESSION['quiz_answer_info'])) {
    foreach ($_SESSION['quiz_answer_info'] as $answer_info) {
        $questions_id[] = $answer_info['question_id'];
        $choices_id[] = ($answer_info['choice_id']) ?? 0;
        $time_spent[] = $answer_info['time_spent'];
    }
}

$correct_answers = 0;
$incorrect_answers = 0;
foreach ($choices_id as $choice_id) {
    if (!($choice_id == 0)) {
        $stmt = $pdo->prepare("SELECT is_correct FROM choices WHERE id = :choice_id");
        $stmt->execute([":choice_id" => $choice_id]);
        $choice = $stmt->fetch();
        
        if ($choice['is_correct']) {
            $correct_answers++;
        } else {
            $incorrect_answers++;
        }
    }
}

$time = array_sum($time_spent);
$average_time_per_question = ($time / count($questions_id));
$fastest_time = min($time_spent);
$slowest_time = max($time_spent);

$count_zero_choices = 0;
$answered = 0;
foreach ($choices_id as $choice) {
    if ($choice == 0) {
        $count_zero_choices++;
    }
    $answered++;
}

$unanswered = ($total_questions - $answered) + $count_zero_choices;
$accuracy = ($correct_answers / $total_questions) * 100;
$exp = count_total_exp($course_id, $module_id);
$base_exp = $exp[0];
$intelligent_exp = $base_exp * ($correct_answers / $total_questions);
$exp_gain = $base_exp + $intelligent_exp;

$stmt = $pdo->prepare("SELECT DISTINCT topic_id FROM questions WHERE assessment_id = :assessment_id");
$stmt->execute([":assessment_id" => $assessment_id]);
$topics_id = $stmt->fetchAll();

$correct_count_per_topic = [];
$total_questions_per_topic = [];
foreach ($topics_id as $i => $tid) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE assessment_id = :assessment_id AND topic_id = :topic_id");
    $stmt->execute([":assessment_id" => $assessment_id, ":topic_id" => $tid['topic_id']]);
    $questions = $stmt->fetchAll();
    $total_questions_per_topic[$tid['topic_id']] = count($questions);

    $correct = 0;
    $wrong = 0;

    foreach ($questions as $question) {
        foreach ($choices_id as $choice_id) {
            if (!($choice_id == 0)) {
                $stmt = $pdo->prepare("SELECT * FROM choices WHERE id = :choice_id AND question_id = :question_id AND is_correct = 1");
                $stmt->execute([":choice_id" => $choice_id, ":question_id" => $question['id']]);
                $correct_answer = $stmt->fetch();

                if ($correct_answer) {
                    $correct++;
                }
            }
        }
    }

    $correct_count_per_topic[$tid['topic_id']] = [
        "correct_count" => $correct
    ];
}

$stmt = $pdo->prepare("SELECT * FROM student_score WHERE user_id = :student_id AND assessment_id = :assessment_id");
$stmt->execute([
    ":student_id" => $student_id, 
    ":assessment_id" => $assessment_id
]);
$student_score = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT experience, intelligent_exp FROM users WHERE id=:student_id");
    $stmt->execute([":student_id" => $student_id]);
    $users = $stmt->fetch();

    $pdo->beginTransaction();
    try {
      if ($student_score) {
        $stmt = $pdo->prepare("UPDATE users 
                SET 
                  experience = (experience - :old_exp_gained) + :new_exp_gained, 
                  intelligent_exp = (intelligent_exp - :old_intelligent_exp_gained) + :new_intelligent_exp_gained
                WHERE id = :student_id");
        $stmt->execute([
          ":old_exp_gained" => $student_score['exp_gained'],
          ":new_exp_gained" => $exp_gain,
          ":old_intelligent_exp_gained" => $student_score['intelligent_exp_gained'],
          ":new_intelligent_exp_gained" => $intelligent_exp,
          ":student_id" => $student_id
        ]);

        $stmt = $pdo->prepare("UPDATE student_score SET last_score = :score, attempt_count = attempt_count + 1, seconds_spent = :seconds_spent, exp_gained = :exp_gained, intelligent_exp_gained = :performance_exp WHERE id = :student_score_id");
        $stmt->execute([
          ":score" => $correct_answers, 
          ":student_score_id" => $student_score['id'],
          ":seconds_spent" => $time,
          ":exp_gained" => $exp_gain,
          ":performance_exp" => $intelligent_exp
        ]);
        $student_score_id = $student_score['id'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO student_score (user_id, assessment_id, last_score, attempt_count, seconds_spent, exp_gained, intelligent_exp_gained) VALUES (:user_id, :assessment_id, :score, 1, :seconds_spent, :exp_gained, :performance_exp)");
        $stmt->execute([
          ":user_id" => $student_id, 
          ":assessment_id" => $assessment_id, 
          ":score" => $correct_answers,
          ":seconds_spent" => $time,
          ":exp_gained" => $exp_gain,
          ":performance_exp" => $intelligent_exp
        ]);
        $student_score_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("UPDATE users 
                SET 
                  experience = experience + :exp_gained, 
                  intelligent_exp = intelligent_exp + :intelligent_exp_gained
                WHERE id = :student_id");
        $stmt->execute([
          ":exp_gained" => $exp_gain,
          ":intelligent_exp_gained" => $intelligent_exp,
          ":student_id" => $student_id]);
      }

      $stmt = $pdo->prepare("INSERT INTO student_attempt_tracker (score, student_score_id, seconds_spent, exp_gained, intelligent_exp_gain) VALUES (:score, :student_score_id, :seconds_spent, :exp_gained, :performance_exp)");
      $stmt->execute([
        ":score" => $correct_answers, 
        ":student_score_id" => $student_score_id,
        ":seconds_spent" => $time,
        ":exp_gained" => $exp_gain,
        ":performance_exp" => $intelligent_exp
      ]);
      $attempt_id = $pdo->lastInsertId();

      foreach ($_SESSION['quiz_answer_info'] as $answer_info) {
        $question_id = (int) $answer_info['question_id'];
        $choice_id = (int) $answer_info['choice_id'];
        $is_correct = null;
        if (!($choice_id == 0)) {
        $stmt = $pdo->prepare("SELECT c.is_correct, q.topic_id FROM choices c
                JOIN questions q ON c.question_id = q.id
                WHERE c.question_id = :question_id AND c.id = :choice_id");
        $stmt->execute([
          ":question_id" => $question_id, 
          ":choice_id" => $choice_id
        ]);
        $result = $stmt->fetch();

        $is_correct = ($result && $result['is_correct']) ? 1 : 0;
    } else {
        $stmt = $pdo->prepare("SELECT topic_id FROM questions WHERE id = :question_id");
        $stmt->execute([":question_id" => $question_id]);
        $result = $stmt->fetch();
    }

        $stmt = $pdo->prepare("INSERT INTO student_performance (user_id, assessment_id, question_id, result, topic_id, attempt_id) VALUES (:student_id, :assessment_id, :question_id, :is_correct, :topic_id, :attempt_id)");
        $stmt->execute([
          ":student_id" => $student_id, 
          ":assessment_id" => $assessment_id, 
          ":question_id" => $question_id, 
          ":is_correct" => $is_correct, 
          ":topic_id" => $result['topic_id'], 
          ":attempt_id" => $attempt_id
        ]);
      }

      $stmt = $pdo->prepare("SELECT * FROM topics_completed WHERE student_id = :student_id AND topic_id = :topic_id");
      $stmt->execute([":student_id" => $student_id, ":topic_id" => $result['topic_id']]);
      $topic_completed = $stmt->fetch();

      if (!$topic_completed) {
        $stmt = $pdo->prepare("INSERT INTO topics_completed (student_id, topic_id) VALUES (:student_id, :topic_id)");
        $stmt->execute([":student_id" => $student_id, ":topic_id" => $result['topic_id']]);
      }
      
      $pdo->commit();
      
      // DAILY GOALS INTEGRATION - ADDED HERE
      try {
          include 'functions/daily_goals_function.php';
          $goalsSystem = new DailyGoalsSystem($pdo);
          
          // Track module quiz completion
          $goalsSystem->updateGoalProgress($student_id, 'quizzes_completed');
          
          // Track perfect scores
          if ($correct_answers == $total_questions) {
              $goalsSystem->updateGoalProgress($student_id, 'perfect_scores');
          }
      } catch (Exception $e) {
          // Silently fail goals tracking to not break the main flow
          error_log("Goals tracking error in module result: " . $e->getMessage());
      }
      
    } catch (Exception $e) {
      $pdo->rollBack();     
      throw $e;
    }
    include 'functions/get_student_progress.php';
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
        .result-frame {
            border: 3px solid var(--color-heading);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3), 0 0 0 5px var(--color-heading-secondary); 
        }

        .status-passed {
            color: var(--color-green-button);
            font-weight: bold;
        }
        .status-failed {
            color: var(--color-red-button);
            font-weight: bold;
        }

        .exp-box {
            background-color: var(--color-card-section-bg);
            border-left: 5px solid var(--color-heading);
        }

        .performance-bar {
            height: 8px;
            background-color: var(--color-card-border);
            border-radius: 9999px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-out;
        }
        
        .bar-fill {
            height: 8px;
            border-radius: 9999px;
            transition: background-color 0.3s ease;
        }

        .bar-fill.default {
            background-color: #9ca3af;
        }

        .interactive-button {
            font-weight: bold;
            border-width: 2px;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.2s;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
        }
        .interactive-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
        }
        .review-button {
            background-color: var(--color-button-primary);
            color: white;
            border-color: var(--color-button-primary);
            box-shadow: 0 4px 0 var(--color-heading-secondary);
        }
        .retry-button {
            background-color: var(--color-red-button);
            color: white;
            border-color: var(--color-red-button);
            box-shadow: 0 4px 0 var(--color-red-button-hover);
        }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center sticky top-0 z-10" 
                style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-2xl mr-3" style="color: var(--color-heading);"></i>
                <h1 class="text-2xl font-bold" style="color: var(--color-text);">Assessment Debrief</h1>
            </div>
            <a href="modules.php" class="px-4 py-2 rounded-full transition-all interactive-button secondary-action text-sm font-semibold">
                 <i class="fas fa-home mr-2"></i> Back to Module
            </a>
        </header>

        <main class="p-4 sm:p-8 max-w-7xl mx-auto flex-1 w-full"> <div class="flex flex-col sm:flex-row justify-between items-start mb-6 pb-2 border-b" style="border-color: var(--color-card-border);">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold mb-1" style="color: var(--color-heading);">Assessment Result</h2>
                    <p class="text-xs sm:text-sm" style="color: var(--color-text-secondary);">
                        Lesson: <?= $assessment['name'] ?> • Date: <?= date("Y-m-d") ?> • Duration: <?= count_time_left($assessment['time_set']) ?>
                    </p>
                </div>
                <div class="flex items-center mt-4 sm:mt-0">
                    <span class="text-lg sm:text-xl font-bold mr-2" style="color: var(--color-text-secondary);">Status</span>
                    <span class="text-xl sm:text-2xl status-passed"><?= ($accuracy >= 60) 
                        ? "<span class='text-green-500 font-semibold'><i class='fas fa-check-circle mr-1'></i> Passed</span>" 
                        : "<span class='text-red-500 font-semibold'><i class='fas fa-times-circle mr-1'></i> Failed</span>" 
                    ?></span> 
                </div>
            </div>

            <div class="result-frame p-6 rounded-xl shadow-2xl flex flex-col lg:flex-row gap-6" 
                style="background-color: var(--color-card-bg);">
                    
                <div class="w-full lg:w-1/3 pr-0 lg:pr-6 border-b lg:border-r lg:border-b-0 pb-6 lg:pb-0" 
                    style="border-color: var(--color-card-border);">
                    <?php 
                    if ($accuracy < 50) {
                        $feedback = "<i class='fas fa-times-circle text-red-500 mr-1'></i> Needs serious improvement!";
                    } elseif ($accuracy < 60) {
                        $feedback = "<i class='fas fa-exclamation-circle text-orange-500 mr-1'></i> Keep trying, you can do it!";
                    } elseif ($accuracy < 70) {
                        $feedback = "<i class='fas fa-lightbulb text-amber-500 mr-1'></i> Almost there — review the basics.";
                    } elseif ($accuracy < 80) {
                        $feedback = "<i class='fas fa-thumbs-up text-purple-500 mr-1'></i> Nice effort!";
                    } elseif ($accuracy < 90) {
                        $feedback = "<i class='fas fa-smile text-blue-500 mr-1'></i> Good job!";
                    } elseif ($accuracy < 100) {
                        $feedback = "<i class='fas fa-trophy text-green-500 mr-1'></i> Excellent work!";
                    } else {
                        $feedback = "<i class='fas fa-crown text-emerald-500 mr-1'></i> Perfect score! Outstanding!";
                    }
                    ?>
                    <div class="mb-6">
                        <p class="text-5xl sm:text-6xl font-extrabold mb-1" style="color: var(--color-button-primary);"><?= $correct_answers ?> / <?= $total_questions ?></p>
                        <p class="text-lg sm:text-xl font-bold mb-4" style="color: var(--color-text);"><?= number_format($accuracy, 2) ?>% — <?= $feedback ?></p>
                        <p class="text-sm" style="color: var(--color-text-secondary);">Time Spent: <span class="font-bold"><?= count_time_left($time) ?></span></p>
                    </div>

                    <div class="exp-box p-4 rounded-lg shadow-inner mb-6">
                        <p class="text-lg font-bold mb-2" style="color: var(--color-heading);">EXP Gained <span class="float-right font-extrabold text-xl status-passed">+<?= $exp_gain ?></span></p>
                        <ul class="text-sm space-y-1" style="color: var(--color-text);">
                            <li class="flex justify-between">Base EXP: <span><?= $base_exp ?></span></li>
                            <li class="flex justify-between">Intelligent EXP: <span>+<?= $intelligent_exp ?></span></li>
                            <li class="flex justify-between font-bold" style="color: var(--color-heading-secondary);">Rank (class): <span>3 / 24</span></li>
                        </ul>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: var(--color-button-primary); color: white;">Accuracy <?= number_format($accuracy, 2) ?>%</span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: var(--color-heading-secondary); color: white;">Quiz Completed</span>
                    </div>

                </div>

                <div class="w-full lg:w-2/5 px-0 lg:px-6 border-b lg:border-r lg:border-b-0 pb-6 lg:pb-0">
                    
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="w-full md:w-1/2">
                            <h3 class="text-xl sm:text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Score Breakdown</h3>
                            <ul class="text-base sm:text-lg space-y-2 mb-8" style="color: var(--color-text);">
                                <li class="flex justify-between font-bold">Correct: <span class="status-passed"><?= $correct_answers ?> / <?= $total_questions ?></span></li>
                                <li class="flex justify-between">Wrong: <span class="font-bold" style="color: var(--color-red-button);"><?= $incorrect_answers ?></span></li>
                                <li class="flex justify-between">Unanswered: <span class="font-bold" style="color: var(--color-text-secondary);"><?= $unanswered ?></span></li>
                            </ul>
                        </div>

                        <div class="w-full md:w-1/2">
                            <h3 class="text-xl sm:text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Time Details</h3>
                            <ul class="text-base sm:text-lg space-y-2" style="color: var(--color-text);">
                                <li class="flex justify-between">Total time: <span class="font-bold"><?= count_time_left($time) ?></span></li>
                                <li class="flex justify-between">Average per question: <span class="font-bold"><?= count_time_left($average_time_per_question) ?></span></li>
                                <li class="flex justify-between">Fastest: <span class="font-bold status-passed"><?= count_time_left($fastest_time) ?></span></li>
                                <li class="flex justify-between">Slowest: <span class="font-bold" style="color: var(--color-red-button);"></strong> <?= count_time_left($slowest_time) ?></span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3 pl-0 lg:pl-6 flex flex-col justify-between">
                    
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold mb-4" style="color: var(--color-heading);">Per-topic performance</h3>
                        <div class="space-y-4">
                            <?php 
                            foreach ($correct_count_per_topic as $topic_id => $counts) {
                                $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = :topic_id");
                                $stmt->execute([":topic_id" => $topic_id]);
                                $topic = $stmt->fetch();

                                $performance = number_format((($counts['correct_count'] / $total_questions_per_topic[$topic_id]) * 100), 2);

                                echo "
                                <div class='topic-item'>
                                    <div class='flex justify-between mb-1 text-sm' style='color: var(--color-text);'>
                                        <span class='font-bold'>{$topic['title']}</span>
                                        <span class='font-extrabold'>{$performance}%</span>
                                    </div>
                                    <div class='performance-bar'>
                                        <div class='bar-fill' style='width: {$performance}%;' data-score='{$performance}'></div>
                                    </div>
                                </div>
                                ";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="pt-6 space-y-3">
                        
                        <a href="#" class="w-full py-2 rounded-full transition interactive-button secondary-action flex items-center justify-center font-semibold text-sm">
                            <i class="fas fa-file-pdf mr-2"></i> Download Report (PDF)
                        </a>
                        <a href="bridge.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&location=moduleassessment" 
                        class="w-full py-3 rounded-full transition duration-300 ease-in-out interactive-button review-button flex items-center justify-center font-extrabold text-sm">
                            <i class="fas fa-rotate-right mr-2 text-base"></i> Retry Assessment
                        </a>

                    </div>
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
        
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.bar-fill').forEach(bar => {
                const score = parseInt(bar.dataset.score, 10);
                let color = '#9ca3af';

                if (score < 50) color = '#ef4444';
                else if (score < 60) color = '#f97316';
                else if (score < 70) color = '#f59e0b';
                else if (score < 80) color = '#8b5cf6';
                else if (score < 90) color = '#3b82f6';
                else if (score < 100) color = '#10b981';
                else color = '#22c55e';

                bar.style.backgroundColor = color;
            });
        });
    </script>
</body>
</html>