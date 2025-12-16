<?php
require __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Update Account Information
if (isset($_POST['action']) && $_POST['action'] === 'update_account') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact'] ?? '');

    // Check if email is already used by another user
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $admin_id]);
    
    if ($check->fetch()) {
        $_SESSION['error'] = 'Email is already in use by another account';
        header("Location: settings.php");
        exit;
    }

    $sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$first_name, $middle_name, $last_name, $email, $contact, $admin_id])) {
        $_SESSION['success'] = 'Account information updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update account information';
    }
    
    header("Location: settings.php");
    exit;
}

// Change Password
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = 'New password must be at least 8 characters long';
        header("Location: settings.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New passwords do not match';
        header("Location: settings.php");
        exit;
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = 'Current password is incorrect';
        header("Location: settings.php");
        exit;
    }

    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    if ($update->execute([$hashed_password, $admin_id])) {
        $_SESSION['success'] = 'Password changed successfully';
    } else {
        $_SESSION['error'] = 'Failed to change password';
    }
    
    header("Location: settings.php");
    exit;
}

// Fallback
$_SESSION['error'] = 'Invalid request';
header("Location: settings.php");
exit;
