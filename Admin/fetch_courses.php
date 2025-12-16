<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');

// ✅ Check if learner ID is passed
if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No learner ID provided"]);
    exit;
}

$learner_id = (int)$_GET['id'];
if ($learner_id <= 0) {
    echo json_encode(["error" => "Invalid learner ID"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT c.title AS course_title, c.description, sc.enrolled_at
        FROM student_courses sc
        JOIN courses c ON sc.course_id = c.id
        JOIN users u ON sc.student_id = u.id
        WHERE sc.student_id = ? AND u.type = 'learners'
        ORDER BY sc.enrolled_at DESC
    ");
    $stmt->execute([$learner_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($courses);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
