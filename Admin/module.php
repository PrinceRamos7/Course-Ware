<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
$offset = ($page - 1) * $limit;

// Count total modules
$count_sql = "SELECT COUNT(*) AS total 
              FROM modules m 
              LEFT JOIN courses c ON m.course_id = c.id 
              WHERE 1=1";

$params = [];

if ($course_id > 0) {
    $count_sql .= " AND m.course_id = :course_id";
    $params[':course_id'] = $course_id;
}

if ($search !== '') {
    $count_sql .= " AND (m.title LIKE :search OR m.description LIKE :search OR c.title LIKE :search)";
    $params[':search'] = "%$search%";
}

$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_modules = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_modules / $limit);

// Fetch modules with course title
$modules_sql = "SELECT m.*, c.title AS course_title 
                FROM modules m 
                LEFT JOIN courses c ON m.course_id = c.id 
                WHERE 1=1";

if ($course_id > 0) {
    $modules_sql .= " AND m.course_id = :course_id";
}

if ($search !== '') {
    $modules_sql .= " AND (m.title LIKE :search OR m.description LIKE :search OR c.title LIKE :search)";
}

$modules_sql .= " ORDER BY m.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($modules_sql);

if ($course_id > 0) {
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
}
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$modules_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses for dropdown
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Modules - FixLearn</title>
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
    <h1 class="text-2xl font-bold">Modules Page</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition hover-scale">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

<!-- Search + Add -->
<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
  <!-- Search -->
  <form method="GET" class="flex w-full sm:w-auto gap-2">
    <?php if ($course_id > 0): ?>
      <input type="hidden" name="course_id" value="<?= $course_id; ?>">
    <?php endif; ?>
    <input 
      type="text" 
      name="search" 
      value="<?= htmlspecialchars($search); ?>" 
      placeholder="Search modules..." 
      class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
    >
    <button 
      type="submit" 
      class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 active:scale-95 transition"
    >
      Search
    </button>
  </form>

  <!-- Add Module -->
  <button 
    id="openAddModule" 
    class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition"
  >
    <i class="fas fa-plus"></i> Add Module
  </button>
</div>

<!-- Modules Table -->
<div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
          <th class="p-3 border-b font-semibold">#</th>
          <th class="p-3 border-b font-semibold">Title</th>
          <th class="p-3 border-b font-semibold">Description</th>
          <th class="p-3 border-b font-semibold">Course</th>
          <th class="p-3 border-b font-semibold">Required Score</th>
          <th class="p-3 border-b font-semibold text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (!empty($modules_result)): ?>
          <?php $i = $offset + 1; foreach ($modules_result as $row): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="p-3"><?= $i++; ?></td>
              <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['title']); ?></td>
              <td class="p-3 text-gray-600"><?= htmlspecialchars(substr($row['description'], 0, 60)); ?>...</td>
              <td class="p-3 text-gray-600"><?= htmlspecialchars($row['course_title']); ?></td>
              <td class="p-3 text-center"><?= $row['required_score']; ?></td>
              <td class="p-3 flex justify-center gap-3">
                <!-- Topics -->
                <a href="topics.php?module_id=<?= $row['id']; ?>" 
                   class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                   Topics
                </a> 
              
              <!-- Edit -->
                <button 
                   class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editModuleBtn"
                   data-id="<?= $row['id']; ?>"
                   data-title="<?= htmlspecialchars($row['title']); ?>"
                   data-description="<?= htmlspecialchars($row['description']); ?>"
                   data-course="<?= $row['course_id']; ?>"
                   data-score="<?= $row['required_score']; ?>">
                   <i class="fas fa-edit"></i>
                </button>
                <!-- Delete -->
                <a href="module_code.php?action=delete&id=<?= $row['id']; ?>&course_id=<?= $course_id; ?>" 
                   onclick="return confirm('Delete this module?');"
                   class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                   <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="p-6 text-center text-gray-500">No modules found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex justify-center flex-wrap gap-2">
      <?php for($p=1; $p<=$total_pages; $p++): ?>
        <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?><?= $course_id > 0 ? "&course_id=$course_id" : ""; ?>" 
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

<!-- Back to Courses Button -->
<div class="mt-6 text-right">
    <a href="course.php" 
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
       ← Back to Courses
    </a>
</div>

<!-- Add Module Modal -->
<div id="addModuleModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeAddModule"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="modalContent">
    <h3 class="text-xl font-bold mb-4">Add New Module</h3>
    <form method="POST" action="module_code.php">
      <input type="hidden" name="action" value="add">
      <?php if ($course_id > 0): ?>
        <input type="hidden" name="course_id" value="<?= $course_id; ?>">
      <?php endif; ?>

      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Title</label>
        <input type="text" name="title" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Description</label>
        <textarea name="description" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Required Score</label>
        <input type="number" name="required_score" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelModal" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Module Modal (unchanged except course dropdown) -->
<div id="editModuleModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeEditModule"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="editModalContent">
    <h3 class="text-xl font-bold mb-4">Edit Module</h3>
    <form method="POST" action="module_code.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editModuleId">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Title</label>
        <input type="text" name="title" id="editModuleTitle" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Description</label>
        <textarea name="description" id="editModuleDescription" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Course</label>
        <select name="course_id" id="editModuleCourse" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <?php foreach ($courses as $course): ?>
            <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['title']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Required Score</label>
        <input type="number" name="required_score" id="editModuleScore" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelEditModal" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 transition">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Fade animation
  document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 200));

  // Add Modal
  const openBtn = document.getElementById('openAddModule');
  const closeBtn = document.getElementById('closeAddModule');
  const cancelBtn = document.getElementById('cancelModal');
  const modal = document.getElementById('addModuleModal');
  const modalContent = document.getElementById('modalContent');
  function openModal() { modal.classList.remove('hidden'); setTimeout(() => modalContent.classList.remove('translate-x-full'), 10);}
  function closeModal() { modalContent.classList.add('translate-x-full'); setTimeout(() => modal.classList.add('hidden'), 300);}
  openBtn.addEventListener('click', openModal);
  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);

  // Edit Modal
  const editBtns = document.querySelectorAll('.editModuleBtn');
  const editModal = document.getElementById('editModuleModal');
  const editModalContent = document.getElementById('editModalContent');
  const closeEditBtn = document.getElementById('closeEditModule');
  const cancelEditBtn = document.getElementById('cancelEditModal');

  editBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('editModuleId').value = btn.dataset.id;
      document.getElementById('editModuleTitle').value = btn.dataset.title;
      document.getElementById('editModuleDescription').value = btn.dataset.description;
      document.getElementById('editModuleCourse').value = btn.dataset.course;
      document.getElementById('editModuleScore').value = btn.dataset.score;
      editModal.classList.remove('hidden');
      setTimeout(() => editModalContent.classList.remove('translate-x-full'), 10);
    });
  });
  function closeEditModal() { editModalContent.classList.add('translate-x-full'); setTimeout(() => editModal.classList.add('hidden'), 300);}
  closeEditBtn.addEventListener('click', closeEditModal);
  cancelEditBtn.addEventListener('click', closeEditModal);
</script>
</body>
</html>
