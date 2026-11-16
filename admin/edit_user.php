<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Edit User - PSA Admin';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$id = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM admins WHERE id = $id")->fetch_assoc();
if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    // Handle profile picture upload
    $profile_pic = $user['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = '../uploads/users/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        // Delete old profile pic if exists
        if ($profile_pic && file_exists($uploadDir . $profile_pic)) {
            @unlink($uploadDir . $profile_pic);
        }
        $profile_pic = time() . '_' . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $profile_pic);
    }

    // Track old values for audit
    $oldValues = [
        'username' => $user['username'],
        'email' => $user['email'],
        'profile_pic' => $user['profile_pic']
    ];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, password=?, profile_pic=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $profile_pic, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, profile_pic=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $profile_pic, $id);
    }

    if ($stmt->execute()) {
        $newValues = [
            'username' => $username,
            'email' => $email,
            'profile_pic' => $profile_pic
        ];
        logAudit($conn, 'update', 'admins', $id, $oldValues, $newValues);
        $success = "User updated successfully!";
        $user = $conn->query("SELECT * FROM admins WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update user.";
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Edit User</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update user details</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_users.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
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

        <form action="" method="post" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Profile Picture (leave blank to keep existing)</label>
                    <input type="file" name="profile_pic" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                    <?php if (!empty($user['profile_pic'])): ?>
                        <div class="mt-2">
                            <img src="uploads/users/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current: <?= htmlspecialchars($user['profile_pic']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_users.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Update User</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
