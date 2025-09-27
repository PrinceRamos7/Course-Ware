<?php
require __DIR__ . '/../config.php';

// Handle AJAX request to fetch users of a code
if (isset($_GET['fetch_users']) && isset($_GET['code_id'])) {
    $code_id = (int)$_GET['code_id'];

    $sql = "SELECT rcu.id, CONCAT(s.first_name, ' ', s.last_name) AS name, rcu.used_at
            FROM registration_code_uses rcu
            JOIN learners s ON rcu.id = s.id
            WHERE rcu.registration_code_id = :code_id
            ORDER BY rcu.used_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['code_id' => $code_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($users);
    exit; 
}

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Fetch courses for select dropdown
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Count total
$count_sql = "SELECT COUNT(*) FROM registration_codes WHERE code LIKE :search";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute(['search' => "%$search%"]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch registration codes with usage count
$sql = "SELECT rc.*, 
               c.title AS course_title,
               COUNT(rcu.id) AS used_count
        FROM registration_codes rc
        LEFT JOIN registration_code_uses rcu ON rc.id = rcu.registration_code_id
        LEFT JOIN courses c ON rc.course_id = c.id
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
  .sidebar-modal { position: fixed; top: 0; right: 0; height: 100%; max-width: 32rem; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; overflow-y: auto; box-shadow: -4px 0 12px rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 1rem; }
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
                  <!-- View -->
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

<!-- Add/Edit Modal -->
<div id="codeModal" class="sidebar-modal">
  <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Code</h2>
  <form method="POST" action="registration_code.php" class="space-y-3">
    <input type="hidden" name="id" id="codeId">
    <input type="hidden" name="action" id="formAction" value="add">
    <div>
      <label class="block mb-1 font-medium">Code</label>
      <input type="text" name="code" id="codeInput" required class="w-full px-3 py-2 border rounded-lg">
    </div>
    <div>
      <label class="block mb-1 font-medium">Course</label>
      <select name="course_id" id="courseSelect" class="w-full px-3 py-2 border rounded-lg">
        <?php foreach($courses as $c): ?>
          <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['title']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="active" id="activeCheckbox" value="1" class="h-4 w-4">
      <label for="activeCheckbox" class="font-medium">Active</label>
    </div>
    <div>
      <label class="block mb-1 font-medium">Expires At</label>
      <input type="date" name="expires_at" id="expiresAt" class="w-full px-3 py-2 border rounded-lg">
    </div>
    <div class="flex justify-end gap-2 mt-4">
      <button type="button" id="closeModal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
    </div>
  </form>
</div>

<!-- View Users Modal -->
<div id="viewUsersModal" class="sidebar-modal">
  <h2 class="text-xl font-bold mb-4">Learners Using Code: <span id="viewCodeText"></span></h2>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
          <th class="p-3 border-b font-semibold">Student ID</th>
          <th class="p-3 border-b font-semibold">Name</th>
          <th class="p-3 border-b font-semibold">Used At</th>
        </tr>
      </thead>
      <tbody id="usersTableBody" class="divide-y divide-gray-100">
        <tr>
          <td colspan="3" class="p-4 text-center text-gray-500">Loading...</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="flex justify-end mt-4">
    <button id="closeViewUsersModal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
  </div>
</div>

<!-- Overlay -->
<div id="overlay" class="hidden modal-overlay"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));

  const modal = document.getElementById("codeModal");
  const overlay = document.getElementById("overlay");
  const modalTitle = document.getElementById("modalTitle");
  const formAction = document.getElementById("formAction");
  const codeId = document.getElementById("codeId");
  const codeInput = document.getElementById("codeInput");
  const courseSelect = document.getElementById("courseSelect");
  const activeCheckbox = document.getElementById("activeCheckbox");
  const expiresAt = document.getElementById("expiresAt");

  const viewModal = document.getElementById("viewUsersModal");
  const viewCodeText = document.getElementById("viewCodeText");
  const usersTableBody = document.getElementById("usersTableBody");
  const closeViewBtn = document.getElementById("closeViewUsersModal");

  function openModal() { modal.classList.add("show"); overlay.classList.remove("hidden"); }
  function closeModal() { modal.classList.remove("show"); overlay.classList.add("hidden"); }

  function openViewModal() { viewModal.classList.add("show"); overlay.classList.remove("hidden"); }
  function closeViewModal() { viewModal.classList.remove("show"); overlay.classList.add("hidden"); }

  document.getElementById("openAddModal").addEventListener("click", () => {
    modalTitle.textContent = "Add Code";
    formAction.value = "add";
    codeId.value = ""; codeInput.value = ""; courseSelect.selectedIndex = 0;
    activeCheckbox.checked = false; expiresAt.value = "";
    openModal();
  });

  document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      modalTitle.textContent = "Edit Code";
      formAction.value = "edit";
      codeId.value = btn.dataset.id;
      codeInput.value = btn.dataset.code;
      courseSelect.value = btn.dataset.course;
      activeCheckbox.checked = btn.dataset.active == 1;
      expiresAt.value = btn.dataset.expires || "";
      openModal();
    });
  });

  document.getElementById("closeModal").addEventListener("click", closeModal);
  closeViewBtn.addEventListener("click", closeViewModal);
  overlay.addEventListener("click", () => { closeModal(); closeViewModal(); });

  document.querySelectorAll(".viewBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const codeIdVal = btn.dataset.id;
      const codeText = btn.dataset.code;
      viewCodeText.textContent = codeText;
      usersTableBody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-gray-500">Loading...</td></tr>';

      fetch(`code_redeemer.php?fetch_users=1&code_id=${codeIdVal}`)
        .then(res => res.json())
        .then(data => {
          if (data.length > 0) {
            usersTableBody.innerHTML = data.map(user => `
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3 text-gray-600">${user.student_id}</td>
                <td class="p-3 text-gray-600">${user.name}</td>
                <td class="p-3 text-gray-600">${user.used_at}</td>
              </tr>
            `).join('');
          } else {
            usersTableBody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-gray-500">No learners have used this code yet.</td></tr>';
          }
        })
        .catch(err => {
          usersTableBody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-red-500">Error fetching learners.</td></tr>';
          console.error(err);
        });

      openViewModal();
    });
  });
});
</script>
</body>
</html>
