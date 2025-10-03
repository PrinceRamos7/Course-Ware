<?php
require __DIR__ . '/../config.php';

// ✅ Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Check login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// ✅ Fetch admin data
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM admins WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ If no admin found, fallback
if (!$admin || !is_array($admin)) {
    $admin = [
        'name'       => 'Unknown User',
        'email'      => 'N/A',
        'created_at' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - ISUtoLearn</title>
  <link rel="stylesheet" href="../output.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      background-color: var(--color-main-bg, #f9fafb);
      font-family: Arial, sans-serif;
    }
    .fade-in { opacity: 0; transform: translateY(20px); transition: all 0.5s ease-in-out; }
    .fade-in.visible { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body class="min-h-screen flex">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content-wrapper flex-grow flex flex-col">
  <?php include "header.php"; renderHeader("My Profile"); ?>

  <main class="flex-1 p-10">
    <div class="max-w-3xl mx-auto">

      <!-- Profile Card -->
      <div class="max-w-2xl mx-auto mt-12 bg-white rounded-2xl shadow-xl overflow-hidden fade-in relative">
        <!-- Banner -->
        <div class="h-36 bg-gradient-to-r from-green-700 to-green-400"></div>

        <!-- Profile Info -->
        <div class="px-6 pb-10 relative">
          <!-- Avatar Circle -->
          <div class="absolute -top-16 left-1/2 transform -translate-x-1/2 w-32 h-32 rounded-full bg-white shadow-lg border-4 border-white flex items-center justify-center overflow-hidden">
            <span class="text-5xl font-bold text-green-600">
              <?= htmlspecialchars(strtoupper(substr($admin['name'], 0, 1))) ?>
            </span>
          </div>

          <!-- User Info -->
          <div class="mt-20 text-center">
            <h1 class="text-3xl font-semibold text-gray-800">
              <?= htmlspecialchars($admin['name']) ?>
            </h1>
            <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($admin['email']) ?></p>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
            <div class="p-4 bg-gray-50 rounded-lg shadow-sm border text-center">
              <p class="text-sm text-gray-500 font-medium">📧 Email</p>
              <p class="text-gray-800 font-semibold mt-1"><?= htmlspecialchars($admin['email']) ?></p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg shadow-sm border text-center">
              <p class="text-sm text-gray-500 font-medium">📅 Member Since</p>
              <p class="text-gray-800 font-semibold mt-1">
                <?= $admin['created_at'] ? date("F d, Y", strtotime($admin['created_at'])) : "N/A" ?>
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-center mt-8 space-x-4">
            <button onclick="openEditModal()" 
              class="px-6 py-2.5 rounded-lg bg-green-600 text-white font-medium shadow hover:bg-green-700 transition">
              <i class="fas fa-edit mr-2"></i>Edit Profile
            </button>
            <a href="login.php?action=logout" 
              class="px-6 py-2.5 rounded-lg bg-red-500 text-white font-medium shadow hover:bg-red-600 transition">
              <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Side Modal (Edit Profile) -->
<div id="editProfileModal" class="fixed inset-0 z-50 hidden">
  <!-- Background overlay -->
  <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeEditModal()"></div>

  <!-- Slide-in Panel -->
  <div id="editProfilePanel" class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-6">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4">Edit Profile</h2>
      
      <form method="POST" action="update_profile.php" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" 
            class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" 
            class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">New Password</label>
          <input type="password" name="password" placeholder="••••••••" 
            class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-green-500 focus:border-green-500">
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Fade-in animation for card
  document.querySelectorAll(".fade-in").forEach(el => {
    setTimeout(() => el.classList.add("visible"), 100);
  });
});

function openEditModal() {
  document.getElementById('editProfileModal').classList.remove('hidden');
  setTimeout(() => {
    document.getElementById('editProfilePanel').classList.remove('translate-x-full');
    document.getElementById('editProfilePanel').classList.add('translate-x-0');
  }, 10);
}

function closeEditModal() {
  const panel = document.getElementById('editProfilePanel');
  panel.classList.remove('translate-x-0');
  panel.classList.add('translate-x-full');
  setTimeout(() => {
    document.getElementById('editProfileModal').classList.add('hidden');
  }, 300);
}
</script>

</body>
</html>
