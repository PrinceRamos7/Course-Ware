<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total courses
$count_sql = "SELECT COUNT(*) AS total FROM courses";
if ($search !== '') $count_sql .= " WHERE title LIKE :search OR description LIKE :search";
$stmt = $conn->prepare($count_sql);
if ($search !== '') $stmt->execute(['search' => "%$search%"]);
else $stmt->execute();
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_courses / $limit);

// Fetch courses with lesson count
$courses_sql = "SELECT c.*, COUNT(m.id) AS lesson_count 
                FROM courses c 
                LEFT JOIN modules m ON c.id = m.course_id";
if ($search !== '') $courses_sql .= " WHERE c.title LIKE :search OR c.description LIKE :search";
$courses_sql .= " GROUP BY c.id ORDER BY c.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($courses_sql);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$courses_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Courses - FixLearn</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
  .fade-slide.show { opacity: 1; transform: translateY(0); }
  .sidebar-modal { position: fixed; top: 0; right: 0; height: 100%; width: 96; max-width: 24rem; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; overflow-y: auto; box-shadow: -4px 0 12px rgba(0,0,0,0.2); }
  .sidebar-modal.show { transform: translateX(0); }
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }

    .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
  .fade-slide.show { opacity: 1; transform: translateY(0); }
  
  .sidebar-modal { 
    position: fixed; 
    top: 0; 
    right: 0; 
    height: 100%; 
    width: 96; 
    max-width: 32rem; /* increased width */
    background: white; 
    z-index: 50; 
    transform: translateX(100%); 
    transition: transform 0.3s ease; 
    overflow-y: auto; 
    box-shadow: -4px 0 12px rgba(0,0,0,0.2); 
    padding: 1.5rem;
  }
  .sidebar-modal.show { transform: translateX(0); }
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8 rounded-2xl">
    <h1 class="text-2xl font-bold">Courses Page</h1>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition">
      <i class="fas fa-user-circle text-2xl"></i><span>Admin</span>
    </button>
  </header>

<!-- Search + Add -->
<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
  <form method="GET" class="flex w-full sm:w-auto gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search courses..." class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
    <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">Search</button>
  </form>
  <button id="openAddCourse" class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 transition">
    <i class="fas fa-plus"></i> Add Course
  </button>
</div>

<!-- Courses Table -->
<div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden fade-slide">
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
          <th class="p-3 border-b font-semibold">#</th>
          <th class="p-3 border-b font-semibold">Title</th>
          <th class="p-3 border-b font-semibold">Description</th>
          <th class="p-3 border-b font-semibold text-center">Lessons</th>
          <th class="p-3 border-b font-semibold text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (!empty($courses_result)): ?>
          <?php $i = $offset + 1; foreach ($courses_result as $row): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="p-3"><?= $i++; ?></td>
              <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['title']); ?></td>
              <td class="p-3 text-gray-600"><?= htmlspecialchars(substr($row['description'], 0, 60)); ?>...</td>
              <td class="p-3 text-center"><?= $row['lesson_count']; ?></td>
              <td class="p-3 flex justify-center gap-3">
                <a href="module.php?course_id=<?= $row['id']; ?>" class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">Modules</a>
                <button class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition viewStudentsBtn" data-id="<?= $row['id']; ?>" data-title="<?= htmlspecialchars($row['title']); ?>"><i class="fas fa-user-graduate"></i></button>
                <button class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editCourseBtn" data-id="<?= $row['id']; ?>" data-title="<?= htmlspecialchars($row['title']); ?>" data-description="<?= htmlspecialchars($row['description']); ?>"><i class="fas fa-edit"></i></button>
                <a href="course_code.php?id=<?= $row['id']; ?>" onclick="return confirm('Delete this course?');" class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition"><i class="fas fa-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="p-6 text-center text-gray-500">No courses found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex justify-center flex-wrap gap-2">
      <?php for($p=1; $p<=$total_pages; $p++): ?>
        <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>" class="px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition <?= $p==$page ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>"><?= $p; ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Sidebar Modals -->
<!-- Add Course Sidebar Modal -->
<div id="addCourseModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeAddCourse"></div>
  <div class="sidebar-modal" id="modalContent">
    <h3 class="text-2xl font-bold mb-4">Add New Course</h3>
    <form method="POST" action="course_code.php">
      <input type="hidden" name="action" value="add">
      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Title</label>
        <input type="text" name="title" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Description</label>
        <textarea name="description" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" rows="6" required></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelModal" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Course Sidebar Modal -->
<div id="editCourseModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeEditCourse"></div>
  <div class="sidebar-modal" id="editModalContent">
    <h3 class="text-2xl font-bold mb-4">Edit Course</h3>
    <form method="POST" action="course_code.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editCourseId">
      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Title</label>
        <input type="text" name="title" id="editCourseTitle" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500" required>
      </div>
      <div class="mb-4">
        <label class="block mb-1 text-gray-700">Description</label>
        <textarea name="description" id="editCourseDescription" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-green-500" rows="6" required></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditModal" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">Save</button>
      </div>
    </form>
  </div>
</div>
<!-- View Students -->
<div id="viewStudentsModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeViewStudents"></div>
  <div class="sidebar-modal">
    <h3 class="text-xl font-bold mb-4" id="studentsModalTitle">Enrolled Students</h3>
    <ul id="studentsList" class="divide-y divide-gray-100 text-gray-700 max-h-[70vh] overflow-y-auto"></ul>
    <div class="flex justify-end mt-4">
      <button id="cancelViewStudents" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition">Close</button>
    </div>
  </div>
</div>

<script>
  // Fade table animation
  document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 200));

  // Add Modal
  const openAddBtn = document.getElementById('openAddCourse');
  const addModal = document.getElementById('addCourseModal');
  const addModalContent = document.getElementById('modalContent');
  const closeAddBtn = document.getElementById('closeAddCourse');
  const cancelAddBtn = document.getElementById('cancelModal');
  function openAddModal() { addModal.classList.remove('hidden'); setTimeout(() => addModalContent.classList.add('show'), 10);}
  function closeAddModal() { addModalContent.classList.remove('show'); setTimeout(() => addModal.classList.add('hidden'), 300);}
  openAddBtn.addEventListener('click', openAddModal);
  closeAddBtn.addEventListener('click', closeAddModal);
  cancelAddBtn.addEventListener('click', closeAddModal);

  // Edit Modal
  const editBtns = document.querySelectorAll('.editCourseBtn');
  const editModal = document.getElementById('editCourseModal');
  const editModalContent = document.getElementById('editModalContent');
  const closeEditBtn = document.getElementById('closeEditCourse');
  const cancelEditBtn = document.getElementById('cancelEditModal');

  editBtns.forEach(btn => btn.addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('editCourseId').value = btn.dataset.id;
    document.getElementById('editCourseTitle').value = btn.dataset.title;
    document.getElementById('editCourseDescription').value = btn.dataset.description;
    editModal.classList.remove('hidden'); setTimeout(() => editModalContent.classList.add('show'), 10);
  }));
  function closeEditModal() { editModalContent.classList.remove('show'); setTimeout(() => editModal.classList.add('hidden'), 300);}
  closeEditBtn.addEventListener('click', closeEditModal);
  cancelEditBtn.addEventListener('click', closeEditModal);

  // View Students Modal
  const viewBtns = document.querySelectorAll('.viewStudentsBtn');
  const viewModal = document.getElementById('viewStudentsModal');
  const viewModalContent = viewModal.querySelector('.sidebar-modal');
  const closeViewBtn = document.getElementById('closeViewStudents');
  const cancelViewBtn = document.getElementById('cancelViewStudents');
  const studentsList = document.getElementById('studentsList');
  const studentsModalTitle = document.getElementById('studentsModalTitle');

  viewBtns.forEach(btn => btn.addEventListener('click', async e => {
    const courseId = btn.dataset.id;
    const courseTitle = btn.dataset.title;
    studentsModalTitle.textContent = `Enrolled Students - ${courseTitle}`;
    studentsList.innerHTML = '<li class="p-3 text-gray-500">Loading...</li>';
    try {
      const res = await fetch(`students_in_course.php?course_id=${courseId}`);
      const data = await res.json();
      studentsList.innerHTML = data.length ? data.map(s => `<li class="p-3">${s.first_name} ${s.last_name}</li>`).join('') : '<li class="p-3 text-gray-500">No students enrolled.</li>';
    } catch(err) { studentsList.innerHTML = '<li class="p-3 text-red-500">Error loading students.</li>'; console.error(err);}
    viewModal.classList.remove('hidden'); setTimeout(() => viewModalContent.classList.add('show'), 10);
  }));
  function closeViewModal() { viewModalContent.classList.remove('show'); setTimeout(() => viewModal.classList.add('hidden'), 300);}
  closeViewBtn.addEventListener('click', closeViewModal);
  cancelViewBtn.addEventListener('click', closeViewModal);
</script>
</body>
</html>
