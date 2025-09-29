<?php
include '../pdoconfig.php';

function count_estimated_time($course_id, $module_id = null)
{
    global $pdo;

    if ($module_id === null) {
        $stmt = $pdo->prepare("SELECT SUM(t.estimated_minute) AS total_minutes FROM topics t
                                JOIN modules m ON t.module_id = m.id
                                JOIN courses c ON m.course_id = c.id
                            WHERE c.id = :course_id");
        $stmt->execute([':course_id' => $course_id]);
        $topics = $stmt->fetch();
        $total_minutes = $topics['total_minutes'];
    } else {
        $stmt = $pdo->prepare("SELECT SUM(t.estimated_minute) AS total_minutes FROM topics t
                                JOIN modules m ON t.module_id = m.id
                                JOIN courses c ON m.course_id = c.id
                            WHERE m.course_id = :course_id AND m.id = :module_id");
        $stmt->execute([':course_id' => $course_id, ':module_id' => $module_id]);
        $topics = $stmt->fetch();
        $total_minutes = $topics['total_minutes'];
    }

    return $total_minutes;
}

function count_time_left($time_remaining) {
    $minutes = floor($time_remaining / 60);
    $seconds = $time_remaining % 60;

    if ($minutes > 0) {
        return $minutes . ' minutes ' . $seconds . ' seconds';
    } else {
        return $seconds . ' seconds';
    }
}
