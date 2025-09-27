<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total codes
$count_sql = "SELECT COUNT(*) AS total FROM registration_codes";
if ($search !== '') {
    $count_sql .= " WHERE code LIKE :search";
}
$stmt = $conn->prepare($count_sql);
if ($search !== '') $stmt->execute(['search' => "%$search%"]);
else $stmt->execute();
$total_codes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_codes / $limit);

// Fetch codes with usage count
$codes_sql = "
    SELECT rc.*, COUNT(rcu.id) AS used_count
    FROM registration_codes rc
    LEFT JOIN registration_code_uses rcu ON rc.id = rcu.registration_code_id
";
if ($search !== '') $codes_sql .= " WHERE rc.code LIKE :search";
$codes_sql .= " GROUP BY rc.id ORDER BY rc.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($codes_sql);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$codes_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Registration Codes - Admin Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
.sidebar-modal { 
  position: absolute; right:0; top:0; height:100%; width:28rem; 
  background:white; padding:1.5rem; overflow-y:auto; 
  transform: translateX(100%); transition: transform 0.3s ease; 
}
.sidebar-modal.show { transform: translateX(0); }
.modal-overlay { position:absolute; inset:0; background:black; opacity:0.4; }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col p-6">
  <!-- Header -->
  <header class="bg-white shadow-md p-6 flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold">Registration Codes</h1>
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
        placeholder="Search codes..." 
        class="w-full sm:w-72 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
      >
      <button 
        type="submit" 
        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 active:scale-95 transition"
      >
        Search
      </button>
    </form>

    <!-- Add Code -->
    <button 
      id="openAddCode" 
      class="flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition"
    >
      <i class="fas fa-plus"></i> Add Code
    </button>
  </div>

  <!-- Codes Table -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">#</th>
            <th class="p-3 border-b font-semibold">Code</th>
            <th class="p-3 border-b font-semibold">Course ID</th>
            <th class="p-3 border-b font-semibold text-center">Active</th>
            <th class="p-3 border-b font-semibold text-center">Used</th>
            <th class="p-3 border-b font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($codes_result)): ?>
            <?php $i = $offset + 1; foreach ($codes_result as $row): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3"><?= $i++; ?></td>
                <td class="p-3 font-semibold text-gray-800"><?= htmlspecialchars($row['code']); ?></td>
                <td class="p-3 text-center"><?= $row['course_id']; ?></td>
                <td class="p-3 text-center">
                  <?= $row['active'] ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Yes</span>' 
                                     : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">No</span>'; ?>
                </td>
                <td class="p-3 text-center">
                  <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs"><?= $row['used_count']; ?></span>
                </td>
                <td class="p-3 flex justify-center gap-3">
                  <!-- View students button -->
                  <button 
                    class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition viewCodeBtn"
                    data-id="<?= $row['id']; ?>"
                    data-code="<?= htmlspecialchars($row['code']); ?>">
                    <i class="fas fa-eye"></i>
                  </button>

                  <!-- Edit button -->
                  <button 
                     class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition editCodeBtn"
                     data-id="<?= $row['id']; ?>"
                     data-code="<?= htmlspecialchars($row['code']); ?>"
                     data-course="<?= $row['course_id']; ?>"
                     data-active="<?= $row['active']; ?>"
                     data-expires="<?= $row['expires_at']; ?>">
                     <i class="fas fa-edit"></i>
                  </button>

                  <!-- Delete button -->
                  <a href="registration_code_code.php?id=<?= $row['id']; ?>" 
                     onclick="return confirm('Delete this code?');"
                     class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                     <i class="fas fa-trash"></i> 
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="p-6 text-center text-gray-500">No registration codes found.</td>
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
             <?= $p==$page ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
            <?= $p; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Code Modal -->
<div id="addCodeModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeAddCode"></div>
  <div class="sidebar-modal" id="addCodeContent">
    <h3 class="text-2xl font-bold mb-4">Add Registration Code</h3>
    <form method="POST" action="registration_code_code.php">
      <input type="hidden" name="action" value="add">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Code</label>
        <input type="text" name="code" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Course ID</label>
        <input type="number" name="course_id" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Expires At</label>
        <input type="datetime-local" name="expires_at" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAddCode" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" name="btn_add" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Code Modal -->
<div id="editCodeModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeEditCode"></div>
  <div class="sidebar-modal" id="editCodeContent">
    <h3 class="text-2xl font-bold mb-4">Edit Registration Code</h3>
    <form method="POST" action="registration_code_code.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editCodeId">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Code</label>
        <input type="text" name="code" id="editCode" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Course ID</label>
        <input type="number" name="course_id" id="editCourse" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Active</label>
        <select name="active" id="editActive" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">Expires At</label>
        <input type="datetime-local" name="expires_at" id="editExpires" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditCode" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Cancel</button>
        <button type="submit" name="btn_edit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- View Students Modal -->
<div id="viewStudentsModal" class="hidden fixed inset-0 z-50 flex">
  <div class="modal-overlay" id="closeViewStudents"></div>
  <div class="sidebar-modal" id="viewStudentsContent">
    <h3 class="text-2xl font-bold mb-4">Students Using Code: <span id="viewCodeLabel"></span></h3>
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
            <th class="p-3 border-b font-semibold">#</th>
            <th class="p-3 border-b font-semibold">Student Name</th>
            <th class="p-3 border-b font-semibold">Email</th>
            <th class="p-3 border-b font-semibold">Used At</th>
          </tr>
        </thead>
        <tbody id="viewStudentsTable" class="divide-y divide-gray-100">
          <tr><td colspan="4" class="p-6 text-center text-gray-500">Loading...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="flex justify-end mt-4">
      <button type="button" id="cancelViewStudents" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">Close</button>
    </div>
  </div>
</div>

<script>
  // Add Modal
  const openAddBtn = document.getElementById('openAddCode');
  const addModal = document.getElementById('addCodeModal');
  const addContent = document.getElementById('addCodeContent');
  const closeAddBtn = document.getElementById('closeAddCode');
  const cancelAddBtn = document.getElementById('cancelAddCode');
  function openAddModal(){addModal.classList.remove('hidden');setTimeout(()=>addContent.classList.add('show'),10);}
  function closeAddModal(){addContent.classList.remove('show');setTimeout(()=>addModal.classList.add('hidden'),300);}
  openAddBtn.addEventListener('click',openAddModal);
  closeAddBtn.addEventListener('click',closeAddModal);
  cancelAddBtn.addEventListener('click',closeAddModal);

  // Edit Modal
  const editBtns = document.querySelectorAll('.editCodeBtn');
  const editModal = document.getElementById('editCodeModal');
  const editContent = document.getElementById('editCodeContent');
  const closeEditBtn = document.getElementById('closeEditCode');
  const cancelEditBtn = document.getElementById('cancelEditCode');
  const editCodeId = document.getElementById('editCodeId');
  const editCode = document.getElementById('editCode');
  const editCourse = document.getElementById('editCourse');
  const editActive = document.getElementById('editActive');
  const editExpires = document.getElementById('editExpires');
  editBtns.forEach(btn=>{
    btn.addEventListener('click',()=>{
      editCodeId.value=btn.dataset.id;
      editCode.value=btn.dataset.code;
      editCourse.value=btn.dataset.course;
      editActive.value=btn.dataset.active;
      editExpires.value=btn.dataset.expires?btn.dataset.expires.replace(' ','T'):'';
      editModal.classList.remove('hidden');
      setTimeout(()=>editContent.classList.add('show'),10);
    });
  });
  function closeEditModal(){editContent.classList.remove('show');setTimeout(()=>editModal.classList.add('hidden'),300);}
  closeEditBtn.addEventListener('click',closeEditModal);
  cancelEditBtn.addEventListener('click',closeEditModal);

  // View Students Modal
  const viewBtns = document.querySelectorAll('.viewCodeBtn');
  const viewModal = document.getElementById('viewStudentsModal');
  const viewContent = document.getElementById('viewStudentsContent');
  const closeViewBtn = document.getElementById('closeViewStudents');
  const cancelViewBtn = document.getElementById('cancelViewStudents');
  const viewCodeLabel = document.getElementById('viewCodeLabel');
  const viewStudentsTable = document.getElementById('viewStudentsTable');

  viewBtns.forEach(btn=>{
    btn.addEventListener('click',async()=>{
      const codeId = btn.dataset.id;
      const codeLabel = btn.dataset.code;
      viewCodeLabel.textContent = codeLabel;
      viewStudentsTable.innerHTML='<tr><td colspan="4" class="p-6 text-center text-gray-500">Loading...</td></tr>';
      viewModal.classList.remove('hidden');
      setTimeout(()=>viewContent.classList.add('show'),10);
      try{
        const response = await fetch(`registration_code_view.php?code_id=${codeId}`);
        const data = await response.json();
        if(data.length===0){
          viewStudentsTable.innerHTML='<tr><td colspan="4" class="p-6 text-center text-gray-500">No student has used this code yet.</td></tr>';
        }else{
          viewStudentsTable.innerHTML=data.map((s,i)=>`
            <tr class="hover:bg-gray-50 transition">
              <td class="p-3">${i+1}</td>
              <td class="p-3">${s.first_name} ${s.last_name}</td>
              <td class="p-3">${s.email}</td>
              <td class="p-3">${s.used_at}</td>
            </tr>`).join('');
        }
      }catch(err){
        console.error(err);
        viewStudentsTable.innerHTML='<tr><td colspan="4" class="p-6 text-center text-red-500">Error loading students.</td></tr>';
      }
    });
  });

  function closeViewModal(){viewContent.classList.remove('show');setTimeout(()=>viewModal.classList.add('hidden'),300);}
  closeViewBtn.addEventListener('click',closeViewModal);
  cancelViewBtn.addEventListener('click',closeViewModal);
</script>
</body>
</html>
