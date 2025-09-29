<?php
include '../pdoconfig.php';

function count_topics($course_id, $module_id = null) {
    global $pdo;

    if ($module_id === null) {
        $stmt = $pdo->prepare("
            SELECT COUNT(t.id) AS total_topics 
            FROM topics t
            JOIN modules m ON t.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE c.id = :course_id
        ");
        $stmt->execute([":course_id" => $course_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(t.id) AS total_topics 
            FROM topics t
            JOIN modules m ON t.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE m.course_id = :course_id AND m.id = :module_id
        ");
        $stmt->execute([":course_id" => $course_id, ":module_id" => $module_id]);
    }

    $topics = $stmt->fetch();
    return $topics["total_topics"];
}

?>
