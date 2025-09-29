<?php
include '../pdoconfig.php';

function count_total_exp($course_id, $module_id = null)
{
    global $pdo;

    if ($module_id === null) {
        $stmt = $pdo->prepare("SELECT SUM(t.total_exp) AS total_exp FROM topics t
                                JOIN modules m ON t.module_id = m.id
                                JOIN courses c ON m.course_id = c.id
                            WHERE c.id = :course_id");
        $stmt->execute([':course_id' => $course_id]);
        $topics = $stmt->fetch();
        $base_exp = $topics['total_exp'];
        $performance_exp = $base_exp * 0.6;
    } else {
        $stmt = $pdo->prepare("SELECT SUM(t.total_exp) AS total_exp FROM topics t
                                JOIN modules m ON t.module_id = m.id
                                JOIN courses c ON m.course_id = c.id
                            WHERE m.course_id = :course_id AND m.id = :module_id");
        $stmt->execute([':course_id' => $course_id, ':module_id' => $module_id]);
        $topics = $stmt->fetch();
        $base_exp = $topics['total_exp'];
        $performance_exp = $base_exp * 0.6;
    }

    return [$base_exp, $performance_exp];
}
?>