<?php
require __DIR__ . '/../config.php';

// ✅ Add Code
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $code = trim($_POST['code']);
    $course_id = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    $active = isset($_POST['active']) && $_POST['active'] == '1' ? 1 : 0;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    // Validate course_id
    if ($course_id === null || $course_id <= 0) {
        $_SESSION['error'] = 'Please select a valid course';
        header("Location: code_redeemer.php");
        exit;
    }

    // Verify course exists
    $check_course = $conn->prepare("SELECT id FROM courses WHERE id = ?");
    $check_course->execute([$course_id]);
    if (!$check_course->fetch()) {
        $_SESSION['error'] = 'Selected course does not exist. Please select a valid course.';
        header("Location: code_redeemer.php");
        exit;
    }

    try {
        $sql = "INSERT INTO registration_codes (code, course_id, active, expires_at) 
                VALUES (:code, :course_id, :active, :expires_at)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':code' => $code,
            ':course_id' => $course_id,
            ':active' => $active,
            ':expires_at' => $expires_at
        ]);

        $_SESSION['success'] = 'Registration code added successfully';
        header("Location: code_redeemer.php?msg=added");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header("Location: code_redeemer.php");
        exit;
    }
}

// ✅ Edit Code
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $code = trim($_POST['code']);
    $course_id = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    // Validate course_id
    if ($course_id === null || $course_id <= 0) {
        $_SESSION['error'] = 'Please select a valid course';
        header("Location: code_redeemer.php");
        exit;
    }

    try {
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

        $_SESSION['success'] = 'Registration code updated successfully';
        header("Location: code_redeemer.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to update code: Invalid course selected';
        header("Location: code_redeemer.php");
        exit;
    }
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
