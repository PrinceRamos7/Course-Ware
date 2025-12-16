<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ADD LEARNER
    if ($action === 'add') {
        $first = $_POST['first_name'];
        $middle = $_POST['middle_name'] ?? '';
        $last = $_POST['last_name'];
        $email = $_POST['email'];
        $contact = $_POST['contact_number'];
        $status = $_POST['status'];
        $password = $_POST['password'];
        $confirm = $_POST['conpass'];

        if ($password !== $confirm) {
            echo "<script>alert('Passwords do not match!'); window.location.href='learners.php';</script>";
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, contact_number, status, password_hash, type, address) 
                VALUES (:first, :middle, :last, :email, :contact, :status, :password, 'learners', '')";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':first' => $first,
            ':middle' => $middle,
            ':last' => $last,
            ':email' => $email,
            ':contact' => $contact,
            ':status' => $status,
            ':password' => $hashed
        ]);

        if ($success) {
            echo "<script>alert('Learner added successfully!'); window.location.href='learners.php';</script>";
        } else {
            echo "<script>alert('Failed to add learner. Try again.'); window.location.href='learners.php';</script>";
        }
    }

    // EDIT LEARNER
    if ($action === 'edit') {
        $id = $_POST['id'];
        $first = $_POST['first_name'];
        $middle = $_POST['middle_name'] ?? '';
        $last = $_POST['last_name'];
        $email = $_POST['email'];
        $contact = $_POST['contact_number'];
        $status = $_POST['status'];
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!empty($password)) {
            if ($password !== $confirm) {
                echo "<script>alert('Passwords do not match!'); window.location.href='learners.php';</script>";
                exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users 
                    SET first_name=:first, middle_name=:middle, last_name=:last, email=:email, 
                        contact_number=:contact, status=:status, password_hash=:password 
                    WHERE id=:id AND type='learners'";
            $params = [
                ':first' => $first,
                ':middle' => $middle,
                ':last' => $last,
                ':email' => $email,
                ':contact' => $contact,
                ':status' => $status,
                ':password' => $hashed,
                ':id' => $id
            ];
        } else {
            $sql = "UPDATE users 
                    SET first_name=:first, middle_name=:middle, last_name=:last, email=:email, 
                        contact_number=:contact, status=:status 
                    WHERE id=:id AND type='learners'";
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
            echo "<script>alert('Learner updated successfully!'); window.location.href='learners.php';</script>";
        } else {
            echo "<script>alert('Failed to update learner. Try again.'); window.location.href='learners.php';</script>";
        }
    }
}

// DELETE LEARNER
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Delete related reports/feedback first to avoid FK constraint
        $stmt1 = $conn->prepare("DELETE FROM reports_feedback WHERE learner_id=:id");
        $stmt1->execute([':id' => $id]);

        // Delete learner
        $stmt2 = $conn->prepare("DELETE FROM users WHERE id=:id AND type='learners'");
        $success = $stmt2->execute([':id' => $id]);

        if ($success) {
            echo "<script>alert('Learner deleted successfully!'); window.location.href='learners.php';</script>";
        } else {
            echo "<script>alert('Failed to delete learner. Try again.'); window.location.href='learners.php';</script>";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo "<script>alert('Cannot delete learner because it is linked to other data.'); window.location.href='learners.php';</script>";
        } else {
            echo "<script>alert('Database error occurred.'); window.location.href='learners.php';</script>";
        }
    }
}
?>
