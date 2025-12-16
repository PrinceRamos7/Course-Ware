<?php
require __DIR__ . '/../config.php';

// ✅ Add Topic
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $content     = $_POST['content'];
    $module_id   = (int) $_POST['module_id'];
    $minute      = (int) ($_POST['estimated_minute'] ?? 0);
    $xp          = (int) ($_POST['total_exp'] ?? 0);

    $sql = "INSERT INTO topics (title, description, content, module_id, estimated_minute, total_exp) 
            VALUES (:title, :description, :content, :module_id, :minute, :xp)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'title'       => $title,
        'description' => $description,
        'content'     => $content,
        'module_id'   => $module_id,
        'minute'      => $minute,
        'xp'          => $xp
    ]);

    if ($success) {
        $_SESSION['success'] = 'Topic Added Successfully';
        header("Location: topics.php?module_id=$module_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Add Topic';
        header("Location: topics.php?module_id=$module_id");
        exit;
    }
}

// ✅ Edit Topic
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id          = (int) $_POST['id'];
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $content     = $_POST['content'];
    $module_id   = (int) $_POST['module_id'];
    $minute      = (int) ($_POST['estimated_minute'] ?? 0);
    $xp          = (int) ($_POST['total_exp'] ?? 0);

    $sql = "UPDATE topics 
            SET title = :title, description = :description, content = :content,
                estimated_minute = :minute, total_exp = :xp 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'title'       => $title,
        'description' => $description,
        'content'     => $content,
        'minute'      => $minute,
        'xp'          => $xp,
        'id'          => $id
    ]);

    if ($success) {
        $_SESSION['success'] = 'Topic Updated Successfully';
        header("Location: topics.php?module_id=$module_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Update Topic';
        header("Location: topics.php?module_id=$module_id");
        exit;
    }
}

// ✅ Delete Topic
if (isset($_GET['id'])) {
    $id        = (int) $_GET['id'];
    $module_id = (int) ($_GET['module_id'] ?? 0);

    $sql = "DELETE FROM topics WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute(['id' => $id]);

    if ($success) {
        $_SESSION['success'] = 'Topic Deleted Successfully';
        header("Location: topics.php?module_id=$module_id");
        exit;
    } else {
        $_SESSION['error'] = 'Failed to Delete Topic';
        header("Location: topics.php?module_id=$module_id");
        exit;
    }
}
?>
