<?php
require __DIR__ . '/../config.php';

// ✅ Add Learner
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];

    $sql = "INSERT INTO learners (first_name, middle_name, last_name, email, contact_number, status) 
            VALUES (:first_name, :middle_name, :last_name, :email, :contact_number, :status)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'email' => $email,
        'contact_number' => $contact_number,
        'status' => $status
    ]);

    if ($success) {
        ?>
        <script>
            alert("Learner Added Successfully");
            window.location.href="learners.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Add Learner");
            window.location.href="learners.php";
        </script>
        <?php
    }
}

// ✅ Edit Learner
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];

    $sql = "UPDATE learners 
            SET first_name = :first_name, middle_name = :middle_name, last_name = :last_name, 
                email = :email, contact_number = :contact_number, status = :status
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'email' => $email,
        'contact_number' => $contact_number,
        'status' => $status,
        'id' => $id
    ]);

    if ($success) {
        ?>
        <script>
            alert("Learner Updated Successfully");
            window.location.href="learners.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Update Learner");
            window.location.href="learners.php";
        </script>
        <?php
    }
}

// ✅ Delete Learner
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM learners WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute(['id' => $id]);

    if ($success) {
        ?>
        <script>
            alert("Learner Deleted Successfully");
            window.location.href="learners.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Delete Learner");
            window.location.href="learners.php";
        </script>
        <?php
    }
}
?>
