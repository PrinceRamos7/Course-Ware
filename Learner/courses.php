<?php
include 'functions/format_time.php';
include 'functions/count_modules.php';
include 'functions/count_topics.php';
include 'functions/count_estimated_time.php';
include 'functions/count_progress_percentage.php';
include 'functions/get_student_progress.php';

$student_id = $_SESSION['student_id'];

$redeem_code = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll_form'])) {
        $enroll_code = strtolower($_POST['course_code']);

        $stmt = $pdo->prepare(
            'SELECT * FROM registration_codes WHERE code = :redeem_code AND active = 1 AND expires_at >= NOW()',
        );
        $stmt->execute([':redeem_code' => $enroll_code]);
        $registration_code = $stmt->fetch();

        if ($registration_code) {
            $pdo->beginTransaction();
            try {
                $redeem_code_id = $registration_code['id'];
                $course_id = $registration_code['course_id'];
                $redeem_code = 'available';

                $stmt = $pdo->prepare('UPDATE registration_codes SET active = 0 WHERE id = :redeem_code_id');
                $stmt->execute([':redeem_code_id' => $redeem_code_id]);

                $stmt = $pdo->prepare('INSERT INTO registration_code_uses (registration_code_id, student_id, used_at) VALUES (:redeem_code_id, :student_id, NOW())');
                $stmt->execute([
                    ':redeem_code_id' => $redeem_code_id,
                    ':student_id' => $student_id,
                ]);

                $stmt = $pdo->prepare('INSERT INTO student_courses (student_id, course_id) VALUES (:student_id, :course_id)');
                $stmt->execute([
                    ':student_id' => $student_id,
                    ':course_id' => $course_id,
                ]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        } else {
            $redeem_code = 'not_available';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISUtoLearn</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script> 

    <style>
        /* Custom scrollbar hide for consistency */
        .custom-scrollbar-hide::-webkit-scrollbar { 
            display: none; 
        }
        .custom-scrollbar-hide { 
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }

        /* Custom Colors based on variables for dynamic feedback */
        .color-success-text { color: var(--color-green-500, #10b981); }
        .color-error-text { color: var(--color-red-500, #ef4444); }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">
    
    <?php include 'sidebar.php'?>
    
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar-hide">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center z-10" 
            style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">📚 Courses</h1>
            </div>
        </header>

        <main class="flex-1 px-6 md:px-12 py-8 flex flex-col items-center justify-start">
            
            <!-- Enrollment Button (Initially Visible) -->
            <button id="show-enrollment-btn" class="p-4 rounded-2xl shadow-xl mb-8 text-center transition-all hover:opacity-90 flex items-center justify-center"
            style="background-color: var(--color-card-bg); border: 2px solid var(--color-heading-secondary); color: var(--color-heading);">
                <i class="fas fa-gift mr-2"></i> Enroll Course
            </button>

            <!-- Enrollment Form Section (Initially Hidden) -->
            <form id="enrollment-section" method="POST" class="p-6 rounded-2xl shadow-xl w-full max-w-md mx-auto text-center space-y-4 mb-8 transition-all hidden" 
            style="background-color: var(--color-card-bg); border: 2px solid var(--color-heading-secondary);">
                <h2 class="text-2xl font-extrabold" style="color: var(--color-heading);">Enroll Course</h2>
                <p class="text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                    Enter a course code to enroll in additional courses.
                </p>
                <div class="space-y-3">
                    <input id="course-code-input" type="text" name="course_code" placeholder="Enter course code..."
                        class="w-full px-4 py-2 rounded-xl text-center transition-all focus:outline-none focus:ring-2 focus:ring-offset-2"
                        style="background-color: var(--color-card-section-bg); color: var(--color-text); border: 1px solid var(--color-card-border); focus-ring-color: var(--color-button-primary);">
                    <p id="enrollment-message" class="font-bold hidden text-xs"></p>
                    <div class="flex space-x-2">
                        <button type="submit" name="enroll_form" class="flex-1 px-4 py-2 rounded-xl font-bold transition-all hover:opacity-90 flex items-center justify-center"
                                style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-primary);">
                            Enroll <i class="fas fa-gift ml-2"></i>
                        </button>
                        <button type="button" id="close-enrollment-btn" class="flex-1 px-4 py-2 rounded-xl font-bold transition-all hover:opacity-90 flex items-center justify-center"
                                style="background-color: var(--color-card-section-bg); color: var(--color-text); border: 1px solid var(--color-card-border);">
                            Close <i class="fas fa-times ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div id="enrollment-section" class="p-6 rounded-2xl shadow-xl w-full max-w-md mx-auto text-center space-y-4 mb-8 transition-all hidden" 
            style="background-color: var(--color-card-bg); border: 2px solid var(--color-heading-secondary);">
                <h2 class="text-2xl font-extrabold" style="color: var(--color-heading);">Enroll Course</h2>
                <p class="text-sm leading-relaxed" style="color: var(--color-text-secondary);">
                    Enter a course code to enroll in additional courses.
                </p>
                <div class="space-y-3">
                    <input id="course-code-input" type="text" name="course_code" placeholder="Enter course code..."
                        class="w-full px-4 py-2 rounded-xl text-center transition-all focus:outline-none focus:ring-2 focus:ring-offset-2"
                        style="background-color: var(--color-card-section-bg); color: var(--color-text); border: 1px solid var(--color-card-border); focus-ring-color: var(--color-button-primary);">
                    <p id="enrollment-message" class="font-bold hidden text-xs"></p>
                    <div class="flex space-x-2">
                        <button id="redeem-code-btn" class="flex-1 px-4 py-2 rounded-xl font-bold transition-all hover:opacity-90 flex items-center justify-center"
                                style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text); border: 1px solid var(--color-button-primary);">
                            Enroll <i class="fas fa-gift ml-2"></i>
                        </button>
                        <button id="close-enrollment-btn" class="flex-1 px-4 py-2 rounded-xl font-bold transition-all hover:opacity-90 flex items-center justify-center"
                                style="background-color: var(--color-card-section-bg); color: var(--color-text); border: 1px solid var(--color-card-border);">
                            Close <i class="fas fa-times ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Courses Section -->
            <div id="courses-section" class="p-8 md:p-10 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto space-y-8"
            style="background-color: var(--color-card-bg); border: 2px solid var(--color-card-border);">
                
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">Available Courses</h2>
                </div>
                
                <div id="courses-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                    <?php
                    $stmt = $pdo->prepare("SELECT c.* FROM courses c
                                    JOIN student_courses sc ON c.id = sc.course_id
                                WHERE sc.student_id = :student_id");
                    $stmt->execute([":student_id" => $student_id]);
                    $courses = $stmt->fetchAll();

                    if ($courses) {
                        foreach ($courses as $course) {
                            $total_minutes = count_estimated_time($course['id']);
                            $exp_gain = count_total_exp($course['id']);
                            $modules = count_modules($course['id']);

                            echo "
                            <div class='p-5 rounded-xl space-y-3 shadow-lg transition-all hover:shadow-xl hover:scale-[1.02] cursor-pointer h-full flex flex-col justify-between'
                                style='background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-border);'>
    
                                <div class='space-y-3'>
                                    <i class='fas fa-book text-3xl' style='color: var(--color-indigo-500, #6366f1);'></i>
                                    <h4 class='text-xl font-bold' style='color: var(--color-heading);'>{$course['title']}</h4>
                                    <p class='text-sm line-clamp-3' style='color: var(--color-text-secondary);'>{$course['description']}</p>
                                </div>

                                <div class='mt-4 pt-4 border-t' style='border-color: var(--color-card-border);'>
                                    <div class='flex justify-between items-center text-xs mb-1 font-semibold'>
                                        <span style='color: var(--color-text-secondary);'>Progress</span>
                                        <span style='color: var(--color-heading);'>" . count_progress_percentage($course['id']) . "%</span>
                                    </div>
                                    <div class='w-full h-2 rounded-full mb-3' style='background-color: var(--color-progress-bg);'>
                                        <div class='h-full rounded-full' 
                                            style='width: " . count_progress_percentage($course['id']) . "%; background-color: var(--color-indigo-500, #6366f1); transition: width 0.5s;'>
                                        </div>
                                    </div>

                                    <div class='text-xs font-semibold mb-2' style='color: var(--color-text-secondary);'>
                                        <span>" . count_topics($course['id']) . " Topics</span> • 
                                        <span>{$modules['total_modules']} Modules</span> • 
                                        <span>" . formatTime($total_minutes) . "</span> • 
                                        <span>" . number_format($exp_gain[0]) . " EXP</span>
                                    </div>

                                    <a href='modules.php?course_id={$course['id']}' class='inline-flex items-center px-4 py-2 rounded-full text-xs font-bold transition-all bg-opacity-10'
                                    style='background-color: var(--color-indigo-500, #6366f1); color: var(--color-button-secondary-text);'>
                                        Continue <i class='fas fa-play ml-2'></i>
                                    </a>
                                </div>
                            </div>
                            "; 
                        }
                    }
                    ?>
                </div>
            </div>
            
        </main>
    </div>

    <script>
        // Theme application functionality
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }

        // Toggle enrollment form visibility
        document.getElementById('show-enrollment-btn').addEventListener('click', function() {
            const enrollmentSection = document.getElementById('enrollment-section');
            const showButton = document.getElementById('show-enrollment-btn');
            
            enrollmentSection.classList.remove('hidden');
            showButton.classList.add('hidden');
            
            // Animate the form appearance
            gsap.fromTo(enrollmentSection, 
                { opacity: 0, y: -20 }, 
                { opacity: 1, y: 0, duration: 0.4, ease: "power2.out" }
            );
        });

        // Close enrollment form
        document.getElementById('close-enrollment-btn').addEventListener('click', function() {
            const enrollmentSection = document.getElementById('enrollment-section');
            const showButton = document.getElementById('show-enrollment-btn');
            
            // Animate the form disappearance
            gsap.to(enrollmentSection, {
                opacity: 0,
                y: -20,
                duration: 0.3,
                onComplete: () => {
                    enrollmentSection.classList.add('hidden');
                    showButton.classList.remove('hidden');
                    
                    // Clear any messages and input
                    document.getElementById('enrollment-message').classList.add('hidden');
                    document.getElementById('course-code-input').value = '';
                }
            });
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            applyThemeFromLocalStorage();
        });

        let redeemCodeStatus = '<?php echo $redeem_code; ?>';
        const enrollmentMessage = document.getElementById('enrollment-message');
        if (redeemCodeStatus === 'available') {
            enrollmentMessage.textContent = 'Success! You have been enrolled in the course.';
            enrollmentMessage.classList.remove('hidden', 'color-error-text');
            enrollmentMessage.classList.add('color-success-text');
        } else if (redeemCodeStatus === 'not_available') {
            enrollmentMessage.textContent = 'Error: Invalid or expired course code.';
            enrollmentMessage.classList.remove('hidden', 'color-success-text');
            enrollmentMessage.classList.add('color-error-text');
        }
    </script>
</body>
</html>