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

    $course_id = $_POST['course_id'] ?? 0;
    
    if ($success) {
        $_SESSION['success'] = 'Assessment Added Successfully';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Add Assessment';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
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

    $course_id = $_POST['course_id'] ?? 0;
    
    if ($success) {
        $_SESSION['success'] = 'Assessment Updated Successfully';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Update Assessment';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
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
        $_SESSION['success'] = 'Assessment Deleted Successfully';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Delete Assessment';
        header("Location: assessment.php?module_id=$module_id&course_id=$course_id");
        exit;
    }
}

// ✅ Add question
if (isset($_POST['action']) && $_POST['action'] === 'add_question') {
    $question = trim($_POST['question']);
    $assessment_id = (int)$_POST['select_assessment_id'];
    $topic_id = !empty($_POST['select_topic_id']) ? (int)$_POST['select_topic_id'] : null;
    $module_id = (int)$_POST['module_id'];
    $course_id = (int)$_POST['course_id'];

    try {
        // Insert with or without topic_id based on whether it's provided
        if ($topic_id !== null && $topic_id > 0) {
            $sql = "INSERT INTO questions (question, assessment_id, topic_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([$question, $assessment_id, $topic_id]);
        } else {
            $sql = "INSERT INTO questions (question, assessment_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([$question, $assessment_id]);
        }

        if ($success) {
            $_SESSION['success'] = 'Question Added Successfully';
            header("Location: questions.php?assessment_id=$assessment_id&module_id=$module_id&course_id=$course_id");
            exit;
        } else {
            $_SESSION['error'] = 'Failed to Add Question';
            header("Location: questions.php?assessment_id=$assessment_id&module_id=$module_id&course_id=$course_id");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database Error: Invalid topic or assessment selected';
        header("Location: questions.php?assessment_id=$assessment_id&module_id=$module_id&course_id=$course_id");
        exit;
    }
}

// ✅ Edit question
if (isset($_POST['action']) && $_POST['action'] === 'edit_question') {
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

// Fallback: redirect to module page if accessed directly
$_SESSION['error'] = 'Invalid request';
header("Location: module.php");
exit;
?>
