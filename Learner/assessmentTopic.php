<?php
include 'functions/get_student_progress.php';

if (isset($_GET['course_id']) && isset($_GET['module_id']) && isset($_GET['topic_id'])  && isset($_GET['assessment_id'])) {
    $course_id = (int) $_GET['course_id'];
    $module_id = (int) $_GET['module_id'];
    $topic_id = (int) $_GET['topic_id'];
    $assessment_id = (int) $_GET['assessment_id'];
    $index = (int) $_GET['index'];
}

if (!isset($_SESSION['answeredCount'])) {
    $_SESSION['answeredCount'] = 0;
};

$stmt = $pdo->prepare("SELECT count(q.id) AS total_items FROM questions q
                JOIN assessments a ON q.topic_id = a.topic_id
        WHERE q.topic_id = :topic_id AND a.type = 'topic' AND q.assessment_id = :assessment_id");
$stmt->execute([":topic_id" => $topic_id, ":assessment_id" => $assessment_id]);
$assessment_info = $stmt->fetch();

$stmt = $pdo->prepare('SELECT * FROM topics WHERE id = :topic_id AND module_id = :module_id');
$stmt->execute([':topic_id' => $topic_id, ':module_id' => $module_id]);
$topic = $stmt->fetch();
$topic_name = $topic['title'];

$stmt = $pdo->prepare('SELECT * FROM assessments WHERE topic_id = :topic_id AND module_id = :module_id AND type = "topic"');
$stmt->execute([':topic_id' => $topic_id, ':module_id' => $module_id]);
$assessment = $stmt->fetch();
$duration = $assessment['time_set'];

if (!isset($_SESSION['quiz_end_time'])) {
    $_SESSION['quiz_end_time'] = time() + $duration;
}

if (!isset($_SESSION['topic_question_id'])) {
    $_SESSION['topic_question_id'] = [];
}

if (!isset($_SESSION['topic_answer_details'])) {
    $_SESSION['topic_answer_details'] = [];
} 

if (!isset($_SESSION['topic_question_id'][$index])) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE assessment_id = :assessment_id AND topic_id = :topic_id');
    $stmt->execute([':assessment_id' => $assessment_id, ':topic_id' => $topic_id]);
    $questions = $stmt->fetchAll();
    foreach ($questions as $i => $question) {
        $_SESSION['topic_question_id'][$i] = $question['id'];
    }
}

$max_qid = max($_SESSION['topic_question_id']);
$min_qid = min($_SESSION['topic_question_id']);

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $answers = $_POST['answers'] ?? [];

    if (!empty($answers)) {
        // Get the first key and value
        $question_id = array_key_first($answers);   // the key
        $choice_id   = $answers[$question_id];  
    }

    $_SESSION['topic_answer_details'][$index] = [
        "question_id" => ($question_id) ?? null,
        "choice_id" => ($choice_id) ?? null
    ];

    $_SESSION['answeredCount'] = count(array_filter(
        array_column($_SESSION['topic_answer_details'], 'choice_id'),
        fn($cid) => $cid != null
    ));

    if (isset($_POST['map_index']) && $_POST['action'] === 'map_btn') {
        header("location: assessmentTopic.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$assessment_id}&index=" . $_POST['map_index']);
        exit;
    }

    if ($_POST['action'] === 'next') {
        $index = $index + 1;
        header("location: assessmentTopic.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$assessment_id}&index=" . $index . "");
        exit;
    } elseif ($_POST['action'] === 'prev') {
        $index = $index - 1;
        header("location: assessmentTopic.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$assessment_id}&index=" . $index . "");
        exit;
    } elseif ($_POST['action'] === 'submit_answers') {
        unset($_SESSION['quiz_end_time']);

        if (!($_SESSION['answeredCount'] == $assessment['total_items'])) {
            $not_complete = true;
            $remaining = $assessment['total_items'] - $_SESSION['answeredCount'];
        } else {
            $confirmation = true;
        }
    } elseif ($_POST['action'] === 'confirm_submit') {
        header("location: assessmentTopicResult.php?course_id={$course_id}&module_id={$module_id}&topic_id={$topic_id}&assessment_id={$assessment_id}");
        exit;
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn - Assessment</title>
    <link rel="stylesheet" href="../output.css"> 
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<style>

/* --- BASE STYLES --- */

body {
    background-color: var(--color-main-bg);
    color: var(--color-text);
    font-family: ui-sans-serif, system-ui, sans-serif;
    transition: background 0.5s ease, color 0.5s ease;
    padding: 0;
}

/* --- CUSTOM RADIO BUTTONS --- */
.custom-radio input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    display: inline-block;
    position: relative;
    height: 1.25rem; 
    width: 1.25rem;
    border-radius: 50%; 
    border: 3px solid var(--color-radio-border);
    background-color: var(--color-radio-bg);
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease-in-out;
    flex-shrink: 0;
}
.custom-radio input[type="radio"]:checked {
    border-color: var(--color-radio-checked);
    background-color: var(--color-radio-checked);
    box-shadow: 0 0 0 2px var(--color-card-bg) inset;
}
.custom-radio input[type="radio"]:checked::after {
    content: '';
    display: block;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 0.35rem; 
    height: 0.35rem; 
    border-radius: 50%; 
    background-color: var(--color-card-bg);
    opacity: 0;
    transition: opacity 0.2s;
}

/* --- CHOICE CARDS (BUTTON-LIKE SELECTIONS) --- */
.choice-card {
    transition: all 0.2s ease-in-out, transform 0.1s ease;
    cursor: pointer;
    border: 3px solid var(--color-card-border);
    box-shadow: 0 4px 0 var(--color-card-border);
    background-color: var(--color-card-bg);
}
.choice-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-heading);
    box-shadow: 0 6px 0 var(--color-heading);
}
.choice-card.selected {
    transform: translateY(2px);
    border-color: var(--color-heading-secondary) !important;
    background-color: var(--color-card-section-bg) !important;
    box-shadow: 0 2px 0 var(--color-heading-secondary) !important;
}
body.dark-mode .choice-card.selected {
    background-color: var(--color-card-section-bg) !important;
    box-shadow: 0 2px 0 var(--color-heading-secondary) !important; 
}

/* --- NAVIGATION BUTTONS (Gamified Look) --- */
.q-nav-button {
    background-color: var(--color-button-secondary);
    color: var(--color-button-secondary-text);
    box-shadow: 0 3px 0px var(--color-button-secondary-text);
}
.q-nav-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 0px var(--color-button-secondary-text);
}
.q-nav-button.answered {
    background-color: var(--color-green-button);
    color: white;
    box-shadow: 0 3px 0px var(--color-green-button-dark);
}
.q-nav-button.answered:hover {
    box-shadow: 0 4px 0px var(--color-green-button-dark);
}
.q-nav-button.current {
    transform: scale(1.1);
    border: 3px solid var(--color-heading-secondary);
    box-shadow: 0 4px 0px var(--color-heading-secondary) !important;
}

.nav-btn {
    box-shadow: 0 4px 0 var(--color-button-primary-hover);
    transition: all 0.1s;
}
.nav-btn:active {
    transform: translateY(4px);
    box-shadow: none;
}
#nextBtn.submit-btn {
    background-color: var(--color-green-button) !important;
    box-shadow: 0 4px 0 var(--color-green-button-dark);
}
#nextBtn.submit-btn:active {
    transform: translateY(4px);
    box-shadow: none;
}

/* --- MODAL STYLES --- */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.8);
}
.modal-content-frame {
    background-color: var(--color-card-bg);
    border: 4px solid var(--color-heading);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 0 5px var(--color-heading-secondary);
}

</style>
</head>
<body class="min-h-screen flex flex-col" style="background-color: var(--color-main-bg);">

    <header class="backdrop-blur-sm p-4 shadow-lg flex justify-between items-center z-20 sticky top-0" style="background-color: var(--color-header-bg); border-bottom: 2px solid var(--color-heading);">
        <div class="flex items-center space-x-4">
            <img src="../images/isu-logo.png" alt="ISU Logo" class="w-10 h-10 object-contain">
            <h1 class="text-lg font-extrabold font-bungee tracking-wider text-[var(--color-heading)]">
                ISU<span class="text-[var(--color-icon)]">to</span><span class="bg-gradient-to-r bg-clip-text text-transparent from-orange-400 to-yellow-500">Learn</span>
            </h1>
            <span id="headerQuestionCount" class="text-sm font-semibold p-1 px-3 rounded-full" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                Question <?= $index + 1 ?> of <?= $assessment_info['total_items']?>
            </span>
        </div>
        <div class="flex items-center space-x-4">
            <div class="flex items-center rounded-full px-3 py-1 text-sm font-extrabold border-2 hidden" style="background-color: var(--color-card-bg); color: var(--color-text); border-color: var(--color-heading-secondary);">
                <i class="far fa-clock mr-2" style="color: var(--color-heading-secondary);"></i>
                <span id="timer">30:00</span>
            </div>
            <button id="themeToggleBtn" class="py-2 px-3 rounded-full transition-colors duration-200 shadow-md" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                <i class="fas fa-moon"></i>
            </button>
            <a href="topicContent.php?course_id=<?= $course_id ?>&module_id=<?= $module_id ?>&topic_id=<?= $topic_id ?>" 
            class="flex items-center font-bold py-2 px-4 rounded-lg transition-colors nav-btn mr-2"
            style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">
                <i class="fas fa-arrow-left mr-2"></i> Back to Topic
            </a>
        </div>
    </header>

    <div class="flex flex-1 p-8 space-x-8"> 
        <aside class="w-72 rounded-2xl p-6 shadow-2xl flex flex-col justify-between h-[100vh] sticky top-24 border-4" 
            style="background-color: var(--color-sidebar-bg); color: var(--color-text); border-color: var(--color-sidebar-border);">
            <div>
                <h2 class="text-xl font-extrabold mb-4 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-scroll mr-2" style="color: var(--color-heading-secondary);"></i> Quest Map
                </h2>
                <!--<div id="questionNavGrid" class="grid grid-cols-5 gap-3 mb-8">
                    <button class="q-nav-button current w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold">1</button>
                    <button class="q-nav-button w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold">2</button>
                    <button class="q-nav-button answered w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold">3</button>
                    <button class="q-nav-button w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold">4</button>
                    <button class="q-nav-button w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold">5</button>
                </div>-->

                    <div id="questionNavGrid" class="grid grid-cols-5 gap-3 mb-8">
                        <?php
                        for ($i = 0; $i < $assessment_info['total_items']; $i++) {
                            $answered = (isset($_SESSION['topic_answer_details'][$i]['choice_id'])) ? " answered" : "";
                            $current = ($i == $index) ? "current" : "";
                            echo "<button name='map-btn' class='q-nav-button {$current}{$answered} w-full h-8 flex items-center justify-center rounded-lg text-sm font-extrabold' value='{$i}'>". $i + 1 ."</button>";
                        }
                        ?>
                    </div>


                <h2 class="text-lg font-bold mb-3 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-info-circle mr-2" style="color: var(--color-heading-secondary);"></i> Legend
                </h2>
                <div class="space-y-2 text-sm" style="color: var(--color-text-secondary);">
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full mr-2" style="background-color: var(--color-green-button);"></span> Completed
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full border-2 mr-2" style="border-color: var(--color-heading-secondary); background-color: var(--color-heading);"></span> Active Quest
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full mr-2" style="background-color: var(--color-button-secondary);"></span> Unexplored
                    </div>
                </div>
            </div>

            <?php
            $progress = ($_SESSION['answeredCount'] / $assessment_info['total_items']) * 100;
            ?>

            <div class="mt-8 pt-6 border-t-2" style="border-color: var(--color-card-section-border);">
                <h3 class="text-lg font-bold mb-2 flex items-center" style="color: var(--color-heading);">
                    <i class="fas fa-level-up-alt mr-2" style="color: var(--color-heading-secondary);"></i>Progress
                </h3>
                <div class="w-full rounded-full h-3 mb-2 border-2" style="background-color: var(--color-progress-bg); border-color: var(--color-progress-bg);">
                    <div id="progressFill" class="h-full rounded-full transition-all duration-300" style="background: var(--color-progress-fill); width: <?=$progress?>%;"></div>
                </div>
                <p id="progressText" class="text-sm font-semibold" style="color: var(--color-text-secondary);"><?= $_SESSION['answeredCount'] ?> / 10 Quests Completed</p>
            </div>
        </aside>

        <main class="flex-1 backdrop-blur-md rounded-2xl p-8 shadow-2xl flex flex-col relative overflow-hidden border-4" 
            style="background-color: var(--color-card-bg); border-color: var(--color-sidebar-border);">
            <div id="quizContent" class="flex-1 flex flex-col"
            style="background-color: var(--color-card-bg); border-color: var(--color-card-border);">
                    <!-- Top info -->
                <div class="mb-4 p-3 rounded-lg border-l-4"
                    style="background-color: var(--color-card-section-bg); border-color: var(--color-heading);">
                    <span class="text-sm font-bold" style="color: var(--color-text-on-section);">
                        Level: <?= $index + 1 ?> / <?= $assessment_info['total_items'] ?> - Multiple Choice
                    </span>
                </div>

                <?php
                $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = :question_id');
                $stmt->execute([":question_id" => $_SESSION['topic_question_id'][$index]]);
                $question = $stmt->fetch();
                ?>
                <form method='POST' id='assessment_form'>
                    <!-- Question text -->
                    <h3 class="text-2xl font-extrabold mb-8 leading-relaxed" style="color: var(--color-text);">
                        <?= $question['question'] ?>
                    </h3>

                    <!-- Choices -->
                    <div id="choicesContainer" class="choicesContainer space-y-4 flex-1">
                        <?php
                        $stmt = $pdo->prepare('SELECT * FROM choices WHERE question_id = :question_id');
                        $stmt->execute([':question_id' => $question['id']]);
                        $choices = $stmt->fetchAll();

                        $letter = 'A';
                        foreach ($choices as $i => $choice) {
                            $checked = (isset($_SESSION['topic_answer_details'][$index]['choice_id']) && $_SESSION['topic_answer_details'][$index]['choice_id'] == $choice['id']) ? " checked" : "";
                            $selected = (isset($_SESSION['topic_answer_details'][$index]['choice_id']) && $_SESSION['topic_answer_details'][$index]['choice_id'] == $choice['id']) ? " selected" : "";
                            echo "
                            <label class='choice-card p-4 rounded-xl flex items-center group cursor-pointer {$selected}'>
                                <input type='radio' id='{$choice['id']}' name='answers[{$choice['question_id']}]' value='" .$choice['id']."' class='custom-radio mr-4' {$checked}>
                                <span class='text-lg font-semibold' style='color: var(--color-text);'>{$letter}. " . htmlspecialchars($choice['choice']) . "</span>
                            </label>
                            ";
                            $letter++;
                        }
                        ?>
                    </class=>
                    <div class="mt-8 pt-6 border-t-2 flex justify-between items-center" value=style="border-color: var(--color-card-section-border);">
                        <button type="submit" name='action' value='prev' id="prevBtn" class="font-bold py-2 px-5 rounded-lg transition-colors nav-btn <?= (($_SESSION['topic_question_id'][$index] == $min_qid) ? 'opacity-50 cursor-not-allowed' : '') ?>"
                        style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);" <?= (($_SESSION['topic_question_id'][$index] == $min_qid) ? 'disabled' : '') ?>>
                            <i class="fas fa-arrow-left mr-2"></i> Previous Quest
                        </button>
                        <span id="selectAnswerHint" class="text-sm italic font-semibold flex items-center" style="color: var(--color-heading-secondary);">
                            <i class="fas fa-bolt mr-2" style="color: var(--color-heading);"></i> Answer Required
                        </span>
                        <button type="submit" name="action" value="<?= (($_SESSION['topic_question_id'][$index] == $max_qid) ? 'submit_answers' : 'next') ?>" id="nextBtn" class="font-bold py-2 px-5 rounded-lg transition-colors nav-btn" 
                            style="background-color: var(--color-button-primary); color: white;">
                            <?= (($_SESSION['topic_question_id'][$index] == $max_qid) ? 'Submit Answers <i class="fas fa-check-circle ml-2"></i>' : 'Next Quest <i class="fas fa-arrow-right ml-2"></i>') ?>
                        </button>

                        <button type="submit" name="action" value="map_btn" class="hidden" id="map_click"></button>
                        <input type="hidden" name="map_index" id="imap_click" value="">

                        <button type="submit" name="action" value="confirm_submit" class="hidden" id="confirm_submit"></button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div id="submitConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden modal-backdrop">
        <div class="modal-content-frame text-center p-8 rounded-xl w-full max-w-md space-y-6">
            <i class="fas fa-feather-alt text-6xl drop-shadow" style="color: var(--color-heading);"></i>
            
            <h3 class="text-3xl font-extrabold" style="color: var(--color-heading);">Finalize Assessment</h3>
            
            <p class="text-lg font-medium" style="color: var(--color-text);">
                You have answered **all <span id="modalAnsweredCount"><?= $assessment_info['total_items'] ?></span> questions**.
                <br>
                <span class="font-bold text-red-500 mt-2 block">Are you sure you want to submit your test?</span>
                <span class="text-sm italic block" style="color: var(--color-text-secondary);">(You cannot return to edit your answers.)</span>
            </p>

            <div class="flex justify-center space-x-4 pt-4">
                <button id="cancelSubmissionBtn" class="font-bold py-3 px-6 rounded-lg transition-transform nav-btn" 
                    style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text); box-shadow: 0 4px 0 var(--color-button-secondary-text);">
                    <i class="fas fa-arrow-left mr-2"></i> Review Answers
                </button>
                <button type='submit' name='action' value='confirm_submit' id="confirmSubmissionBtn" class="font-bold py-3 px-6 rounded-lg transition-transform nav-btn" onclick="submit_answer()"
                    style="background-color: var(--color-green-button); color: white; box-shadow: 0 4px 0 var(--color-green-button-dark);">
                    <i class="fas fa-check-circle mr-2"></i> Yes, Submit
                </button>
            </div>
        </div>
    </div>

    <div id="notCompletedWarningModal" class="fixed inset-0 flex items-center justify-center z-50 hidden modal-backdrop">
        <div class="modal-content-frame text-center p-8 rounded-xl w-full max-w-sm space-y-6" 
            style="border-color: var(--color-heading-secondary);">
            
            <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 drop-shadow"></i>
            
            <h3 class="text-3xl font-extrabold" style="color: var(--color-heading);">Quest Incomplete!</h3>
            
            <p class="text-lg font-medium" style="color: var(--color-text);">
                You must answer **ALL** questions before you can submit. 
                <br>
                <span id="warningMissingCount" class="font-bold text-red-500 mt-2 block"></span>
            </p>

            <button id="closeWarningBtn" class="font-bold py-3 px-8 rounded-lg transition-transform nav-btn" 
                style="background-color: var(--color-button-primary); color: white;">
                <i class="fas fa-map-marker-alt mr-2"></i> Continue Quest
            </button>
        </div>
    </div>

    <script>
        /*document.getElementById('assessment_form').addEventListener("submit", function(e) {
            let answered = 0;
            const questions = document.querySelectorAll('.choicesContainer'); // use class

            questions.forEach(question => {
                const choices = question.querySelectorAll("input[type='radio']");
                let hasAnswer = false;

                choices.forEach(choice => {
                    if (choice.checked) {
                        hasAnswer = true;
                    }
                });

                if (hasAnswer) {
                    answered++;
                }
            });

            if (answered !== questions.length) {
                let remaining_question = questions.length - answered;
                document.getElementById('notCompletedWarningModal').classList.remove('hidden');
                document.getElementById('warningMissingCount').textContent = `You still have ${remaining_question} questions remaining.`;
                e.preventDefault();
            }
        });*/

        let not_complete = <?php echo json_encode($not_complete ?? false); ?>;
        let remaining    = <?php echo json_encode($remaining ?? 0); ?>;
        let confirmation = <?php echo json_encode($confirmation ?? false); ?>;

        if (not_complete) {
            document.getElementById('notCompletedWarningModal').classList.remove('hidden');
            document.getElementById('warningMissingCount').textContent = `You still have ${remaining} questions remaining.`;
        }

        if (confirmation) {
            document.getElementById('submitConfirmationModal').classList.remove('hidden');
        }

        document.getElementById('closeWarningBtn').addEventListener('click', function(e) {
            document.getElementById('notCompletedWarningModal').classList.add('hidden');
        });

        document.getElementById('cancelSubmissionBtn').addEventListener('click', function(e) {
            document.getElementById('submitConfirmationModal').classList.add('hidden');
        });

        function submit_answer() {
            document.getElementById('confirm_submit').click();
        }

        document.querySelectorAll("input[type=radio]").forEach(radio => {
            radio.addEventListener("change", function () {
                // remove "selected" from all labels
                document.querySelectorAll(".choice-card").forEach(label => {
                    label.classList.remove("selected");
                });
                // add "selected" to the parent label of the clicked radio
                this.closest("label").classList.add("selected");
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".q-nav-button").forEach(btn => {
                btn.addEventListener("click", () => {
                    let val = btn.value;

                    const cval = document.getElementById("imap_click");
                    cval.value = val;

                    const map_btn = document.getElementById("map_click");
                    map_btn.click();
                });
            });
        });
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
    </script>
</body>
</html>