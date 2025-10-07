<?php
include 'functions/get_student_progress.php';
include_once 'functions/count_total_exp.php';
$_SESSION['total_answered'] = null;

if (isset($_GET['course_id']) && isset($_GET['module_id'])) {
    $course_id = (int) $_GET['course_id'];
    $module_id = (int) $_GET['module_id'];
    $question_id = $_GET['question_id'] ?? null;
}

$stmt = $pdo->prepare('SELECT * FROM modules WHERE course_id = :course_id AND id = :module_id');
$stmt->execute([':course_id' => $course_id, ':module_id' => $module_id]);
$module = $stmt->fetch();
$module_name = $module['title'];

$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'module' AND module_id = :module_id");
$stmt->execute([':module_id' => $module_id]);
$module_assessment = $stmt->fetch();
$module_assessment_id = $module_assessment['id'];
$duration = $module_assessment['time_set'];

if (!isset($_SESSION['quiz_end_time'])) {
    $_SESSION['quiz_end_time'] = time() + $duration;
}
$remaining = $_SESSION['quiz_end_time'] - time();
if ($remaining < 0) {
    $remaining = 0;
}

$stmt = $pdo->prepare('SELECT q.*, t.title FROM questions q
                JOIN topics t ON q.topic_id = t.id
            WHERE q.assessment_id = :module_assessment_id ORDER BY q.id ASC');
$stmt->execute([':module_assessment_id' => $module_assessment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questions_id = [];
$questions_text = [];
$question_topic_name = [];
foreach ($questions as $i => $question) {
    $questions_id[$i] = $question['id'];
    $questions_text[$i] = $question['question'];
    $question_topic_name[$i] = $question['title'];
    $_SESSION['questions_id'][$i] = $question['id'];
}
$total_questions = count($questions);
$_SESSION['total_questions'] = $total_questions;

if (!$question_id && isset($questions_id[0])) {
    $index = 0;
    $current_question_id = $questions_id[0];
} else {
    $index = array_search($question_id, $questions_id);
    $index = $index !== false ? $index : 0;
    $current_question_id = $questions_id[$index] ?? null;
}

$next_index = $index + 1;
$is_final_question = $index == $total_questions - 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['quiz_answer_info'])) {
        $_SESSION['quiz_answer_info'] = [];
    }

    $post_index = (int) $_POST['index'];
    $question_id = (int) $_POST['question_id'];

    $choice_id = $_POST['choice'] ?? 0; 
    $time_spent = (int) $_POST['time_spent'];

    $_SESSION['quiz_answer_info'][$post_index] = [
        'question_id' => $question_id,
        'choice_id' => $choice_id,

        'time_spent' => ($_SESSION['quiz_answer_info'][$post_index]['time_spent'] ?? 0) + $time_spent, 
    ];
    

    if ($_POST['action'] === 'submit_next' && $post_index < $total_questions - 1) {
        $target_q_id = $_SESSION['questions_id'][$post_index + 1];
        header(
            "location: assessmentModule.php?course_id={$course_id}&module_id={$module_id}&question_id=" . $target_q_id,
        );
        exit;
    } 

    elseif ($_POST['action'] === 'confirm_submit' || $_POST['action'] === 'time_out_submit') {
        $total_answered = 0;
        if (!empty($_SESSION['quiz_answer_info'])) {
            foreach ($_SESSION['quiz_answer_info'] as $answer) {
                if (!empty($answer['choice_id'])) {
                    $total_answered++;
                }
            }
        }
        
        if (!isset($_SESSION['total_answered'])) {
            $_SESSION['total_answered'] = $total_answered;
        }

        header(
            "location: assessmentModuleResult.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$module_assessment_id}",
        );
        exit;
    }
}

$current_question_id = $questions_id[$index] ?? null; 
$current_topic_name = $question_topic_name[$index] ?? 'Review';

$exp = count_total_exp($course_id, $module_id);
$total_exp = $exp[0];
$exp_each_question = $total_exp > 0 && $total_questions > 0 ? round($total_exp / $total_questions) : 10;

$questions_per_page = 5;
$total_pages = ceil($total_questions / $questions_per_page);
$current_page = floor($index / $questions_per_page) + 1;
$start_index_for_page = ($current_page - 1) * $questions_per_page;

$current_choices = [];
if ($current_question_id) {
    $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
    $stmt->execute([':question_id' => $current_question_id]);
    $current_choices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$answered_count_for_modal = 0;
if (!empty($_SESSION['quiz_answer_info'])) {
    foreach ($_SESSION['quiz_answer_info'] as $answer) {
        if (!empty($answer['choice_id'])) {
            $answered_count_for_modal++;
        }
    }
}
$unanswered_count = $total_questions - $answered_count_for_modal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>
        .timer-style {
            background-color: var(--color-xp-bg); 
            color: var(--color-text);
        }

        body { 
            padding: 0; 
            min-height: 100vh; 
            background-color: var(--color-main-bg); 
            color: var(--color-text); 
        }
        .main-header { 
            padding: 0.75rem 1.5rem; 
            background-color: var(--color-header-bg); 
            border-bottom: 3px solid var(--color-heading); 
            backdrop-filter: blur(5px); 
        }
        main { 
            padding: 1.5rem; 
            flex-grow: 1; 
        }
        .lesson-frame { 
            border: 4px solid var(--color-heading); 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15), 0 0 0 5px var(--color-xp-bg); 
            height: 100%; 
            min-height: 55vh; 
            background-color: var(--color-card-bg); 
            padding: 1rem; 
        }
        .question-card { 
            background-color: var(--color-user-bg); 
            border: 2px solid var(--color-heading); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08); 
            padding: 1.5rem; 
            position: relative; 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
        }
        .quiz-option { 
            background-color: var(--color-card-bg); 
            border: 2px solid var(--color-card-border); 
            cursor: pointer; 
            transition: all 0.2s ease; 
            color: var(--color-text); 
            box-shadow: 0 3px 0 var(--color-card-border); 
            border-radius: 0.5rem; 
            padding: 0.75rem 1rem; 
        }
        .quiz-option:hover { 
            border-color: var(--color-heading-secondary); 
            transform: translateY(-1px); 
            box-shadow: 0 4px 0 var(--color-heading-secondary); 
            background-color: var(--color-sidebar-link-hover); 
        }
        .quiz-option.selected { 
            border-color: var(--color-heading); 
            background-color: var(--color-sidebar-link-active); 
            box-shadow: 0 3px 0 var(--color-heading); 
            transform: translateY(0); 
        }
        #next-button { 
            transition: background-color 0.2s, transform 0.1s; 
            background-color: var(--color-button-primary); 
            color: white; 
            box-shadow: 0 4px 0 var(--color-button-primary-hover); 
            border-radius: 9999px; 
            padding: 0.6rem 1.25rem; 
            font-size: 0.95rem; 
        }
        #next-button:hover:not(:disabled) { 
            background-color: var(--color-button-primary-hover); 
            transform: translateY(1px); 
            box-shadow: 0 2px 0 var(--color-button-primary-hover); 
        }
        .xp-display { 
            background-color: var(--color-xp-bg); 
            color: var(--color-xp-text); 
            padding: 0.4rem 0.8rem; 
            font-size: 0.9rem; 
        }
        #progress-bar-fill { 
            background: var(--color-progress-fill); 
            border-radius: 9999px; 
            height: 100%; 
            transition: width 0.5s ease-out; 
        }
        .point-badge { 
            position: absolute; 
            top: -10px; 
            right: 10px; 
            background-color: var(--color-heading-secondary); 
            color: white; 
            padding: 0.4rem 0.8rem; 
            box-shadow: 0 4px 0 #e85d03; 
            border: 2px solid white; 
            animation: pulse-shadow 2s infinite alternate; 
        }
        .w-full.h-4.mb-6 { 
            background-color: var(--color-progress-bg); 
            border-color: var(--color-heading); 
        }
        .page-grid { 
            display: grid; 
            grid-template-columns: 250px 1fr; 
            gap: 1.5rem; 
            align-items: start; 
        }
        .quest-map-sidebar { 
            background-color: var(--color-card-bg); 
            border: 3px solid var(--color-heading); 
            padding: 1rem; 
            border-radius: 0.75rem; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }
        .quest-map-title { 
            color: var(--color-heading); 
            padding-bottom: 0.5rem; 
            border-bottom: 2px solid var(--color-card-border); 
            margin-bottom: 1rem; 
        }
        .question-link { 
            padding: 0.5rem 0.75rem; 
            margin-bottom: 0.5rem; 
            border-radius: 0.5rem; 
            cursor: pointer; 
            transition: background-color 0.2s, color 0.2s; 
            font-weight: 600; 
            color: var(--color-sidebar-text); 
            border: 1px solid transparent; 
            width: 100%; 
            text-align: left; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
        }
        .question-link:hover { 
            background-color: var(--color-sidebar-link-hover); 
        }
        
        .question-link.answered { 
            color: var(--color-heading);
        } 
        .question-link.answered:hover { 
            background-color: var(--color-sidebar-link-hover); 
        }

        .question-link.active.unanswered { 
            background-color: var(--color-heading-secondary); 
            color: white; 
            border: 1px solid #f97316; 
        }
        
        .question-link.active.answered { 
            background-color: var(--color-heading); 
            color: white; 
            border: 1px solid #16a34a; 
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
        /* Modal Styles */
        .modal-backdrop {
            background-color: var(--color-popup-bg);
        }
        .modal-content-frame {
            background-color: var(--color-card-bg);
            border: 4px solid var(--color-heading);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            border-radius: 0.75rem;
        }
        .nav-btn {
            transition: all 0.2s;
        }
        .nav-btn:hover {
            transform: translateY(1px);
            box-shadow: 0 2px 0 !important;
        }
        @keyframes pulse-shadow { 
            0% { 
                box-shadow: 0 4px 0 #e85d03; /* Darker orange */
            } 
            100% { 
                box-shadow: 0 4px 0 var(--color-button-secondary), 0 0 10px var(--color-button-secondary); 
            } 
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .page-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .quest-map-sidebar {
                order: 2;
                margin-top: 1rem;
            }
            .lesson-frame {
                order: 1;
                min-height: auto;
                padding: 1rem;
            }
            .main-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                text-align: left;
                padding: 1rem;
            }
            .question-card {
                padding: 1rem;
            }
            .question-card h4 {
                font-size: 1.1rem;
            }
            .pagination-number {
                width: 28px;
                height: 28px;
                font-size: 0.85rem;
            }
            #next-button {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
            .xp-display {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }
            .question-link {
                font-size: 0.9rem;
                padding: 0.5rem;
            }
            h1.text-4xl {
                font-size: 1.6rem;
            }
            h2.text-xl {
                font-size: 1.1rem;
            }
            .point-badge {
                top: -8px;
                right: 8px;
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
            .quiz-option {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
            }
            #choices-container {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .main-header {
                padding: 0.75rem;
            }
            main {
                padding: 1rem;
            }
            .lesson-frame {
                padding: 0.75rem;
            }
            .question-card {
                padding: 0.75rem;
            }
            .quest-map-sidebar {
                padding: 0.75rem;
            }
            .pagination-number {
                width: 24px;
                height: 24px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans">

    <?php include 'sidebar.php'; ?>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-button md:hidden fixed top-4 left-4 z-50 bg-[var(--color-card-bg)] border border-[var(--color-card-border)] rounded-lg p-2 text-[var(--color-text)]">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <!-- Overlay -->
    <div class="sidebar-overlay md:hidden"></div>

    <header class="main-header shadow-xl px-4 md:px-6 lg:px-8 flex flex-col md:flex-row md:justify-between md:items-center items-start gap-3 md:gap-0 sticky top-0 z-10 ml-0 md:ml-16">
        
        <div class="flex flex-col">
            <h1 class="text-xl md:text-2xl font-extrabold" style="color: var(--color-heading);">🎯 Module Assessment</h1>
            <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Section: <?= $module_name ?> Assessment</h6>
        </div>

        <div id="quiz-header-right" class="flex items-center space-x-3 md:space-x-4">
            <div id="timer-display" class="xp-display rounded-md text-red-600 font-extrabold flex items-center timer-style text-sm md:text-base">
                <i class="fas fa-clock mr-1"></i> Time: <span class="ml-1 font-extrabold"><?= gmdate("H:i:s", $remaining) ?></span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto flex-1 flex flex-col w-full min-h-full p-3 md:p-4 lg:p-6 ml-0 md:ml-16"> 
        
        <?php if (isset($_SESSION['validation_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-3 md:mb-4 text-sm md:text-base" role="alert">
                <p class="font-bold">Submission Failed</p>
                <p><?= $_SESSION['validation_error'] ?></p>
            </div>
            <?php unset($_SESSION['validation_error']); ?>
        <?php endif; ?>

        <div class="mb-3 md:mb-4 flex flex-col md:flex-row md:justify-between md:items-center items-start gap-2 md:gap-0 w-full">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold" style="color: var(--color-heading);">Quest Log</h1>
            <h2 class="text-lg md:text-xl font-bold" style="color: var(--color-heading-secondary);">Focus: <?= $current_topic_name ?></h2>
        </div>
        
        <div class="page-grid flex-1">
            
            <div class="quest-map-sidebar" style="border-color: var(--color-card-border); box-shadow: 8px 8px 0px 0px; var(--color-heading-secondary);">
                <h3 class="text-base md:text-lg font-extrabold quest-map-title mb-3" style="color: var(--color-heading); border-color: var(--color-card-border);">Quest Map</h3>
                <div class="flex flex-col space-y-1" id="question-list">
                    <?php 
                    for ($i = $start_index_for_page; $i < min($start_index_for_page + $questions_per_page, $total_questions); $i++) {
                        $q_num = $i + 1;
                        $q_id = $questions_id[$i];
                        $is_answered = isset($_SESSION['quiz_answer_info'][$i]['choice_id']) && $_SESSION['quiz_answer_info'][$i]['choice_id'] != 0;
                        $is_active = $q_id == $current_question_id;

                        $class_list = '';
                        $icon_class = 'fa-circle-dot';
                        if ($is_active) {
                            $class_list .= ' active';
                            $class_list .= $is_answered ? ' answered' : ' unanswered';
                            $icon_class = $is_answered ? 'fa-check-circle' : 'fa-circle-dot';
                        } elseif ($is_answered) {
                            $class_list .= ' answered';
                            $icon_class = 'fa-check-circle';
                        }
                        
                        $icon_color_class = $is_active ? 'text-white' : ($is_answered ? 'text-green-600' : 'text-gray-500');

                        echo "
                            <a href='assessmentModule.php?course_id={$course_id}&module_id={$module_id}&question_id={$q_id}' 
                                class='question-link{$class_list}' data-question-id='{$q_id}' data-index='{$i}'>
                                <i class='fas {$icon_class} mr-2 {$icon_color_class}'></i>
                                Question {$q_num}
                            </a>
                        ";
                    }
                    ?>
                </div>
                
                <div class="flex justify-center mt-3 md:mt-4 space-x-1 md:space-x-2" id="pagination-nav">
                    <?php 
                    for ($p = 1; $p <= $total_pages; $p++) {
                        $page_active = $p == $current_page ? ' active' : '';
                        $first_q_index_on_page = ($p - 1) * $questions_per_page;
                        $page_link_q_id = $questions_id[$first_q_index_on_page] ?? null;

                        echo "<a href='assessmentModule.php?course_id={$course_id}&module_id={$module_id}&question_id={$page_link_q_id}'
                                class='pagination-number {$page_active}' data-page='{$p}'>
                                {$p}
                            </a>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="flex-1 flex flex-col w-full min-h-full">
                
                <div class="flex justify-between items-center mb-3 md:mb-4 p-2 md:p-3 bg-[var(--color-card-bg)] border border-[var(--color-card-border)] rounded-lg shadow-inner">
                    <div class="flex-1 mr-3 md:mr-4">
                        <p class="text-xs md:text-sm font-semibold mb-1">Quest Progress: <?= $index + 1 ?>/<?= $total_questions ?></p>
                        <div class="w-full h-3 md:h-4 rounded-full border-2" style="border-color: var(--color-heading);">
                            <div id="progress-bar-fill" style="width: <?= $total_questions > 0 ? (($index + 1) / $total_questions) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <div class="lesson-frame flex-1 flex flex-col rounded-xl shadow-2xl">
                    
                <form id="module-assessment-form" class="flex-1 flex flex-col" method="POST" action="assessmentModule.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>">
                    <div id="assessment-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                        <div id="carousel-inner" class="flex transition-transform duration-500 h-full" style="transform: translateX(0%);">
                            <div id="question-container" class="carousel-item min-w-full h-full flex flex-col justify-between p-1">
                                <div class="question-card flex-1 flex flex-col"> 
                                    
                                    <input type='hidden' name='index' value='<?= $index ?>' id='question-index-input'>
                                    <input type='hidden' name='question_id' value='<?= $current_question_id ?>' id='question-id-input'>
                                    <input type='hidden' id='time_spent' name='time_spent' value='0'>
                                    
                                    <div class="point-badge" style="background-color: var(--color-heading-secondary);">
                                        <i class="fas fa-star mr-1"></i> <span id="xp-display"><?= $exp_each_question ?></span> XP
                                    </div>
                                    
                                    <div class="p-3 md:p-4 lg:p-6 rounded-lg mb-4 md:mb-6 shadow-md" style="background-color: var(--color-button-secondary); border: 2px solid var(--color-heading-secondary);">
                                        <h4 class="text-lg md:text-xl lg:text-2xl font-extrabold" style="color: var(--color-text);">
                                            <span id="question-number-display"><?= $index + 1 ?></span>. <span id="question-text-display"><?= $questions_text[$index] ?? 'No question found.' ?></span>
                                        </h4>
                                    </div>
                                    
                                    <div id="choices-container" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 option-group" data-q-index="<?= $index ?>">
                                        <?php
                                        $current_choice_id = $_SESSION['quiz_answer_info'][$index]['choice_id'] ?? 0;
                                        $letter = 'A';
                                        foreach ($current_choices as $choice) {
                                            $checked = $current_choice_id == $choice['id'] ? ' checked' : '';
                                            $selected_class = $current_choice_id == $choice['id'] ? ' selected' : '';
                                            echo "
                                                <label for='choice_{$choice['id']}' class='quiz-option p-3 md:p-4 rounded-lg flex items-center cursor-pointer{$selected_class}'>
                                                    <span class='text-base md:text-lg font-extrabold mr-3 md:mr-4' style='color: var(--color-heading-secondary);'>{$letter}.</span> 
                                                    <p class='text-base md:text-lg'>{$choice['choice']}</p>
                                                    <input type='radio' id='choice_{$choice['id']}' name='choice' value='{$choice['id']}'{$checked} class='hidden'>
                                                </label>
                                            ";
                                            $letter++;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center items-center mt-3 md:mt-4 p-2 md:p-3 border-t" style="border-color: var(--color-card-border);">
                        
                        <button type="button" 
                                        name="action" 
                                        id="modal-trigger-button" 
                                        data-is-final="<?= $is_final_question ? 'true' : 'false' ?>"
                                        class="transition font-semibold py-2 md:py-3 px-4 md:px-6 rounded-lg nav-btn flex items-center text-sm md:text-base
                                        <?= $is_final_question ? 'bg-green-700' : 'bg-green-600 text-white shadow-md hover:bg-green-700' ?>"
                                        style="background-color: <?= $is_final_question ? 'var(--color-heading)' : 'var(--color-button-primary)' ?>; color: white; box-shadow: 0 4px 0 <?= $is_final_question ? 'var(--color-text-on-section)' : 'var(--color-button-primary-hover)' ?>;">
                            <?= $is_final_question ? '<i class="fas fa-check-circle mr-2"></i> Submit Assessment': 'Submit & Next Quest <i class="fas fa-arrow-right ml-2"></i>' ?>
                        </button>
                        
                        <input type='hidden' id='final_action_input' name='action' value=''> 
                    </div>
                </form>
                </div>
            </div>
            
        </div>
    </main>

    <div id="submitConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden modal-backdrop">
        <div class="modal-content-frame text-center p-4 md:p-6 lg:p-8 rounded-xl w-11/12 max-w-md space-y-4 md:space-y-6 mx-4" style="border-color: var(--color-heading);">
            <i class="fas fa-feather-alt text-4xl md:text-5xl lg:text-6xl drop-shadow" style="color: var(--color-heading);"></i>
            
            <h3 class="text-xl md:text-2xl lg:text-3xl font-extrabold" style="color: var(--color-heading);">Finalize Assessment</h3>
            
            <p class="text-base md:text-lg font-medium" style="color: var(--color-text);">
                You have answered **all <span id="modalAnsweredCount"><?= $total_questions ?></span> questions**.
                <br>
                <span class="font-bold text-red-500 mt-2 block">Are you sure you want to submit your test?</span>
                <span class="text-xs md:text-sm italic block" style="color: var(--color-text-secondary);">(You cannot return to edit your answers.)</span>
            </p>

            <div class="flex flex-col md:flex-row justify-center space-y-3 md:space-y-0 md:space-x-4 pt-3 md:pt-4">
                <button id="cancelSubmissionBtn" class="font-bold py-2 md:py-3 px-3 md:px-6 rounded-lg transition-transform nav-btn text-sm md:text-base" 
                    style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); box-shadow: 0 4px 0 var(--color-button-secondary-text);">
                    <i class="fas fa-arrow-left mr-2"></i> Review Answers
                </button>
                <button type='button' id="confirmSubmissionBtn" class="font-bold py-2 md:py-3 px-3 md:px-6 rounded-lg transition-transform nav-btn text-sm md:text-base" 
                    style="background-color: var(--color-green-button); color: white; box-shadow: 0 4px 0 var(--color-green-button-hover);">
                    <i class="fas fa-check-circle mr-2"></i> Yes, Submit
                </button>
            </div>
        </div>
    </div>

    <div id="incompleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden modal-backdrop">
        <div class="modal-content-frame text-center p-4 md:p-6 lg:p-8 rounded-xl w-11/12 max-w-md space-y-4 md:space-y-6 mx-4" style="border-color: var(--color-heading-secondary);">
            <i class="fas fa-exclamation-triangle text-4xl md:text-5xl lg:text-6xl drop-shadow text-red-500"></i>
            
            <h3 class="text-xl md:text-2xl lg:text-3xl font-extrabold" style="color: var(--color-heading-secondary);">Quest Incomplete!</h3>
            
            <p class="text-base md:text-lg font-medium" style="color: var(--color-text);">
                You have answered <strong id="incompleteAnsweredCount" class="text-green-600"><?= $answered_count_for_modal ?></strong> out of **<?= $total_questions ?>** questions.
                <br>
                Please answer the remaining <strong id="incompleteMissingCount" class="text-red-500"><?= $unanswered_count ?></strong> questions.
                <span class="text-xs md:text-sm italic block mt-2" style="color: var(--color-text-secondary);">(All questions must be answered to proceed.)</span>
            </p>

            <div class="flex justify-center pt-3 md:pt-4">
                <button id="closeIncompleteModalBtn" class="font-bold py-2 md:py-3 px-4 md:px-6 rounded-lg transition-transform nav-btn text-sm md:text-base" 
                    style="background-color: var(--color-heading); color: white; box-shadow: 0 4px 0 var(--color-text-on-section);">
                    <i class="fas fa-times-circle mr-2"></i> Close & Review
                </button>
            </div>
        </div>
    </div>

    <script>
        const totalQuestions = <?= $total_questions ?>;
        const submitConfirmationModal = document.getElementById('submitConfirmationModal');
        const incompleteModal = document.getElementById('incompleteModal');
        const form = document.getElementById('module-assessment-form');
        const modalTriggerButton = document.getElementById('modal-trigger-button');
        const cancelSubmissionBtn = document.getElementById('cancelSubmissionBtn');
        const confirmSubmissionBtn = document.getElementById('confirmSubmissionBtn');
        const closeIncompleteModalBtn = document.getElementById('closeIncompleteModalBtn');
        const finalActionInput = document.getElementById('final_action_input');

        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            document.body.classList.toggle('dark-mode', isDarkMode);
        }

        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);

        function attachOptionListeners() {
            document.querySelectorAll("input[name='choice']").forEach(radio => {
                radio.addEventListener("change", function() {
                    document.querySelectorAll("#choices-container .quiz-option").forEach(opt => {
                        opt.classList.remove("selected");
                    });
                    this.closest(".quiz-option").classList.add("selected");
                });
            });
        }
        document.addEventListener('DOMContentLoaded', attachOptionListeners);

        let timeSpentInterval;
        function startQuestionTimer() {
            let secondsSpent = 0;
            const timeSpentInput = document.getElementById('time_spent');
            if (timeSpentInterval) clearInterval(timeSpentInterval);
            timeSpentInterval = setInterval(() => {
                secondsSpent++;
                timeSpentInput.value = secondsSpent;
            }, 1000);
        }
        document.addEventListener('DOMContentLoaded', startQuestionTimer);

        modalTriggerButton.addEventListener('click', function(event) {
            const isFinal = this.getAttribute('data-is-final') === 'true';
            
            if (!isFinal) {
                finalActionInput.value = 'submit_next';
                form.submit();
                return;
            }

            let answeredCount = <?= $answered_count_for_modal ?>;
            const currentQIndex = parseInt(document.getElementById('question-index-input').value);
            const currentChoiceSelected = document.querySelector("input[name='choice']:checked") !== null;
            const currentQWasAnsweredInSession = <?= $current_choice_id ?> != 0;

            let finalAnsweredCount = answeredCount;

            if (currentQIndex === totalQuestions - 1) {
                if (currentChoiceSelected && !currentQWasAnsweredInSession) {
                    finalAnsweredCount++;
                } else if (!currentChoiceSelected && currentQWasAnsweredInSession) {
                    finalAnsweredCount--;
                }
            }
            
            const finalMissing = totalQuestions - finalAnsweredCount;

            if (finalAnsweredCount < totalQuestions) {
                document.getElementById('incompleteAnsweredCount').textContent = finalAnsweredCount;
                document.getElementById('incompleteMissingCount').textContent = finalMissing;
                incompleteModal.classList.remove('hidden');
            } else {
                submitConfirmationModal.classList.remove('hidden');
            }
        });

        cancelSubmissionBtn.addEventListener('click', function() {
            submitConfirmationModal.classList.add('hidden');
        });

        closeIncompleteModalBtn.addEventListener('click', function() {
            incompleteModal.classList.add('hidden');
        });

        confirmSubmissionBtn.addEventListener('click', function() {
            finalActionInput.value = 'confirm_submit';
            form.submit();
        });

        // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
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