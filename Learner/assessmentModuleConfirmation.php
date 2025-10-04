<?php
include 'functions/get_student_progress.php';
include_once 'functions/count_total_exp.php';
$_SESSION['total_answered'] = null;

if (isset($_GET['course_id']) && isset($_GET['module_id']) && isset($_GET['assessment_id'])) {
    $course_id = (int) $_GET['course_id'];
    $module_id = (int) $_GET['module_id'];
    $assessment_id = (int) $_GET['assessment_id'];
}
$question_id = $_SESSION['last_question_id'];

$stmt = $pdo->prepare("SELECT * FROM assessments WHERE type = 'module' AND module_id = :module_id");
$stmt->execute([':module_id' => $module_id]);
$module_assessment = $stmt->fetch();
$module_assessment_id = $module_assessment['id'];

$stmt = $pdo->prepare('SELECT q.*, t.title FROM questions q
            JOIN topics t ON q.topic_id = t.id
        WHERE q.assessment_id = :module_assessment_id',);
$stmt->execute([':module_assessment_id' => $module_assessment_id]);
$questions = $stmt->fetchAll();
$total_questions = count($questions);

$exp = count_total_exp($course_id, $module_id);
$total_exp = $exp[0];
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

        .main-header {
            padding: 0.75rem 1.5rem; 
            background-color: var(--color-header-bg); 
            border-bottom: 3px solid var(--color-heading); 
            backdrop-filter: blur(5px);
        }

        main {
            padding: 2rem 1.5rem; 
            flex-grow: 1; 
        }

        .lesson-frame {
            border: 4px solid var(--color-heading); 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15), 0 0 0 5px var(--color-xp-bg); 
            height: 100%;
            min-height: 65vh;
            background-color: var(--color-card-bg); 
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .question-card {
            background-color: var(--color-user-bg); 
            border: 2px solid var(--color-heading); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            position: relative; 
            width: 100%;
            max-width: 700px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        .quiz-option {
            background-color: var(--color-card-bg); 
            border: 2px solid var(--color-card-border); 
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--color-text);
            box-shadow: 0 3px 0 var(--color-card-border); 
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
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

        #next-button, #prev-button {
            transition: background-color 0.2s, transform 0.1s;
            background-color: var(--color-button-primary);
            color: white;
            box-shadow: 0 4px 0 var(--color-button-primary-hover);
            border-radius: 9999px;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
        }
        #next-button:hover:not(:disabled), #prev-button:hover:not(:disabled) {
            background-color: var(--color-button-primary-hover);
            transform: translateY(1px);
            box-shadow: 0 2px 0 var(--color-button-primary-hover);
        }

        #submit-link {
            background-color: var(--color-heading) !important;
            box-shadow: 0 4px 0 #14532d !important;
            padding: 1rem 2rem;
        }
        #submit-link:hover {
            background-color: #14532d !important;
            transform: translateY(2px);
            box-shadow: 0 2px 0 #14532d !important;
        }

        .xp-display {
            background-color: var(--color-xp-bg); 
            color: var(--color-xp-text); 
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }

        #progress-bar-fill {
            background: var(--color-progress-fill);
            border-radius: 9999px;
            height: 100%;
            transition: width 0.5s ease-out;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .point-badge {
            position: absolute;
            top: -12px; 
            right: 12px;
            background-color: var(--color-heading-secondary); 
            color: white;
            padding: 0.5rem 1rem;
            box-shadow: 0 4px 0 #c2410c; 
            border: 2px solid white;
            animation: pulse-shadow 2s infinite alternate;
            font-size: 0.9rem;
        }

        @keyframes pulse-shadow {
            0% { box-shadow: 0 4px 0 #c2410c; }
            100% { box-shadow: 0 4px 0 #fdba74, 0 0 10px #fed7aa; }
        }

        #carousel-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .carousel-item {
            width: 100%;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans">
    <?php
    $progress = ($_SESSION['answeredCount'] / $total_questions) * 100;
    ?>

    <header class="main-header shadow-xl px-8 flex justify-between items-center sticky top-0 z-10">
        <div class="flex flex-col">
            <h1 class="text-2xl font-extrabold" style="color: var(--color-heading);">🎯 <?= $module_assessment['name'] ?></h1>
            <h6 class="text-xs font-bold" style="color: var(--color-text-secondary);">Module Assessment: Focused Review</h6>
        </div>

        <div id="total-points-display" class="xp-display flex items-center">
            <i class="fas fa-coins mr-2"></i> Total XP Points: <span class="ml-1 font-extrabold" id="quiz-total-points"><?= $_SESSION['gainedExp'] ?> / <?=$total_exp?></span>
        </div> 
    </header>

    <main class="max-w-6xl mx-auto flex-1 flex flex-col w-full min-h-full"> 
        <div class="mb-4 text-center">
            <h1 class="text-4xl font-extrabold mb-2" style="color: var(--color-heading);">Module Assessment</h1>
            <h2 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Section: Confirmation</h2>
        </div>
        
        <div class="w-full h-4 mb-6 rounded-full border-2 border-green-700">
            <div id="progress-bar-fill" style="width: <?=$progress?>%;"></div>
        </div>

        <div class="lesson-frame flex-1 flex flex-col rounded-xl shadow-2xl">
            <div id="assessment-carousel" class="relative overflow-hidden flex-1 flex flex-col">
                <div id="carousel-inner" class="flex transition-transform duration-500 h-full" >
                    <div class="carousel-item min-w-full p-1 h-full flex flex-col justify-center items-center">
                        <div class="question-card p-6 text-center">
                            <i class="fas fa-scroll text-6xl mb-4" style="color: var(--color-heading);"></i>
                            <h3 class="text-3xl font-extrabold mb-3" style="color: var(--color-heading);">Quest Log Complete!</h3>
                            <p class="text-lg leading-relaxed font-bold mb-3" style="color: var(--color-heading-secondary);">
                                You've faced all <?= $total_questions ?> challenges.
                            </p>
                            <p class="text-base leading-relaxed" style="color: var(--color-text);">
                                Proceed to the **Finish & Continue** button to finalize your submission and claim your rewards!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-4 p-3 border-t" style="border-color: var(--color-card-border);">
                
                <button type="button" id="prev-button" class="transition font-semibold flex items-center bg-green-600 text-white shadow-md hover:bg-green-700" onclick="window.location.href='assessmentModule.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&question_id=<?=$question_id?>'">
                    <i class="fas fa-arrow-left mr-2"></i> Previous Quest
                </button>

                <div id="progress-text" class="text-sm font-semibold" style="color: var(--color-text-secondary);">
                    Assessment Complete - Ready for Submission!
                </div>

                <!--assessmentModuleResult.php?course_id=<?=$course_id?>&module_id=<?=$module_id?>&assessment_id=<?=$module_assessment_id?>-->
                <a href="#" id="submit-link" class="rounded-full transition font-extrabold text-lg flex items-center justify-center" 
                style="color: white; text-decoration: none; background-color: var(--color-heading) !important; box-shadow: 0 4px 0 #14532d !important; padding: 1rem 2rem;"
                    aria-label="Finish and Continue to Submit Assessment">
                    <i class="fas fa-gavel mr-3"></i> Finish & Continue
                </a>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>
