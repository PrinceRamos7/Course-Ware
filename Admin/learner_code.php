<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ ADD LEARNER
    if ($action === 'add') {
        $first = $_POST['first_name'];
        $middle = $_POST['middle_name'] ?? '';
        $last = $_POST['last_name'];
        $email = $_POST['email'];
        $contact = $_POST['contact_number'];
        $status = $_POST['status'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

      $sql = "INSERT INTO learners (first_name, middle_name, last_name, email, contact_number, status, password_hash) 
        VALUES (:first, :middle, :last, :email, :contact, :status, :password)";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([
    ':first' => $first,
    ':middle' => $middle,
    ':last' => $last,
    ':email' => $email,
    ':contact' => $contact,
    ':status' => $status,
    ':password' => $password
]);
    }
//hi
        if ($success) {
            header("Location: learners.php?msg=added");
            exit;
        } else {
            header("Location: learners.php?msg=error");
            exit;
        }
    }
// ✅ EDIT LEARNER
if ($action === 'edit') {
    $id = $_POST['id'];
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'] ?? '';
    $last = $_POST['last_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_number'];
    $status = $_POST['status'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // If password fields are provided, check if they match
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            // Redirect back if mismatch
            header("Location: learners.php?msg=password_mismatch");
            exit;
        }

        // Hash the password
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE learners 
                SET first_name=:first, middle_name=:middle, last_name=:last, email=:email, 
                    contact_number=:contact, status=:status, password=:password 
                WHERE id=:id";
        $params = [
            ':first' => $first,
            ':middle' => $middle,
            ':last' => $last,
            ':email' => $email,
            ':contact' => $contact,
            ':status' => $status,
            ':password' => $password_hashed,
            ':id' => $id
        ];
    } else {
        // Update without password
        $sql = "UPDATE learners 
                SET first_name=:first, middle_name=:middle, last_name=:last, email=:email, 
                    contact_number=:contact, status=:status 
                WHERE id=:id";
        $params = [
            ':first' => $first,
            ':middle' => $middle,
            ':last' => $last,
            ':email' => $email,
            ':contact' => $contact,
            ':status' => $status,
            ':id' => $id
        ];
    }

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute($params);

    if ($success) {
        header("Location: learners.php?msg=updated");
        exit;
    } else {
        header("Location: learners.php?msg=error");
        exit;
    }
}

// ✅ DELETE LEARNER (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'] ?? 0;

    if ($id) {
        $sql = "DELETE FROM learners WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([':id' => $id]);

        if ($success) {
            header("Location: learners.php?msg=deleted");
            exit;
        } else {
            header("Location: learners.php?msg=error");
            exit;
        }
    }
}
