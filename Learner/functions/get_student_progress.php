<?php
include '../pdoconfig.php';
include 'calculate_level.php';
include_once 'count_total_exp.php';

$stmt = $pdo->prepare("SELECT id, experience, intelligent_exp FROM users WHERE id = :student_id");
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$users = $stmt->fetch();

$stmt = $pdo->prepare(
        "SELECT rc.course_id FROM registration_code_uses rcu
            JOIN registration_codes rc ON rcu.registration_code_id = rc.id
        WHERE rcu.student_id = :student_id");
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$registration_code_uses = $stmt->fetch();

if ($registration_code_uses) {
    $course_id = $registration_code_uses['course_id'];

    $exp = count_total_exp($course_id);
    $user_exp = $exp[0];
    $intelligent_exp = $exp[1];

    $user_level = getUserLevel($users['experience'], $user_exp, 10);
    $intelligent_level = getUserLevel($users['intelligent_exp'], $intelligent_exp, 10);

    $user_exp = $user_level[2];
    $user_lvl = $user_level[0];
    $next_goal_exp = $user_level[3];
    $progress = number_format($user_level[1], 2);

    $intelligent_exp = $intelligent_level[2];
    $intelligent_lvl = $intelligent_level[0];
    $next_goal_intelligent_exp = $intelligent_level[3];
    $intelligent_progress = number_format($intelligent_level[1], 2);
}
?>