<?php
require __DIR__ . '/../config.php';

// ✅ Add assessment
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $time_set = $_POST['time_set'];
    $module_id = $_POST['module_id'];

    $sql = "INSERT INTO assessments (name, type, time_set, module_id) 
            VALUES (:name, :type, :time_set, :module_id)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'name' => $name,
        'type' => $type,
        'time_set' => $time_set,
        'module_id' => $module_id
    ]);

    if ($success) {
        ?>
        <script>
            alert("Assessment Added Successfully");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $_POST['course_id'] ?? '' ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Add Assessment");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $_POST['course_id'] ?? '' ?>";
        </script>
        <?php
    }
}

// ✅ Edit assessment
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $type = $_POST['type'];
    $time_set = $_POST['time_set'];
    $module_id = $_POST['module_id'];

    $sql = "UPDATE assessments 
            SET name = :name, type = :type, time_set = :time_set 
            WHERE id = :id AND module_id = :module_id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'name' => $name,
        'type' => $type,
        'time_set' => $time_set,
        'id' => $id,
        'module_id' => $module_id
    ]);

    if ($success) {
        ?>
        <script>
            alert("Assessment Updated Successfully");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $_POST['course_id'] ?? '' ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Update Assessment");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $_POST['course_id'] ?? '' ?>";
        </script>
        <?php
    }
}

// ✅ Delete assessment
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $module_id = $_GET['module_id'];
    $course_id = $_GET['course_id'] ?? '';

    $sql = "DELETE FROM assessments WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute(['id' => $id]);

    if ($success) {
        ?>
        <script>
            alert("Assessment Deleted Successfully");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $course_id ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Delete Assessment");
            window.location.href="assessment.php?module_id=<?= $module_id ?>&course_id=<?= $course_id ?>";
        </script>
        <?php
    }
}

if ($_POST['action'] === 'edit_question') {
    $id = (int)$_POST['id'];
    $question = trim($_POST['question']);
    $assessment_id = (int)$_POST['assessment_id'];
    $module_id = (int)$_POST['module_id'];
    $course_id = (int)$_POST['course_id'];

    $stmt = $conn->prepare("UPDATE questions SET question = ? WHERE id = ?");
    $stmt->execute([$question, $id]);

    header("Location: questions.php?assessment_id=$assessment_id&module_id=$module_id&course_id=$course_id&success=updated");
    exit;
}

?>
