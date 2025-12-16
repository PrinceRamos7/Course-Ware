<?php
session_start();
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ REGISTER
    if ($action === 'register') {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Validation
        if ($password !== $confirm) {
            echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters."]);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (first_name, last_name, email, password_hash, type, address, contact_number, status) 
                    VALUES (:first_name, '', :email, :password, 'admin', '', '', 'active')";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([
                ':first_name' => $name,
                ':email'      => $email,
                ':password'   => $hashedPassword
            ]);

            if ($success) {
                echo json_encode(["status" => "success", "message" => "Account created successfully!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Registration failed."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
        }
        exit;
    }

    // ✅ LOGIN
    if ($action === 'login') {
        $email    = trim($_POST['email']);
        $password = $_POST['password'] ?? '';

        $sql = "SELECT id, first_name, password_hash FROM users WHERE email = :email AND type = 'admin' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['first_name'];
            echo json_encode(["status" => "success", "message" => "Login successful!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
        }
        exit;
    }
}

// ❌ If no valid action
echo json_encode(["status" => "error", "message" => "Invalid request."]);
exit;
