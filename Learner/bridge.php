<?php
require_once '../pdoconfig.php';

if (isset($_GET['location']) && $_GET['location'] === 'training_assessment') {
    unset($_SESSION['training_progress']);
    unset($_SESSION['original_questions']);
    unset($_SESSION['shuffled_questions_order']);
    $course_id = $_GET['course_id'];

    header("Location: training_assessment_mode.php?course_id={$course_id}");
    exit();
}
?>
