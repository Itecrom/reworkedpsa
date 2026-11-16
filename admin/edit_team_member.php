<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Edit Team Member - PSA Admin';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_team.php");
    exit;
}

$id = (int)$_GET['id'];
$member = $conn->query("SELECT * FROM team_members WHERE id = $id")->fetch_assoc();
if (!$member) {
    die("Team member not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $google = trim($_POST['google'] ?? '');

    $image = $member['image'];
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/team/';
        if (is_dir($uploadDir)) {
            $image = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }
    }

    $stmt = $conn->prepare("UPDATE team_members SET name=?, position=?, category=?, email=?, phone=?, bio=?, image=?, facebook=?, twitter=?, linkedin=?, google=? WHERE id=?");
    $stmt->bind_param("sssssssssssi", $name, $position, $category, $email, $phone, $bio, $image, $facebook, $twitter, $linkedin, $google, $id);

    if ($stmt->execute()) {
        $oldValues = ['name' => $member['name'], 'position' => $member['position']];
        $newValues = ['name' => $name, 'position' => $position];
        logAudit($conn, 'update', 'team_members', $id, $oldValues, $newValues);
        $success = "Team member updated successfully!";
        $member = $conn->query("SELECT * FROM team_members WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update team member.";
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Edit Team Member</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update team member details</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_team.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
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
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Position <span class="text-red-500">*</span></label>
                    <input type="text" name="position" value="<?= htmlspecialchars($member['position']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Category</label>
                    <select name="category" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="executive" <?= $member['category'] === 'executive' ? 'selected' : '' ?>>Executive</option>
                        <option value="board" <?= $member['category'] === 'board' ? 'selected' : '' ?>>Board</option>
                        <option value="staff" <?= $member['category'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="other" <?= $member['category'] === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Bio</label>
                    <textarea name="bio" rows="4" class="ckeditor w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Facebook URL</label>
                    <input type="url" name="facebook" value="<?= htmlspecialchars($member['facebook'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Twitter URL</label>
                    <input type="url" name="twitter" value="<?= htmlspecialchars($member['twitter'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">LinkedIn URL</label>
                    <input type="url" name="linkedin" value="<?= htmlspecialchars($member['linkedin'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Google URL</label>
                    <input type="url" name="google" value="<?= htmlspecialchars($member['google'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Profile Image (leave blank to keep existing)</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                    <?php if (!empty($member['image'])): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current: <?= htmlspecialchars($member['image']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_team.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Update Member</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
