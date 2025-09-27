<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ADD MODULE
    if ($action === 'add') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $course_id = $_POST['course_id'];
        $required_score = $_POST['required_score'];

        $sql = "INSERT INTO modules (title, description, course_id, required_score) 
                VALUES (:title, :description, :course_id, :required_score)";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':course_id' => $course_id,
            ':required_score' => $required_score,
        ]);

      if ($success) {
    echo "<script>
        alert('Module added successfully!');
        window.location.href='module.php?course_id=" . $course_id . "';
    </script>";
} else {
    echo "<script>
        alert('Failed to add module. Try again.');
        window.location.href='module.php?course_id=" . $course_id . "';
    </script>";
}

    }

    // EDIT MODULE
    if ($action === 'edit') {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $course_id = $_POST['course_id'];
        $required_score = $_POST['required_score'];

        $sql = "UPDATE modules 
                SET title = :title, description = :description, course_id = :course_id, required_score = :required_score 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':course_id' => $course_id,
            ':required_score' => $required_score,
            ':id' => $id,
        ]);

        if ($success) {
            echo "<script>alert('Module updated successfully!'); window.location.href='module.php';</script>";
        } else {
            echo "<script>alert('Failed to update module. Try again.'); window.location.href='module.php';</script>";
        }
    }
}

// DELETE MODULE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM modules WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([':id' => $id]);

    if ($success) {
        echo "<script>alert('Module deleted successfully!'); window.location.href='module.php';</script>";
    } else {
        echo "<script>alert('Failed to delete module. Try again.'); window.location.href='module.php';</script>";
    }
}
?>
