<?php
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';

// Build WHERE clause dynamically
$where = "WHERE (name LIKE :search OR email LIKE :search)";
$params = [':search' => "%$search%"];
if (!empty($typeFilter)) {
    $where .= " AND type = :type";
    $params[':type'] = $typeFilter;
}

// Count total users
$count_sql = "SELECT COUNT(*) FROM admin $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch users with pagination
$sql = "SELECT * FROM admin $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) $stmt->bindValue($key, $val);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Users - FixLearn</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" type="image/png" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* Base animation/modal styles remain here */
    .fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
    .fade-slide.show { opacity: 1; transform: translateY(0); }
    /* These use CSS variables directly for color since standard Tailwind utilities aren't built for them */
    .sidebar-modal { 
        position: fixed; top: 0; right: 0; height: 100%; max-width: 28rem; width: 100%; 
        background: var(--color-card-bg); 
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
        background: var(--color-popup-bg); 
        z-index: 40; 
    }
        .main-content-wrapper {
    margin-left: 4rem; /* Default margin-left: w-16 (4 units = 4rem) */
    transition: margin-left 0.3s ease-in-out;
}
#sidebar:hover ~ .main-content-wrapper {
    margin-left: 14rem; /* New margin-left: w-56 (14 units = 14rem) */
}
    .header-bg { 
        background-color: var(--color-card-bg); /* Use pure white for header */
        border-bottom: 2px solid var(--color-sidebar-border); /* Stronger header line using ISU yellow */
    }
body{
  padding:0;
}
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content-wrapper flex-grow flex flex-col">

    <?php include "header.php";
    renderHeader("Users");
    ?>

    <div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3 w-full">
        <form method="GET" id="searchForm" class="flex flex-wrap sm:flex-nowrap gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search users..."
                class="flex-1 px-4 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-icon)] transition text-[var(--color-text)]">

            <select name="type" onchange="document.getElementById('searchForm').submit();"
                class="px-4 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:ring-2 focus:ring-[var(--color-icon)] transition text-[var(--color-text)]">
                <option value="">All Categories</option>
                <option value="Admin" <?= ($typeFilter==='Admin') ? 'selected' : '' ?>>Admin</option>
                <option value="Teachers" <?= ($typeFilter==='Teachers') ? 'selected' : '' ?>>Teacher</option>
            </select>
            
            <button type="submit" class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-medium rounded-lg shadow hover:bg-[var(--color-button-primary-hover)] transition">
                Search
            </button>
        </form>

        <button id="openAddUser" class="flex items-center gap-2 px-5 py-2 bg-[var(--color-button-primary)] text-white font-medium rounded-lg shadow hover:bg-[var(--color-button-primary-hover)] transition">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <div class="bg-[var(--color-card-bg)] pb-6 m-6 rounded-2xl shadow-lg border border-[var(--color-card-border)] overflow-hidden fade-slide">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-secondary)] text-sm uppercase tracking-wider">
                        <th class="p-3 border-b-2 border-[var(--color-card-border)] font-semibold">Name</th>
                        <th class="p-3 border-b-2 border-[var(--color-card-border)] font-semibold">Email</th>
                        <th class="p-3 border-b-2 border-[var(--color-card-border)] font-semibold">Type</th>
                        <th class="p-3 border-b-2 border-[var(--color-card-border)] font-semibold">Created</th>
                        <th class="p-3 border-b-2 border-[var(--color-card-border)] font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-card-border)]">
                    <?php if ($users): ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-[var(--color-card-section-bg)] transition">
                                <td class="p-3 font-semibold text-[var(--color-heading)]"><?= htmlspecialchars($u['name']); ?></td>
                                <td class="p-3 text-[var(--color-text)]"><?= htmlspecialchars($u['email']); ?></td>
                                <td class="p-3 text-[var(--color-text-secondary)]">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= ($u['type'] === 'Admin') 
                                            ? 'bg-[var(--color-green-button-light)] text-[var(--color-green-button)]' 
                                            : 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)]'; ?>">
                                        <?= htmlspecialchars($u['type']); ?>
                                    </span>
                                </td>
                                <td class="p-3 text-[var(--color-text-secondary)] text-sm"><?= $u['created_at']; ?></td>
                                <td class="p-3 flex justify-center gap-3">
                                    <button class="px-3 py-1 text-sm font-medium bg-[var(--color-green-button-light)] text-[var(--color-green-button)] rounded-lg hover:opacity-80 transition editUserBtn"
                                        data-id="<?= $u['id']; ?>"
                                        data-name="<?= htmlspecialchars($u['name']); ?>"
                                        data-email="<?= htmlspecialchars($u['email']); ?>"
                                        data-type="<?= $u['type']; ?>">
                                        <i class="fas fa-edit text-yellow-700"></i>
                                    </button>
                                    <a href="users_action.php?action=delete&id=<?= $u['id']; ?>" onclick="return confirm('Delete this user?');" 
                                        class="px-3 py-1 text-sm font-medium bg-[var(--color-red-badge)] text-[var(--color-red-badge-text)] rounded-lg hover:opacity-80 transition">
                                        <i class="fas fa-trash text-red-500"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-6 text-center text-[var(--color-text-secondary)]">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center flex-wrap gap-2">
            <?php for ($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>" 
                    class="px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition 
                    <?= $i==$page 
                        ? 'bg-[var(--color-button-primary)] text-white shadow-md' 
                        : 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-[var(--color-button-secondary-hover)]'; ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div id="userSidebar" class="sidebar-modal">
    <h2 id="modalTitle" class="text-2xl font-extrabold mb-6 text-[var(--color-heading)]">Add User</h2>
    <form method="POST" action="users_action.php" class="space-y-4">
        <input type="hidden" name="id" id="userId">
        <input type="hidden" name="action" id="formAction" value="add">

        <div>
            <label class="block mb-1 font-medium text-[var(--color-text)]">Name</label>
            <input type="text" name="name" id="userName" required 
                class="w-full px-3 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-icon)] text-[var(--color-text)]">
        </div>

        <div>
            <label class="block mb-1 font-medium text-[var(--color-text)]">Email</label>
            <input type="email" name="email" id="userEmail" required 
                class="w-full px-3 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-icon)] text-[var(--color-text)]">
        </div>

        <div>
            <label class="block mb-1 font-medium text-[var(--color-text)]">Password</label>
            <input type="password" name="password" id="userPassword" 
                class="w-full px-3 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-icon)] text-[var(--color-text)]">
            <small class="text-[var(--color-text-secondary)]">Leave blank when editing if you don’t want to change password.</small>
        </div>

        <div>
            <label class="block mb-1 font-medium text-[var(--color-text)]">Type</label>
            <select name="type" id="userType" 
                class="w-full px-3 py-2 border border-[var(--color-input-border)] bg-[var(--color-input-bg)] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-icon)] text-[var(--color-text)]">
                <option value="Admin">Admin</option>
                <option value="Teachers">Teacher</option>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <button type="button" id="closeSidebar" class="px-4 py-2 bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] rounded-lg hover:bg-[var(--color-button-secondary-hover)] transition font-medium">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-[var(--color-button-primary)] text-white rounded-lg hover:bg-[var(--color-button-primary-hover)] transition font-medium">Save</button>
        </div>
    </form>
</div>
<div id="overlay" class="hidden modal-overlay"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".fade-slide").forEach(el => el.classList.add("show"));

    const sidebar = document.getElementById("userSidebar");
    const overlay = document.getElementById("overlay");
    const modalTitle = document.getElementById("modalTitle");
    const formAction = document.getElementById("formAction");
    const userId = document.getElementById("userId");
    const userName = document.getElementById("userName");
    const userEmail = document.getElementById("userEmail");
    const userPassword = document.getElementById("userPassword");
    const userType = document.getElementById("userType");

    function openSidebar() { sidebar.classList.add("show"); overlay.classList.remove("hidden"); }
    function closeSidebar() { sidebar.classList.remove("show"); overlay.classList.add("hidden"); }

    document.getElementById("openAddUser").addEventListener("click", () => {
        modalTitle.textContent = "Add User";
        formAction.value = "add";
        userId.value = "";
        userName.value = "";
        userEmail.value = "";
        userPassword.value = "";
        userType.value = "Admin";
        openSidebar();
    });

    document.querySelectorAll(".editUserBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            modalTitle.textContent = "Edit User";
            formAction.value = "edit";
            userId.value = btn.dataset.id;
            userName.value = btn.dataset.name;
            userEmail.value = btn.dataset.email;
            userPassword.value = "";
            userType.value = btn.dataset.type;
            openSidebar();
        });
    });

    document.getElementById("closeSidebar").addEventListener("click", closeSidebar);
    overlay.addEventListener("click", closeSidebar);
});
</script>
</body>
</html>