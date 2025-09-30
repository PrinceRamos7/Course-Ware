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
.main-content-wrapper { margin-left: 4rem; transition: margin-left 0.3s ease-in-out; }
#sidebar:hover ~ .main-content-wrapper { margin-left: 14rem; }
.status-active { background:#dcfce7; color:#16a34a; }
.status-inactive { background:#fee2e2; color:#ef4444; }
.status-pending { background:#fffbeb; color:#f59e0b; }
.sidebar-modal { position:fixed; top:0; right:0; height:100%; width:100%; max-width:32rem;
    background:var(--color-card-bg); z-index:50; transform:translateX(100%); transition:transform 0.3s ease-out;
    overflow-y:auto; box-shadow:-4px 0 12px rgba(0,0,0,0.2); padding:1.5rem; border-left:5px solid var(--color-heading);}
.sidebar-modal.show { transform:translateX(0); }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:40; }
.card-bg { background-color:var(--color-card-bg); }
.input-themed { border-color:var(--color-input-border); background:var(--color-input-bg); color:var(--color-input-text); }
.placeholder-\[var\(--color-input-placeholder\)\]::placeholder { color:var(--color-input-placeholder);}
body{padding:0;}
.header-bg { background-color:var(--color-card-bg); border-bottom:2px solid var(--color-sidebar-border);}
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content-wrapper flex-grow flex flex-col">
    <?php include "header.php"; renderHeader("Manage Learners"); ?>

    <!-- Search + Add button -->
    <div class="m-6 flex flex-col sm:flex-row justify-between items-center mt-4 mb-6 gap-3 w-full">
        <form method="GET" class="flex w-full sm:w-auto gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" 
                placeholder="Search by name, email, or contact..." 
                class="w-full sm:w-80 px-4 py-2 border rounded-lg input-themed focus:ring-2 focus:ring-[var(--color-heading)]">
            <button type="submit" class="px-5 py-2 bg-[var(--color-heading)] text-white font-bold rounded-lg">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <button id="openAddLearner" class="mr-12 flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading-secondary)] text-white font-bold rounded-md">
            <i class="fas fa-user-plus"></i> Add New Learner
        </button>
    </div>

    <!-- Table -->
    <div class="m-6 card-bg pb-6 rounded-xl shadow-xl border overflow-hidden flex-grow">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-sm uppercase tracking-wider">
                        <th class="p-4 font-bold rounded-tl-xl">#</th>
                        <th class="p-4 font-bold">Name</th>
                        <th class="p-4 font-bold">Email</th>
                        <th class="p-4 font-bold">Contact</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-center rounded-tr-xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (!empty($learners)): ?>
                        <?php $i=$offset+1; foreach($learners as $learner): ?>
                        <tr class="hover:bg-[var(--color-sidebar-link-hover)]">
                            <td class="p-4"><?= $i++; ?></td>
                            <td class="p-4 font-semibold"><?= htmlspecialchars($learner['first_name'].' '.($learner['middle_name']?$learner['middle_name'].' ':'').$learner['last_name']); ?></td>
                            <td class="p-4 italic"><?= htmlspecialchars($learner['email']); ?></td>
                            <td class="p-4"><?= htmlspecialchars($learner['contact_number']); ?></td>
                            <td class="p-4">
                                <?php $status_class = match(strtolower($learner['status'])) {
                                    'active'=>'status-active','inactive'=>'status-inactive', default=>'status-pending'}; ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $status_class; ?>">
                                    <?= ucfirst($learner['status']); ?>
                                </span>
                            </td>
                            <td class="p-4 flex justify-center gap-3">
                                <button class="px-3 py-2 text-sm bg-[var(--color-button-secondary)] rounded-lg editLearnerBtn"
                                    data-id="<?= $learner['id']; ?>" data-first="<?= htmlspecialchars($learner['first_name']); ?>"
                                    data-middle="<?= htmlspecialchars($learner['middle_name']); ?>" data-last="<?= htmlspecialchars($learner['last_name']); ?>"
                                    data-email="<?= htmlspecialchars($learner['email']); ?>" data-contact="<?= htmlspecialchars($learner['contact_number']); ?>"
                                    data-status="<?= $learner['status']; ?>"><i class="fas fa-edit"></i></button>
                                <a href="learner_code.php?action=delete&id=<?= $learner['id']; ?>" 
                                    onclick="return confirm('Are you sure?');"
                                    class="px-3 py-2 text-sm bg-red-50 text-red-600 rounded-lg"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="p-6 text-center">No learners found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages>1): ?>
        <div class="mt-8 flex justify-center gap-2 border-t pt-4">
            <?php for($p=1;$p<=$total_pages;$p++): ?>
            <a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>" 
                class="px-5 py-2 rounded-full text-sm font-bold <?= $p==$page?'bg-[var(--color-heading)] text-white':'bg-[var(--color-button-secondary)]'; ?>">
                <?= $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Learner Modal -->
    <div id="addLearnerModal" class="hidden fixed inset-0 z-50 flex justify-end">
        <div class="modal-overlay" id="closeAddLearner"></div>
        <div class="sidebar-modal" id="modalContent">
            <h3 class="text-3xl font-extrabold mb-6"><i class="fas fa-user-plus mr-2"></i> Add New Learner</h3>
            <form method="POST" action="learner_code.php" id="learnerForm">
                <input type="hidden" name="action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><label>First Name</label><input type="text" name="first_name" required class="w-full p-3 border rounded-lg"></div>
                    <div><label>Middle Name</label><input type="text" name="middle_name" class="w-full p-3 border rounded-lg"></div>
                    <div class="md:col-span-2"><label>Last Name</label><input type="text" name="last_name" required class="w-full p-3 border rounded-lg"></div>
                </div>
                <div class="mb-4"><label>Email</label><input type="email" name="email" required class="w-full p-3 border rounded-lg"></div>
                <div class="mb-4"><label>Contact</label><input type="text" name="contact_number" required class="w-full p-3 border rounded-lg"></div>
                <div class="mb-6"><label>Status</label><select name="status" class="w-full p-3 border rounded-lg"><option value="active">Active</option><option value="inactive">Inactive</option><option value="pending">Pending</option></select></div>
                <div class="mb-4"><label>Password</label><input type="password" name="password" id="password" required class="w-full p-3 border rounded-lg"><small id="password-strength"></small></div>
                <div class="mb-4"><label>Confirm Password</label><input type="password" name="conpass" id="confirmPassword" required class="w-full p-3 border rounded-lg"><small id="password-match"></small></div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" id="cancelModal" class="px-5 py-2 bg-gray-400 text-white rounded-lg">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-5 py-2 bg-[var(--color-heading-secondary)] text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Learner Modal -->
<!-- Edit Learner Modal -->
<div id="editLearnerModal" class="hidden fixed inset-0 z-50 flex justify-end">
    <div class="modal-overlay" id="closeEditLearner"></div>
    <div class="sidebar-modal" id="editModalContent">
        <h3 class="text-3xl font-extrabold mb-6"><i class="fas fa-user-edit mr-2"></i> Edit Learner</h3>
        <form method="POST" action="learner_code.php" id="editLearnerForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" required class="w-full p-3 border rounded-lg">
                </div>
                <div>
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" id="edit_middle_name" class="w-full p-3 border rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" required class="w-full p-3 border rounded-lg">
                </div>
            </div>

            <!-- Contact -->
            <div class="mb-4">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required class="w-full p-3 border rounded-lg">
            </div>
            <div class="mb-4">
                <label>Contact</label>
                <input type="text" name="contact_number" id="edit_contact" required class="w-full p-3 border rounded-lg">
            </div>
            <div class="mb-6">
                <label>Status</label>
                <select name="status" id="edit_status" class="w-full p-3 border rounded-lg">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label>New Password <small class="text-gray-500">(leave blank to keep current)</small></label>
                <input type="password" name="password" id="password" class="w-full p-3 border rounded-lg">
                <small id="password-strength" class="text-sm"></small>
            </div>
            <div class="mb-4">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirmPassword" class="w-full p-3 border rounded-lg">
                <small id="password-match" class="text-sm"></small>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" id="cancelEditModal" class="px-5 py-2 bg-gray-400 text-white rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[var(--color-heading)] text-white rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
// Add Modal
const openBtn=document.getElementById('openAddLearner'),modal=document.getElementById('addLearnerModal'),
closeOverlay=document.getElementById('closeAddLearner'),cancelBtn=document.getElementById('cancelModal'),
modalContent=document.getElementById('modalContent');
function openModal(){modal.classList.remove('hidden');setTimeout(()=>modalContent.classList.add('show'),10);}
function closeModal(){modalContent.classList.remove('show');setTimeout(()=>modal.classList.add('hidden'),300);}
openBtn.addEventListener('click',openModal);closeOverlay.addEventListener('click',closeModal);cancelBtn.addEventListener('click',closeModal);

// Edit Modal
const editBtns=document.querySelectorAll('.editLearnerBtn'),editModal=document.getElementById('editLearnerModal'),
editOverlay=document.getElementById('closeEditLearner'),cancelEdit=document.getElementById('cancelEditModal'),
editContent=document.getElementById('editModalContent');
function openEdit(){editModal.classList.remove('hidden');setTimeout(()=>editContent.classList.add('show'),10);}
function closeEdit(){editContent.classList.remove('show');setTimeout(()=>editModal.classList.add('hidden'),300);}
editBtns.forEach(btn=>btn.addEventListener('click',()=>{document.getElementById('edit_id').value=btn.dataset.id;
document.getElementById('edit_first_name').value=btn.dataset.first;document.getElementById('edit_middle_name').value=btn.dataset.middle;
document.getElementById('edit_last_name').value=btn.dataset.last;document.getElementById('edit_email').value=btn.dataset.email;
document.getElementById('edit_contact').value=btn.dataset.contact;document.getElementById('edit_status').value=btn.dataset.status;
openEdit();}));editOverlay.addEventListener('click',closeEdit);cancelEdit.addEventListener('click',closeEdit);
document.getElementById('editLearnerForm').addEventListener('submit', function(e) {
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirmPassword').value;
    const matchText = document.getElementById('password-match');

    if (pass !== confirm) {
        e.preventDefault();
        matchText.textContent = "Passwords do not match!";
        matchText.classList.add("text-red-500");
    } else {
        matchText.textContent = "";
    }
});
// Password Validation
const pwd=document.getElementById('password'),con=document.getElementById('confirmPassword'),
strength=document.getElementById('password-strength'),match=document.getElementById('password-match'),
submit=document.getElementById('submitBtn');
pwd.addEventListener('input',()=>{let v=pwd.value;if(v.length<6){strength.textContent='Weak';strength.style.color='red';}
else if(/[A-Z]/.test(v)&&/[0-9]/.test(v)&&/[^A-Za-z0-9]/.test(v)){strength.textContent='Strong';strength.style.color='green';}
else{strength.textContent='Medium';strength.style.color='orange';}});
con.addEventListener('input',()=>{if(con.value!==pwd.value){match.textContent='Passwords do not match';match.style.color='red';submit.disabled=true;}
else{match.textContent='Passwords match';match.style.color='green';submit.disabled=false;}});
</script>
</body>
</html>
