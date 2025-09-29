<?php
include '../config.php';

function count_progress_percentage($course_id, $module_id = null)
{
    global $pdo;

    if ($module_id === null) {
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = :course_id");
        $stmt->execute([':course_id' => $course_id]);
        $modules = $stmt->fetchAll();

        $total_modules = 0;
        $sum_percentage = 0.0;
        foreach ($modules as $module) {
            $stmt = $pdo->prepare("SELECT COUNT(t.id) AS total_topics FROM topics t WHERE t.module_id = :module_id");
            $stmt->execute(['module_id' => $module['id']]);
            $topics = $stmt->fetch();

            $stmt = $pdo->prepare("SELECT COUNT(tc.id) AS completed_topics FROM topics_completed tc
                        JOIN topics t ON tc.topic_id = t.id
                    WHERE tc.student_id = :student_id AND t.module_id = :module_id");
            $stmt->execute([":student_id" => $_SESSION['student_id'], ":module_id" => $module['id']]);
            $t = $stmt->fetch();

            if ($t['completed_topics'] > 0) { 
                $progress_percentage = ($t['completed_topics'] / $topics['total_topics']) * 100;
            } else {
                $progress_percentage = 0;
            }

            $sum_percentage += $progress_percentage;
            $total_modules++;
        }

        if ($total_modules === 0) {
            return 0;
        }

        $progress_percentage = ($sum_percentage / $total_modules);
        return round($progress_percentage, 2);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(t.id) AS total_topics FROM topics t WHERE t.module_id = :module_id");
        $stmt->execute(['module_id' => $module_id]);
        $topics = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT COUNT(tc.id) AS completed_topics FROM topics_completed tc
                JOIN topics t ON tc.topic_id = t.id
            WHERE tc.student_id = :student_id AND t.module_id = :module_id");
        $stmt->execute([":student_id" => $_SESSION['student_id'], ":module_id" => $module_id]);
        $t = $stmt->fetch();

        if ($t['completed_topics'] > 0) { 
            $progress_percentage = ($t['completed_topics'] / $topics['total_topics']) * 100;
        } else {
            $progress_percentage = 0;
        }

        if ($topics['total_topics'] == 0) {
            return 0;
        }

        return round($progress_percentage, 2);
    }
}
?>
