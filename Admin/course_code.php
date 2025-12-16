<?php
require __DIR__ . '/../config.php';

// ✅ Add course (handles both btn_add and action=add)
if (isset($_POST['btn_add']) || (isset($_POST['action']) && $_POST['action'] === 'add')) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "INSERT INTO courses (title, description) VALUES (:title, :description)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'description' => $description
    ]);

    if ($stmt) {
        // Check if request came from dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Course Added Successfully");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Failed to Add Course");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    }
}

// ✅ Edit course (handles both btn_edit and action=edit)
if (isset($_POST['btn_edit']) || (isset($_POST['action']) && $_POST['action'] === 'edit')) {
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
        // Check if request came from dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Course Updated Successfully");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Failed to Update Course");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    }
}

// ✅ Delete course
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];

    $sql = "DELETE FROM courses WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    if ($stmt) {
        // Check if request came from dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Course Deleted Successfully");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $redirect = (strpos($referer, 'dashboard.php') !== false) ? 'dashboard.php' : 'course.php';
        ?>
        <script>
            alert("Failed to Delete Course");
            window.location.href="<?= $redirect ?>";
        </script>
        <?php
    }
}
?>
