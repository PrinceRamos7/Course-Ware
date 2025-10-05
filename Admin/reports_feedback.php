<?php
require __DIR__ . '/../config.php';

// ✅ Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Check login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// --- Pagination & Search Setup ---
$limit = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

// --- WHERE Clause ---
$where = "WHERE (rf.title LIKE :search
              OR rf.category LIKE :search
              OR CONCAT(l.first_name, ' ', l.last_name) LIKE :search
              OR c.title LIKE :search)";

if (!empty($filter_type)) {
    $where .= " AND rf.type = :filter_type";
}

// --- Count total ---
$count_sql = "SELECT COUNT(*) 
              FROM reports_feedback rf
              JOIN learners l ON rf.learner_id = l.id
              JOIN courses c ON rf.course_id = c.id
              $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if (!empty($filter_type)) {
    $count_stmt->bindValue(':filter_type', $filter_type, PDO::PARAM_STR);
}
$count_stmt->execute();
$total = $count_stmt->fetchColumn();
$total_pages = ($limit > 0) ? ceil($total / $limit) : 1;

// --- Fetch paginated data ---
$sql = "SELECT rf.*, 
               CONCAT(l.first_name, ' ', l.last_name) AS learner_name,
               c.title AS course_title
        FROM reports_feedback rf
        JOIN learners l ON rf.learner_id = l.id
        JOIN courses c ON rf.course_id = c.id
        $where
        ORDER BY rf.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if (!empty($filter_type)) {
    $stmt->bindValue(':filter_type', $filter_type, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --- Pagination & Search Setup ---
$limit = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

// --- WHERE Clause ---
$where = "WHERE (rf.title LIKE :search
              OR rf.category LIKE :search
              OR CONCAT(l.first_name, ' ', l.last_name) LIKE :search
              OR c.title LIKE :search)";

if (!empty($filter_type)) {
    $where .= " AND rf.type = :filter_type";
}

// --- Count total ---
$count_sql = "SELECT COUNT(*) 
              FROM reports_feedback rf
              JOIN learners l ON rf.learner_id = l.id
              JOIN courses c ON rf.course_id = c.id
              $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if (!empty($filter_type)) {
    $count_stmt->bindValue(':filter_type', $filter_type, PDO::PARAM_STR);
}
$count_stmt->execute();
$total = $count_stmt->fetchColumn();
$total_pages = ($limit > 0) ? ceil($total / $limit) : 1;

// --- Fetch paginated data ---
$sql = "SELECT rf.*, 
               CONCAT(l.first_name, ' ', l.last_name) AS learner_name,
               c.title AS course_title
        FROM reports_feedback rf
        JOIN learners l ON rf.learner_id = l.id
        JOIN courses c ON rf.course_id = c.id
        $where
        ORDER BY rf.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if (!empty($filter_type)) {
    $stmt->bindValue(':filter_type', $filter_type, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports & Feedback - ISUtoLearn</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" href="../images/isu-log.png">
<link rel="icon" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    .card { transition: all 0.3s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .status-badge {
      padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
    }
    .status-Pending { background-color: #fef3c7; color: #92400e; }
    .status-In\ Review { background-color: #05339C; color:#05339C; }
    .status-Resolved { background-color: #dcfce7; color: #166534; }
    .status-Closed { background-color: #f3f4f6; color: #374151; }

    /* Modal animation */
    #detailsModal { transition: opacity 0.3s ease; }
    #detailsModal.show { opacity: 1; visibility: visible; }
    #detailsModal.hide { opacity: 0; visibility: hidden; }
  </style>
</head>

<body class="min-h-screen flex bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

 <div class="main-content-wrapper flex-grow flex flex-col">
    <?php include "header.php"; renderHeader("Reports & Feedback"); ?>

  <main class="flex-1 p-8">
    <div class="max-w-6xl mx-auto">
      <!-- Search bar -->
     <form class="flex items-center gap-3 mb-6" method="GET">
  <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
         placeholder="Search reports or feedback..."
         class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">

  <select name="filter_type" 
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
    <option value="">All Types</option>
    <option value="Report" <?= (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Report') ? 'selected' : '' ?>>Report</option>
    <option value="Feedback" <?= (isset($_GET['filter_type']) && $_GET['filter_type'] === 'Feedback') ? 'selected' : '' ?>>Feedback</option>
  </select>

  <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
    <i class="fas fa-search"></i>
  </button>
</form>

      

      <!-- Reports list -->
      <?php if (empty($reports)): ?>
        <div class="text-center text-gray-500 py-20">
          <i class="fas fa-inbox text-5xl mb-4"></i>
          <p>No reports or feedback found.</p>
        </div>
      <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($reports as $r): ?>
<div class="card bg-white rounded-2xl p-5 shadow-sm border" data-report-id="<?= $r['id'] ?>">
              <div class="flex justify-between items-start mb-3">
<span class="status-badge px-3 py-1 text-sm font-semibold <?= 'status-' . str_replace(' ', '\ ', $r['status']) ?>">
                  <?= htmlspecialchars($r['status']) ?>
                </span>
                <span class="text-xs text-gray-500"><?= date("M d, Y", strtotime($r['created_at'])) ?></span>
              </div>

              <h2 class="text-lg font-semibold text-gray-800 mb-1"><?= htmlspecialchars($r['title']) ?></h2>
              <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($r['category']) ?> • <?= htmlspecialchars($r['type']) ?></p>

              <p class="text-gray-700 text-sm line-clamp-3 mb-3"><?= htmlspecialchars($r['message']) ?></p>

              <div class="border-t pt-3 text-sm text-gray-500">
                <p><strong>Course:</strong> <?= htmlspecialchars($r['course_title']) ?></p>
                <p><strong>Learner:</strong> <?= htmlspecialchars($r['learner_name']) ?></p>
              </div>

              
              <div class="flex justify-end mt-4">
              <button 
  class="text-green-700 hover:text-green-900 text-sm font-medium view-details-btn"
  data-report='<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
  <i class="fas fa-eye mr-1"></i> View Details
</button>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-10">
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 rounded-lg border <?= $i === $page ? 'bg-green-600 text-white' : 'bg-white hover:bg-green-50' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>


<!-- SIDE MODAL -->
<div id="detailsModal" class="fixed inset-0 z-50 flex items-center justify-end bg-black/40 backdrop-blur-sm hidden transition-opacity duration-300">
  <div id="modalPanel" 
       class="w-full max-w-md h-full bg-white shadow-2xl transform translate-x-full transition-all duration-300 ease-in-out rounded-l-2xl overflow-y-auto">
    
    <!-- 🧭 Header -->
    <div class="p-5 border-b flex justify-between items-center bg-gradient-to-r from-green-600 to-green-500 text-white rounded-tl-2xl">
      <h2 class="text-lg font-semibold flex items-center gap-2">
        <i class="fas fa-file-alt text-white"></i> Report / Feedback Details
      </h2>
      <button id="closeModal" 
              class="text-white/80 hover:text-white transition text-xl">
        <i class="fas fa-times"></i>
      </button>
    </div>
<!-- 📋 Improved Modal Content -->
<div id="modalContent" class="p-6 space-y-6 text-gray-800">

  <!-- 🧾 Basic Info -->
  <div class="grid grid-cols-2 gap-4 text-sm">
    <input type="hidden" id="detailId">

    <div class="col-span-2">
      <p class="text-lg font-semibold text-gray-900 mb-1" id="detailTitle"></p>
      <p class="text-xs text-gray-500">Report Details</p>
    </div>

    <div>
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Category</label>
      <p id="detailCategory" class="font-medium text-gray-900"></p>
    </div>

    <div>
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Type</label>
      <p id="detailType" class="font-medium text-gray-900"></p>
    </div>

    <div class="col-span-2">
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Status</label>
      <select id="detailStatus"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
        <option value="Pending">Pending</option>
        <option value="In Review">In Review</option>
        <option value="Resolved">Resolved</option>
        <option value="Closed">Closed</option>
      </select>
    </div>

    <div>
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Learner</label>
      <p id="detailLearner" class="font-medium text-gray-900"></p>
    </div>

    <div>
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Course</label>
      <p id="detailCourse" class="font-medium text-gray-900"></p>
    </div>

    <div class="col-span-2">
      <label class="block text-gray-500 text-xs uppercase tracking-wider mb-1">Date Created</label>
      <p id="detailDate" class="font-medium text-gray-900"></p>
    </div>
  </div>

  <!-- 📨 Message -->
  <div>
    <h3 class="text-gray-800 font-semibold mb-2">Message</h3>
    <div id="detailMessage"
         class="border border-gray-200 p-4 rounded-xl bg-gray-50 shadow-inner text-sm leading-relaxed text-gray-700"></div>
  </div>

  <!-- 💾 Actions -->
  <div class="pt-4 border-t flex gap-3">
    <button id="updateStatusBtn"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2 shadow hover:shadow-lg">
      <i class="fas fa-save"></i> Update Status
    </button>
    <button id="closeModalFooter"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-gray-700 transition">
      Cancel
    </button>
  </div>

</div>


<script>
function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.className = `fixed top-5 right-5 px-4 py-2 rounded-lg text-white shadow-lg ${
    type === "success" ? "bg-green-600" : "bg-red-600"
  }`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("detailsModal");
  const panel = document.getElementById("modalPanel");
  const closeModalBtn = document.getElementById("closeModal");
  const updateBtn = document.getElementById("updateStatusBtn");

  // Open modal
  document.querySelectorAll(".view-details-btn").forEach(button => {
    button.addEventListener("click", () => {
      const report = JSON.parse(button.dataset.report);

      document.getElementById("detailId").value = report.id;
      document.getElementById("detailTitle").textContent = report.title;
      document.getElementById("detailCategory").textContent = report.category;
      document.getElementById("detailType").textContent = report.type;
      document.getElementById("detailLearner").textContent = report.learner_name;
      document.getElementById("detailCourse").textContent = report.course_title;
      document.getElementById("detailDate").textContent = new Date(report.created_at).toLocaleString();
      document.getElementById("detailMessage").textContent = report.message;
      document.getElementById("detailStatus").value = report.status;

      // keep report id on panel for later DOM update
      panel.dataset.reportId = report.id;

      modal.classList.remove("hidden");
      setTimeout(() => panel.classList.remove("translate-x-full"), 10);
    });
  });

  // Close
  closeModalBtn.addEventListener("click", closeModal);
  modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
  function closeModal() {
    panel.classList.add("translate-x-full");
    setTimeout(() => modal.classList.add("hidden"), 300);
  }

  // Update status
  updateBtn.addEventListener("click", async () => {
    const id = document.getElementById("detailId").value;
    const status = document.getElementById("detailStatus").value;

    if (!id) { alert("Invalid report id"); return; }

    updateBtn.disabled = true;
    const originalText = updateBtn.innerHTML;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

    try {
      const res = await fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
      });

      const text = await res.text(); // get raw response
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        console.error("Non-JSON response from server:", text);
        alert("Server returned an unexpected response. Check console / network tab.");
        return;
      }

      if (!res.ok) {
        console.error("Server error:", res.status, data);
        alert("Error: " + (data.message || res.statusText));
        return;
      }

 if (data.success) {
  // Update the status badge on the card without reload (if card has data-report-id attr)
  const card = document.querySelector(`[data-report-id="${id}"]`);
  if (card) {
    const badge = card.querySelector('.status-badge');
    if (badge) {
      badge.textContent = status;
      badge.style.transition = "all 0.3s ease";
badge.style.transform = "scale(1.1)";
setTimeout(() => badge.style.transform = "scale(1)", 150);

      // normalize class name: replace spaces with hyphens
      badge.className = 'status-badge status-' + status.replace(/\s+/g, '-');
    }
  }
  alert("✅ " + (data.message || "Status updated"));
  closeModal();
}
else {
        alert("⚠️ " + (data.message || "Unknown response"));
      }
    } catch (err) {
      console.error(err);
      alert("❌ Something went wrong while updating. See console for details.");
    } finally {
      updateBtn.disabled = false;
      updateBtn.innerHTML = originalText;
    }
  });
});
</script>
</body>
</html>
