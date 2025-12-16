<?php
require __DIR__ . '/../config.php';

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo json_encode(["error" => "Invalid course ID"]);
    exit;
}

$course_id = (int) $_GET['course_id'];

try {
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email
        FROM users u
        INNER JOIN student_courses sc ON u.id = sc.student_id
        WHERE sc.course_id = ? AND u.type = 'learners'
    ");
    $stmt->execute([$course_id]);
    $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(empty($learners)
        ? ["message" => "No students enrolled in this course yet."]
        : $learners
    );

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
