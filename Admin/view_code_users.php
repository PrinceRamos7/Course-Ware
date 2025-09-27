<?php
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid registration code ID.']);
    exit;
}

$code_id = (int) $_GET['id'];

try {
    $sql = "SELECT l.first_name, l.last_name, rcu.used_at
            FROM registration_code_uses rcu
            JOIN learners l ON l.id = rcu.student_id
            WHERE rcu.registration_code_id = :id
            ORDER BY rcu.used_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $code_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
