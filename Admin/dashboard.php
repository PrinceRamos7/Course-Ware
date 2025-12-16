<?php
// index.php

include '../config.php'; // Assuming this sets up $conn (PDO connection)

// --- 1. Set Error Mode and Define Helper Function ---
// Ensure PDO throws exceptions for better error reporting than silent failures
if (isset($conn)) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} else {
    // Fail loudly if the connection from config.php wasn't set up
    die("FATAL ERROR: Database connection (\$conn) is not initialized from config.php.");
}

/**
 * Helper function to fetch a single COUNT(*) value from the database.
 * @param PDO $conn The PDO connection object.
 * @param string $tableName The name of the table to count.
 * @return int The count of rows, or 0 on failure/empty result.
 */
function fetchSingleCount($conn, $tableName) {
    try {
        $sql = "SELECT COUNT(*) AS count FROM $tableName";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    } catch (PDOException $e) {
        // Halt execution and display the error for immediate debugging (e.g., if table name is wrong)
        die("DATABASE QUERY ERROR: Failed to count table '$tableName'. Error: " . $e->getMessage());
    }
}

$students = $conn->query("SELECT id, first_name, last_name, email, created_at FROM users WHERE type = 'learners'")->fetchAll(PDO::FETCH_ASSOC);
$courses = $conn->query("SELECT id, title, description FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$modules = $conn->query("SELECT id, title, description FROM modules")->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Calculate All Statistics ---
$totalLearners = count($students);
$totalCourses = count($courses);
$totalModules = count($modules);

// Total Enrollments
$totalEnrollments = $conn->query("SELECT COUNT(*) as count FROM student_courses")->fetch(PDO::FETCH_ASSOC)['count'];

// Total Assessments
$totalAssessments = $conn->query("SELECT COUNT(*) as count FROM assessments")->fetch(PDO::FETCH_ASSOC)['count'];

// Total Topics
$totalTopics = $conn->query("SELECT COUNT(*) as count FROM topics")->fetch(PDO::FETCH_ASSOC)['count'];

// Active Learners (logged in last 30 days)
$activeLearners = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM student_login_streak WHERE last_login_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch(PDO::FETCH_ASSOC)['count'];

// Completion Rate
$completedTopics = $conn->query("SELECT COUNT(*) as count FROM topics_completed")->fetch(PDO::FETCH_ASSOC)['count'];
$totalPossibleCompletions = $totalLearners * $totalTopics;
$completionRate = $totalPossibleCompletions > 0 ? round(($completedTopics / $totalPossibleCompletions) * 100, 1) : 0;

// Average Score (using last_score from student_score table)
$avgScore = $conn->query("SELECT AVG(last_score) as avg FROM student_score WHERE last_score IS NOT NULL")->fetch(PDO::FETCH_ASSOC)['avg'];
$avgScore = $avgScore ? round($avgScore, 1) : 0;

// Recent Learners
try {
    $recentLearners = $conn->query("SELECT first_name, last_name, created_at FROM users WHERE type = 'learners' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DATABASE QUERY ERROR: Failed to fetch recent learners. Error: " . $e->getMessage());
}

// Learner Growth (last 6 months)
$learnerGrowth = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE type = 'learners' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Course Enrollment Stats
$courseEnrollments = $conn->query("
    SELECT c.title, COUNT(sc.id) as enrollments
    FROM courses c
    LEFT JOIN student_courses sc ON c.id = sc.course_id
    GROUP BY c.id, c.title
    ORDER BY enrollments DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Top Performers
$topPerformers = $conn->query("
    SELECT u.first_name, u.last_name, u.experience, u.intelligent_exp
    FROM users u
    WHERE u.type = 'learners'
    ORDER BY u.experience DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Activity
$recentActivity = $conn->query("
    SELECT u.first_name, u.last_name, 'Completed Topic' as activity, tc.completed_at as activity_date
    FROM topics_completed tc
    JOIN users u ON tc.student_id = u.id
    WHERE u.type = 'learners'
    ORDER BY tc.completed_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ISUtoLearn Admin Dashboard</title>
<link rel="stylesheet" href="../output.css">
<link rel="icon" href="../images/isu-logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    :root {
        /* Defining the few custom colors used only in the dashboard that aren't in your main theme */
        --color-teal: #0d9488; /* Used for the Course card */
        --color-dark-green: #14532d; /* Used for the Module card */
    }
    body { 
        color: var(--color-text); 
        background-color: var(--color-main-bg); 
        padding: 0;
    }
    .header-bg { 
        background-color: var(--color-card-bg); /* Use pure white for header */
        border-bottom: 2px solid var(--color-sidebar-border); /* Stronger header line using ISU yellow */
    }
    .card-bg {
        background-color: var(--color-card-bg);
        border: 1px solid var(--color-card-border);
    }

    /* Animations & Utility */
    .fade-in { opacity: 0; transform: translateY(15px); transition: all 0.4s ease-out; }
    .fade-in.visible { opacity: 1; transform: translateY(0); }
    .hover-scale:hover { 
        transform: scale(1.02); 
        box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1); 
        transition: all 0.3s ease-in-out; 
    }
    
    /* Themed Buttons: Mapped to your explicit button variables */
    .btn-primary-themed { 
        background-color: var(--color-button-primary); 
        color: white; 
        padding: 0.5rem 1rem; 
        border-radius: 0.375rem; 
        transition: background-color 0.2s; 
    }
    .btn-primary-themed:hover { 
        background-color: var(--color-button-primary-hover); 
    }
    
    .btn-secondary-themed {
        background-color: var(--color-button-secondary);
        color: var(--color-button-secondary-text);
        padding: 0.5rem 1rem; 
        border-radius: 0.375rem; 
        transition: background-color 0.2s;
    }
    .btn-secondary-themed:hover {
        background-color: var(--color-sidebar-link-active); /* Using a similar yellow hover */
    }

    /* Modal Structure - Adjusted to match new colors */
    .modal { display: none; position: fixed; inset: 0; background: var(--color-popup-bg); align-items: center; justify-content: center; z-index: 50; }
    .modal.active { display: flex; }
    .modal-content {
        background: var(--color-popup-content-bg); /* Using the pop-up background */
        border-radius: 0.5rem; 
        max-height: 90vh; 
        overflow-y: auto; 
        padding: 1.5rem;
        width: 95%; max-width: 800px;
        transform: translateY(-50px); opacity: 0;
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }
    .modal.active .modal-content { transform: translateY(0); opacity: 1; }
    
    /* Input field styling in modals/tables */
    .input-themed {
        background-color: var(--color-input-bg);
        border-color: var(--color-input-border);
        color: var(--color-input-text);
    }
    .input-themed:focus {
        border-color: var(--color-icon); /* ISU Yellow focus */
        box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.5); 
        outline: none;
    }
.main-content-wrapper {
  margin-left: 4rem; /* Default margin-left: w-16 (4rem) */
  transition: margin-left 0.3s ease-in-out;
}

#sidebar:hover ~ .main-content-wrapper {
  margin-left: 14rem; /* Expanded sidebar (w-56 = 14rem) */
}

/* ✅ Animation section must be outside of selector blocks */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
  .animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
  }

</style>
</head>
<body class="min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content-wrapper flex-grow flex flex-col">
    <?php include "header.php";
    renderHeader("ISU Admin Dashboard");
    ?>

<main class="p-6 space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-heading)]" data-target="usersModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Total Learners</h3>
                <i class="fas fa-users text-[var(--color-heading)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-heading)] text-2xl"><?= $totalLearners ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Registered</p>
        </div>
        
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in border-l-4 border-blue-500">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Active Learners</h3>
                <i class="fas fa-user-check text-blue-500 text-xl"></i>
            </div>
            <div class="stat-number font-bold text-blue-500 text-2xl"><?= $activeLearners ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Last 30 days</p>
        </div>
        
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-teal)]" data-target="coursesModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Total Courses</h3>
                <i class="fas fa-book-open text-[var(--color-teal)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-teal)] text-2xl"><?= $totalCourses ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Active</p>
        </div>
        
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-dark-green)]" data-target="modulesModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Total Modules</h3>
                <i class="fas fa-layer-group text-[var(--color-dark-green)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-dark-green)] text-2xl"><?= $totalModules ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Content units</p>
        </div>

        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in border-l-4 border-[var(--color-heading-secondary)]">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Enrollments</h3>
                <i class="fas fa-graduation-cap text-[var(--color-heading-secondary)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-heading-secondary)] text-2xl"><?= $totalEnrollments ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Total</p>
        </div>

        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in border-l-4 border-purple-500">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)] text-xs">Avg Score</h3>
                <i class="fas fa-chart-line text-purple-500 text-xl"></i>
            </div>
            <div class="stat-number font-bold text-purple-500 text-2xl"><?= $avgScore ?>%</div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Performance</p>
        </div>
    </div>

    <hr class="border-[var(--color-sidebar-border)]">

    <!-- Analytics Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Learner Growth Chart -->
        <div class="card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
                <i class="fas fa-chart-area mr-2"></i>Learner Growth (Last 6 Months)
            </h2>
            <div style="height: 250px; position: relative;">
                <canvas id="learnerGrowthChart"></canvas>
            </div>
        </div>

        <!-- Course Enrollments Chart -->
        <div class="card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
                <i class="fas fa-chart-bar mr-2"></i>Top Courses by Enrollment
            </h2>
            <div style="height: 250px; position: relative;">
                <canvas id="courseEnrollmentChart"></canvas>
            </div>
        </div>

        <!-- Course Distribution Pie Chart -->
        <div class="card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
                <i class="fas fa-chart-pie mr-2"></i>Course Distribution
            </h2>
            <div style="height: 250px; position: relative;">
                <canvas id="courseDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <hr class="border-[var(--color-sidebar-border)]">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Activity -->
        <div class="card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
                <i class="fas fa-clock mr-2"></i>Recent Activity
            </h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                <?php if (count($recentActivity) > 0): ?>
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[var(--color-card-section-bg)] hover:bg-[var(--color-card-section-hover)] transition">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-[var(--color-text)]">
                                    <?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?>
                                </p>
                                <p class="text-xs text-[var(--color-text-secondary)]"><?= htmlspecialchars($activity['activity']) ?></p>
                            </div>
                            <span class="text-xs text-[var(--color-text-secondary)]">
                                <?= date('M d, H:i', strtotime($activity['activity_date'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-sm text-[var(--color-text-secondary)] py-4">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-2 card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-3 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
                <i class="fas fa-trophy mr-2"></i>Top Performers
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--color-card-section-border)]">
                    <thead class="bg-yellow-50/50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">Rank</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">Learner Name</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">XP</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">Intelligence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-card-section-border)]">
                        <?php if (count($topPerformers) > 0): ?>
                            <?php $rank = 1; foreach ($topPerformers as $performer): ?>
                                <tr class="hover:bg-yellow-100/30">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full <?= $rank == 1 ? 'bg-yellow-400 text-yellow-900' : ($rank == 2 ? 'bg-gray-300 text-gray-700' : ($rank == 3 ? 'bg-orange-300 text-orange-900' : 'bg-gray-100 text-gray-600')) ?> font-bold text-sm">
                                            <?= $rank ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-[var(--color-text)]">
                                        <?= htmlspecialchars($performer['first_name'] . ' ' . $performer['last_name']) ?>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                            <?= number_format($performer['experience']) ?> XP
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                                            <?= number_format($performer['intelligent_exp'] ?? 0) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php $rank++; endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="px-3 py-2 text-center text-sm text-[var(--color-text-secondary)]">No data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Quick Actions -->
    <div class="card-bg p-5 rounded-lg shadow-md fade-in">
        <h2 class="text-lg font-semibold mb-4 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">
            <i class="fas fa-bolt mr-2"></i>Quick Actions
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a id="openAddCourse" class="flex flex-col items-center justify-center p-4 rounded-lg bg-green-50 hover:bg-green-100 transition cursor-pointer border-2 border-green-200">
                <i class="fas fa-plus-circle text-3xl text-green-600 mb-2"></i>
                <span class="text-sm font-semibold text-green-700">Add Course</span>
            </a>
            <a href="learners.php" class="flex flex-col items-center justify-center p-4 rounded-lg bg-blue-50 hover:bg-blue-100 transition border-2 border-blue-200">
                <i class="fas fa-users text-3xl text-blue-600 mb-2"></i>
                <span class="text-sm font-semibold text-blue-700">Manage Learners</span>
            </a>
            <a href="module.php" class="flex flex-col items-center justify-center p-4 rounded-lg bg-purple-50 hover:bg-purple-100 transition border-2 border-purple-200">
                <i class="fas fa-layer-group text-3xl text-purple-600 mb-2"></i>
                <span class="text-sm font-semibold text-purple-700">Manage Modules</span>
            </a>
            <a href="reports_feedback.php" class="flex flex-col items-center justify-center p-4 rounded-lg bg-orange-50 hover:bg-orange-100 transition border-2 border-orange-200">
                <i class="fas fa-chart-bar text-3xl text-orange-600 mb-2"></i>
                <span class="text-sm font-semibold text-orange-700">View Reports</span>
            </a>
        </div>
    </div>
</main>
</div>

<!-- Add Course Modal -->
<div id="addCourseModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <!-- Overlay -->
  <div id="closeAddCourse" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

  <!-- Modal Content -->
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 animate-fadeIn">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-bold text-[var(--color-heading)]">
        <i class="fas fa-folder-plus mr-2 text-[var(--color-button-primary)]"></i>
        Add New Course
      </h3>
      <button id="closeAddCourseBtn" class="text-gray-500 hover:text-gray-800">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>

    <!-- Form -->
    <form method="POST" action="course_code.php" class="space-y-5">
      <input type="hidden" name="action" value="add">

      <!-- Title -->
      <div>
        <label class="block mb-2 font-semibold text-[var(--color-text)]">Title</label>
        <input type="text" name="title" placeholder="e.g., Introduction to Python"
          class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-button-primary)] focus:outline-none"
          required>
      </div>

      <!-- Description -->
      <div>
        <label class="block mb-2 font-semibold text-[var(--color-text)]">Description</label>
        <textarea name="description" placeholder="A brief description of the course content..."
          class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-button-primary)] focus:outline-none resize-none"
          rows="5" required></textarea>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-4 border-t border-[var(--color-card-border)]">
        <button type="button" id="cancelModal"
          class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
          Cancel
        </button>
        <button type="submit" name="btn_add"
          class="px-5 py-2 bg-[var(--color-button-primary)] text-white rounded-lg hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
          <i class="fas fa-check-circle mr-1"></i> Add Course
        </button>
      </div>
    </form>
  </div>
</div>
</main>
</div>

<?php   
// Function to render the modals, adjusted for more compact display
function renderModal($id, $title, $items, $fields) {
    echo '<div id="'.$id.'" class="modal">';
    echo '<div class="modal-content">';
    echo '<div class="flex justify-between items-center mb-3">';
    echo '<h2 class="text-xl font-bold text-[var(--color-heading)]">'.$title.'</h2>';
    echo '<button class="closeModal text-xl text-gray-500 hover:text-gray-800"><i class="fas fa-times"></i></button>';
    echo '</div>';
    
    // Search input styling
    echo '<input type="text" class="searchInput w-full mb-4 p-2 border rounded-lg text-sm input-themed" placeholder="Search '.$title.'...">';
    
    // Scrollable list container
    echo '<div class="overflow-y-auto max-h-[60vh] border border-[var(--color-card-section-border)] rounded">'; 
    echo '<ul class="divide-y divide-[var(--color-card-section-border)] modal-list">';
    if (empty($items)) {
         echo '<li class="p-3 text-sm text-[var(--color-text-secondary)]">No records found.</li>';
    } else {
        foreach ($items as $item) {
            // List item hover uses a subtle yellow-green hover from your sidebar theme
            echo '<li class="p-3 text-sm hover:bg-[var(--color-sidebar-link-hover)] transition duration-150">';
            $line_parts = [];
            foreach ($fields as $field_key => $field_label) {
                $value = htmlspecialchars($item[$field_key] ?? 'N/A');
                // Check if the key exists before trying to format the date
                if (array_key_exists($field_key, $item) && str_contains($field_key, 'created_at')) {
                    // Check if value is a valid date string before strtotime
                    if (strtotime($value) !== false) {
                         $value = date('Y-m-d', strtotime($value));
                    }
                }

                // More compact display: Label in secondary color, value in dark text
                $line_parts[] = '<span class="text-[var(--color-text-secondary)]">' . $field_label . ':</span> <span class="font-medium text-[var(--color-text)]">' . $value . '</span>';
            }
            echo implode(' &bull; ', $line_parts); // Using a middle dot separator
            echo '</li>';
        }
    }
    echo '</ul></div></div></div>';
}

// Define fields with labels for better modal presentation
// Note: Added 'email' and 'created_at' back to studentFields for full context in the modal
$studentFields = ['id' => 'ID', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'created_at' => 'Joined'];
$courseFields = ['id' => 'ID', 'title' => 'Title', 'description' => 'Description'];
$moduleFields = ['id' => 'ID', 'title' => 'Title', 'description' => 'Description'];

renderModal('usersModal', 'All Registered Learners', $students, $studentFields);
renderModal('coursesModal', 'All Active Courses', $courses, $courseFields);
renderModal('modulesModal', 'All Content Modules', $modules, $moduleFields);
?>

<script>
    // --- 4. JavaScript ---
    
    // Animate on scroll
    const fadeEls = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    fadeEls.forEach(el => observer.observe(el));

    // Open/Close modal logic
    document.querySelectorAll('.hover-scale[data-target]').forEach(card => {
        card.addEventListener('click', () => {
            const modalId = card.dataset.target;
            const modal = document.getElementById(modalId);
            modal.classList.add('active');
            // Ensure modal content is visible before focusing
            setTimeout(() => {
                const searchInput = modal.querySelector('.searchInput');
                if (searchInput) {
                    searchInput.focus();
                }
            }, 300); 
        });
    });

    document.querySelectorAll('.closeModal').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal').classList.remove('active');
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', e => {
            const modalContent = modal.querySelector('.modal-content');
            if(e.target === modal || (modalContent && !modalContent.contains(e.target))) {
                modal.classList.remove('active');
            }
        }); 
    });

    // Modal search logic
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});

// Open Add Course Modal
document.getElementById("openAddCourse").addEventListener("click", () => {
    document.getElementById("addCourseModal").classList.remove("hidden");
});

// Close Add Course Modal
document.getElementById("cancelModal").addEventListener("click", () => {
    document.getElementById("addCourseModal").classList.add("hidden");
});
document.getElementById("closeAddCourse").addEventListener("click", () => {
    document.getElementById("addCourseModal").classList.add("hidden");
});

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

        // --- Initialize Charts ---
        
        // Learner Growth Chart
        const learnerGrowthCtx = document.getElementById('learnerGrowthChart');
        if (learnerGrowthCtx) {
            const learnerGrowthData = <?= json_encode($learnerGrowth) ?>;
            const months = learnerGrowthData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });
            const counts = learnerGrowthData.map(item => parseInt(item.count));

            new Chart(learnerGrowthCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'New Learners',
                        data: counts,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgb(34, 197, 94)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: { size: 12 },
                            bodyFont: { size: 11 },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { size: 10 },
                                padding: 5
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 0,
                                autoSkip: true
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Course data for both bar and pie charts
        const courseData = <?= json_encode($courseEnrollments) ?>;

        // Course Enrollment Chart
        const courseEnrollmentCtx = document.getElementById('courseEnrollmentChart');
        if (courseEnrollmentCtx) {
            const courseNames = courseData.map(item => item.title.length > 20 ? item.title.substring(0, 20) + '...' : item.title);
            const enrollments = courseData.map(item => parseInt(item.enrollments));

            new Chart(courseEnrollmentCtx, {
                type: 'bar',
                data: {
                    labels: courseNames,
                    datasets: [{
                        label: 'Enrollments',
                        data: enrollments,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(139, 92, 246)',
                            'rgb(236, 72, 153)'
                        ],
                        borderWidth: 2,
                        borderRadius: 6,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: { size: 12 },
                            bodyFont: { size: 11 },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { size: 10 },
                                padding: 5
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 0,
                                autoSkip: false
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Course Distribution Pie Chart
        const courseDistCtx = document.getElementById('courseDistributionChart');
        if (courseDistCtx) {
            // Get top 5 courses for pie chart
            const topCourses = courseData.slice(0, 5);
            const courseNames = topCourses.map(item => item.title);
            const courseEnrollments = topCourses.map(item => parseInt(item.enrollments));
            
            // Generate colors
            const colors = [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ];

            new Chart(courseDistCtx, {
                type: 'pie',
                data: {
                    labels: courseNames,
                    datasets: [{
                        data: courseEnrollments,
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 10 },
                                padding: 10,
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: { size: 12 },
                            bodyFont: { size: 11 },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

</script>
</body>
</html>