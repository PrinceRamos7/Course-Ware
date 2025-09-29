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



$students = $conn->query("SELECT id, first_name, last_name, email, created_at FROM learners")->fetchAll(PDO::FETCH_ASSOC);
$courses = $conn->query("SELECT id, title FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$modules = $conn->query("SELECT id, title FROM modules")->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Calculate All Statistics (The fix for $totalEnrollments is here) ---
$totalLearners = count($students);
$totalCourses = count($courses);
$totalModules = count($modules);


try {
    $recentLearners = $conn->query("SELECT first_name, last_name, created_at FROM learners ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DATABASE QUERY ERROR: Failed to fetch recent learners. Error: " . $e->getMessage());
}

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
<style>
    :root {
        /* Defining the few custom colors used only in the dashboard that aren't in your main theme */
        --color-teal: #0d9488; /* Used for the Course card */
        --color-dark-green: #14532d; /* Used for the Module card */
    }
    body { 
        color: var(--color-text); 
        background-color: var(--color-main-bg); 
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
</style>
</head>
<body class="min-h-screen flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col">
    <?php include "header.php";
    renderHeader("ISU Admin Dashboard");
    ?>

<main class="p-6 space-y-6">
    <div class="grid grid-cols-4 gap-4">
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-heading)]" data-target="usersModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)]">Total Learners</h3>
                <i class="fas fa-users text-[var(--color-heading)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-heading)]"><?= $totalLearners ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Registered users</p>
        </div>
        
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-teal)]" data-target="coursesModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)]">Total Courses</h3>
                <i class="fas fa-book-open text-[var(--color-teal)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-teal)]"><?= $totalCourses ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Active programs</p>
        </div>
        
        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in cursor-pointer border-l-4 border-[var(--color-dark-green)]" data-target="modulesModal">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)]">Total Modules</h3>
                <i class="fas fa-layer-group text-[var(--color-dark-green)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-dark-green)]"><?= $totalModules ?></div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Content units</p>
        </div>

        <div class="card-bg p-4 rounded-lg shadow-md hover-scale fade-in border-l-4 border-[var(--color-heading-secondary)]">
            <div class="flex justify-between items-start mb-2">
                <h3 class="stat-title font-semibold text-[var(--color-text-secondary)]">Total Enrollments</h3>
                <i class="fas fa-graduation-cap text-[var(--color-heading-secondary)] text-xl"></i>
            </div>
            <div class="stat-number font-bold text-[var(--color-heading-secondary)]"> 2 </div>
            <p class="text-xs text-[var(--color-text-secondary)] mt-1">Cumulative registrations</p>
        </div>
    </div>

    <hr class="border-[var(--color-sidebar-border)]">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 card-bg p-5 rounded-lg shadow-md fade-in">
            <h2 class="text-lg font-semibold mb-3 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">Recent Learner Signups</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--color-card-section-border)]">
                    <thead class="bg-yellow-50/50"> <tr>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">Learner Name</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-[var(--color-text)] uppercase tracking-wider">Date Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-card-section-border)]">
                        <?php if (count($recentLearners) > 0): ?>
                            <?php foreach ($recentLearners as $learner): ?>
                                <tr class="hover:bg-yellow-100/30"> <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-[var(--color-text)]">
                                    <?= htmlspecialchars($learner['first_name'] . ' ' . $learner['last_name']) ?>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                        <?= date('Y-m-d', strtotime($learner['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="px-3 py-2 text-center text-sm text-[var(--color-text-secondary)]">No recent signups found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="card-bg p-5 rounded-lg shadow-md fade-in">
                <h2 class="text-lg font-semibold mb-3 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">System Health</h2>
                <div class="space-y-3 text-sm">
                    <p class="flex justify-between items-center">
                        <span class="font-medium">Database Status:</span>
                        <span class="text-[var(--color-heading)] font-bold"><i class="fas fa-check-circle mr-1"></i> Online</span>
                    </p>
                    <p class="flex justify-between items-center">
                        <span class="font-medium">Cache Status:</span>
                        <span class="text-[var(--color-heading-secondary)] font-bold"><i class="fas fa-times-circle mr-1"></i> Disabled</span>
                    </p>
                    <p class="flex justify-between items-center">
                        <span class="font-medium">Last Backup:</span>
                        <span class="text-[var(--color-text-secondary)] font-bold">2 hours ago</span>
                    </p>
                </div>
            </div>

            <div class="card-bg p-5 rounded-lg shadow-md fade-in">
                <h2 class="text-lg font-semibold mb-3 border-b pb-2 border-[var(--color-card-section-border)] text-[var(--color-heading)]">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="/admin/create_course.php" class="block w-full text-center btn-primary-themed">
                        <i class="fas fa-plus mr-2"></i> Create New Course
                    </a>
                    <a href="/admin/manage_users.php" class="block w-full text-center btn-secondary-themed">
                        <i class="fas fa-user-edit mr-2"></i> Manage Users
                    </a>
                </div>
            </div>
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
$courseFields = ['id' => 'ID', 'title' => 'Title', 'created_at' => 'Date Added'];
$moduleFields = ['id' => 'ID', 'title' => 'Title'];

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
        const input = modal.querySelector('.searchInput');
        const list = modal.querySelector('.modal-list');
        if (input && list) {
            input.addEventListener('input', () => {
                const query = input.value.toLowerCase();
                list.querySelectorAll('li').forEach(li => {
                    li.style.display = li.textContent.toLowerCase().includes(query) ? 'block' : 'none';
                });
            });
        }
    });
</script>
</body>
</html>