<?php
require __DIR__ . '/../config.php';

// --- PHP DATA LOGIC (SQL CLEANED FOR WHITESPACE) ---
// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$offset = ($page - 1) * $limit;

// Count total
$count_sql = "SELECT COUNT(*) FROM registration_codes WHERE code LIKE :search";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute(['search' => "%$search%"]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

//Count Query
$count_sql = "SELECT COUNT(*) FROM registration_codes WHERE code LIKE :search";
if ($status !== '') {
    $count_sql .= " AND active = :status";
}
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($status !== '') {
    $count_stmt->bindValue(':status', $status, PDO::PARAM_INT);
}
$count_stmt->execute();
$total_items = $count_stmt->fetchColumn();

$sql = "SELECT rc.*, 
               c.title AS course_title, 
               COUNT(rcu.id) AS used_count
        FROM registration_codes rc
        LEFT JOIN registration_code_uses rcu ON rc.id = rcu.registration_code_id
        LEFT JOIN courses c ON rc.course_id = c.id
        WHERE rc.code LIKE :search";

if ($status !== '') {
    $sql .= " AND rc.active = :status";
}

$sql .= " GROUP BY rc.id
          ORDER BY rc.created_at DESC
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($status !== '') {
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- MOCK COURSE DATA for MODALS (Required for the form SELECT) ---
$courses = [
    ['id' => 1, 'title' => 'Calculus 101'],
    ['id' => 2, 'title' => 'Intro to Programming'],
    ['id' => 3, 'title' => 'Digital Arts Theory'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>ISUtoLearn - Registration Codes</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" type="image/png" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>

    .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
    .fade-slide.show { opacity: 1; transform: translateY(0); }

    /* Modal Styles using custom variables */
    .sidebar-modal { 
        position: fixed; top: 0; right: 0; height: 100%; max-width: 32rem; 
        background: var(--color-card-bg); /* Mapped to root var */
        z-index: 50; 
        transform: translateX(100%); 
        transition: transform 0.3s ease; 
        overflow-y: auto; 
        box-shadow: -4px 0 12px rgba(0,0,0,0.2); 
        padding: 1.5rem; 
    }
    .sidebar-modal.show { transform: translateX(0); }
    .modal-overlay { 
        position: fixed; inset: 0; 
        background: var(--color-popup-bg); /* Mapped to root var */
        z-index: 40; 
    }
 
    /* Utility classes for custom colors (mapping root vars) */
    .bg-main-bg { background-color: var(--color-main-bg); }
    .bg-card-bg { background-color: var(--color-card-bg); }
    .text-heading { color: var(--color-heading); }
    .text-heading-secondary { color: var(--color-heading-secondary); }
    .text-color-text { color: var(--color-text); }
    .text-color-text-secondary { color: var(--color-text-secondary); }
    
    /* Button & Input Theming */
    .bg-button-primary { background-color: var(--color-button-primary); }
    .hover-bg-button-primary-hover:hover { background-color: var(--color-button-primary-hover); }
    .bg-button-secondary { background-color: var(--color-button-secondary); }
    .hover-bg-button-secondary-hover:hover { background-color: var(--color-button-secondary-hover); }
    .text-button-secondary-text { color: var(--color-button-secondary-text); }
    .border-card-border { border-color: var(--color-card-border); }
    .border-input-border { border-color: var(--color-input-border); }
    .ring-icon { --tw-ring-color: var(--color-icon); }
    .bg-color-input-bg { background-color: var(--color-input-bg); }
    
    /* Table & Badge Theming */
    .bg-table-header { background-color: var(--color-card-section-bg); } /* Using card-section-bg for table header */
    .text-table-header { color: var(--color-text-secondary); }
    .bg-active-badge { background-color: var(--color-green-button); color: var(--color-card-bg); } /* Green button color with white/card-bg text */
    .bg-inactive-badge { background-color: var(--color-red-badge); color: var(--color-red-badge-text); }
    .text-action-primary { color: var(--color-button-primary); }
    .bg-action-primary-light { background-color: var(--color-button-primary-light); }
    .bg-action-danger-light { background-color: var(--color-red-badge); color: var(--color-red-badge-text); } 
    
    /* View modal special background */
        .header-bg { 
        background-color: var(--color-card-bg); /* Use pure white for header */
        border-bottom: 2px solid var(--color-sidebar-border); /* Stronger header line using ISU yellow */
    }
        .main-content-wrapper {
    margin-left: 4rem; /* Default margin-left: w-16 (4 units = 4rem) */
    transition: margin-left 0.3s ease-in-out;
}
#sidebar:hover ~ .main-content-wrapper {
    margin-left: 14rem; /* New margin-left: w-56 (14 units = 14rem) */
}
body{
  padding:0;
}
    .bg-view-user { background-color: var(--color-card-section-bg); }
    .border-card-section-border { border-color: var(--color-card-border); } /* Using card-border for user box border */
</style>
</head>
<body class="bg-main-bg min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-grow main-content-wrapper flex flex-col">
    
    <?php 
  
    include "header.php";
    renderHeader("Registration Codes")
    ?>

    <div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3">
<form method="GET" class="flex w-full sm:w-auto gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search codes..." 
        class="w-full sm:w-72 px-4 py-2 border border-input-border bg-color-input-bg rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text">

    <select name="status" class="px-3 py-2 border border-input-border bg-color-input-bg rounded-lg text-color-text">
        <option value="">All Status</option>
        <option value="1" <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
    </select>

    <button type="submit" 
        class="px-5 py-2 bg-button-primary text-white font-medium rounded-lg shadow hover-bg-button-primary-hover transition">
        <i class="fas fa-search"></i>
    </button>
</form>

        <button id="openAddModal" class="flex items-center gap-2 px-5 py-2 bg-button-primary text-white font-medium rounded-lg shadow hover-bg-button-primary-hover transition">
            <i class="fas fa-plus"></i> Add Code
        </button>
    </div>

    <div class="bg-card-bg pb-6 m-6 rounded-2xl shadow-xl border border-card-border overflow-hidden fade-slide">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-table-header text-table-header text-sm uppercase tracking-wider">
                        <th class="p-3 border-b-2 border-card-border font-semibold">Code</th>
                        <th class="p-3 border-b-2 border-card-border font-semibold">Course</th>
                        <th class="p-3 border-b-2 border-card-border font-semibold text-center">Used</th>
                        <th class="p-3 border-b-2 border-card-border font-semibold text-center">Status</th>
                        <th class="p-3 border-b-2 border-card-border font-semibold">Expires At</th>
                        <th class="p-3 border-b-2 border-card-border font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-card-border">
                    <?php if ($codes): ?>
                        <?php foreach ($codes as $row): ?>
                            <tr class="hover:bg-table-header transition">
                                <td class="p-3 font-bold text-heading"><?= htmlspecialchars($row['code']); ?></td>
                                <td class="p-3 text-color-text-secondary"><?= htmlspecialchars($row['course_title'] ?? 'N/A'); ?></td>
                                <td class="p-3 text-center text-color-text"><?= $row['used_count']; ?></td>
                                <td class="p-3 text-center">
                                    <?= $row['active'] 
                                        ? '<span class="px-3 py-1 text-xs font-bold bg-active-badge rounded-full shadow-sm">Active</span>' 
                                        : '<span class="px-3 py-1 text-xs font-bold bg-inactive-badge rounded-full shadow-sm">Inactive</span>'; ?>
                                </td>
                                <td class="p-3 text-color-text-secondary text-sm"><?= $row['expires_at'] ?? 'N/A'; ?></td>
                                <td class="p-3 flex justify-center gap-2">
                                    <button class="viewBtn px-3 py-1 text-sm font-medium bg-action-primary-light text-action-primary rounded-lg hover:bg-button-primary-hover transition"
                                        data-id="<?= $row['id']; ?>"
                                        data-code="<?= htmlspecialchars($row['code']); ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="editBtn px-3 py-1 text-sm font-medium bg-button-secondary text-button-secondary-text rounded-lg hover-bg-button-secondary-hover transition" 
                                        data-id="<?= $row['id']; ?>"
                                        data-code="<?= htmlspecialchars($row['code']); ?>"
                                        data-course="<?= $row['course_id']; ?>"
                                        data-active="<?= $row['active']; ?>"
                                        data-expires="<?= $row['expires_at']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="registration_code.php?action=delete&id=<?= $row['id']; ?>" onclick="return confirm('Delete this code?');" 
                                        class="px-3 py-1 text-sm font-medium bg-action-danger-light rounded-lg hover:opacity-80 transition">
                                        <i class="fas fa-trash text-red-500"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-4 text-center text-color-text-secondary">No codes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-center space-x-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
           <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" 
   class="px-4 py-2 rounded-lg text-sm font-bold shadow transition 
   <?= $i == $page ? 'bg-button-primary text-white' : 'bg-button-secondary text-button-secondary-text hover-bg-button-secondary-hover' ?>">
   <?= $i ?>
</a>
        <?php endfor; ?>
    </div>
</div>
 
<div id="viewModal" class="hidden modal-overlay flex items-center justify-center">
  <div id="viewContent" class="sidebar-modal w-full sm:w-[32rem]">
    <h2 class="text-2xl font-extrabold mb-4 text-heading">User of Code <span id="viewCodeName" class="text-heading-secondary"></span></h2>
    <div id="viewUsers" class="space-y-3">
      <p class="text-color-text-secondary">Loading...</p>
    </div>
    <div class="flex justify-end mt-6">
        <button type="button" id="closeView" class="px-5 py-2 bg-button-secondary text-button-secondary-text rounded-lg hover-bg-button-secondary-hover transition font-medium shadow">Close</button>
    </div>
  </div>
</div>

<div id="editModal" class="hidden modal-overlay flex items-center justify-center">
  <div id="editContent" class="sidebar-modal w-full sm:w-[32rem]">
    <h2 class="text-2xl font-extrabold mb-6 text-heading">Edit Code <span id="editCodeName" class="text-heading-secondary"></span></h2>

    <form id="editForm" method="POST" action="edit_registration_code.php" class="space-y-4">
        <input type="hidden" name="id" id="editId">

        <div>
            <label for="editCode" class="block text-sm font-medium text-color-text">Code</label>
            <input type="text" name="code" id="editCode" 
                class="mt-1 block w-full px-3 py-2 border border-input-border rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text bg-color-input-bg" required>
        </div>

        <div>
            <label for="editCourse" class="block text-sm font-medium text-color-text">Course</label>
            <select name="course_id" id="editCourse" 
                class="mt-1 block w-full px-3 py-2 border border-input-border bg-color-input-bg rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text" required>
                <option value="">Select a Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label for="editExpires" class="block text-sm font-medium text-color-text">Expires At</label>
            <input type="datetime-local" name="expires_at" id="editExpires" 
                class="mt-1 block w-full px-3 py-2 border border-input-border rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text bg-color-input-bg">
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="active" id="editActive" value="1" 
                class="h-4 w-4 text-button-primary border-gray-300 rounded focus:ring-icon">
            <label for="editActive" class="text-sm font-medium text-color-text">Active</label>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <button type="button" id="closeEdit" class="px-4 py-2 bg-button-secondary text-button-secondary-text rounded-lg hover-bg-button-secondary-hover transition font-medium shadow">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-button-primary text-white font-medium rounded-lg shadow hover-bg-button-primary-hover transition">Save Changes</button>
        </div>
    </form>
  </div>
</div>

<div id="addModal" class="hidden modal-overlay flex items-center justify-center">
  <div id="addContent" class="sidebar-modal w-full sm:w-[32rem]">
    <h2 class="text-2xl font-extrabold mb-6 text-heading">Add New Code</h2>

    <form id="addForm" method="POST" action="add_registration_code.php" class="space-y-4">
        <div>
            <label for="addCode" class="block text-sm font-medium text-color-text">Code</label>
            <input type="text" name="code" id="addCode" 
                class="mt-1 block w-full px-3 py-2 border border-input-border rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text bg-color-input-bg" required>
        </div>

        <div>
            <label for="addCourse" class="block text-sm font-medium text-color-text">Course</label>
            <select name="course_id" id="addCourse" 
                class="mt-1 block w-full px-3 py-2 border border-input-border bg-color-input-bg rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text" required>
                <option value="">Select a Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label for="addExpires" class="block text-sm font-medium text-color-text">Expires At (Optional)</label>
            <input type="datetime-local" name="expires_at" id="addExpires" 
                class="mt-1 block w-full px-3 py-2 border border-input-border rounded-lg shadow-sm focus:outline-none focus:ring-2 ring-icon transition text-color-text bg-color-input-bg">
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="active" id="addActive" value="1" checked 
                class="h-4 w-4 text-button-primary border-gray-300 rounded focus:ring-icon">
            <label for="addActive" class="text-sm font-medium text-color-text">Active</label>
        </div>

        <div class="flex justify-end pt-4 space-x-3">
            <button type="button" id="closeAdd" class="px-4 py-2 bg-button-secondary text-button-secondary-text rounded-lg hover-bg-button-secondary-hover transition font-medium shadow">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-button-primary text-white font-medium rounded-lg shadow hover-bg-button-primary-hover transition">Create Code</button>
        </div>
    </form>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    // Show the table after a slight delay for the fade-slide effect
    document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));

    // --- Modal References ---
    const viewModal = document.getElementById("viewModal");
    const viewContent = document.getElementById("viewContent");
    const viewCodeName = document.getElementById("viewCodeName");
    const viewUsers = document.getElementById("viewUsers"); // Added missing viewUsers reference

    const editModal = document.getElementById("editModal");
    const editContent = document.getElementById("editContent");
    const editCodeName = document.getElementById("editCodeName");
    const editId = document.getElementById("editId");
    const editCode = document.getElementById("editCode");
    const editCourse = document.getElementById("editCourse");
    const editActive = document.getElementById("editActive");
    const editExpires = document.getElementById("editExpires");

    const addModal = document.getElementById("addModal");
    const addContent = document.getElementById("addContent");


    // --------------------------------
    // 1. VIEW MODAL LOGIC 👁️
    // --------------------------------
    document.querySelectorAll(".viewBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            viewCodeName.textContent = btn.dataset.code;
            viewUsers.innerHTML = "<p class='text-color-text-secondary animate-pulse'>Loading...</p>";
            viewModal.classList.remove("hidden"); viewContent.classList.add("show");

            fetch("view_code_users.php?id=" + btn.dataset.id)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        viewUsers.innerHTML = "<p class='text-action-danger-light'>Error: " + data.error + "</p>";
                        return;
                    }
                    if (data.length > 0) {
                        viewUsers.innerHTML = data.map(u => `
                            <div class="p-3 border rounded-lg shadow-sm bg-view-user border-card-section-border"> 
                                <p class="font-semibold text-color-text">${u.first_name} ${u.last_name}</p> 
                                <p class="text-sm text-color-text-secondary">Used at: ${u.used_at}</p> 
                            </div> 
                        `).join("");
                    } else {
                        viewUsers.innerHTML = "<p class='text-color-text-secondary'>No students used this code yet. 🥺</p>";
                    }
                })
                .catch(err => {
                    viewUsers.innerHTML = "<p class='text-action-danger-light'>Error loading users.</p>";
                    console.error(err);
                });
        });
    });

    document.getElementById("closeView").addEventListener("click", () => {
        viewContent.classList.remove("show"); 
        setTimeout(() => viewModal.classList.add("hidden"), 300); 
    });

    // --------------------------------
    // 2. EDIT MODAL LOGIC ✏️
    // --------------------------------
    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            // 1. Populate the form fields with data attributes
            editId.value = btn.dataset.id;
            editCode.value = btn.dataset.code;
            editCodeName.textContent = btn.dataset.code;
            editCourse.value = btn.dataset.course; 
            editActive.checked = btn.dataset.active === '1'; 
            
            // Handle datetime-local format conversion
            const expiresAt = btn.dataset.expires;
            if (expiresAt && expiresAt !== '0000-00-00 00:00:00') {
                const datePart = expiresAt.substring(0, 10);
                const timePart = expiresAt.substring(11, 16);
                editExpires.value = `${datePart}T${timePart}`;
            } else {
                editExpires.value = '';
            }

            // 2. Show the modal
            editModal.classList.remove("hidden");
            editContent.classList.add("show");
        });
    });

    document.getElementById("closeEdit").addEventListener("click", () => {
        editContent.classList.remove("show");
        setTimeout(() => editModal.classList.add("hidden"), 300); 
    });
    
    // --------------------------------
    // 3. ADD MODAL LOGIC ➕
    // --------------------------------
    document.getElementById("openAddModal").addEventListener("click", () => {
        document.getElementById("addForm").reset();

        addModal.classList.remove("hidden");
        addContent.classList.add("show");
    });

    document.getElementById("closeAdd").addEventListener("click", () => {
        addContent.classList.remove("show");
        setTimeout(() => addModal.classList.add("hidden"), 300); 
    });
});
</script>
</body>
</html>