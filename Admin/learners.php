<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total learners
$count_sql = "SELECT COUNT(*) AS total FROM learners";
$params = [];
if ($search !== '') {
    $count_sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR contact_number LIKE :search";
    $params['search'] = "%$search%";
}
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_learners = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_learners / $limit);

// Fetch learners
$sql = "SELECT * FROM learners";
if ($search !== '') {
    $sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR contact_number LIKE :search";
}
$sql .= " ORDER BY id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$learners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Learners - Admin Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <!-- Header -->
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold">Learners</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

  <!-- Search + Add -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
    <!-- Search -->
    <form method="GET" class="flex w-full sm:w-auto gap-2">
      <input 
        type="text" 
        name="search" 
        value="<?= htmlspecialchars($search); ?>" 
        placeholder="Search learners..." 
        class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
      >
      <button 
        type="submit" 
        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 active:scale-95 transition"
      >
        Search
      </button>
    </form>

    <!-- Add Learner -->
    <button 
      id="openAddLearner" 
      class="flex items-center gap-2 px-5 py-2 bg-green-600 text-white font-medium rounded-lg shadow hover:bg-green-700 active:scale-95 transition"
    >
      <i class="fas fa-plus"></i> 
    </button>
  </div>

  <!-- Learners Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">#</th>
            <th class="p-3 border-b font-semibold">Name</th>
            <th class="p-3 border-b font-semibold">Email</th>
            <th class="p-3 border-b font-semibold">Contact</th>
            <th class="p-3 border-b font-semibold">Status</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($learners)): ?>
            <?php $i = $offset + 1; foreach ($learners as $learner): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3"><?= $i++; ?></td>
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($learner['first_name'] . ' ' . ($learner['middle_name'] ? $learner['middle_name'] . ' ' : '') . $learner['last_name']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($learner['email']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($learner['contact_number']); ?></td>
                <td class="p-3"><?= ucfirst($learner['status']); ?></td>
                <td class="p-3 flex justify-center gap-3">
                  <!-- Edit -->
                  <button 
                     class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editLearnerBtn"
                     data-id="<?= $learner['id']; ?>"
                     data-first="<?= htmlspecialchars($learner['first_name']); ?>"
                     data-middle="<?= htmlspecialchars($learner['middle_name']); ?>"
                     data-last="<?= htmlspecialchars($learner['last_name']); ?>"
                     data-email="<?= htmlspecialchars($learner['email']); ?>"
                     data-contact="<?= htmlspecialchars($learner['contact_number']); ?>"
                     data-status="<?= $learner['status']; ?>"
                  >
                     <i class="fas fa-edit"></i>
                  </button>
                  <!-- Delete -->
                  <a href="learner_code.php?action=delete&id=<?= $learner['id']; ?>" 
                     onclick="return confirm('Delete this learner?');"
                     class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                     <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="p-6 text-center text-gray-500">No learners found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="mt-6 flex justify-center flex-wrap gap-2">
        <?php for($p=1; $p<=$total_pages; $p++): ?>
          <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
             class="px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition 
             <?= $p==$page 
                ? 'bg-blue-600 text-white shadow-md' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
            <?= $p; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>


<script>
  // Modal functionality
  const openBtn = document.getElementById('openAddLearner');
  const closeBtn = document.getElementById('closeAddLearner');
  const cancelBtn = document.getElementById('cancelModal');
  const modalContent = document.getElementById('modalContent');

  function openModal() {
    modal.classList.remove('hidden');
    setTimeout(() => modalContent.classList.remove('translate-x-full'), 10);
  }
  function closeModal() {
    modalContent.classList.add('translate-x-full');
    setTimeout(() => modal.classList.add('hidden'), 300);
  }

  openBtn.addEventListener('click', openModal);
  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
</script>
</body>
</html>
