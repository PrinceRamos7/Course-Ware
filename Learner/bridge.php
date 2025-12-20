<?php
require_once '../pdoconfig.php';

if (isset($_GET['location']) && $_GET['location'] === 'training_assessment') {
    unset($_SESSION['training_progress']);
    unset($_SESSION['topics_id']);
    unset($_SESSION['adaptive_current_topic_index']);
    unset($_SESSION['topic_index']);
    unset($_SESSION['mastery_each_topic']);
    unset($_SESSION['answer_result_tracker']);
    unset($_SESSION['adaptive_question_history']);
    unset($_SESSION['adaptive_questions_by_topic']);
    unset($_SESSION['adaptive_question_index_by_topic']);
    unset($_SESSION['adaptive_answered_by_topic']);

    header("Location: training_assessment_mode.php?course_id={$_GET['course_id']}");
    exit();
}

if (isset($_GET['location']) && $_GET['location'] === 'moduleassessment') {
    $course_id = $_GET['course_id'];
    unset($_SESSION['total_answered']);
    unset($_SESSION['quiz_end_time']);
    unset($_SESSION['total_questions']);
    unset($_SESSION['quiz_answer_info']);

    header("Location: assessmentModule.php?course_id={$course_id}&module_id={$_GET['module_id']}");
    exit();
}    
?>
