<?php
include 'functions/get_student_progress.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['quiz_answer_info'])) {
        $_SESSION['quiz_answer_info'] = [];
    }

    $index = (int) $_POST['index'];
    $question_id = (int) $_POST['question_id'];
    $choice_id = $_POST['choice'] ?? 0;
    $time_spent = (int) $_POST['time_spent'];

    if (!isset($_SESSION['quiz_answer_info'][$index])) {
        $_SESSION['quiz_answer_info'][$index] = [
            'question_id' => $question_id,
            'choice_id' => $choice_id,
            'time_spent' => $time_spent,
        ];
    } else {
        $_SESSION['quiz_answer_info'][$index]['choice_id'] = $choice_id;
        $_SESSION['quiz_answer_info'][$index]['time_spent'] += $time_spent;
    }

    if ($_POST['action'] === 'next') {
        $index = $index + 1;
        header(
            "location: assessmentModule.php?course_id={$course_id}&module_id={$module_id}&question_id=" .
                $_SESSION['questions_id'][$index] .
                '',
        );
    } elseif ($_POST['action'] === 'prev') {
        $index = $index - 1;
        header(
            "location: assessmentModule.php?course_id={$course_id}&module_id={$module_id}&question_id=" .
                $_SESSION['questions_id'][$index] .
                '',
        );
    } elseif ($_POST['action'] === 'submit_answers' || $_POST['action'] === 'time_out_submit') {
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

        $incomplete = false;
        if ($_SESSION['total_answered'] < $_SESSION['total_questions'] && $_POST['action'] === 'submit_answers') {
            $incomplete = true;
        } else {
            header(
                "location: assessmentModuleResult.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$module_assessment_id}",
            );
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn - Quest Log</title>
    <link rel="stylesheet" href="../output.css"> 
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>
        body {
            padding: 0;
            min-height: 100vh;
            background-color: var(--color-main-bg); 
            color: var(--color-text); 
        }

        /* Header Optimization */
        .main-header {
            padding: 0.75rem 1.5rem; 
            background-color: var(--color-header-bg); 
            border-bottom: 3px solid var(--color-heading); 
            backdrop-filter: blur(5px);
        }

        /* Main Content Optimization */
        main {
            padding: 1.5rem; 
            flex-grow: 1; 
        }

        /* Styling for the central quiz frame */
        .lesson-frame {
            border: 4px solid var(--color-heading); 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15), 0 0 0 5px var(--color-xp-bg); 
            height: 100%;
            min-height: 55vh;
            background-color: var(--color-card-bg); 
            padding: 1rem;
        }
        
        /* Question Card Professional Styling - "Quest Details" */
        .question-card {
            background-color: var(--color-user-bg); 
            border: 2px solid var(--color-heading); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            position: relative; 
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Quiz Option Styling - "Action Buttons" */
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
        
        /* Temporary Selection State */
        .quiz-option.selected {
            border-color: var(--color-heading); 
            background-color: var(--color-sidebar-link-active); 
            box-shadow: 0 3px 0 var(--color-heading); 
            transform: translateY(0); 
        }

        /* Navigation Buttons */
        #next-button, #prev-button {
            transition: background-color 0.2s, transform 0.1s;
            background-color: var(--color-button-primary);
            color: white;
            box-shadow: 0 4px 0 var(--color-button-primary-hover);
            border-radius: 9999px;
            padding: 0.6rem 1.25rem;
            font-size: 0.95rem;
        }

        #next-button:hover:not(:disabled), #prev-button:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(1px);
            box-shadow: 0 2px 0 var(--color-button-primary-hover);
        }
        
        /* Submit Link Styling (Final Action Button) */
        #submit-link {
            background-color: var(--color-heading) !important;
            box-shadow: 0 4px 0 #14532d !important;
            padding: 0.75rem 1.5rem;
        }
        #submit-link:hover {
            background-color: #14532d !important;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #14532d !important;
        }

        /* XP/Points Display Header Styling */
        .xp-display {
            background-color: var(--color-xp-bg); 
            color: var(--color-xp-text); 
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        /* Progress Bar Styling */
        #progress-bar-fill {
            background: var(--color-progress-fill);
            border-radius: 9999px;
            height: 100%;
            transition: width 0.5s ease-out;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        /* Question point badge */
        .point-badge {
            position: absolute;
            top: -10px; 
            right: 10px;
            background-color: var(--color-heading-secondary); 
            color: white;
            padding: 0.4rem 0.8rem;
            box-shadow: 0 4px 0 #c2410c; 
            border: 2px solid white;
            animation: pulse-shadow 2s infinite alternate;
        }
        
        /* Ensure progress bar track uses the correct background and border */
        .w-full.h-4.mb-6 {
            background-color: var(--color-progress-bg);
            border-color: var(--color-heading);
        }

        @keyframes pulse-shadow {
            0% { box-shadow: 0 4px 0 #c2410c; }
            100% { box-shadow: 0 4px 0 #fdba74, 0 0 10px #fed7aa; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans">

    <header class="main-header shadow-xl px-8 flex justify-between items-center sticky top-0 z-10">
        
        <div class="flex flex-col">
            <h1 class="text-2xl font-extrabold" style="color: var(--color-heading);">🎯 Python Quest Log</h1>
            <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Module Assessment: Focused Review</h6>
        </div>

        <div id="total-points-display" class="xp-display flex items-center">
            <i class="fas fa-coins mr-2"></i> Total Points: <span class="ml-1 font-extrabold" id="quiz-total-points">2 / 650</span>
        </div> 
    </header>

    <main class="max-w-6xl mx-auto flex-1 flex flex-col w-full min-h-full"> 

        <div class="mb-4 text-center">
            <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Module Assessment</h1>
            <h2 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Section 1: Variables & Data Types</h2>
        </div>
        
        <div class="w-full h-4 mb-6 rounded-full border-2 border-green-700">
            <div id="progress-bar-fill" style="width: 40%;"></div>
        </div>

        <div class="lesson-frame flex-1 flex flex-col rounded-xl shadow-2xl">
            
            <form id="module-assessment-form" class="flex-1 flex flex-col" method="POST" action="assessmentModule.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>">
                <input type="hidden" name="index" value="1">
                <input type="hidden" name="question_id" value="123">
                <input type="hidden" name="time_spent" value="45">
                
                <div id="assessment-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                    <div id="carousel-inner" class="flex transition-transform duration-500 h-full" >
                        <!-- Question 1 -->
                        <div class="carousel-item min-w-full h-full flex flex-col justify-between p-1">
                            <div class="question-card flex-1 flex flex-col justify-center"> 
                                <?php
                                $stmt = $pdo->prepare(
                                    'SELECT * FROM questions WHERE assessment_id = :module_assessment_id',
                                );
                                $stmt->execute([':module_assessment_id' => $module_assessment_id]);
                                $questions = $stmt->fetchAll();

                                $questions_id = [];
                                $questions_text = [];
                                $_SESSION['questions_id'] = [];

                                foreach ($questions as $i => $question) {
                                    $questions_id[$i] = $question['id'];
                                    $questions_text[$i] = $question['question'];
                                    $_SESSION['questions_id'][$i] = $question['id'];
                                }

                                $min_question_id = min($questions_id);
                                $max_question_id = max($questions_id);
                                $_SESSION['total_questions'] = count($questions_id);

                                if (!$question_id) {
                                    $index = 0;
                                } else {
                                    $index = array_search($question_id, $questions_id);
                                }
                                $question_id = $questions_id[$index];
                                ?>
                                <input type='hidden' name='index' value='<?= $index ?>'>
                                <input type='hidden' name='question_id' value='<?= $question_id ?>'>
                                <input type='hidden' id='time_spent' name='time_spent' value=''>
                                <div class="point-badge">
                                    <i class="fas fa-star mr-1"></i> 100 XP
                                </div>
                                <p class="text-sm font-bold mb-3" style="color: var(--color-heading-secondary);">
                                    QUEST 1 / 5
                                </p>
                                <h4 class="text-xl font-extrabold mb-6" style="color: var(--color-text);">
                                    <?= $index + 1 ?>. <?= $questions_text[$index] ?>
                                </h4>
                                <div class="space-y-3 option-group" data-q-index="0">
                                    <?php
                                    $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
                                    $stmt->execute([':question_id' => $question_id]);
                                    $choices = $stmt->fetchAll();

                                    $letter = 'A';
                                    foreach ($choices as $choice) {
                                        $checked =
                                            isset($_SESSION['quiz_answer_info'][$index]['choice_id']) &&
                                            $_SESSION['quiz_answer_info'][$index]['choice_id'] == $choice['id']
                                                ? ' checked'
                                                : '';
                                        echo "
                                            <label for='{$choice['id']}' class='quiz-option p-4 rounded-lg flex items-center cursor-pointer'>
                                                <span class='text-lg font-extrabold mr-4' style='color: var(--color-heading-secondary);'>{$letter}.</span> 
                                                <p class='text-lg'>{$choice['choice']}</p>
                                                <input type='radio' id='{$choice['id']}' name='choice' value='{$choice['id']}'{$checked} class='hidden'>
                                            </label>
                                        ";
                                        $letter++;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completion Slide -->
                        <div class="carousel-item min-w-full p-1 h-full flex flex-col justify-center items-center">
                            <div class="question-card p-6 text-center" style="max-width: 500px; border-top: 4px solid var(--color-heading-secondary); transform: scale(1.02);">
                                <i class="fas fa-scroll text-5xl mb-3" style="color: var(--color-heading);"></i>
                                <h3 class="text-2xl font-extrabold mb-2" style="color: var(--color-heading);">Quest Log Complete!</h3>
                                <p class="text-lg leading-relaxed font-bold mb-3" style="color: var(--color-heading-secondary);">
                                    You've faced all 5 challenges.
                                </p>
                                <p class="text-base leading-relaxed" style="color: var(--color-text);">
                                    Proceed to the **Finish & Continue** button to finalize your submission and claim your rewards!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-2 p-3 border-t" style="border-color: var(--color-card-border);">
                    
                    <button type="submit" name="action" value='prev'<?= $question_id === $min_question_id ? 'disabled' : '' ?> id="prev-button" class="transition font-semibold flex items-center 
                                <?= $question_id === $min_question_id ? 'bg-gray-400 text-gray-200 cursor-not-allowed shadow-none opacity-50' : 'bg-green-600 text-white shadow-md hover:bg-green-700' ?>">
                        <i class="fas fa-arrow-left mr-2"></i> Previous Quest
                    </button>

                    <div id="progress-text" class="text-sm font-semibold" style="color: var(--color-text-secondary);">
                        Progress: 2 of 5 Quests Completed
                    </div>

                    <!--<a href="../module_assessment_result/index.php?course_id=<?php echo $course_id; ?>&module_id=<?php echo $module_id; ?>&topic_id=<?php echo $topic_id ??''; ?>&assessment_id=<?php echo $module_assessment_id; ?>" 
                       id="submit-link" 
                       class="rounded-full transition font-extrabold text-lg flex items-center justify-center" 
                       style="display: none; color: white; text-decoration: none; background-color: var(--color-heading) !important; box-shadow: 0 4px 0 #14532d !important; padding: 0.75rem 1.5rem;"
                       aria-label="Finish and Continue to Submit Assessment">
                        <i class="fas fa-gavel mr-3"></i> Finish & Continue
                    </a>-->

                    <button type="submit" name="action" value="<?= $question_id === $max_question_id
                        ? 'submit_answers'
                        : 'next' ?>" id="next-button" class="transition font-semibold flex items-center">
                        <?= $question_id === $max_question_id ? '<i class="fas fa-check mr-2"></i> Submit Answers': 'Next Quest <i class="fas fa-arrow-right ml-2"></i>' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Simple theme application (keeping this minimal JS as requested)
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
        document.querySelectorAll("input[name='choice']").forEach(radio => {
            radio.addEventListener("change", function() {
                // remove highlight from all
                document.querySelectorAll(".quiz-option").forEach(opt => {
                    opt.classList.remove("selected");
                });

                // add highlight to the selected option
                this.closest(".quiz-option").classList.add("selected");
            });
        });

    </script>
</body>
</html>