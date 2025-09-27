<?php
require __DIR__ . '/../config.php';

// ✅ Add User
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $sql = "INSERT INTO admin (name, email, password, type) 
            VALUES (:name, :email, :password, :type)";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'type' => $type
    ]);

    if ($success) {
        ?>
        <script>
            alert("User Added Successfully");
            window.location.href="users.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Add User");
            window.location.href="users.php";
        </script>
        <?php
    }
}

// ✅ Edit User
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    if (!empty($password)) {
        // update with password
        $sql = "UPDATE admin 
                SET name = :name, email = :email, password = :password, type = :type 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'type' => $type,
            'id' => $id
        ]);
    } else {
        // update without password
        $sql = "UPDATE admin 
                SET name = :name, email = :email, type = :type 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'type' => $type,
            'id' => $id
        ]);
    }

    if ($success) {
        ?>
        <script>
            alert("User Updated Successfully");
            window.location.href="users.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Update User");
            window.location.href="users.php";
        </script>
        <?php
    }
}

// ✅ Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM admin WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute(['id' => $id]);

    if ($success) {
        ?>
        <script>
            alert("User Deleted Successfully");
            window.location.href="users.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Failed to Delete User");
            window.location.href="users.php";
        </script>
        <?php
    }
}
?>
