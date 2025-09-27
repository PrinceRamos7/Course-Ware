<?php
require __DIR__ . '/../config.php';

// ✅ Add course
if (isset($_POST['btn_add'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "INSERT INTO courses (title, description) VALUES (:title, :description)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'description' => $description
    ]);

    if ($stmt) {
        ?>
        <script>
            alert("Course Added Successfully");
            window.location.href="course.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Add Course");
            window.location.href="course.php";
        </script>
        <?php
    }
}

// ✅ Edit course
if (isset($_POST['btn_edit'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "UPDATE courses SET title = :title, description = :description WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'description' => $description,
        'id' => $id
    ]);

    if ($stmt) {
        ?>
        <script>
            alert("Course Updated Successfully");
            window.location.href="course.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Update Course");
            window.location.href="course.php";
        </script>
        <?php
    }
}

// ✅ Delete course
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM courses WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    if ($stmt) {
        ?>
        <script>
            alert("Course Deleted Successfully");
            window.location.href="course.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Delete Course");
            window.location.href="course.php";
        </script>
        <?php
    }
}
?>
