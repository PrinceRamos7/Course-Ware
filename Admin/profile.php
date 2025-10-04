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
<body class="flex min-h-screen">

<?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-wrapper flex-grow flex flex-col">

<?php include "header.php"; renderHeader("My Profile"); ?>

<main class="flex-1 p-6 sm:p-10 bg-gray-50">
  <div class="max-w-4xl mx-auto fade-in">
    
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
          <button onclick="openEditModal()" class="btn btn-edit shadow-md">
            <i class="fas fa-pen-to-square"></i> Edit Profile
          </button>
          <a href="login.php?action=logout" class="btn btn-logout shadow-md">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
          </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach(el => {
    setTimeout(() => el.classList.add("visible"), 100);
  });
});
</script>

</body>
</html>
