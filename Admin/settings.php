<?php
require __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND type = 'admin'");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-grow ml-16 transition-all duration-300 flex flex-col">
        <?php 
        include "header.php";
        renderHeader("Settings");
        ?>

        <!-- Session Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="m-6 mb-0 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?= htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="m-6 mb-0 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="p-6 space-y-6">
            <!-- Account Settings -->
            <div class="bg-[var(--color-card-bg)] rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-[var(--color-heading)] mb-6">
                    <i class="fas fa-user-cog mr-2"></i> Account Settings
                </h2>
                
                <form method="POST" action="update_settings.php" class="space-y-4">
                    <input type="hidden" name="action" value="update_account">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($admin['first_name'] ?? ''); ?>" 
                                class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($admin['middle_name'] ?? ''); ?>" 
                                class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($admin['last_name'] ?? ''); ?>" 
                                class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" 
                            class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Contact Number</label>
                        <input type="text" name="contact" value="<?= htmlspecialchars($admin['contact'] ?? ''); ?>" 
                            class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]">
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-3 bg-[var(--color-button-primary)] text-white rounded-lg hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-[var(--color-card-bg)] rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-[var(--color-heading)] mb-6">
                    <i class="fas fa-lock mr-2"></i> Change Password
                </h2>
                
                <form method="POST" action="update_settings.php" class="space-y-4">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Current Password</label>
                        <input type="password" name="current_password" 
                            class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">New Password</label>
                        <input type="password" name="new_password" id="new_password"
                            class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                        <small class="text-gray-500">Minimum 8 characters</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-text)] mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="w-full p-3 border border-[var(--color-input-border)] rounded-lg focus:ring-2 focus:ring-[var(--color-icon)]" required>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-3 bg-[var(--color-button-primary)] text-white rounded-lg hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
                            <i class="fas fa-key mr-2"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>


        </div>
    </div>

    <script>
        // Password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = this.value;
            
            if (newPass !== confirmPass) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
