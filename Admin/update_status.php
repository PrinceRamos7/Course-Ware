<?php
require __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Debug flag (set to false in production)
$DEBUG = true;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Fetch & validate
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
    exit;
}

$allowed_statuses = ['Pending', 'In Review', 'Resolved', 'Closed'];
if (!in_array($status, $allowed_statuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Safety: ensure $conn exists (your config.php should create PDO as $conn)
if (!isset($conn) || !$conn) {
    error_log("update_status.php: DB connection not found");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $DEBUG ? 'DB connection not found' : 'Internal server error']);
    exit;
}

try {
    // Ensure PDO throws exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("UPDATE reports_feedback SET status = :status, updated_at = NOW() WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);

    $rows = $stmt->rowCount();
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'rows_affected' => $rows
    ]);
} catch (PDOException $e) {
    // Log full details to server log
    error_log("update_status.php PDOException: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $DEBUG ? 'Database error: ' . $e->getMessage() : 'Database error'
    ]);
    exit;
}
