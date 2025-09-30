<?php
require __DIR__ . '/../config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Save session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['admin_type'] = $user['type'];

        header("Location: ../Admin/dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login - FixLearn</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
  .fade-slide.show { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md fade-slide">
  <div class="flex flex-col items-center mb-6">
    <i class="fas fa-graduation-cap text-blue-600 text-5xl mb-3"></i>
    <h1 class="text-2xl font-bold">FixLearn Login</h1>
    <p class="text-gray-500 text-sm">Welcome back! Please login to continue.</p>
  </div>

  <?php if ($error): ?>
    <div class="mb-4 px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm">
      <?= htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block mb-1 font-medium">Email</label>
      <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
      <label class="block mb-1 font-medium">Password</label>
      <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
    </div>

    <button type="submit" class="w-full py-2 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 transition">
      <i class="fas fa-sign-in-alt mr-2"></i> Login
    </button>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));
});
</script>
</body>
</html>
