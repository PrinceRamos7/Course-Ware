<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total
$count_sql = "SELECT COUNT(*) FROM registration_codes WHERE code LIKE :search";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute(['search' => "%$search%"]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch registration codes with usage count
$sql = "SELECT rc.*, 
               COUNT(rcu.id) AS used_count
        FROM registration_codes rc
        LEFT JOIN registration_code_uses rcu 
               ON rc.id = rcu.registration_code_id
        WHERE rc.code LIKE :search
        GROUP BY rc.id
        ORDER BY rc.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Registration Codes - FixLearn</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
  .fade-slide.show { opacity: 1; transform: translateY(0); }
  .sidebar-modal { position: fixed; top: 0; right: 0; height: 100%; max-width: 32rem; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; overflow-y: auto; box-shadow: -4px 0 12px rgba(0,0,0,0.2); padding: 1.5rem; }
  .sidebar-modal.show { transform: translateX(0); }
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <!-- Header -->
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8 rounded-2xl">
    <h1 class="text-2xl font-bold">Registration Codes</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

  <!-- Search + Add -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
    <form method="GET" class="flex w-full sm:w-auto gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search codes..." class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
      <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">Search</button>
    </form>
    <button id="openAddModal" class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg shadow hover:from-green-700 hover:to-emerald-700 transition">
      <i class="fas fa-plus"></i> Add Code
    </button>
  </div>

  <!-- Codes Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">Code</th>
            <th class="p-3 border-b font-semibold">Course</th>
            <th class="p-3 border-b font-semibold text-center">Used</th>
            <th class="p-3 border-b font-semibold text-center">Active</th>
            <th class="p-3 border-b font-semibold">Expires At</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if ($codes): ?>
            <?php foreach ($codes as $row): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['code']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($row['course_title'] ?? 'N/A'); ?></td>
                <td class="p-3 text-center"><?= $row['used_count']; ?></td>
                <td class="p-3 text-center">
                  <?= $row['active'] ? '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Active</span>' : '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Inactive</span>'; ?>
                </td>
                <td class="p-3 text-gray-600"><?= $row['expires_at']; ?></td>
                <td class="p-3 flex justify-center gap-2">
                  <!-- View Users -->
                  <button class="viewBtn px-3 py-1 text-sm font-medium bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition"
                    data-id="<?= $row['id']; ?>"
                    data-code="<?= htmlspecialchars($row['code']); ?>">
                    <i class="fas fa-eye"></i>
                  </button>
                  <!-- Edit -->
                  <button class="editBtn px-3 py-1 text-sm font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition" 
                    data-id="<?= $row['id']; ?>"
                    data-code="<?= htmlspecialchars($row['code']); ?>"
                    data-course="<?= $row['course_id']; ?>"
                    data-active="<?= $row['active']; ?>"
                    data-expires="<?= $row['expires_at']; ?>">
                    <i class="fas fa-edit"></i>
                  </button>
                  <!-- Delete -->
                  <a href="registration_code.php?action=delete&id=<?= $row['id']; ?>" onclick="return confirm('Delete this code?');" class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="p-4 text-center text-gray-500">No codes found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <div class="mt-6 flex justify-center space-x-2">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 rounded-lg text-sm font-medium <?= $i == $page ? 'bg-blue-600 text-white shadow' : 'bg-gray-200 hover:bg-gray-300' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="hidden modal-overlay flex items-center justify-center">
  <div id="viewContent" class="sidebar-modal w-full sm:w-[32rem]">
    <h2 class="text-xl font-bold mb-4">Users of Code <span id="viewCodeName"></span></h2>
    <div id="viewUsers" class="space-y-2">
      <p class="text-gray-500">Loading...</p>
    </div>
    <div class="flex justify-end mt-4">
      <button id="closeView" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Close</button>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));

  // View Modal
  const viewModal = document.getElementById("viewModal");
  const viewContent = document.getElementById("viewContent");
  const viewUsers = document.getElementById("viewUsers");
  const viewCodeName = document.getElementById("viewCodeName");

  document.querySelectorAll(".viewBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      viewCodeName.textContent = btn.dataset.code;
      viewUsers.innerHTML = "<p class='text-gray-500 animate-pulse'>Loading...</p>";
      viewModal.classList.remove("hidden"); viewContent.classList.add("show");

      fetch("view_code_users.php?id=" + btn.dataset.id)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            viewUsers.innerHTML = "<p class='text-red-500'>Error: " + data.error + "</p>";
            return;
          }
          if (data.length > 0) {
            viewUsers.innerHTML = data.map(u => `
              <div class="p-3 border rounded-lg shadow-sm bg-gray-50">
                <p class="font-semibold">${u.first_name} ${u.last_name}</p>
                <p class="text-sm text-gray-500">Used at: ${u.used_at}</p>
              </div>
            `).join("");
          } else {
            viewUsers.innerHTML = "<p class='text-gray-500'>No students used this code yet.</p>";
          }
        })
        .catch(err => {
          viewUsers.innerHTML = "<p class='text-red-500'>Error loading users.</p>";
          console.error(err);
        });
    });
  });

  document.getElementById("closeView").addEventListener("click", () => {
    viewModal.classList.add("hidden"); viewContent.classList.remove("show");
  });
});
</script>
</body>
</html>
