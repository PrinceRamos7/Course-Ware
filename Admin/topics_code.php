<?php
require __DIR__ . '/../config.php';

// ✅ Add Topic
if (isset($_POST['btn_add'])) {
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
        ?>
        <script>
            alert("Topic Added Successfully");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Add Topic");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
    }
}

// ✅ Edit Topic
if (isset($_POST['btn_edit'])) {
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
        ?>
        <script>
            alert("Topic Updated Successfully");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Update Topic");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
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
        ?>
        <script>
            alert("Topic Deleted Successfully");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Delete Topic");
            window.location.href="topics.php?module_id=<?= $module_id ?>";
        </script>
        <?php
    }
}
?>
