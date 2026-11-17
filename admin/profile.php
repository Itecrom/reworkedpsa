<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'My Profile - PSA Admin';

$username = $_SESSION['admin'];
$admin = $conn->query("SELECT * FROM admins WHERE username = '" . $conn->real_escape_string($username) . "'")->fetch_assoc();

if (!$admin) {
    die("Admin not found.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Handle profile picture upload
    $profile_pic = $admin['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/users/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            $error = "Unable to prepare profile picture storage.";
        }
        if (empty($error)) {
            $profile_pic = time() . '_' . basename($_FILES['profile_pic']['name']);
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $profile_pic)) {
                if (!empty($admin['profile_pic']) && file_exists($uploadDir . basename($admin['profile_pic']))) {
                    unlink($uploadDir . basename($admin['profile_pic']));
                }
            } else {
                $error = "Unable to save the profile picture.";
            }
        }
    }

    // Validate current password if trying to change password
    if (!empty($new_password)) {
        if (!password_verify($current_password, $admin['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        }
    }

    if (empty($error)) {
        $oldValues = [
            'username' => $admin['username'],
            'email' => $admin['email'],
            'profile_pic' => $admin['profile_pic']
        ];

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, password=?, profile_pic=? WHERE id=?");
            $stmt->bind_param("ssssi", $new_username, $email, $hashed_password, $profile_pic, $admin['id']);
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, profile_pic=? WHERE id=?");
            $stmt->bind_param("sssi", $new_username, $email, $profile_pic, $admin['id']);
        }

        if ($stmt->execute()) {
            $newValues = [
                'username' => $new_username,
                'email' => $email,
                'profile_pic' => $profile_pic
            ];
            logAudit($conn, 'update', 'admins', $admin['id'], $oldValues, $newValues);
            $success = "Profile updated successfully!";
            $_SESSION['admin'] = $new_username;
            $admin = $conn->query("SELECT * FROM admins WHERE id = " . (int)$admin['id'])->fetch_assoc();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 md:ml-64 min-h-screen transition-all duration-300" id="main-content">
    <!-- Top Header Bar -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40 transition-colors duration-300">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button id="menu-toggle" class="md:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">My Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update your account settings</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if ($success): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <div class="relative inline-block">
                        <?php if (!empty($admin['profile_pic']) && file_exists(__DIR__ . '/../uploads/users/' . basename($admin['profile_pic']))): ?>
                            <img src="../uploads/users/<?= rawurlencode(basename($admin['profile_pic'])) ?>" alt="Profile" class="w-24 h-24 rounded-full object-cover mx-auto mb-4">
                        <?php else: ?>
                            <div class="w-24 h-24 bg-psaGreen rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-white text-3xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($admin['email'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Role: <?= htmlspecialchars($admin['role'] ?? 'admin') ?></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Joined: <?= date('M d, Y', strtotime($admin['created_at'] ?? 'now')) ?></p>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <form action="" method="post" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Profile Picture</label>
                            <input type="file" name="profile_pic" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload a new profile picture (optional)</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mt-8 mb-4">Change Password</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Leave blank to keep your current password.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Current Password</label>
                            <input type="password" name="current_password" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">New Password</label>
                            <input type="password" name="new_password" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        </div>
                        <div>
                            <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                        <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
