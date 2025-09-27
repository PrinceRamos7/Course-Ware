<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_GET['course_id'])) {
    echo json_encode([]);
    exit;
}

$course_id = (int)$_GET['course_id'];

try {
    // Fetch students enrolled in this course using student_courses table
    $stmt = $conn->prepare("
        SELECT s.first_name, s.last_name, sc.enrolled_at
        FROM students s
        INNER JOIN student_courses sc ON s.id = sc.student_id
        WHERE sc.course_id = :course_id
        ORDER BY s.last_name, s.first_name
    ");
    $stmt->execute(['course_id' => $course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);
} catch (Exception $e) {
    echo json_encode([]);
}
