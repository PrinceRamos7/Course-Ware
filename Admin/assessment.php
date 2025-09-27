<?php
require __DIR__ . '/../config.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Params
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($module_id <= 0) {
    header("Location: module.php?course_id=$course_id&error=invalid_module");
    exit;
}

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Get module info
$mod = $conn->prepare("SELECT id, title FROM modules WHERE id = ?");
$mod->execute([$module_id]);
$module = $mod->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    header("Location: module.php?course_id=$course_id&error=module_not_found");
    exit;
}

// Count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM assessments WHERE module_id = :mid";
$params = ['mid' => $module_id];
if ($search !== '') {
    $count_sql .= " AND name LIKE :search";
    $params['search'] = "%$search%";
}
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_assessments = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = max(1, ceil($total_assessments / $limit));

// Fetch assessments
$sql = "SELECT * FROM assessments WHERE module_id = :mid";
if ($search !== '') $sql .= " AND name LIKE :search";
$sql .= " ORDER BY id DESC LIMIT $offset, $limit"; // safe because integers
$stmt = $conn->prepare($sql);
$stmt->bindValue(':mid', $module_id, PDO::PARAM_INT);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->execute();
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Assessments - <?= htmlspecialchars($module['title']); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
.fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
.fade-slide.show { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <!-- Header -->
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold">Assessments (<?= htmlspecialchars($module['title']); ?>)</h1>
    
  </header>

  <!-- Search + Add -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
    <!-- Search -->
    <form method="GET" class="flex w-full sm:w-auto gap-2">
      <input type="hidden" name="module_id" value="<?= $module_id; ?>">
      <input type="hidden" name="course_id" value="<?= $course_id; ?>">
      <input 
        type="text" 
        name="search" 
        value="<?= htmlspecialchars($search); ?>" 
        placeholder="Search assessments..." 
        class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
      >
      <button type="submit" 
        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 active:scale-95 transition">
        Search
      </button>
    </form>

    <!-- Add Assessment -->
    <button 
      id="openAddAssessment" 
      class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition"
    >
      <i class="fas fa-plus"></i> Add Assessment
    </button>
  </div>

  <!-- Assessments Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">#</th>
            <th class="p-3 border-b font-semibold">Name</th>
            <th class="p-3 border-b font-semibold">Type</th>
            <th class="p-3 border-b font-semibold">Time</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($assessments)): ?>
            <?php $i = $offset + 1; foreach ($assessments as $row): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3"><?= $i++; ?></td>
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['name']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($row['type']); ?></td>
                <td class="p-3 text-gray-600"><?= (int)$row['time_set']; ?> mins</td>
                <td class="p-3 flex justify-center gap-3">
                  <button 
                     class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editAssessmentBtn"
                     data-id="<?= $row['id']; ?>"
                     data-name="<?= htmlspecialchars($row['name']); ?>"
                     data-type="<?= htmlspecialchars($row['type']); ?>"
                     data-time="<?= $row['time_set']; ?>">
                     <i class="fas fa-edit"></i>
                  </button>
                  <a href="assessment_code.php?action=delete&id=<?= $row['id']; ?>&module_id=<?= $module_id; ?>" 
                     onclick="return confirm('Delete this assessment?');"
                     class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                    <i class="fas fa-trash"></i> 
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-6 text-center text-gray-500">No assessments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="mt-6 flex justify-center flex-wrap gap-2">
        <?php for($p=1; $p<=$total_pages; $p++): ?>
          <a href="?module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>&page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
             class="px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition 
             <?= $p==$page ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
            <?= $p; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Assessment Modal -->
<div id="addAssessmentModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeAddAssessment"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="addModalContent">
    <h3 class="text-xl font-bold mb-4">Add New Assessment</h3>
    <form method="POST" action="assessment_code.php">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="module_id" value="<?= $module_id; ?>">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Name</label>
        <input type="text" name="name" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Type</label>
        <select name="type" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="module">Module</option>
          <option value="topic">Topic</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Time (mins)</label>
        <input type="number" name="time_set" min="1" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelAddAssessment" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">Add</button>
      </div>
    </form>
    
  </div>
  
</div>

<!-- Edit Assessment Modal -->
<div id="editAssessmentModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeEditAssessment"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="editModalContent">
    <h3 class="text-xl font-bold mb-4">Edit Assessment</h3>
    <form method="POST" action="assessment_code.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editAssessmentId">
      <input type="hidden" name="module_id" value="<?= $module_id; ?>">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Name</label>
        <input type="text" name="name" id="editAssessmentName" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Type</label>
        <select name="type" id="editAssessmentType" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="module">Module</option>
          <option value="topic">Topic</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Time (mins)</label>
        <input type="number" name="time_set" id="editAssessmentTime" min="1" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelEditAssessment" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 transition">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Fade animation
  document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 200));

  // Add Modal
  const addBtn = document.getElementById('openAddAssessment');
  const addModal = document.getElementById('addAssessmentModal');
  const addModalContent = document.getElementById('addModalContent');
  const closeAdd = document.getElementById('closeAddAssessment');
  const cancelAdd = document.getElementById('cancelAddAssessment');
  addBtn.addEventListener('click', () => { addModal.classList.remove('hidden'); setTimeout(() => addModalContent.classList.remove('translate-x-full'), 10);});
  function closeAddModal(){ addModalContent.classList.add('translate-x-full'); setTimeout(()=>addModal.classList.add('hidden'), 300);}
  closeAdd.addEventListener('click', closeAddModal);
  cancelAdd.addEventListener('click', closeAddModal);

  // Edit Modal
  const editBtns = document.querySelectorAll('.editAssessmentBtn');
  const editModal = document.getElementById('editAssessmentModal');
  const editModalContent = document.getElementById('editModalContent');
  const closeEdit = document.getElementById('closeEditAssessment');
  const cancelEdit = document.getElementById('cancelEditAssessment');

  editBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('editAssessmentId').value = btn.dataset.id;
      document.getElementById('editAssessmentName').value = btn.dataset.name;
      document.getElementById('editAssessmentType').value = btn.dataset.type;
      document.getElementById('editAssessmentTime').value = btn.dataset.time;
      editModal.classList.remove('hidden');
      setTimeout(() => editModalContent.classList.remove('translate-x-full'), 10);
    });
  });
  function closeEditModal(){ editModalContent.classList.add('translate-x-full'); setTimeout(()=>editModal.classList.add('hidden'), 300);}
  closeEdit.addEventListener('click', closeEditModal);
  cancelEdit.addEventListener('click', closeEditModal);
</script>
</body>
</html>
