<?php
// =====================================================================
// 1. PHP LOGIC (Database Connection, Pagination, Search, and Fetch)
// =====================================================================
require __DIR__ . '/../config.php';

// --- Input Handling ---
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// --- Total Count Query ---
$count_sql = "SELECT COUNT(*) AS total FROM courses";
if ($search !== '') {
    $count_sql .= " WHERE title LIKE :search OR description LIKE :search";
}

$stmt = $conn->prepare($count_sql);
if ($search !== '') {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_courses / $limit);

// --- Fetch Courses Query ---
$courses_sql = "
    SELECT 
        c.*, 
        COUNT(m.id) AS lesson_count 
    FROM courses c 
    LEFT JOIN modules m ON c.id = m.course_id
";

if ($search !== '') {
    $courses_sql .= " WHERE c.title LIKE :search OR c.description LIKE :search";
}

$courses_sql .= " 
    GROUP BY c.id 
    ORDER BY c.id DESC 
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($courses_sql);

if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
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
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <style>

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

        /* Modal Styles (Retained from previous fix) */
        .fade-slide { 
            opacity: 0; 
            transform: translateY(20px); 
            transition: opacity .8s ease, transform .8s ease; 
        }
        .fade-slide.show { 
            opacity: 1; 
            transform: translateY(0); 
        }
        .sidebar-modal { 
            position: fixed; 
            top: 0; 
            right: 0; 
            height: 100%; 
            width: 100%; 
            max-width: 32rem; 
            background: var(--color-card-bg); 
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
            background: rgba(0,0,0,0.4); 
            z-index: 40; 
        }
    </style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto">
        <header class="header-bg shadow-lg p-4 flex justify-between items-center sticky top-0 z-10 mb-6">
            <div class="fade-in">
                <h1 class="text-xl font-bold text-[var(--color-heading)]">ISU Learning Platform Admin Dashboard</h1>
                <p class="text-sm text-[var(--color-text-secondary)]">Centralized system overview and management tools.</p>
            </div>
            <button class="flex items-center space-x-2 px-3 py-2 text-sm rounded-md shadow bg-gray-100 hover:bg-gray-200 transition hover-scale text-[var(--color-text)]">
                <i class="fas fa-user-circle text-xl text-[var(--color-heading)]"></i>
                <span class="font-semibold">Administrator</span>
            </button>
        </header>

        <div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3">
            <form method="GET" class="flex w-full sm:w-auto gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($search); ?>" 
                    placeholder="Search courses by title or description..." 
                    class="w-full sm:w-80 px-4 py-2 border border-[var(--color-input-border)] rounded-md shadow-inner bg-[var(--color-input-bg)] text-[var(--color-input-text)] focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition placeholder-[var(--color-input-placeholder)]"
                >
                <button 
                    type="submit" 
                    class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-bold rounded-md shadow-md hover:bg-[var(--color-button-primary-hover)] transition"
                >
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <button 
                id="openAddCourse" 
                class="flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading)] text-white font-bold rounded-md shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-[1.02]"
            >
                <i class="fas fa-plus-circle"></i> Create New Course
            </button>
        </div>

        <div class="card-bg p-8 rounded-xl shadow-xl border border-[var(--color-card-border)] overflow-hidden fade-slide m-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider border-b border-[var(--color-card-section-border)]">
                            <th class="p-4 rounded-tl-xl font-bold">#</th>
                            <th class="p-4 font-bold">Title</th>
                            <th class="p-4 font-bold">Description</th>
                            <th class="p-4 font-bold text-center">Lessons</th>
                            <th class="p-4 rounded-tr-xl font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-card-border)]">
                        <?php if (!empty($courses_result)): ?>
                            <?php $i = $offset + 1; foreach ($courses_result as $row): ?>
                                <tr class="hover:bg-gray-50/5 transition duration-150">
                                    <td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
                                    <td class="p-4 font-semibold text-[var(--color-heading-secondary)]"><?= htmlspecialchars($row['title']); ?></td>
                                    <td class="p-4 text-[var(--color-text-secondary)] italic">
                                        <?= htmlspecialchars(substr($row['description'], 0, 75)); ?><?= strlen($row['description']) > 75 ? '...' : ''; ?>
                                    </td>
                                    <td class="p-4 text-center font-bold text-lg text-[var(--color-heading)]"><?= $row['lesson_count']; ?></td>
                                    <td class="p-4 flex justify-center flex-wrap gap-3">
                                        <a href="module.php?course_id=<?= $row['id']; ?>" class="px-4 py-2 text-sm font-semibold bg-blue-50 text-blue-700 rounded-full hover:bg-blue-100 transition shadow-sm" title="View Modules"><i class="fas fa-list-ul"></i> Modules</a>
                                        <button class="px-3 py-2 text-sm font-medium bg-yellow-100 text-yellow-700 rounded-full hover:bg-yellow-200 transition shadow-sm viewStudentsBtn" data-id="<?= $row['id']; ?>" data-title="<?= htmlspecialchars($row['title']); ?>" title="View Enrolled Students"><i class="fas fa-user-graduate"></i></button>
                                        <button class="px-3 py-2 text-sm font-medium bg-green-50 text-green-700 rounded-full hover:bg-green-100 transition shadow-sm editCourseBtn" data-id="<?= $row['id']; ?>" data-title="<?= htmlspecialchars($row['title']); ?>" data-description="<?= htmlspecialchars($row['description']); ?>" title="Edit Course"><i class="fas fa-edit"></i></button>
                                        <a href="course_code.php?id=<?= $row['id']; ?>&action=delete" onclick="return confirm('Are you sure you want to delete the course: <?= htmlspecialchars($row['title']); ?>? This action cannot be undone.');" class="px-3 py-2 text-sm font-medium bg-red-50 text-red-600 rounded-full hover:bg-red-100 transition shadow-sm" title="Delete Course"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-6 text-center text-gray-500 font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No courses found. Try a different search term.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center flex-wrap gap-2">
                    <?php for($p=1; $p<=$total_pages; $p++): ?>
                        <a 
                            href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
                            class="px-5 py-2 rounded-full text-sm font-semibold shadow-md transition transform hover:scale-105 
                            <?= $p==$page ? 'bg-[var(--color-heading-secondary)] text-white' : 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-yellow-200'; ?>"
                        >
                            <?= $p; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="addCourseModal" class="hidden fixed inset-0 z-50 flex justify-end">
            <div class="modal-overlay" id="closeAddCourse"></div>
            <div class="sidebar-modal">
                <h3 class="text-3xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-folder-plus mr-2"></i> Add New Course</h3>
                <form method="POST" action="course_code.php">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-5">
                        <label class="block mb-2 font-semibold text-[var(--color-text)]">Title</label>
                        <input type="text" name="title" placeholder="e.g., Introduction to Python" class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-button-primary)]" required>
                    </div>
                    <div class="mb-6">
                        <label class="block mb-2 font-semibold text-[var(--color-text)]">Description</label>
                        <textarea name="description" placeholder="A brief description of the course content..." class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-button-primary)]" rows="6" required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-card-border)]">
                        <button type="button" id="cancelModal" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition font-medium">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-[var(--color-button-primary)] text-white rounded-lg hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                            <i class="fas fa-check-circle mr-1"></i> Add Course
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editCourseModal" class="hidden fixed inset-0 z-50 flex justify-end">
            <div class="modal-overlay" id="closeEditCourse"></div>
            <div class="sidebar-modal" id="editModalContent">
                <h3 class="text-3xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-pen-to-square mr-2"></i> Edit Course</h3>
                <form method="POST" action="course_code.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editCourseId">
                    <div class="mb-5">
                        <label class="block mb-2 font-semibold text-[var(--color-text)]">Title</label>
                        <input type="text" name="title" id="editCourseTitle" class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-green-500" required>
                    </div>
                    <div class="mb-6">
                        <label class="block mb-2 font-semibold text-[var(--color-text)]">Description</label>
                        <textarea name="description" id="editCourseDescription" class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-green-500" rows="6" required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-card-border)]">
                        <button type="button" id="cancelEditModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-300 transition font-medium">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition font-bold shadow-md">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div id="viewStudentsModal" class="hidden fixed inset-0 z-50 flex justify-end">
            <div class="modal-overlay" id="closeViewStudents"></div>
            <div class="sidebar-modal">
                <h3 class="text-2xl font-bold mb-4 text-[var(--color-heading)]" id="studentsModalTitle"><i class="fas fa-users-viewfinder mr-2"></i> Enrolled Students</h3>
                <ul id="studentsList" class="divide-y divide-[var(--color-card-border)] text-[var(--color-text)] max-h-[70vh] overflow-y-auto bg-gray-50/5 p-3 rounded-lg border border-[var(--color-card-border)]"></ul>
                <div class="flex justify-end mt-6 pt-4 border-t border-[var(--color-card-border)]">
                    <button id="cancelViewStudents" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Close</button>
                </div>
            </div>
        </div>

    </div> 
    
    <script>
        // --- Utility Functions for Modals ---
        function setupModal(openBtnId, modalId, contentClass, closeOverlayId, cancelBtnId) {
            const openBtn = openBtnId ? document.getElementById(openBtnId) : null;
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector(contentClass);
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
            
            return { openModal, closeModal };
        }

        // --- Fade Table Animation ---
        document.querySelectorAll('.fade-slide').forEach((el, i) => 
            setTimeout(() => el.classList.add('show'), i * 150)
        );

        // --- Initialize Modals ---

        // Add Modal Setup
        setupModal('openAddCourse', 'addCourseModal', '.sidebar-modal', 'closeAddCourse', 'cancelModal');

        // Edit Modal Setup
        const { closeModal: closeEditModal } = setupModal(null, 'editCourseModal', '.sidebar-modal', 'closeEditCourse', 'cancelEditModal');
        const editBtns = document.querySelectorAll('.editCourseBtn');
        const editModal = document.getElementById('editCourseModal');
        const editModalContent = document.getElementById('editModalContent');

        editBtns.forEach(btn => btn.addEventListener('click', e => {
            e.preventDefault();
            // Populate form fields with course data
            document.getElementById('editCourseId').value = btn.dataset.id;
            document.getElementById('editCourseTitle').value = btn.dataset.title;
            document.getElementById('editCourseDescription').value = btn.dataset.description;
            
            // Open the modal
            editModal.classList.remove('hidden'); 
            setTimeout(() => editModalContent.classList.add('show'), 10);
        }));

        // View Students Modal Setup
        const { closeModal: closeViewModal } = setupModal(null, 'viewStudentsModal', '.sidebar-modal', 'closeViewStudents', 'cancelViewStudents');
        const viewBtns = document.querySelectorAll('.viewStudentsBtn');
        const studentsList = document.getElementById('studentsList');
        const studentsModalTitle = document.getElementById('studentsModalTitle');

        viewBtns.forEach(btn => btn.addEventListener('click', async e => {
            const courseId = btn.dataset.id;
            const courseTitle = btn.dataset.title;
            
            studentsModalTitle.innerHTML = `<i class="fas fa-users-viewfinder mr-2"></i> Enrolled Students: <span class="text-[var(--color-heading-secondary)]">${courseTitle}</span>`;
            studentsList.innerHTML = '<li class="p-3 text-gray-500 font-medium flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Loading student list...</li>';
            
            // Open the modal immediately
            const viewModal = document.getElementById('viewStudentsModal');
            const viewModalContent = viewModal.querySelector('.sidebar-modal');
            viewModal.classList.remove('hidden'); 
            setTimeout(() => viewModalContent.classList.add('show'), 10);

            try {
                // Fetch student data (Adjust the URL as necessary)
                const res = await fetch(`students_in_course.php?course_id=${courseId}`);
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                const data = await res.json();

                studentsList.innerHTML = data.length 
                    ? data.map(s => `<li class="p-3 hover:bg-white/10 transition flex items-center rounded-md"><i class="fas fa-user-check mr-3 text-[var(--color-heading)]"></i>${s.first_name} ${s.last_name}</li>`).join('') 
                    : '<li class="p-3 text-gray-500 font-medium">No students enrolled in this course yet.</li>';

            } catch(err) { 
                studentsList.innerHTML = '<li class="p-3 text-red-500 font-medium flex items-center"><i class="fas fa-circle-exclamation mr-2"></i> Error loading students.</li>'; 
                console.error("Error fetching students:", err);
            }
        }));
        
    </script>
</body>
</html>