<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Module filter
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// Count total topics
$count_sql = "SELECT COUNT(*) AS total FROM topics t 
              LEFT JOIN modules m ON t.module_id = m.id";
$where = [];
$params = [];

if ($module_id > 0) {
    $where[] = "t.module_id = :module_id";
    $params[':module_id'] = $module_id;
}
if ($search !== '') {
    $where[] = "(t.title LIKE :search OR t.description LIKE :search OR m.title LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($where) $count_sql .= " WHERE " . implode(" AND ", $where);

$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_topics = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_topics / $limit);

// Fetch topics with module title
$topics_sql = "SELECT t.*, m.title AS module_title 
               FROM topics t 
               LEFT JOIN modules m ON t.module_id = m.id";
if ($where) $topics_sql .= " WHERE " . implode(" AND ", $where);
$topics_sql .= " ORDER BY t.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($topics_sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$topics_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch modules for dropdown (when adding/editing topics)
$modules = $conn->query("SELECT id, title FROM modules ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Topics - FixLearn</title>
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
    <h1 class="text-2xl font-bold">Topics Page</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition hover-scale">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

  <!-- Search + Add -->
  <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
    <form method="GET" class="flex w-full sm:w-auto gap-2">
      <input type="hidden" name="module_id" value="<?= $module_id; ?>">
      <input 
        type="text" 
        name="search" 
        value="<?= htmlspecialchars($search); ?>" 
        placeholder="Search topics..." 
        class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
      >
      <button 
        type="submit" 
        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 active:scale-95 transition"
      >
        Search
      </button>
    </form>

    <!-- Add Topic -->
    <button 
      id="openAddTopic" 
      class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition"
    >
      <i class="fas fa-plus"></i> Add Topic
    </button>
  </div>

  <!-- Topics Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">#</th>
            <th class="p-3 border-b font-semibold">Title</th>
            <th class="p-3 border-b font-semibold">Description</th>
            <th class="p-3 border-b font-semibold">Module</th>
            <th class="p-3 border-b font-semibold text-center">Estimated Minutes</th>
            <th class="p-3 border-b font-semibold text-center">XP</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($topics_result)): ?>
            <?php $i = $offset + 1; foreach ($topics_result as $row): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3"><?= $i++; ?></td>
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['title']); ?></td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars(substr($row['description'], 0, 60)); ?>...</td>
                <td class="p-3 text-gray-600"><?= htmlspecialchars($row['module_title']); ?></td>
                <td class="p-3 text-center"><?= $row['estimated_minute']; ?></td>
                <td class="p-3 text-center"><?= $row['total_exp']; ?></td>
                <td class="p-3 flex justify-center gap-2">
                  
                  <!-- Assessment -->
                  <a href="assessment.php?topic_id=<?= $row['id']; ?>&module_id=<?= $row['module_id']; ?>" 
                     class="px-3 py-1 text-sm font-medium bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition">
                     Assessments
                  </a>

                  <!-- Edit -->
                  <button 
                     class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editTopicBtn"
                     data-id="<?= $row['id']; ?>"
                     data-title="<?= htmlspecialchars($row['title']); ?>"
                     data-description="<?= htmlspecialchars($row['description']); ?>"
                     data-content="<?= htmlspecialchars($row['content']); ?>"
                     data-module="<?= $row['module_id']; ?>"
                     data-minute="<?= $row['estimated_minute']; ?>"
                     data-exp="<?= $row['total_exp']; ?>">
                     <i class="fas fa-edit"></i>
                  </button>

                  <!-- Delete -->
                  <a href="topics_code.php?id=<?= $row['id']; ?>&module_id=<?= $row['module_id']; ?>"
                     onclick="return confirm('Delete this topic?');"
                     class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition">
                     <i class="fa fa-trash"></i>
                  </a>

                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="p-6 text-center text-gray-500">No topics found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="mt-6 flex justify-center flex-wrap gap-2">
        <?php for($p=1; $p<=$total_pages; $p++): ?>
          <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>&module_id=<?= $module_id; ?>" 
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

  <!-- Back Button -->
  <div class="mt-6 text-right">
      <a href="module.php" 
         class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
         ← Back to Modules
      </a>
  </div>
</div>

<!-- Add Topic Modal -->
<div id="addTopicModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeAddTopic"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="addModalContent">
    <h3 class="text-xl font-bold mb-4">Add New Topic</h3>
    <form method="POST" action="topics_code.php">
      <input type="hidden" name="action" value="add">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Title</label>
        <input type="text" name="title" class="w-full p-3 border rounded-lg" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Description</label>
        <textarea name="description" class="w-full p-3 border rounded-lg" rows="3"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Content</label>
        <textarea name="content" class="w-full p-3 border rounded-lg" rows="4"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Module</label>
        <select name="module_id" class="w-full p-3 border rounded-lg" required>
          <option value="">-- Select Module --</option>
          <?php foreach ($modules as $module): ?>
            <option value="<?= $module['id']; ?>"><?= htmlspecialchars($module['title']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Estimated Minutes</label>
        <input type="number" name="estimated_minute" class="w-full p-3 border rounded-lg">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">XP</label>
        <input type="number" name="total_exp" class="w-full p-3 border rounded-lg">
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelAddTopic" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Topic Modal -->
<div id="editTopicModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black bg-opacity-40" id="closeEditTopic"></div>
  <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-xl p-6 overflow-y-auto transform translate-x-full transition-transform duration-300" id="editModalContent">
    <h3 class="text-xl font-bold mb-4">Edit Topic</h3>
    <form method="POST" action="topics_code.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editTopicId">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Title</label>
        <input type="text" name="title" id="editTopicTitle" class="w-full p-3 border rounded-lg" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Description</label>
        <textarea name="description" id="editTopicDescription" class="w-full p-3 border rounded-lg" rows="3"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Content</label>
        <textarea name="content" id="editTopicContent" class="w-full p-3 border rounded-lg" rows="4"></textarea>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Module</label>
        <select name="module_id" id="editTopicModule" class="w-full p-3 border rounded-lg" required>
          <?php foreach ($modules as $module): ?>
            <option value="<?= $module['id']; ?>"><?= htmlspecialchars($module['title']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Estimated Minutes</label>
        <input type="number" name="estimated_minute" id="editTopicMinute" class="w-full p-3 border rounded-lg">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">XP</label>
        <input type="number" name="total_exp" id="editTopicExp" class="w-full p-3 border rounded-lg">
      </div>
      <div class="flex justify-end space-x-2">
        <button type="button" id="cancelEditTopic" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Fade animation
  document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 200));

  // Add Modal
  const addBtn = document.getElementById('openAddTopic');
  const addModal = document.getElementById('addTopicModal');
  const addModalContent = document.getElementById('addModalContent');
  document.getElementById('closeAddTopic').onclick = closeAdd;
  document.getElementById('cancelAddTopic').onclick = closeAdd;
  function openAdd() { addModal.classList.remove('hidden'); setTimeout(() => addModalContent.classList.remove('translate-x-full'), 10); }
  function closeAdd() { addModalContent.classList.add('translate-x-full'); setTimeout(() => addModal.classList.add('hidden'), 300); }
  addBtn.onclick = openAdd;

  // Edit Modal
  const editBtns = document.querySelectorAll('.editTopicBtn');
  const editModal = document.getElementById('editTopicModal');
  const editModalContent = document.getElementById('editModalContent');
  document.getElementById('closeEditTopic').onclick = closeEdit;
  document.getElementById('cancelEditTopic').onclick = closeEdit;

  editBtns.forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      document.getElementById('editTopicId').value = btn.dataset.id;
      document.getElementById('editTopicTitle').value = btn.dataset.title;
      document.getElementById('editTopicDescription').value = btn.dataset.description;
      document.getElementById('editTopicContent').value = btn.dataset.content;
      document.getElementById('editTopicModule').value = btn.dataset.module;
      document.getElementById('editTopicMinute').value = btn.dataset.minute;
      document.getElementById('editTopicExp').value = btn.dataset.exp;
      editModal.classList.remove('hidden');
      setTimeout(() => editModalContent.classList.remove('translate-x-full'), 10);
    }
  });
  function closeEdit() { editModalContent.classList.add('translate-x-full'); setTimeout(() => editModal.classList.add('hidden'), 300); }
</script>
</body>
</html>
