<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total learners
$count_sql = "SELECT COUNT(*) AS total FROM learners";
$params = [];
if ($search !== '') {
    $count_sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR contact_number LIKE :search";
    $params['search'] = "%$search%";
}
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_learners = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_learners / $limit);

// Fetch learners
$sql = "SELECT * FROM learners";
if ($search !== '') {
    $sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR contact_number LIKE :search";
}
$sql .= " ORDER BY id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// This section needs to be closed before the HTML starts, 
// but since you included the entire file content, I'm assuming you meant for the PHP to end here.
?> 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Learners - Admin Panel</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" href="../images/isu-log.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* --- Sidebar Transition Fix --- */
.main-content-wrapper {
    margin-left: 4rem; /* Default margin-left: w-16 (4 units = 4rem) */
    transition: margin-left 0.3s ease-in-out;
}
#sidebar:hover ~ .main-content-wrapper {
    margin-left: 14rem; /* New margin-left: w-56 (14 units = 14rem) */
}

/* --- Status Badges (Hardcoded based on your variables) --- */
.status-active {
    background-color: #dcfce7; /* Light Green */
    color: #16a34a; /* Dark Green */
}
.status-inactive {
    background-color: #fee2e2; /* Light Red */
    color: #ef4444; /* Dark Red */
}
.status-pending {
    background-color: #fffbeb; /* Pale Yellow */
    color: #f59e0b; /* Orange */
}

/* --- Modal Slide-in Animation --- */
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
    transition: transform 0.3s ease-out; 
    overflow-y: auto; 
    box-shadow: -4px 0 12px rgba(0,0,0,0.2); 
    padding: 1.5rem;
    border-left: 5px solid var(--color-heading); /* ISU Green Accent */
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
.card-bg {
    background-color: var(--color-card-bg);
}
.input-themed {
    border-color: var(--color-input-border);
    background-color: var(--color-input-bg);
    color: var(--color-input-text);
}
.placeholder-\[var\(--color-input-placeholder\)\]::placeholder {
    color: var(--color-input-placeholder);
}
body{
  padding:0;
}
    .header-bg { 
        background-color: var(--color-card-bg); /* Use pure white for header */
        border-bottom: 2px solid var(--color-sidebar-border); /* Stronger header line using ISU yellow */
    }
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content-wrapper flex-grow flex flex-col">
                <?php include 'header.php';
                renderHeader("ISU Admin Courses")
                ?>


    <div class="m-6 flex flex-col sm:flex-row justify-between items-center mt-4 mb-6 gap-3 w-full">
        <form method="GET" class="flex w-full sm:w-auto gap-2">
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search); ?>" 
                placeholder="Search by name, email, or contact..." 
                class="w-full sm:w-80 px-4 py-2 border border-[var(--color-input-border)] rounded-lg shadow-inner bg-[var(--color-input-bg)] text-[var(--color-input-text)] focus:outline-none focus:ring-2 focus:ring-[var(--color-heading)] transition placeholder-[var(--color-input-placeholder)] input-themed"
            >
            <button 
                type="submit" 
                class="px-5 py-2 bg-[var(--color-heading)] text-white font-bold rounded-lg shadow-md hover:bg-[var(--color-button-primary-hover)] active:scale-[0.98] transition"
            >
                <i class="fas fa-search"></i>
            </button>
        </form>

        <button 
            id="openAddLearner" 
            class="mr-12 flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading-secondary)] text-white font-bold rounded-md shadow-lg hover:bg-[#e86a11] active:scale-[0.98] transition"
        >
            <i class="fas fa-user-plus"></i> 
            Add New Learner
        </button>
    </div>

    <div class="m-6 card-bg pb-6 rounded-xl shadow-xl border border-[var(--color-card-border)] overflow-hidden flex-grow">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold rounded-tl-xl">#</th>
                        <th class="p-4 font-bold">Name</th>
                        <th class="p-4 font-bold">Email</th>
                        <th class="p-4 font-bold">Contact</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-center rounded-tr-xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-card-border)]">
                    <?php if (!empty($learners)): ?>
                        <?php $i = $offset + 1; foreach ($learners as $learner): ?>
                            <tr class="hover:bg-[var(--color-sidebar-link-hover)] transition duration-150">
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
                                <td class="p-4 font-semibold text-[var(--color-heading)]">
                                    <i class="fas fa-user-tag mr-2 text-[var(--color-heading-secondary)]"></i>
                                    <?= htmlspecialchars($learner['first_name'] . ' ' . ($learner['middle_name'] ? $learner['middle_name'] . ' ' : '') . $learner['last_name']); ?>
                                </td>
                                <td class="p-4 text-[var(--color-text-secondary)] italic"><?= htmlspecialchars($learner['email']); ?></td>
                                <td class="p-4 text-[var(--color-text-secondary)]"><?= htmlspecialchars($learner['contact_number']); ?></td>
                                <td class="p-4">
                                    <?php 
                                        $status_class = match(strtolower($learner['status'])) {
                                            'active' => 'status-active',
                                            'inactive' => 'status-inactive',
                                            default => 'status-pending',
                                        };
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $status_class; ?>">
                                        <?= ucfirst($learner['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4 flex justify-center gap-3">
                                    <button 
                                        class="px-3 py-2 text-sm font-medium bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] rounded-lg hover:bg-[var(--color-sidebar-link-active)] transition editLearnerBtn shadow-sm"
                                        data-id="<?= $learner['id']; ?>"
                                        data-first="<?= htmlspecialchars($learner['first_name']); ?>"
                                        data-middle="<?= htmlspecialchars($learner['middle_name']); ?>"
                                        data-last="<?= htmlspecialchars($learner['last_name']); ?>"
                                        data-email="<?= htmlspecialchars($learner['email']); ?>"
                                        data-contact="<?= htmlspecialchars($learner['contact_number']); ?>"
                                        data-status="<?= $learner['status']; ?>"
                                        title="Edit Learner"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="learner_code.php?action=delete&id=<?= $learner['id']; ?>" 
                                        onclick="return confirm('Are you sure you want to delete this learner? This action cannot be undone.');"
                                        class="px-3 py-2 text-sm font-medium bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition shadow-sm"
                                        title="Delete Learner">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-[var(--color-text-secondary)] font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No learners found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center flex-wrap gap-2 border-t pt-4 border-[var(--color-card-border)]">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
                        class="px-5 py-2 rounded-full text-sm font-bold shadow-md transition transform hover:scale-105 
                        <?= $p==$page 
                            ? 'bg-[var(--color-heading)] text-white' /* ISU Green Active */
                            : 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-[var(--color-sidebar-link-active)]'; /* Yellow/XP button */ ?>">
                        <?= $p; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="addLearnerModal" class="hidden fixed inset-0 z-50 flex justify-end">
        <div class="modal-overlay" id="closeAddLearner"></div>
        <div class="sidebar-modal" id="modalContent">
            <h3 class="text-3xl font-extrabold mb-6 text-[var(--color-heading)]"><i class="fas fa-user-plus mr-2"></i> Add New Learner</h3>
            <form method="POST" action="learner_code.php">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">First Name</label>
                        <input type="text" name="first_name" placeholder="Juan" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" placeholder="Dela Cruz" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Last Name</label>
                        <input type="text" name="last_name" placeholder="Luna" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Email Address</label>
                    <input type="email" name="email" placeholder="juan.luna@example.com" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Contact Number</label>
                    <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Status</label>
                    <select name="status" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div>
                     <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Password</label>
                        <input type="password" name="password" placeholder="Enter Password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                     <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Confirm Password</label>
                        <input type="password" name="conpass" placeholder="Confirm Password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-card-border)]">
                    <button type="button" id="cancelModal" class="px-5 py-2 bg-[var(--color-text-secondary)] text-white rounded-lg hover:bg-gray-600 transition font-medium shadow-md">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[var(--color-heading-secondary)] text-white rounded-lg hover:bg-[#e86a11] transition font-bold shadow-lg">
                        <i class="fas fa-save mr-1"></i> Save Learner
                    </button>
                </div>
            </form>
        </div>
    </div>


        <div id="editLearnerModal" class="hidden fixed inset-0 z-50 flex justify-end">
        <div class="modal-overlay" id="closeAddLearner"></div>
        <div class="sidebar-modal" id="modalContent">
            <h3 class="text-3xl font-extrabold mb-6 text-[var(--color-heading)]"><i class="fas fa-user-plus mr-2"></i> Add New Learner</h3>
            <form method="POST" action="learner_code.php">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">First Name</label>
                        <input type="text" name="first_name" placeholder="Juan" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" placeholder="Dela Cruz" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Last Name</label>
                        <input type="text" name="last_name" placeholder="Luna" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Email Address</label>
                    <input type="email" name="email" placeholder="juan.luna@example.com" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Contact Number</label>
                    <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-[var(--color-text)]">Status</label>
                    <select name="status" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div>
                     <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Password</label>
                        <input type="password" name="password" placeholder="Enter Password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                     <div class="md:col-span-2">
                        <label class="block mb-1 font-semibold text-[var(--color-text)]">Confirm Password</label>
                        <input type="password" name="conpass" placeholder="Confirm Password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--color-heading)] input-themed" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-card-border)]">
                    <button type="button" id="cancelModal" class="px-5 py-2 bg-[var(--color-text-secondary)] text-white rounded-lg hover:bg-gray-600 transition font-medium shadow-md">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[var(--color-heading-secondary)] text-white rounded-lg hover:bg-[#e86a11] transition font-bold shadow-lg">
                        <i class="fas fa-save mr-1"></i> Save Learner
                    </button>
                </div>
            </form>
        </div>
    </div>



</div> <script>
// Modal functionality (Adjusted to use the new modal structure)
const openBtn = document.getElementById('openAddLearner');
const modal = document.getElementById('addLearnerModal');
const closeOverlay = document.getElementById('closeAddLearner');
const cancelBtn = document.getElementById('cancelModal');
const modalContent = document.getElementById('modalContent');

function openModal() {
    modal.classList.remove('hidden');
    // Small delay to allow the 'hidden' removal to register before adding 'show'
    setTimeout(() => modalContent.classList.add('show'), 10);
}
function closeModal() {
    modalContent.classList.remove('show');
    // Wait for the transition (0.3s) before hiding the container
    setTimeout(() => modal.classList.add('hidden'), 300);
}

openBtn.addEventListener('click', openModal);
closeOverlay.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);


// --- Edit Modal Setup ---
const editBtns = document.querySelectorAll('.editLearnerBtn');
editBtns.forEach(btn => btn.addEventListener('click', e => {
    e.preventDefault();
    // **PLACEHOLDER:** Logic to populate and open the 'editLearnerModal' goes here
    // You would typically open a similar modal here and populate its form fields 
    // using the `data-` attributes on the button (e.g., btn.dataset.first, btn.dataset.email)
    console.log("Edit button clicked for ID:", btn.dataset.id);

    // For demonstration, let's open the same modal structure, assuming you'd modify it 
    // to be reusable for both Add and Edit actions.
    openModal();
    // You'd add logic here to change the modal title and button text for "Edit"
}));

</script>
</body>
</html>