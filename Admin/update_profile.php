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

// --- Collect form data safely ---
$name           = trim($_POST['name'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = $_POST['password'] ?? '';
$new_password   = $_POST['new_password'] ?? '';
$match_password = $_POST['match_password'] ?? '';

// --- Validate required fields ---
if (empty($name) || empty($email)) {
    $_SESSION['error'] = "Name and Email cannot be empty.";
    header("Location: profile.php");
    exit;
}

// --- Get current admin data ---
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: profile.php");
    exit;
}

// --- Case 1: Only name/email update ---
if (empty($password) && empty($new_password) && empty($match_password)) {
    $update = $conn->prepare("UPDATE admins SET name = :name, email = :email WHERE id = :id");
    $update->execute([':name' => $name, ':email' => $email, ':id' => $admin_id]);

    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: profile.php");
    exit;
}

// --- Case 2: Password update included ---
if (!empty($password) || !empty($new_password) || !empty($match_password)) {

    // 1️⃣ Verify current password
    if (!password_verify($password, $admin['password'])) {
        $_SESSION['error'] = "Current password is incorrect.";
        header("Location: profile.php");
        exit;
    }

    // 2️⃣ Check new passwords match
    if ($new_password !== $match_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header("Location: profile.php");
        exit;
    }

    // 3️⃣ Hash and update new password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE admins SET name = :name, email = :email, password = :password WHERE id = :id");
    $update->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hashed,
        ':id'       => $admin_id
    ]);

    $_SESSION['success'] = "Profile and password updated successfully.";
    header("Location: profile.php");
    exit;
}

$_SESSION['error'] = "Unexpected error occurred.";
header("Location: profile.php");
exit;
?>
