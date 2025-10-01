<?php
require __DIR__ . '/../config.php';
session_start();

// ✅ Logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// ✅ Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['admin_id'];

// ✅ Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $type  = $_POST['type'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ?, type = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $type, $hashedPassword, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ?, type = ? WHERE id = ?");
        $stmt->execute([$name, $email, $type, $id]);
    }

    header("Location: profile.php?updated=1");
    exit();
}

// ✅ Fetch user details
$stmt = $conn->prepare("SELECT id, name, email, type, created_at FROM admin WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ Invalid user ID.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn - My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        function openEditModal() {
            document.getElementById('editModal').classList.remove('translate-x-full');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('translate-x-full');
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-2xl p-8 transition hover:shadow-2xl">
            <!-- Header -->
            <div class="flex items-center space-x-4 border-b pb-4 mb-6">
                <div class="w-20 h-20 flex items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-3xl font-bold">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($user['name']) ?></h1>
                    <p class="text-gray-500"><?= htmlspecialchars($user['type']) ?></p>
                </div>
            </div>

            <!-- Success Message -->
            <?php if (isset($_GET['updated'])): ?>
                <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 border border-green-300 flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Profile updated successfully!</span>
                </div>
            <?php endif; ?>

            <!-- Profile Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 transition">
                    <label class="block text-sm font-semibold text-gray-600">📧 Email</label>
                    <p class="text-gray-800 mt-1"><?= htmlspecialchars($user['email']) ?></p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 transition">
                    <label class="block text-sm font-semibold text-gray-600">👤 Role</label>
                    <p class="text-gray-800 mt-1"><?= htmlspecialchars($user['type']) ?></p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border sm:col-span-2 hover:bg-gray-100 transition">
                    <label class="block text-sm font-semibold text-gray-600">📅 Member Since</label>
                    <p class="text-gray-800 mt-1"><?= date("F d, Y h:i A", strtotime($user['created_at'])) ?></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex space-x-4">
                <button onclick="openEditModal()" 
                   class="flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                   <i class="fas fa-edit mr-2"></i> Edit Profile
                </button>
                <a href="?action=logout" 
                   class="flex items-center px-5 py-2.5 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition">
                   <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </main>

    <!-- ✅ Side Modal for Editing -->
    <div id="editModal" class="fixed top-0 right-0 w-96 h-full bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <div class="p-6 flex justify-between items-center border-b">
            <h2 class="text-xl font-semibold">Edit Profile</h2>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                       class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                       class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="type" class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    <option value="Admin" <?= $user['type'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Teachers" <?= $user['type'] === 'Teachers' ? 'selected' : '' ?>>Teachers</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password (leave blank if unchanged)</label>
                <input type="password" name="password" 
                       class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
