<?php
session_start();
require __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // redirect to login if not logged in
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($user_id <= 0) {
    die("❌ Invalid user ID.");
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold">My Profile</h1>
    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Logout</a>
  </header>

  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 max-w-3xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Personal Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="font-semibold text-gray-700">First Name</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['first_name']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Middle Name</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['middle_name'] ?? '-'); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Last Name</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['last_name']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Email</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['email']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Contact Number</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['contact_number']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Address</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['address']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Experience</label>
        <p class="text-gray-800"><?= (int)$user['experience']; ?> years</p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Intelligent Experience</label>
        <p class="text-gray-800"><?= (int)$user['intelligent_exp']; ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Status</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['status']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Type</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['type']); ?></p>
      </div>
      <div>
        <label class="font-semibold text-gray-700">Created At</label>
        <p class="text-gray-800"><?= htmlspecialchars($user['created_at']); ?></p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
