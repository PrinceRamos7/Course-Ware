<?php
session_start();

if (isset($_POST['course_id']) && isset($_POST['elapsed_time']) && isset($_POST['action']) && $_POST['action'] === 'save_time') {
    $course_id = (int)$_POST['course_id'];
    $elapsed_time = (int)$_POST['elapsed_time'];
    
    // Update the start time to reflect the elapsed time
    if (isset($_SESSION['testing_start_time'][$course_id])) {
        // Adjust start time so elapsed time matches
        $_SESSION['testing_start_time'][$course_id] = time() - $elapsed_time;
    }
    
    echo json_encode(['success' => true]);
    exit;
}
?>