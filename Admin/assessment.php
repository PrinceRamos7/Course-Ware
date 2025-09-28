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
$sql .= " ORDER BY id DESC LIMIT $offset, $limit";
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
<link rel="stylesheet" href="../output.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
    /* ===================================================================== */
    /* EMBEDDED STYLES (Theming from Dashboard) */
    /* ===================================================================== */
    
    .dashboard-container {
        padding: 1.5rem 2rem;
        gap: 1.5rem;
    }
    
    .header-bg { 
        background-color: var(--color-card-bg); 
        border-bottom: 2px solid var(--color-sidebar-border); 
    }

    .card-bg {
        background-color: var(--color-card-bg);
        border: 1px solid var(--color-card-border);
    }
    
    .input-themed {
        background-color: var(--color-input-bg);
        border: 1px solid var(--color-input-border);
        color: var(--color-input-text);
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }
    .input-themed:focus {
        border-color: var(--color-icon); 
        box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.5); 
        outline: none;
    }

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
    .sidebar-modal.show { /* ADDED/USED FOR JS ANIMATION */
        transform: translateX(0); 
    }
    .modal-overlay { 
        position: fixed; 
        inset: 0; 
        background: var(--color-popup-bg); 
        z-index: 40; 
    }

    .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
    .fade-slide.show { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-y-auto">
    <?php include 'header.php';
    renderHeader("ISU Admin Assessment: " . htmlspecialchars($module['title']))?>

    <div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3">
        <form method="GET" class="flex w-full sm:w-auto gap-2">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search); ?>" 
                placeholder="Search assessments..." 
                class="w-full sm:w-72 px-4 py-2 border rounded-lg shadow-inner input-themed focus:ring-[var(--color-heading)] focus:ring-2 transition placeholder-[var(--color-input-placeholder)]"
            >
            <button type="submit" 
                class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-bold rounded-lg shadow-md hover:bg-[var(--color-button-primary-hover)] active:scale-95 transition">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <button 
            id="openAddAssessment" 
            class="flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading)] text-white font-bold rounded-lg shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-[1.02]"
        >
            <i class="fas fa-plus-circle"></i> Add Assessment
        </button>
    </div>

    <div class="card-bg pb-2 rounded-md m-6 shadow-xl border border-[var(--color-card-border)] overflow-hidden fade-slide">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider border-b border-[var(--color-card-section-border)]">
                        <th class="p-4 rounded-tl-xl font-bold">#</th>
                        <th class="p-4 font-bold">Name</th>
                        <th class="p-4 font-bold">Type</th>
                        <th class="p-4 font-bold">Time Limit</th>
                        <th class="p-4 rounded-tr-xl font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-card-border)]">
                    <?php if (!empty($assessments)): ?>
                        <?php $i = $offset + 1; foreach ($assessments as $row): ?>
                            <tr class="hover:bg-yellow-50/10 transition duration-150">
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
                                <td class="p-4 font-semibold text-[var(--color-heading-secondary)]"><?= htmlspecialchars($row['name']); ?></td>
                                <td class="p-4 text-[var(--color-text)]">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                        <?= $row['type'] == 'module' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'; ?>">
                                        <?= ucfirst(htmlspecialchars($row['type'])); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-[var(--color-heading)] font-bold"><?= (int)$row['time_set']; ?> mins</td>
                                <td class="p-4 flex justify-center gap-3">
                                    <a href="questions.php?assessment_id=<?= $row['id']; ?>&module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>" 
                                        class="px-4 py-2 text-sm font-semibold bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] rounded-full hover:bg-yellow-200 transition shadow-sm" title="Manage Questions">
                                        <i class="fas fa-question-circle mr-1"></i> Questions
                                    </a> 
                                
                                    <button 
                                        class="px-3 py-2 text-sm font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition shadow-sm editAssessmentBtn"
                                        data-id="<?= $row['id']; ?>"
                                        data-name="<?= htmlspecialchars($row['name']); ?>"
                                        data-type="<?= htmlspecialchars($row['type']); ?>"
                                        data-time="<?= $row['time_set']; ?>" title="Edit Assessment">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <a href="assessment_code.php?action=delete&id=<?= $row['id']; ?>&module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>" 
                                        onclick="return confirm('Are you sure you want to delete this assessment?');"
                                        class="px-3 py-2 text-sm font-medium bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition shadow-sm" title="Delete Assessment">
                                        <i class="fas fa-trash-alt"></i> 
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-6 text-center text-[var(--color-text-secondary)] font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No assessments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center flex-wrap gap-2">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <a href="?module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>&page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
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
        <a href="module.php?course_id=<?= $course_id; ?>" 
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-md">
            <i class="fas fa-arrow-left mr-2"></i> Back to Modules
        </a>
    </div>
</div>

<div id="addAssessmentModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay" id="closeAddAssessment"></div>
    <div class="sidebar-modal" id="addModalContent">
        <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-plus-square mr-2 text-[var(--color-icon)]"></i> Add New Assessment</h3>
        <form method="POST" action="assessment_code.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Name</label>
                <input type="text" name="name" class="w-full p-3 rounded-lg input-themed" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Type</label>
                <select name="type" class="w-full p-3 rounded-lg input-themed">
                    <option value="module">Module</option>
                    <option value="topic">Topic</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Time Limit (in minutes)</label>
                <input type="number" name="time_set" min="1" value="30" class="w-full p-3 rounded-lg input-themed" required>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                <button type="button" id="cancelAddAssessment" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                    <i class="fas fa-check-circle mr-1"></i> Add Assessment
                </button>
            </div>
        </form>
        
    </div>
    
</div>

<div id="editAssessmentModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay" id="closeEditAssessment"></div>
    <div class="sidebar-modal" id="editModalContent">
        <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-pen-to-square mr-2 text-[var(--color-icon)]"></i> Edit Assessment</h3>
        <form method="POST" action="assessment_code.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editAssessmentId">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            
            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Name</label>
                <input type="text" name="name" id="editAssessmentName" class="w-full p-3 rounded-lg input-themed" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Type</label>
                <select name="type" id="editAssessmentType" class="w-full p-3 rounded-lg input-themed">
                    <option value="module">Module</option>
                    <option value="topic">Topic</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Time Limit (in minutes)</label>
                <input type="number" name="time_set" id="editAssessmentTime" min="1" class="w-full p-3 rounded-lg input-themed" required>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                <button type="button" id="cancelEditAssessment" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-bold shadow-md">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fade animation
    document.querySelectorAll('.fade-slide').forEach((el, i) => setTimeout(() => el.classList.add('show'), i * 150));

    // --- Modal Utility (FIXED) ---
    function setupSidebarModal(openBtnId, modalId, contentId, closeOverlayId, cancelBtnId) {
        const openBtn = openBtnId ? document.getElementById(openBtnId) : null;
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(contentId);
        const closeOverlay = document.getElementById(closeOverlayId);
        const cancelBtn = document.getElementById(cancelBtnId);

        function openModal() { 
            modal.classList.remove('hidden'); 
            // Correctly apply 'show' class to trigger CSS transition
            setTimeout(() => modalContent.classList.add('show'), 10);
        }
        function closeModal() { 
            // Remove 'show' class to trigger CSS slide-out
            modalContent.classList.remove('show'); 
            // Hide container after transition
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        if(openBtn) openBtn.addEventListener('click', openModal);
        if(closeOverlay) closeOverlay.addEventListener('click', closeModal);
        if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    }

    // Add Modal Setup
    setupSidebarModal('openAddAssessment', 'addAssessmentModal', 'addModalContent', 'closeAddAssessment', 'cancelAddAssessment');

    // Edit Modal Setup
    const editModal = document.getElementById('editAssessmentModal');
    const editModalContent = document.getElementById('editModalContent');
    setupSidebarModal(null, 'editAssessmentModal', 'editModalContent', 'closeEditAssessment', 'cancelEditAssessment');
    const editBtns = document.querySelectorAll('.editAssessmentBtn');

    editBtns.forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            // Populate form fields
            document.getElementById('editAssessmentId').value = btn.dataset.id;
            document.getElementById('editAssessmentName').value = btn.dataset.name;
            document.getElementById('editAssessmentType').value = btn.dataset.type;
            document.getElementById('editAssessmentTime').value = btn.dataset.time;
            
            // Open modal manually using the corrected logic
            editModal.classList.remove('hidden');
            setTimeout(() => editModalContent.classList.add('show'), 10);
        });
    });
</script>
</body>
</html>