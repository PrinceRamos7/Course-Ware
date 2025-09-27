<?php
require __DIR__ . '/../config.php';

// ✅ Add Code
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $code = trim($_POST['code']);
    $course_id = $_POST['course_id'];
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    $sql = "INSERT INTO registration_codes (code, course_id, active, expires_at) 
            VALUES (:code, :course_id, :active, :expires_at)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':code' => $code,
        ':course_id' => $course_id,
        ':active' => $active,
        ':expires_at' => $expires_at
    ]);

    header("Location: code_redeemer.php?msg=added");
    exit;
}

// ✅ Edit Code
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $code = trim($_POST['code']);
    $course_id = $_POST['course_id'];
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    $sql = "UPDATE registration_codes 
            SET code = :code, course_id = :course_id, active = :active, expires_at = :expires_at 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':code' => $code,
        ':course_id' => $course_id,
        ':active' => $active,
        ':expires_at' => $expires_at,
        ':id' => $id
    ]);

    header("Location:code_redeemer.php?msg=updated");
    exit;
}

// ✅ Delete Code
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];

    $sql = "DELETE FROM registration_codes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    header("Location: code_redeemer.php?msg=deleted");
    exit;
}

// 🚨 If no action found
header("Location: code_redeemer.php?msg=invalid");
exit;
