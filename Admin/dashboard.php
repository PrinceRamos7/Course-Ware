<?php
include '../config.php';

// Fetch data from DB
$students = $conn->query("SELECT id, first_name, last_name FROM learners")->fetchAll(PDO::FETCH_ASSOC);
$courses = $conn->query("SELECT id, title FROM courses")->fetchAll(PDO::FETCH_ASSOC);
$modules = $conn->query("SELECT id, title FROM modules")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* Animations */
.fade-in { opacity: 0; transform: translateY(20px); transition: all 0.5s ease-out; }
.fade-in.visible { opacity: 1; transform: translateY(0); }
.hover-scale:hover { transform: scale(1.05); transition: transform 0.3s ease-in-out; }

/* Modal */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 50;
}
.modal.active {
    display: flex;
}
.modal-content {
    background: white;
    border-radius: 1rem;
    max-height: 80vh;
    overflow-y: auto;
    padding: 1.5rem;
    width: 90%;
    max-width: 600px;

    /* Slide down animation */
    transform: translateY(-100px);
    opacity: 0;
    transition: transform 0.4s ease-out, opacity 0.4s ease-out;
}

.modal.active .modal-content {
    transform: translateY(0);
    opacity: 1;
}
</style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col">
<header class="bg-white shadow-md p-6 flex justify-between items-center">
    <div class="fade-in">
        <h1 class="text-2xl font-bold">Hello, Admin!</h1>
        <p class="text-gray-500">Quick overview of the system</p>
    </div>
    <button class="flex items-center space-x-2 px-3 py-2 rounded-full shadow bg-gray-100 hover:bg-gray-200 transition hover-scale">
        <i class="fas fa-user-circle text-2xl"></i>
        <span>Admin</span>
    </button>
</header>

<main class="p-8 space-y-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow hover-scale fade-in cursor-pointer" data-target="usersModal">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Total Users</h3>
                <i class="fas fa-users text-blue-500 text-2xl"></i>
            </div>
            <div class="text-3xl font-bold text-blue-500"><?= count($students) ?></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow hover-scale fade-in cursor-pointer" data-target="coursesModal">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Total Courses</h3>
                <i class="fas fa-book-open text-green-500 text-2xl"></i>
            </div>
            <div class="text-3xl font-bold text-green-500"><?= count($courses) ?></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow hover-scale fade-in cursor-pointer" data-target="modulesModal">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Total Modules</h3>
                <i class="fas fa-book text-purple-500 text-2xl"></i>
            </div>
            <div class="text-3xl font-bold text-purple-500"><?= count($modules) ?></div>
        </div>
    </div>
</main>
</div>

<!-- Modals -->
<?php
function renderModal($id, $title, $items, $fields) {
    echo '<div id="'.$id.'" class="modal">';
    echo '<div class="modal-content">';
    echo '<div class="flex justify-between items-center mb-4">';
    echo '<h2 class="text-xl font-bold">'.$title.'</h2>';
    echo '<button class="closeModal text-gray-500 hover:text-gray-800"><i class="fas fa-times"></i></button>';
    echo '</div>';
    echo '<input type="text" class="searchInput w-full mb-4 p-2 border rounded" placeholder="Search '.$title.'...">';
    echo '<ul class="space-y-2 modal-list">';
    foreach ($items as $item) {
        echo '<li class="p-2 border-b border-gray-200">';
        $line = [];
        foreach ($fields as $field) $line[] = $item[$field];
        echo implode(' - ', $line);
        echo '</li>';
    }
    echo '</ul></div></div>';
}

renderModal('usersModal', 'All Users', $students, ['id','first_name','last_name']);
renderModal('coursesModal', 'All Courses', $courses, ['id','title']);
renderModal('modulesModal', 'All Modules', $modules, ['id','title']);
?>

<script>
// Animate on scroll
const fadeEls = document.querySelectorAll('.fade-in');
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if(entry.isIntersecting) entry.target.classList.add('visible');
    });
}, { threshold: 0.1 });
fadeEls.forEach(el => observer.observe(el));

// Open modal
document.querySelectorAll('.hover-scale[data-target]').forEach(card => {
    card.addEventListener('click', () => {
        const modalId = card.dataset.target;
        document.getElementById(modalId).classList.add('active');
    });
});

// Close modal
document.querySelectorAll('.closeModal').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.modal').classList.remove('active');
    });
});

// Close when clicking outside modal-content
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', e => {
        if(e.target === modal) modal.classList.remove('active');
    });
});

// Modal search
document.querySelectorAll('.modal').forEach(modal => {
    const input = modal.querySelector('.searchInput');
    const list = modal.querySelector('.modal-list');
    input.addEventListener('input', () => {
        const query = input.value.toLowerCase();
        list.querySelectorAll('li').forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
});
</script>
</body>
</html>
