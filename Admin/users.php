<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';

// Build WHERE clause dynamically
$where = "WHERE (name LIKE :search OR email LIKE :search)";
$params = [':search' => "%$search%"];
if (!empty($typeFilter)) {
    $where .= " AND type = :type";
    $params[':type'] = $typeFilter;
}

// Count total users
$count_sql = "SELECT COUNT(*) FROM admin $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch users with pagination
$sql = "SELECT * FROM admin $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) $stmt->bindValue($key, $val);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Users - FixLearn</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
  .fade-slide.show { opacity: 1; transform: translateY(0); }
  .sidebar-modal { position: fixed; top: 0; right: 0; height: 100%; max-width: 28rem; width: 100%; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; overflow-y: auto; box-shadow: -4px 0 12px rgba(0,0,0,0.2); padding: 1.5rem; }
  .sidebar-modal.show { transform: translateX(0); }
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <!-- Header -->
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8 rounded-2xl">
    <h1 class="text-2xl font-bold">Users</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

  <!-- Search + Type + Add -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3 w-full">
    <form method="GET" id="searchForm" class="flex flex-wrap sm:flex-nowrap gap-2 w-full sm:w-auto">
      <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search users..."
        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

      <select name="type" onchange="document.getElementById('searchForm').submit();"
        class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-white focus:ring-2 focus:ring-blue-500 transition">
        <option value="">All Categories</option>
        <option value="Admin" <?= ($typeFilter==='Admin') ? 'selected' : '' ?>>Admin</option>
        <option value="Teachers" <?= ($typeFilter==='Teachers') ? 'selected' : '' ?>>Teacher</option>
      </select>

      <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">
        Search
      </button>
    </form>

    <button id="openAddUser" class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg shadow hover:from-green-700 hover:to-emerald-700 transition">
      <i class="fas fa-plus"></i> Add User
    </button>
  </div>

  <!-- Users Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">Name</th>
            <th class="p-3 border-b font-semibold">Email</th>
            <th class="p-3 border-b font-semibold">Type</th>
            <th class="p-3 border-b font-semibold">Created</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if ($users): ?>
            <?php foreach ($users as $u): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($u['name']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($u['email']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($u['type']); ?></td>
                <td class="p-3 text-gray-500 text-sm"><?= $u['created_at']; ?></td>
                <td class="p-3 flex justify-center gap-3">
                  <button class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editUserBtn"
                    data-id="<?= $u['id']; ?>"
                    data-name="<?= htmlspecialchars($u['name']); ?>"
                    data-email="<?= htmlspecialchars($u['email']); ?>"
                    data-type="<?= $u['type']; ?>">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="users_action.php?action=delete&id=<?= $u['id']; ?>" onclick="return confirm('Delete this user?');" class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="p-6 text-center text-gray-500">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex justify-center flex-wrap gap-2">
      <?php for ($i=1; $i<=$total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>" 
           class="px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition <?= $i==$page ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Sidebar Modal -->
<div id="userSidebar" class="sidebar-modal">
  <h2 id="modalTitle" class="text-xl font-bold mb-4">Add User</h2>
  <form method="POST" action="users_action.php" class="space-y-3">
    <input type="hidden" name="id" id="userId">
    <input type="hidden" name="action" id="formAction" value="add">

    <div>
      <label class="block mb-1 font-medium">Name</label>
      <input type="text" name="name" id="userName" required class="w-full px-3 py-2 border rounded-lg">
    </div>

    <div>
      <label class="block mb-1 font-medium">Email</label>
      <input type="email" name="email" id="userEmail" required class="w-full px-3 py-2 border rounded-lg">
    </div>

    <div>
      <label class="block mb-1 font-medium">Password</label>
      <input type="password" name="password" id="userPassword" class="w-full px-3 py-2 border rounded-lg">
      <small class="text-gray-500">Leave blank when editing if you don’t want to change password.</small>
    </div>

    <div>
      <label class="block mb-1 font-medium">Type</label>
      <select name="type" id="userType" class="w-full px-3 py-2 border rounded-lg">
        <option value="Admin">Admin</option>
        <option value="Teachers">Teacher</option>
      </select>
    </div>

    <div class="flex justify-end gap-2 mt-4">
      <button type="button" id="closeSidebar" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
    </div>
  </form>
</div>
<div id="overlay" class="hidden modal-overlay"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));

  const sidebar = document.getElementById("userSidebar");
  const overlay = document.getElementById("overlay");
  const modalTitle = document.getElementById("modalTitle");
  const formAction = document.getElementById("formAction");
  const userId = document.getElementById("userId");
  const userName = document.getElementById("userName");
  const userEmail = document.getElementById("userEmail");
  const userPassword = document.getElementById("userPassword");
  const userType = document.getElementById("userType");

  function openSidebar() { sidebar.classList.add("show"); overlay.classList.remove("hidden"); }
  function closeSidebar() { sidebar.classList.remove("show"); overlay.classList.add("hidden"); }

  document.getElementById("openAddUser").addEventListener("click", () => {
    modalTitle.textContent = "Add User";
    formAction.value = "add";
    userId.value = "";
    userName.value = "";
    userEmail.value = "";
    userPassword.value = "";
    userType.value = "Admin";
    openSidebar();
  });

  document.querySelectorAll(".editUserBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      modalTitle.textContent = "Edit User";
      formAction.value = "edit";
      userId.value = btn.dataset.id;
      userName.value = btn.dataset.name;
      userEmail.value = btn.dataset.email;
      userPassword.value = "";
      userType.value = btn.dataset.type;
      openSidebar();
    });
  });

  document.getElementById("closeSidebar").addEventListener("click", closeSidebar);
  overlay.addEventListener("click", closeSidebar);
});
</script>
</body>
</html>
