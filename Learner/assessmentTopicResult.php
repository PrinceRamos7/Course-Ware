<?php
require_once '../pdoconfig.php';
include 'functions/format_time.php';
include 'functions/count_estimated_time.php';

$student_id = $_SESSION['student_id'];

if (isset($_GET['course_id']) && isset($_GET['module_id']) && isset($_GET['topic_id']) && isset($_GET['assessment_id'])) {
  $course_id = (int) $_GET['course_id'];
  $module_id = (int) $_GET['module_id'];
  $topic_id = (int) $_GET['topic_id'];
  $assessment_id = (int) $_GET['assessment_id'];
}

$stmt = $pdo->prepare("SELECT  * FROM topics WHERE module_id = :module_id AND id = :topic_id");
$stmt->execute([
  ":module_id" => $module_id, 
  ":topic_id" => $topic_id
]);
$topic = $stmt->fetch();

if (isset($_SESSION['topic_answer_details'])) {
    $score = 0;
    $total = count($_SESSION['topic_answer_details']);

    foreach ($_SESSION['topic_answer_details'] as $index => $answer) {
        $question_id = $_SESSION['topic_answer_details'][$index]['question_id'];
        $choice_id   = $_SESSION['topic_answer_details'][$index]['choice_id'];
        $stmt = $pdo->prepare("SELECT is_correct FROM choices WHERE id = :choice_id AND question_id = :question_id");
        $stmt->execute([
          ':choice_id' => $choice_id, 
          ':question_id' => $question_id
        ]);
        $result = $stmt->fetch();     

        if ($result && $result['is_correct']) {
            $score++;
        }
    }
    $base_exp = $topic['total_exp'];
    $performance_exp = $base_exp * ($score / $total);
    $exp_gain = $base_exp + $performance_exp;

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
          ":new_intelligent_exp_gained" => $performance_exp,
          ":student_id" => $student_id
        ]);

        $stmt = $pdo->prepare("UPDATE student_score SET last_score = :score, attempt_count = attempt_count + 1, seconds_spent = :seconds_spent, exp_gained = :exp_gained, intelligent_exp_gained = :performance_exp WHERE id = :student_score_id");
        $stmt->execute([
          ":score" => $score, 
          ":student_score_id" => $student_score['id'],
          ":seconds_spent" => null,
          ":exp_gained" => $exp_gain,
          ":performance_exp" => $performance_exp
        ]);
        $student_score_id = $student_score['id'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO student_score (user_id, assessment_id, last_score, attempt_count, seconds_spent, exp_gained, intelligent_exp_gained) VALUES (:user_id, :assessment_id, :score, 1, :seconds_spent, :exp_gained, :performance_exp)");
        $stmt->execute([
          ":user_id" => $student_id, 
          ":assessment_id" => $assessment_id, 
          ":score" => $score,
          ":seconds_spent" => null,
          ":exp_gained" => $exp_gain,
          ":performance_exp" => $performance_exp
        ]);
        $student_score_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("UPDATE users 
                SET 
                  experience = experience + :exp_gained, 
                  intelligent_exp = intelligent_exp + :intelligent_exp_gained
                WHERE id = :student_id");
        $stmt->execute([
          ":exp_gained" => $exp_gain,
          ":intelligent_exp_gained" => $performance_exp,
          ":student_id" => $student_id]);
      }

      $stmt = $pdo->prepare("INSERT INTO student_attempt_tracker (score, student_score_id, seconds_spent, exp_gained, intelligent_exp_gain) VALUES (:score, :student_score_id, :seconds_spent, :exp_gained, :performance_exp)");
      $stmt->execute([
        ":score" => $score, 
        ":student_score_id" => $student_score_id,
        ":seconds_spent" => null,
        ":exp_gained" => $exp_gain,
        ":performance_exp" => $performance_exp
      ]);
      $attempt_id = $pdo->lastInsertId();

      foreach ($_SESSION['topic_answer_details'] as $index => $answer) {
        $question_id = $answer['question_id'];
        $choice_id   = $answer['choice_id'];

        $stmt = $pdo->prepare("SELECT is_correct FROM choices WHERE question_id = :question_id AND id = :choice_id");
        $stmt->execute([
          ":question_id" => $question_id, 
          ":choice_id" => $choice_id
        ]);
        $result = $stmt->fetch();

        $is_correct = ($result && $result['is_correct']) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO student_performance (user_id, assessment_id, question_id, result, topic_id, attempt_id) VALUES (:student_id, :assessment_id, :question_id, :is_correct, :topic_id, :attempt_id)");
        $stmt->execute([
          ":student_id" => $student_id, 
          ":assessment_id" => $assessment_id, 
          ":question_id" => $question_id, 
          ":is_correct" => $is_correct, 
          ":topic_id" => $topic_id, 
          ":attempt_id" => $attempt_id
        ]);
      }

      $stmt = $pdo->prepare("SELECT * FROM topics_completed WHERE student_id = :student_id AND topic_id = :topic_id");
      $stmt->execute([":student_id" => $student_id, ":topic_id" => $topic_id]);
      $topic_completed = $stmt->fetch();

      if (!$topic_completed) {
        $stmt = $pdo->prepare("INSERT INTO topics_completed (student_id, topic_id) VALUES (:student_id, :topic_id)");
        $stmt->execute([":student_id" => $student_id, ":topic_id" => $topic_id]);
      }
      
      $pdo->commit();
    } catch (Exception $e) {
      $pdo->rollBack();    
      throw $e;
    }
    include 'functions/get_student_progress.php';
    unset($_SESSION['progress']);
    unset($_SESSION['topic_answer_details']);
    unset($_SESSION['answeredCount']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style>
        /* Custom utility classes based on roots */
        .bg-main { background-color: var(--color-main-bg); }
        .bg-card { background-color: var(--color-card-bg); }
        .text-heading { color: var(--color-heading); }
        .text-heading-secondary { color: var(--color-heading-secondary); }
        .text-primary { color: var(--color-text); }
        .text-secondary { color: var(--color-text-secondary); }
        .border-card { border-color: var(--color-card-border); }
        
        .btn-primary { 
            background-color: var(--color-button-primary); 
            color: white; 
            transition: background-color 0.2s;
        }
        .btn-primary:hover { background-color: var(--color-button-primary-hover); }

        .btn-secondary { 
            background-color: var(--color-button-secondary); 
            color: var(--color-button-secondary-text); 
            transition: background-color 0.2s;
        }
        .btn-secondary:hover { 
            background-color: #fce3a7; /* Slight darker hover for secondary */
        }
        
        .bg-xp { background-color: var(--color-xp-bg); }
        .text-xp { color: var(--color-xp-text); }
        .bg-progress { background-color: var(--color-progress-bg); }
        .bg-progress-fill { background: var(--color-progress-fill); }
        .bg-card-section { 
            background-color: var(--color-card-section-bg); 
            border-color: var(--color-card-section-border);
        }
        .text-on-section { color: var(--color-text-on-section); }
        .text-icon { color: var(--color-icon); }

        /* Animation for XP gain - subtle scale-up */
        @keyframes pop-in {
            0% { transform: scale(0.5); opacity: 0; }
            80% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pop-in {
            animation: pop-in 0.4s ease-out;
        }
        
        /* Add hover effects explicitly since JS isn't adding them anymore */
        .score-block-hover:hover {
            transform: scale(1.03);
            --tw-ring-color: var(--color-heading-secondary);
            --tw-ring-opacity: 0.3;
            box-shadow: 0 0 0 4px var(--tw-ring-color);
            transition: transform 0.3s, box-shadow 0.3s;
        }
    </style>
</head>
<body class="bg-main font-['Inter'] min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8">
    <?php include "sidebar.php";?>
    
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-button md:hidden fixed top-4 left-4 z-50 bg-[var(--color-card-bg)] border border-[var(--color-card-border)] rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <!-- Overlay -->
    <div class="sidebar-overlay md:hidden"></div>

    <!-- Results Card -->
    <div id="results-card" class="bg-card border-card border-4 shadow-xl rounded-md w-full max-w-2xl text-center p-4 sm:p-6 md:p-8 lg:p-10 pop-in ml-0 md:ml-16">
        
        <!-- Header / Rank Display -->
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-heading mb-2">
                Assessment Complete!
            </h1>
            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-primary">
                Topic: <span class="text-heading-secondary"><?=$topic['title']?></span>
            </h2>
        </header>

        <!-- Score & Time Section (Grid Layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 md:mb-8">
            <!-- Left column (2 small cards stacked) -->
            <div class="flex flex-col gap-4 sm:gap-6 lg:col-span-1">
                <!-- 1. XP Earned -->
                <div class="bg-card-section p-3 sm:p-4 rounded-xl border border-card-section-border shadow-md score-block-hover">
                    <div class="text-icon mb-1">
                        <!-- SVG icon -->
                    </div>
                    <p class="text-xs sm:text-sm font-medium text-on-section uppercase">XP Earned</p>
                    <p class="text-xl sm:text-2xl md:text-3xl font-bold text-heading leading-tight" id="xp-gained">
                        +<?=$base_exp?>
                    </p>
                </div>

                <!-- 2. Time Spent -->
                <div class="bg-card-section p-3 sm:p-4 rounded-xl border border-card-section-border shadow-md score-block-hover">
                    <div class="text-icon mb-1">
                        <!-- SVG icon -->
                    </div>
                    <p class="text-xs sm:text-sm font-medium text-on-section uppercase">Performance XP</p>
                    <p class="text-xl sm:text-2xl md:text-3xl font-bold text-heading leading-tight" id="time-spent">
                        +<?= $performance_exp ?>
                    </p>
                </div>
            </div>

            <!-- Right column (large card = Final Score) -->
            <div class="bg-card-section p-4 sm:p-6 md:p-8 rounded-xl border border-card-section-border shadow-md score-block-hover lg:col-span-2 flex flex-col items-center justify-center">
                <div class="text-icon mb-2">
                    <!-- SVG icon -->
                </div>
                <p class="text-sm font-medium text-on-section uppercase">Final Score</p>
                <p class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-heading leading-tight mt-1" id="final-score">
                    <?=round(($score / $total) * 100)?>%
                </p>
                <p class="text-base sm:text-lg md:text-xl font-bold text-heading-secondary mt-2">
                    <?= $score ?> correct answers out of <?= $total ?>
                </p>
            </div>
        </div>

        <!-- Progress Bar (Gamified Element) -->
        <div class="mb-6 md:mb-8 p-3 sm:p-4 rounded-xl bg-xp shadow-inner border border-yellow-500/50">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs sm:text-sm font-bold text-xp">Level <?=$user_lvl?> Progress</span>
                <span class="text-xs sm:text-sm font-bold text-xp"><?=$progress?>%</span>
            </div>
            <div class="w-full bg-progress rounded-full h-2 sm:h-3">
                <div class="bg-progress-fill h-2 sm:h-3 rounded-full" style="width: <?=$progress?>%;"></div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            
            <a href="assessmentTopic.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&topic_id=<?=$topic_id?>&assessment_id=<?= $assessment_id?>&index=0" id="btn-retry" class="btn-primary w-full sm:w-1/2 flex items-center justify-center p-2 sm:p-3 rounded-xl font-bold text-base sm:text-lg shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                <!-- Inline SVG for 'rotate-ccw' icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 sm:w-5 sm:h-5 mr-2">
                    <path d="M12.9 6c-3.7-.8-7.4 1.5-8.2 5.2-.8 3.7 1.5 7.4 5.2 8.2 3.7.8 7.4-1.5 8.2-5.2s-1.5-7.4-5.2-8.2z"/><path d="M12 2v4"/><path d="M18 10h-4"/>
                </svg>
                Retry Quiz
            </a>

            <!-- Back to Topics Button (Secondary Action) -->
            <a href="topicContent.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>&topic_id=<?= $topic_id ?>" id="btn-back" class="btn-secondary w-full sm:w-1/2 flex items-center justify-center p-2 sm:p-3 rounded-xl font-bold text-base sm:text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.02]">
                <!-- Inline SVG for 'layout-grid' icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 sm:w-5 sm:h-5 mr-2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Back to Topics
            </a>
        </div>

    </div>

    <script>
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage();

            // Mobile sidebar functionality
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const body = document.body;
            
            if (mobileMenuButton && sidebar && overlay) {
                function openSidebar() {
                    sidebar.classList.add('mobile-open');
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                }
                
                function closeSidebar() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
                
                mobileMenuButton.addEventListener('click', openSidebar);
                overlay.addEventListener('click', closeSidebar);
                
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', closeSidebar);
                });
            }
        });
    </script>
</body>
</html>