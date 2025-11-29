<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
require_once '../pdoconfig.php';

// Include the existing functions
include '../Learner/functions/completed_info.php';
include '../Learner/functions/calculate_level.php';

// Handle form submission for profile editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $about_me = trim($_POST['about_me']);
    
    // Validate required fields
    if (!empty($first_name) && !empty($last_name)) {
        try {
            $update_query = "UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, address = ?, contact_number = ?, about_me = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_query);
            $stmt->execute([$first_name, $last_name, $middle_name, $address, $contact_number, $about_me, $student_id]);
            
            // Update session variables
            $_SESSION['student_name'] = $first_name . ' ' . ($middle_name ? $middle_name[0] . '.' : '') . ' ' . $last_name;
            
            $success_message = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    } else {
        $error_message = "First name and last name are required!";
    }
}

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $upload_result = handleImageUpload('profile_picture', $student_id, 'profile');
    if ($upload_result['success']) {
        $profile_success = "Profile picture updated successfully!";
    } else {
        $profile_error = $upload_result['error'];
    }
}

// Handle cover photo upload
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === 0) {
    $upload_result = handleImageUpload('cover_photo', $student_id, 'cover');
    if ($upload_result['success']) {
        $cover_success = "Cover photo updated successfully!";
    } else {
        $cover_error = $upload_result['error'];
    }
}

// Function to handle image uploads
function handleImageUpload($file_input_name, $user_id, $type) {
    global $pdo;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file = $_FILES[$file_input_name];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, PNG, and GIF files are allowed.'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size must be less than 5MB.'];
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = $type . '_' . $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Update database
        $column_name = $type === 'profile' ? 'profile_picture' : 'cover_photo';
        $update_query = "UPDATE users SET $column_name = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$new_filename, $user_id]);
        
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'error' => 'Failed to upload file.'];
    }
}

// Fetch user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_query);
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !is_array($user)) {
    die("User not found");
}

// Fetch user's unlocked achievements
$unlocked_achievements = [];
try {
    $achievements_query = "
        SELECT a.*, sa.unlocked_at 
        FROM achievements a 
        INNER JOIN student_achievements sa ON a.id = sa.achievement_id 
        WHERE sa.student_id = ? 
        ORDER BY sa.unlocked_at DESC
    ";
    $stmt = $pdo->prepare($achievements_query);
    $stmt->execute([$student_id]);
    $unlocked_achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If achievements table doesn't exist yet, use empty array
    $unlocked_achievements = [];
}

// Set safe default values from user table
$first_name = isset($user['first_name']) ? htmlspecialchars($user['first_name']) : 'User';
$last_name = isset($user['last_name']) ? htmlspecialchars($user['last_name']) : '';
$middle_name = isset($user['middle_name']) ? htmlspecialchars($user['middle_name']) : '';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : '';
$address = isset($user['address']) ? htmlspecialchars($user['address']) : 'Location not set';
$contact_number = isset($user['contact_number']) ? htmlspecialchars($user['contact_number']) : 'Not set';
$about_me = isset($user['about_me']) ? htmlspecialchars($user['about_me']) : "I'm a passionate learner on a mission to master new skills and collect all the knowledge I can. My journey with ISUtoLearn has been amazing so far, and I'm excited to see what's next! Currently focusing on advancing my skills.";
$created_at = isset($user['created_at']) ? $user['created_at'] : date('Y-m-d H:i:s');
$experience = isset($user['experience']) ? (int)$user['experience'] : 0;
$intelligent_exp = isset($user['intelligent_exp']) ? (int)$user['intelligent_exp'] : 0;

// Get profile images with fallbacks
$profile_picture = isset($user['profile_picture']) && !empty($user['profile_picture']) 
    ? '../uploads/' . $user['profile_picture'] 
    : '../images/yuta.jpg';

$cover_photo = isset($user['cover_photo']) && !empty($user['cover_photo'])
    ? '../uploads/' . $user['cover_photo']
    : '../images/mochay.jpg';

// Use your existing function to get completed courses
$completed_courses = get_completed_courses($student_id);
$courses_completed = is_array($completed_courses) ? count($completed_courses) : 0;

// Use your existing function to calculate level
$maxExp = 1000;
$maxLevel = 10;
list($user_level, $level_progress, $current_exp, $next_level_exp) = getUserLevel($experience, $maxExp, $maxLevel);

// Calculate intelligence level based on intelligent_exp (using your existing logic)
if ($intelligent_exp >= 1000) {
    $intel_level = "Master";
    $intel_percentage = 95;
} elseif ($intelligent_exp >= 700) {
    $intel_level = "Advanced";
    $intel_percentage = 85;
} elseif ($intelligent_exp >= 400) {
    $intel_level = "Intermediate";
    $intel_percentage = 65;
} else {
    $intel_level = "Beginner";
    $intel_percentage = 30;
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
    <style>
        .custom-scrollbar-hide::-webkit-scrollbar { 
            display: none; 
        }
        .custom-scrollbar-hide { 
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .progress-bar-track {
            height: 6px;
            border-radius: 9999px;
            background-color: var(--color-progress-bg);
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-out;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--color-card-bg);
            border: 2px solid var(--color-card-border);
            border-radius: 1rem;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-text-secondary);
        }
        
        .image-upload-container {
            border: 2px dashed var(--color-card-border);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            margin: 1rem 0;
            transition: border-color 0.3s;
        }
        
        .image-upload-container:hover {
            border-color: var(--color-button-primary);
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 0 auto 1rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .tab-container {
            display: flex;
            border-bottom: 1px solid var(--color-card-border);
            margin-bottom: 1rem;
        }
        
        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: var(--color-button-primary);
            color: var(--color-button-primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .unlocked-achievement {
            cursor: pointer;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
        }
        .unlocked-achievement:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), 0 0 10px rgba(79, 70, 229, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex" style="background-color: var(--color-main-bg); color: var(--color-text);">
    
    <?php include 'sidebar.php'?>
    
    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="text-2xl font-bold mb-6" style="color: var(--color-heading);">Edit Profile</h2>
            
            <div class="tab-container">
                <div class="tab active" onclick="switchTab('profile-tab')">Profile Info</div>
                <div class="tab" onclick="switchTab('images-tab')">Images</div>
            </div>
            
            <!-- Profile Info Tab -->
            <div id="profile-tab" class="tab-content active">
                <?php if (isset($success_message)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 border border-green-300">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 border border-red-300">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">First Name *</label>
                            <input type="text" name="first_name" value="<?php echo $first_name; ?>" required
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">Middle Name</label>
                            <input type="text" name="middle_name" value="<?php echo $middle_name; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">Last Name *</label>
                            <input type="text" name="last_name" value="<?php echo $last_name; ?>" required
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">About Me</label>
                            <textarea name="about_me" 
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Tell us about yourself..."><?php echo $about_me; ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">Email</label>
                            <input type="email" value="<?php echo $email; ?>" disabled
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-gray-100 text-gray-500 cursor-not-allowed">
                            <p class="text-xs mt-1" style="color: var(--color-text-secondary);">Email cannot be changed</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">Address</label>
                            <input type="text" name="address" value="<?php echo $address; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text);">Contact Number</label>
                            <input type="tel" name="contact_number" value="<?php echo $contact_number; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-[var(--color-input-border)] bg-[var(--color-input-bg)] text-[var(--color-text)] focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex space-x-4 mt-6">
                        <button type="button" onclick="closeModal()" 
                                class="flex-1 px-4 py-2 rounded-lg border border-[var(--color-card-border)] text-[var(--color-text)] hover:bg-[var(--color-card-section-bg)] transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90"
                                style="background-color: var(--color-button-primary);">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Images Tab -->
            <div id="images-tab" class="tab-content">
                <?php if (isset($profile_success)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 border border-green-300">
                        <?php echo $profile_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($profile_error)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 border border-red-300">
                        <?php echo $profile_error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Profile Picture Upload -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--color-heading);">Profile Picture</h3>
                    <div class="image-upload-container">
                        <div class="image-preview">
                            <img src="<?php echo $profile_picture; ?>" class="w-full h-full object-cover" id="profile-preview">
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="file" name="profile_picture" accept="image/*" class="hidden" id="profile-input" onchange="previewImage(this, 'profile-preview')">
                            <label for="profile-input" class="cursor-pointer px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90 inline-block"
                                   style="background-color: var(--color-button-primary);">
                                <i class="fas fa-camera mr-2"></i>Change Profile Picture
                            </label>
                            <p class="text-xs mt-2" style="color: var(--color-text-secondary);">JPG, PNG, GIF up to 5MB</p>
                        </form>
                    </div>
                </div>
                
                <?php if (isset($cover_success)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 border border-green-300">
                        <?php echo $cover_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($cover_error)): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 border border-red-300">
                        <?php echo $cover_error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Cover Photo Upload -->
                <div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--color-heading);">Cover Photo</h3>
                    <div class="image-upload-container">
                        <div class="image-preview">
                            <img src="<?php echo $cover_photo; ?>" class="w-full h-full object-cover" id="cover-preview">
                        </div>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="file" name="cover_photo" accept="image/*" class="hidden" id="cover-input" onchange="previewImage(this, 'cover-preview')">
                            <label for="cover-input" class="cursor-pointer px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90 inline-block"
                                   style="background-color: var(--color-button-primary);">
                                <i class="fas fa-image mr-2"></i>Change Cover Photo
                            </label>
                            <p class="text-xs mt-2" style="color: var(--color-text-secondary);">JPG, PNG, GIF up to 5MB</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden achievements data for JavaScript -->
    <div id="achievements-data" style="display: none;">
        <?php echo json_encode($unlocked_achievements); ?>
    </div>
    
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar-hide">
        <header class="main-header backdrop-blur-sm p-4 shadow-lg px-6 py-3 flex justify-between items-center z-10" 
                    style="background-color: var(--color-header-bg); border-bottom: 1px solid var(--color-card-border);">
            <div class="flex items-center">
                <button class="mobile-menu-button md:hidden bg-[var(--color-card-bg)] rounded-lg p-2 text-[var(--color-text)]">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-text);">👤 User Profile</h1>
            </div>
        </header>

        <main class="flex-1 px-6 md:px-12 py-8">
            <div class="w-full max-w-5xl mx-auto space-y-8">
                
                <div class="rounded-2xl shadow-2xl" 
                     style="background-color: var(--color-card-bg); border: 2px solid var(--color-card-border);">
                    
                    <div class="h-48 rounded-t-2xl bg-gray-600 overflow-hidden relative" 
                         style="background-image: url('<?php echo $cover_photo; ?>'); background-size: cover; background-position: center; border-bottom: 1px solid var(--color-card-border);">
                         <button onclick="switchToImagesTab()" class="absolute top-4 right-4 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition">
                            <i class="fas fa-camera"></i>
                         </button>
                    </div>

                    <div class="px-8 pb-8">
                        <div class="flex flex-col md:flex-row md:items-end -mt-16 md:-mt-10">
                            <div class="w-32 h-32 rounded-full overflow-hidden shadow-xl flex-shrink-0 bg-white relative" 
                                 style="border: 6px solid var(--color-card-bg);">
                                <img src="<?php echo $profile_picture; ?>" class="w-full h-full object-cover">
                                <button onclick="switchToImagesTab()" class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white p-1 rounded-full hover:bg-opacity-70 transition">
                                    <i class="fas fa-camera text-xs"></i>
                                </button>
                            </div>

                            <div class="pt-4 md:pt-0 md:pl-6 flex-1 text-left md:flex md:justify-between md:items-end">
                                <div class="space-y-1">
                                    <h2 class="text-3xl font-extrabold" style="color: var(--color-heading);">
                                        <?php echo $first_name . ' ' . $last_name; ?>
                                    </h2>
                                    <p class="text-md font-medium" style="color: var(--color-text-secondary);">
                                        <?php echo $email; ?>
                                    </p>
                                </div>
                                <div class="mt-4 md:mt-0 space-x-4">
                                    <button onclick="openModal()" class="px-5 py-2 rounded-full text-sm font-semibold transition hover:opacity-90 shadow-md"
                                            style="background-color: var(--color-button-primary); color: var(--color-button-secondary-text);">
                                        <i class="fas fa-edit mr-2"></i> Edit Profile
                                    </button>
                                    <button onclick="location.href='settings.php'" class="px-5 py-2 rounded-full text-sm font-semibold transition hover:opacity-90 shadow-md"
                                            style="background-color: var(--color-progress-bg); color: var(--color-text-secondary);">
                                        <i class="fas fa-cog mr-2"></i> Settings
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4">
                            <h3 class="text-xl font-bold" style="color: var(--color-heading-secondary);">Learning Progress</h3>
                            <div class="flex flex-col md:flex-row md:space-x-8 space-y-4 md:space-y-0">
                                
                                <div class="flex-1 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium" style="color: var(--color-text);">Level Progress</p>
                                        <p class="text-sm font-bold" style="color: var(--color-text-secondary);">
                                            Level <?php echo $user_level; ?> (<?php echo round($level_progress, 1); ?>%)
                                        </p>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" 
                                             style="background: var(--color-progress-fill); width: <?php echo $level_progress; ?>%;"></div>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium" style="color: var(--color-text);">Intelligence Level</p>
                                        <p class="text-sm font-bold" style="color: var(--color-text-secondary);">
                                            <?php echo $intel_level; ?>
                                        </p>
                                    </div>
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" 
                                             style="background: linear-gradient(to right, var(--color-heading), var(--color-heading-secondary)); width: <?php echo $intel_percentage; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 space-y-8">
                        
                        <div class="p-8 rounded-2xl shadow-lg space-y-4" 
                              style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">📝 About <?php echo $first_name; ?></h3>
                            <p class="text-base leading-relaxed font-light" style="color: var(--color-text);">
                                <?php echo nl2br($about_me); ?>
                            </p>
                            <div class="flex items-center space-x-2 text-sm pt-2" style="color: var(--color-text-secondary);">
                                <i class="fas fa-map-marker-alt"></i>
                                <p><?php echo $address; ?></p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm" style="color: var(--color-text-secondary);">
                                <i class="fas fa-phone"></i>
                                <p><?php echo $contact_number ?: 'Not provided'; ?></p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm" style="color: var(--color-text-secondary);">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Joined <?php echo date('F Y', strtotime($created_at)); ?></p>
                            </div>
                        </div>

                        <div class="p-8 rounded-2xl shadow-lg space-y-4"
                             style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">🔥 Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center border-b pb-2" style="border-color: var(--color-card-border);">
                                    <p class="font-medium" style="color: var(--color-text);">Current Level</p>
                                    <p class="text-xl font-extrabold text-blue-500"><?php echo $user_level; ?></p>
                                </div>
                                <div class="flex justify-between items-center border-b pb-2" style="border-color: var(--color-card-border);">
                                    <p class="font-medium" style="color: var(--color-text);">Courses Completed</p>
                                    <p class="text-xl font-extrabold" style="color: var(--color-button-primary);"><?php echo $courses_completed; ?></p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <p class="font-medium" style="color: var(--color-text);">Total Experience</p>
                                    <p class="text-xl font-extrabold text-green-500"><?php echo $experience; ?> XP</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Real Achievements Section -->
                        <div class="p-8 rounded-2xl shadow-lg space-y-6"
                             style="background-color: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                            <h3 class="text-2xl font-extrabold" style="color: var(--color-heading-secondary);">
                                🎖️ Achievements Unlocked (<?php echo count($unlocked_achievements); ?>)
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                                <?php if (!empty($unlocked_achievements)): ?>
                                    <?php foreach ($unlocked_achievements as $achievement): ?>
                                        <div class="p-4 rounded-xl shadow-md flex flex-col items-center justify-center space-y-2 text-center transition hover:scale-105 unlocked-achievement"
                                             style="background-color: var(--color-card-section-bg); border: 1px solid var(--color-card-section-border);"
                                             onclick="showAchievementModal(<?php echo $achievement['id']; ?>)">
                                            <i class="<?php echo $achievement['icon']; ?> text-4xl" style="color: <?php echo $achievement['icon_color']; ?>;"></i>
                                            <p class="text-sm font-bold mt-2" style="color: var(--color-text);"><?php echo htmlspecialchars($achievement['title']); ?></p>
                                            <p class="text-xs" style="color: var(--color-text-secondary);"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-span-4 text-center p-8 rounded-xl" style="background-color: var(--color-card-section-bg); color: var(--color-text-secondary);">
                                        <i class="fas fa-trophy text-5xl mb-4 opacity-50"></i>
                                        <p class="text-lg font-medium">No achievements unlocked yet</p>
                                        <p class="text-sm mt-2">Complete courses and quizzes to earn achievements!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Function to apply the theme from local storage
        function applyThemeFromLocalStorage() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true'; 
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
        }
        
        // Modal functions
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }
        
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function switchToImagesTab() {
            openModal();
            switchTab('images-tab');
        }
        
        function previewImage(input, previewId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    // Auto-submit the form when file is selected
                    input.form.submit();
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Achievement modal function
        function showAchievementModal(achievementId) {
            const achievementsData = JSON.parse(document.getElementById('achievements-data').textContent);
            const achievement = achievementsData.find(a => a.id == achievementId);
            
            if (!achievement) return;
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50';
            modal.innerHTML = `
                <div class="bg-[var(--color-card-bg)] p-6 rounded-2xl shadow-2xl max-w-sm w-full mx-4 border-2" style="border-color: ${achievement.icon_color}">
                    <div class="text-center">
                        <i class="${achievement.icon} text-6xl mb-4" style="color: ${achievement.icon_color}"></i>
                        <h3 class="text-xl font-bold mb-2" style="color: var(--color-heading);">${achievement.title}</h3>
                        <p class="text-sm mb-4" style="color: var(--color-text-secondary);">${achievement.description}</p>
                        <div class="flex justify-center space-x-6 mb-4 text-sm">
                            <div>
                                <span class="font-bold text-yellow-500">+${achievement.xp_reward} XP</span>
                            </div>
                            <div>
                                <span class="font-bold text-blue-500">+${achievement.intelligence_reward} Intel</span>
                            </div>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90"
                                style="background-color: var(--color-button-primary);">
                            Close
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        document.addEventListener('DOMContentLoaded', applyThemeFromLocalStorage);
    </script>
</body>
</html>