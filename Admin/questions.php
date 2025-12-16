<?php

require __DIR__ . '/../config.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Params (same as your original file style)
$assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : 0;
$module_id     = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$course_id     = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($assessment_id <= 0) {
    header("Location: module.php?course_id=$course_id&error=invalid_assessment");
    exit;
}
if ($module_id <= 0) {
    header("Location: module.php?course_id=$course_id&error=invalid_module");
    exit;
}

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Get module info and course_id if not provided
$mod = $conn->prepare("SELECT id, title, course_id FROM modules WHERE id = ?");
$mod->execute([$module_id]);
$module = $mod->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    header("Location: module.php?course_id=$course_id&error=module_not_found");
    exit;
}

// If course_id is 0 or not provided, get it from the module
if ($course_id <= 0 && isset($module['course_id'])) {
    $course_id = (int)$module['course_id'];
    // Redirect to update the URL with the correct course_id
    header("Location: questions.php?assessment_id=$assessment_id&module_id=$module_id&course_id=$course_id");
    exit;
}

// Fetch assessments and topics for the add/edit dropdowns
$assessStmt = $conn->prepare("SELECT id, name FROM assessments WHERE module_id = :mid OR module_id IS NULL ORDER BY name ASC");
$assessStmt->execute([':mid' => $module_id]);
$assessments = $assessStmt->fetchAll(PDO::FETCH_ASSOC);

$topicStmt = $conn->prepare("SELECT id, title FROM topics WHERE module_id = :mid OR module_id IS NULL ORDER BY title ASC");
$topicStmt->execute([':mid' => $module_id]);
$topics = $topicStmt->fetchAll(PDO::FETCH_ASSOC);

// Count for pagination (join not necessary for count)
$count_sql = "SELECT COUNT(*) AS total FROM questions WHERE assessment_id = :aid";
$params = ['aid' => $assessment_id];
if ($search !== '') {
    $count_sql .= " AND question LIKE :search";
    $params['search'] = "%$search%";
}
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_questions = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = max(1, ceil($total_questions / $limit));

// Fetch questions with assessment name and topic title
$sql = "
    SELECT q.*,
           a.name AS assessment_name,
           t.title AS topic_title
    FROM questions q
    LEFT JOIN assessments a ON q.assessment_id = a.id
    LEFT JOIN topics t ON q.topic_id = t.id
    WHERE q.assessment_id = :aid
";
if ($search !== '') $sql .= " AND q.question LIKE :search";
$sql .= " ORDER BY q.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':aid', $assessment_id, PDO::PARAM_INT);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>ISUtoLearn - Assessment Questions</title>
  <link rel="stylesheet" href="../output.css">
  <link rel="icon" href="../images/isu-logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* ===================================================================== */
    /* EMBEDDED STYLES (Theming from Dashboard) - unchanged from original)   */
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
    renderHeader("ISU Admin Questions: " . htmlspecialchars($module['title']))?>

    <div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3">
        <form method="GET" class="flex w-full sm:w-auto gap-2">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <input type="hidden" name="assessment_id" value="<?= $assessment_id; ?>">
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search); ?>" 
                placeholder="Search questions..." 
                class="w-full sm:w-72 px-4 py-2 border rounded-lg shadow-inner input-themed focus:ring-[var(--color-heading)] focus:ring-2 transition placeholder-[var(--color-input-placeholder)]"
            >
            <button type="submit" 
                class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-bold rounded-lg shadow-md hover:bg-[var(--color-button-primary-hover)] active:scale-95 transition">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <button 
            id="openAddQuestion" 
            class="flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading)] text-white font-bold rounded-lg shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-[1.02]"
        >
            <i class="fas fa-plus-circle"></i> Add Question
        </button>
    </div>

    <div class="card-bg pb-2 rounded-md m-6 shadow-xl border border-[var(--color-card-border)] overflow-hidden fade-slide">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider border-b border-[var(--color-card-section-border)]">
                        <th class="p-4 rounded-tl-xl font-bold">#</th>
                        <th class="p-4 font-bold">Question</th>
                        <th class="p-4 font-bold">Assessment</th>
                        <th class="p-4 font-bold">Topic</th>
                        <th class="p-4 rounded-tr-xl font-bold text-center">Actions</th>    
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-card-border)]">
                    <?php if (!empty($questions)): ?>
                        <?php $i = $offset + 1; foreach ($questions as $row): ?>
                            <tr class="hover:bg-yellow-50/10 transition duration-150">
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
                                <td class="p-4 font-semibold text-[var(--color-heading-secondary)]"><?= nl2br(htmlspecialchars($row['question'])); ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['assessment_name'] ?? '—'); ?></td>
                                <td class="p-4"><?= htmlspecialchars($row['topic_title'] ?? '—'); ?></td>

                                <td class="p-4 flex justify-center gap-3">
                                    <!-- EDIT BUTTON -->
                                    <button 
                                        class="editQuestionBtn px-3 py-2 text-sm font-medium bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition shadow-sm"
                                        data-id="<?= $row['id']; ?>"
                                        data-question="<?= htmlspecialchars($row['question']); ?>"
                                        data-assessment="<?= (int)$row['assessment_id']; ?>"
                                        data-topic="<?= (int)$row['topic_id']; ?>"
                                        title="Edit Question">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>

                                    <!-- DELETE BUTTON -->
                                    <a href="assessment_code.php?action=delete_question&id=<?= $row['id']; ?>&module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>&assessment_id=<?= $assessment_id; ?>" 
                                        onclick="return confirm('Are you sure you want to delete this question?');"
                                        class="px-3 py-2 text-sm font-medium bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition shadow-sm" 
                                        title="Delete Question">
                                        <i class="fas fa-trash-alt"></i> 
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-6 text-center text-[var(--color-text-secondary)] font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No questions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center flex-wrap gap-2">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <a href="?module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>&assessment_id=<?= $assessment_id; ?>&page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
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
        <a href="assessment.php?module_id=<?= $module_id; ?>&course_id=<?= $course_id; ?>" 
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-md">
            <i class="fas fa-arrow-left mr-2"></i> Back to Assessment
        </a>
    </div>
</div>

<!-- ADD QUESTION SIDEMODAL -->
<div id="addQuestionModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay" id="closeAddQuestion"></div>
    <div class="sidebar-modal" id="addModalContent">
        <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-plus-square mr-2 text-[var(--color-icon)]"></i> Add New Question</h3>
        <form method="POST" action="assessment_code.php">
            <input type="hidden" name="action" value="add_question">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <input type="hidden" name="assessment_id" value="<?= $assessment_id; ?>">

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Question</label>
                <textarea name="question" class="w-full p-3 rounded-lg input-themed resize-none" rows="4" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Assessment</label>
                <select name="select_assessment_id" class="w-full p-3 rounded-lg input-themed" required>
                    <option value="">-- Select Assessment --</option>
                    <?php foreach($assessments as $a): ?>
                        <option value="<?= $a['id']; ?>" <?= $a['id'] == $assessment_id ? 'selected' : ''; ?>><?= htmlspecialchars($a['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Topic</label>
                <select name="select_topic_id" class="w-full p-3 rounded-lg input-themed">
                    <option value="">-- Select Topic (optional) --</option>
                    <?php foreach($topics as $t): ?>
                        <option value="<?= $t['id']; ?>"><?= htmlspecialchars($t['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                <button type="button" id="cancelAddQuestion" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                    <i class="fas fa-check-circle mr-1"></i> Add Question
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT QUESTION SIDEMODAL -->
<div id="editQuestionModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay" id="closeEditQuestion"></div>
    <div class="sidebar-modal" id="editQuestionContent">
        <h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]">
            <i class="fas fa-pen-to-square mr-2 text-[var(--color-icon)]"></i> Edit Question
        </h3>

        <form method="POST" action="assessment_code.php">
            <input type="hidden" name="action" value="edit_question">
            <input type="hidden" name="id" id="editQuestionId">
            <input type="hidden" name="module_id" value="<?= $module_id; ?>">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <input type="hidden" name="assessment_id" value="<?= $assessment_id; ?>">

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Question</label>
                <textarea name="question" id="editQuestionText" rows="3" 
                    class="w-full p-3 rounded-lg input-themed resize-none" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Assessment</label>
                <select name="select_assessment_id" id="editSelectAssessment" class="w-full p-3 rounded-lg input-themed" required>
                    <option value="">-- Select Assessment --</option>
                    <?php foreach($assessments as $a): ?>
                        <option value="<?= $a['id']; ?>"><?= htmlspecialchars($a['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1 text-[var(--color-text)]">Topic</label>
                <select name="select_topic_id" id="editSelectTopic" class="w-full p-3 rounded-lg input-themed">
                    <option value="">-- Select Topic (optional) --</option>
                    <?php foreach($topics as $t): ?>
                        <option value="<?= $t['id']; ?>"><?= htmlspecialchars($t['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
                <button type="button" id="cancelEditQuestion" 
                    class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-bold shadow-md">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // === Edit Question Sidebar Modal ===
function setupEditQuestionModal() {
    const editBtns = document.querySelectorAll('.editQuestionBtn');
    const modal = document.getElementById('editQuestionModal');
    const modalContent = document.getElementById('editQuestionContent');
    const closeOverlay = document.getElementById('closeEditQuestion');
    const cancelBtn = document.getElementById('cancelEditQuestion');
    const idField = document.getElementById('editQuestionId');
    const questionField = document.getElementById('editQuestionText');
    const assessmentSelect = document.getElementById('editSelectAssessment');
    const topicSelect = document.getElementById('editSelectTopic');

    function openModal() {
        modal.classList.remove('hidden');
        setTimeout(() => modalContent.classList.add('show'), 10);
    }
    function closeModal() {
        modalContent.classList.remove('show');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            idField.value = btn.dataset.id;
            // dataset.question contains escaped html; assign raw text
            questionField.value = btn.dataset.question;
            // populate selects
            if (btn.dataset.assessment) assessmentSelect.value = btn.dataset.assessment;
            else assessmentSelect.value = '';
            if (btn.dataset.topic) topicSelect.value = btn.dataset.topic;
            else topicSelect.value = '';

            openModal();
        });
    });

    if (closeOverlay) closeOverlay.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
}
setupEditQuestionModal();

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
            setTimeout(() => modalContent.classList.add('show'), 10);
        }
        function closeModal() { 
            modalContent.classList.remove('show'); 
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        if(openBtn) openBtn.addEventListener('click', openModal);
        if(closeOverlay) closeOverlay.addEventListener('click', closeModal);
        if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    }

    // Add Modal Setup
    setupSidebarModal('openAddQuestion', 'addQuestionModal', 'addModalContent', 'closeAddQuestion', 'cancelAddQuestion');

    // Edit Modal Setup (the open is handled by edit buttons)
    setupSidebarModal(null, 'editQuestionModal', 'editQuestionContent', 'closeEditQuestion', 'cancelEditQuestion');
</script>
</body>
</html>
