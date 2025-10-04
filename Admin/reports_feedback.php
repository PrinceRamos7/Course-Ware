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

// --- Pagination & Search ---
$limit = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// --- Count total records ---
$count_sql = "SELECT COUNT(*) 
              FROM reports_feedback rf
              JOIN learners l ON rf.learner_id = l.id
              JOIN courses c ON rf.course_id = c.id
              WHERE rf.title LIKE :search
                 OR rf.category LIKE :search
                 OR CONCAT(l.first_name, ' ', l.last_name) LIKE :search
                 OR c.title LIKE :search";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute([':search' => "%$search%"]);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $limit);

// --- Fetch paginated data ---
$sql = "SELECT rf.*, 
               CONCAT(l.first_name, ' ', l.last_name) AS learner_name,
               c.title AS course_title
        FROM reports_feedback rf
        JOIN learners l ON rf.learner_id = l.id
        JOIN courses c ON rf.course_id = c.id
        WHERE rf.title LIKE :search
           OR rf.category LIKE :search
           OR CONCAT(l.first_name, ' ', l.last_name) LIKE :search
           OR c.title LIKE :search
        ORDER BY rf.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    .card { transition: all 0.3s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .status-badge {
      padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
    }
    .status-Pending { background-color: #fef3c7; color: #92400e; }
    .status-In\ Review { background-color: #dbeafe; color: #1e40af; }
    .status-Resolved { background-color: #dcfce7; color: #166534; }
    .status-Closed { background-color: #f3f4f6; color: #374151; }

    /* Modal animation */
    #detailsModal { transition: opacity 0.3s ease; }
    #detailsModal.show { opacity: 1; visibility: visible; }
    #detailsModal.hide { opacity: 0; visibility: hidden; }
  </style>
</head>

<body class="min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-grow flex flex-col">
  <?php include "header.php"; renderHeader("Reports & Feedback"); ?>

  <main class="flex-1 p-8">
    <div class="max-w-6xl mx-auto">
      <!-- Search bar -->
      <form class="flex items-center gap-3 mb-6" method="GET">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search reports or feedback..."
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
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
            <div class="card bg-white rounded-2xl p-5 shadow-sm border">
              <div class="flex justify-between items-start mb-3">
                <span class="px-3 py-1 text-sm font-semibold <?= 'status-' . str_replace(' ', '\ ', $r['status']) ?>">
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
                <button onclick="viewDetails(<?= $r['id'] ?>)" 
                        class="text-green-700 hover:text-green-900 text-sm font-medium">
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

<!-- Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hide">
  <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-6 shadow-xl">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Report / Feedback Details</h2>
    <div id="modalContent" class="text-gray-700 text-sm"></div>
    <div class="flex justify-end mt-6">
      <button onclick="closeModal()" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
    </div>
  </div>
</div>

<script>
function viewDetails(id) {
  fetch('view_report_feedback.php?id=' + id)
    .then(res => res.text())
    .then(html => {
      document.getElementById('modalContent').innerHTML = html;
      const modal = document.getElementById('detailsModal');
      modal.classList.remove('hide');
      modal.classList.add('show');
    });
}

function closeModal() {
  const modal = document.getElementById('detailsModal');
  modal.classList.remove('show');
  modal.classList.add('hide');
}
</script>

</body>
</html>
