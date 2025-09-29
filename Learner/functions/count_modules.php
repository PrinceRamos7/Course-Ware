<?php
include 'completed_info.php';

function count_modules($course_id) {
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM modules WHERE course_id = :course_id');
    $stmt->execute([':course_id' => $course_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_modules = 0;
    $completed_modules = 0;
    foreach ($modules as $module) {
        $completed = get_completed_info($module['id']);
        if ($completed) {
            $completed_modules++;
        }
        $total_modules++;
    }
    
    return [
        'total_modules' => $total_modules, 
        'completed_modules' => $completed_modules
    ];
}
?>
