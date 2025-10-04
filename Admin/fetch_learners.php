<?php
require __DIR__ . '/../config.php';

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo json_encode(["error" => "Invalid course ID"]);
    exit;
}

$course_id = (int) $_GET['course_id'];

try {
    $stmt = $conn->prepare("
        SELECT l.id, l.first_name, l.last_name, l.email
        FROM learners l
        INNER JOIN student_courses sc ON l.id = sc.student_id
        WHERE sc.course_id = ?
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
