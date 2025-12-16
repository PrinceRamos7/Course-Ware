<?php
require __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT id, name, email, created_at FROM admins WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - ISUtoLearn</title>
  <link rel="stylesheet" href="../output.css">
  <link rel="icon" href="../images/isu-logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Inter', system-ui, sans-serif;
      color: #1f2937;
    }

    .profile-card {
      background: #fff;
      border-radius: 1.5rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      overflow: hidden;
      transition: all 0.3s ease;
    }
    .profile-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    .profile-header {
      background: linear-gradient(135deg, #22c55e, #15803d);
      height: 160px;
      position: relative;
    }

    .avatar {
      position: absolute;
      bottom: -60px; /* overlaps header */
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 5px solid #fff;
      background: #fff;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    .profile-info {
      padding: 80px 1.5rem 2rem;
      text-align: center;
    }

    .profile-info h1 {
      font-size: 1.75rem;
      font-weight: 700;
    }

    .profile-info p {
      color: #6b7280;
      margin-top: 0.25rem;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-top: 2rem;
    }

    .info-card {
      background: #f9fafb;
      padding: 1rem;
      border-radius: 0.75rem;
      border: 1px solid #e5e7eb;
      text-align: center;
      transition: all 0.3s ease;
    }
    .info-card:hover {
      background: #f3f4f6;
    }

    .btn {
      padding: 0.6rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
    }

    .btn-edit {
      background: #16a34a;
      color: white;
    }
    .btn-edit:hover { background: #15803d; }

    .btn-logout {
      background: #ef4444;
      color: white;
    }
    .btn-logout:hover { background: #dc2626; }

    /* Fade-in */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease-in-out;
    }
    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body class="min-h-screen flex bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">

<?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-wrapper flex-grow flex flex-col">

<?php include "header.php"; renderHeader("My Profile"); ?>

<main class="flex-1 p-6 sm:p-10 bg-gray-50">
  <div class="max-w-4xl mx-auto fade-in">
    <?php if (isset($_SESSION['success'])): ?>
  <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 border border-green-300">
    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
  </div>
<?php elseif (isset($_SESSION['error'])): ?>
  <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 border border-red-300">
    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>

    <div class="profile-card">
      <!-- Header with Avatar -->
      <div class="profile-header">
        <div class="avatar">
          <?php if (!empty($admin['avatar_url'])): ?>
            <img src="<?= htmlspecialchars($admin['avatar_url']) ?>" alt="<?= htmlspecialchars($admin['name']) ?>">
          <?php else: ?>
            <span class="text-5xl font-bold text-green-600">
              <?= htmlspecialchars(strtoupper(substr($admin['name'], 0, 1))) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Info -->
      <div class="profile-info">
        <h1><?= htmlspecialchars($admin['name']) ?></h1>
        <p><?= htmlspecialchars($admin['email']) ?></p>

        <div class="info-grid mt-8">
          <div class="info-card">
            <p class="text-sm text-gray-500">📧 Email</p>
            <p class="font-semibold"><?= htmlspecialchars($admin['email']) ?></p>
          </div>
          <div class="info-card">
            <p class="text-sm text-gray-500">📅 Member Since</p>
            <p class="font-semibold">
              <?= $admin['created_at'] ? date("F d, Y", strtotime($admin['created_at'])) : "N/A" ?>
            </p>
          </div>
        </div>

        <div class="flex justify-center gap-4 mt-10">
        <button onclick="openEditModal()" class="px-6 py-2 bg-green-600 text-white rounded-xl font-medium shadow-md hover:bg-green-700 transition-all transform hover:scale-105">
            <i class="fas fa-pen-to-square mr-2"></i>Edit Profile
          </button>

          <a href="login.php?action=logout" class="btn btn-logout shadow-md">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
          </div>

          <!-- Edit Profile Modal -->
<div id="editModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl animate-fade-in">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit Profile</h2>

    <form action="update_profile.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
      </div>
    
<div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
        <input type="password" name="password"  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
      </div>
  
<div>
        <label class="block text-sm font-medium text-gray-600 mb-1">New Password</label>
        <input type="password" name="new_password"  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
      </div>

<div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
        <input type="password" name="match_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
      </div>

      <div class="flex justify-end gap-3 mt-5">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">Save</button>
      </div>
    </form>
  </div>
</div>

</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach(el => {
    setTimeout(() => el.classList.add("visible"), 100);
  });
});
function openEditModal() {
  document.getElementById("editModal").classList.remove("hidden");
}
function closeEditModal() {
  document.getElementById("editModal").classList.add("hidden");
}
</script>

<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fade-in 0.3s ease-in-out; }
</style>


</body>
</html>
