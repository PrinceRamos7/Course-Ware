<?php
// =====================================================================
// 1. PHP LOGIC (Database Connection, Pagination, Search, and Fetch)
// =====================================================================
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

// Get current course title for header if applicable
$current_course_title = 'All Courses';
if ($course_id > 0) {
    $course_stmt = $conn->prepare("SELECT title FROM courses WHERE id = :id");
    $course_stmt->execute([':id' => $course_id]);
    $result = $course_stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $current_course_title = htmlspecialchars($result['title']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Modules - FixLearn</title>
<link rel="stylesheet" href="../output.css">
<link rel="stylesheet" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
    /* ===================================================================== */
    /* 2. EMBEDDED STYLES (Theming from Dashboard) */
    /* ===================================================================== */
    
    /* General Layout Class for consistency */
    .dashboard-container {
        padding: 1.5rem 2rem;
        gap: 1.5rem;
    }
    
    /* Header Styling matching the Dashboard */
    .header-bg { 
        background-color: var(--color-card-bg); 
        border-bottom: 2px solid var(--color-sidebar-border); 
    }

    /* Card Styling for main table wrapper */
    .card-bg {
        background-color: var(--color-card-bg);
        border: 1px solid var(--color-card-border);
    }
    
    /* Input field styling in modals/tables */
    .input-themed {
        background-color: var(--color-input-bg);
        border: 1px solid var(--color-input-border);
        color: var(--color-input-text);
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }
    .input-themed:focus {
        border-color: var(--color-icon); /* ISU Yellow focus */
        box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.5); 
        outline: none;
    }

    /* Side Modal Styles */
    .sidebar-modal { 
        position: fixed; 
        top: 0; 
        right: 0; 
        height: 100%; 
        width: 100%; 
        max-width: 32rem; 
        background: var(--color-popup-content-bg); 
        z-index: 50; 
        transform: translateX(100%); 
        transition: transform 0.3s ease; 
        overflow-y: auto; 
        box-shadow: -4px 0 12px rgba(0,0,0,0.2); 
        padding: 1.5rem;
    }
    .sidebar-modal.show { 
        transform: translateX(0); 
    }
    .modal-overlay { 
        position: fixed; 
        inset: 0; 
        background: var(--color-popup-bg); 
        z-index: 40; 
    }

    /* General Animation */
    .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
    .fade-slide.show { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-y-auto dashboard-container">
    <header class="header-bg shadow-lg p-4 flex justify-between items-center sticky top-0 z-10 rounded-lg mb-8">
        <h1 class="text-2xl font-extrabold text-[var(--color-heading)]">
            <i class="fas fa-layer-group mr-2 text-[var(--color-icon)]"></i> Module Management
            <span class="text-base font-normal text-[var(--color-text-secondary)] block sm:inline ml-0 sm:ml-4">
                &mdash; <?= $current_course_title; ?>
            </span>
        </h1>
        <button class="flex items-center space-x-2 px-3 py-2 text-sm rounded-md shadow bg-[var(--color-user-bg)] hover:bg-gray-200 transition text-[var(--color-user-text)] border border-[var(--color-card-border)]">
            <i class="fas fa-user-circle text-xl text-[var(--color-icon)]"></i>
            <span class="font-semibold hidden sm:inline">Admin</span>
        </button>
    </header>

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
        <form method="GET" class="flex w-full sm:w-auto gap-2">
            <?php if ($course_id > 0): ?>
                <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <?php endif; ?>
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search); ?>" 
                placeholder="Search modules..." 
                class="w-full sm:w-72 px-4 py-2 border rounded-lg shadow-inner input-themed focus:ring-[var(--color-heading)] focus:ring-2 transition placeholder-[var(--color-input-placeholder)]"
            >
            <button 
                type="submit" 
                class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-bold rounded-lg shadow-md hover:bg-[var(--color-button-primary-hover)] active:scale-95 transition"
            >
                <i class="fas fa-search"></i>
            </button>
        </form>

        <button 
            id="openAddModule" 
            class="flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading)] text-white font-bold rounded-lg shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-[1.02]"
        >
            <i class="fas fa-plus-circle"></i> Add Module
        </button>
    </div>

    <div class="card-bg p-8 rounded-xl shadow-xl border border-[var(--color-card-border)] overflow-hidden fade-slide">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider border-b border-[var(--color-card-section-border)]">
                        <th class="p-4 rounded-tl-xl font-bold">#</th>
                        <th class="p-4 font-bold">Title</th>
                        <th class="p-4 font-bold">Course</th>
                        <th class="p-4 font-bold">Description</th>
                        <th class="p-4 font-bold text-center">Score</th>
                        <th class="p-4 rounded-tr-xl font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-card-border)]">
                    <?php if (!empty($modules_result)): ?>
                        <?php $i = $offset + 1; foreach ($modules_result as $row): ?>
                            <tr class="hover:bg-yellow-50/10 transition duration-150">
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
                                <td class="p-4 font-semibold text-[var(--color-heading-secondary)]"><?= htmlspecialchars($row['title']); ?></td>
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= htmlspecialchars($row['course_title']); ?></td>
                                <td class="p-4 text-[var(--color-text-secondary)] italic">
                                    <?= htmlspecialchars(substr($row['description'], 0, 60)); ?><?= strlen($row['description']) > 60 ? '...' : ''; ?>
                                </td>
                                <td class="p-4 text-center font-bold text-[var(--color-heading)]"><?= $row['required_score']; ?>%</td>
                                <td class="p-4 flex justify-center flex-wrap gap-3">
                                    <a href="topics.php?module_id=<?= $row['id']; ?>" 
                                        class="px-4 py-2 text-sm font-semibold bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] rounded-full hover:bg-yellow-200 transition shadow-sm" title="View Topics"><i class="fas fa-list-ol mr-1"></i> Topics
                                    </a> 
                                    
                                    <button 
                                        class="px-3 py-2 text-sm font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition shadow-sm editModuleBtn"
                                        data-id="<?= $row['id']; ?>"
                                        data-title="<?= htmlspecialchars($row['title']); ?>"
                                        data-description="<?= htmlspecialchars($row['description']); ?>"
                                        data-course="<?= $row['course_id']; ?>"
                                        data-score="<?= $row['required_score']; ?>" title="Edit Module">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="module_code.php?action=delete&id=<?= $row['id']; ?>&course_id=<?= $course_id; ?>" 
                                        onclick="return confirm('Are you sure you want to delete this module?');"
                                        class="px-3 py-2 text-sm font-medium bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition shadow-sm" title="Delete Module">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-[var(--color-text-secondary)] font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No modules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center flex-wrap gap-2">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?><?= $course_id > 0 ? "&course_id=$course_id" : ""; ?>" 
                        class="px-5 py-2 rounded-full text-sm font-semibold shadow-md transition transform hover:scale-105 
                        <?= $p==$page 
                            ? 'bg-[var(--color-heading-secondary)] text-white' 
                            : 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-yellow-200'; ?>">
                        <?= $p; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 text-right">
        <a href="course.php" 
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-md">
            <i class="fas fa-arrow-left mr-2"></i> Back to Courses
        </a>
    </div>

    <div id="addModuleModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-overlay" id="closeAddModule"></div>
        <div class="sidebar-modal" id="modalContent">
            <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-folder-plus mr-2 text-[var(--color-icon)]"></i> Add New Module</h3>
            <form method="POST" action="module_code.php">
                <input type="hidden" name="action" value="add">
                <?php if ($course_id > 0): ?>
                    <input type="hidden" name="course_id" value="<?= $course_id; ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Title</label>
                    <input type="text" name="title" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" required>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Description</label>
                    <textarea name="description" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" rows="4"></textarea>
                </div>

                <div class="mb-6">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Required Score (%)</label>
                    <input type="number" name="required_score" min="0" max="100" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" value="70">
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                    <button type="button" id="cancelModal" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                        <i class="fas fa-check-circle mr-1"></i> Add Module
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModuleModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-overlay" id="closeEditModule"></div>
        <div class="sidebar-modal" id="editModalContent">
            <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-pen-to-square mr-2 text-[var(--color-icon)]"></i> Edit Module</h3>
            <form method="POST" action="module_code.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editModuleId">
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Title</label>
                    <input type="text" name="title" id="editModuleTitle" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" required>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Description</label>
                    <textarea name="description" id="editModuleDescription" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" rows="4"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Course</label>
                    <select name="course_id" id="editModuleCourse" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]" required>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block font-semibold mb-1 text-[var(--color-text)]">Required Score (%)</label>
                    <input type="number" name="required_score" min="0" max="100" id="editModuleScore" class="w-full p-3 rounded-lg input-themed focus:ring-[var(--color-icon)]">
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                    <button type="button" id="cancelEditModal" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-bold shadow-md">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fade animation
        document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 200));

        // --- Add Modal Logic ---
        const openBtn = document.getElementById('openAddModule');
        const closeBtn = document.getElementById('closeAddModule');
        const cancelBtn = document.getElementById('cancelModal');
        const modal = document.getElementById('addModuleModal');
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

        // --- Edit Modal Logic ---
        const editBtns = document.querySelectorAll('.editModuleBtn');
        const editModal = document.getElementById('editModuleModal');
        const editModalContent = document.getElementById('editModalContent');
        const closeEditBtn = document.getElementById('closeEditModule');
        const cancelEditBtn = document.getElementById('cancelEditModal');

        editBtns.forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                // Populate form fields
                document.getElementById('editModuleId').value = btn.dataset.id;
                document.getElementById('editModuleTitle').value = btn.dataset.title;
                document.getElementById('editModuleDescription').value = btn.dataset.description;
                document.getElementById('editModuleCourse').value = btn.dataset.course;
                document.getElementById('editModuleScore').value = btn.dataset.score;
                
                // Open modal
                editModal.classList.remove('hidden');
                setTimeout(() => editModalContent.classList.remove('translate-x-full'), 10);
            });
        });
        
        function closeEditModal() { 
            editModalContent.classList.add('translate-x-full'); 
            setTimeout(() => editModal.classList.add('hidden'), 300);
        }
        
        closeEditBtn.addEventListener('click', closeEditModal);
        cancelEditBtn.addEventListener('click', closeEditModal);
    </script>
</body>
</html>