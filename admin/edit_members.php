<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Edit Member - PSA Admin';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$id = (int)$_GET['id'];
$member = $conn->query("SELECT * FROM psa_members WHERE id = $id")->fetch_assoc();
if (!$member) {
    die("Member not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $employer = trim($_POST['employer'] ?? '');
    $expertise = trim($_POST['expertise'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $contacts = trim($_POST['contacts'] ?? '');

    $stmt = $conn->prepare("UPDATE psa_members SET name=?, email=?, phone=?, employer=?, expertise=?, qualification=?, contacts=? WHERE id=?");
    $stmt->bind_param("sssssssi", $name, $email, $phone, $employer, $expertise, $qualification, $contacts, $id);

    if ($stmt->execute()) {
        $oldValues = ['name' => $member['name'], 'expertise' => $member['expertise']];
        $newValues = ['name' => $name, 'expertise' => $expertise];
        logAudit($conn, 'update', 'psa_members', $id, $oldValues, $newValues);
        $success = "Member updated successfully!";
        $member = $conn->query("SELECT * FROM psa_members WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update member.";
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Edit Member</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update member details</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_members.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
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

        <form action="" method="post" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Employer / Institution</label>
                    <input type="text" name="employer" value="<?= htmlspecialchars($member['employer'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Expertise / Position</label>
                    <input type="text" name="expertise" value="<?= htmlspecialchars($member['expertise'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Qualification</label>
                    <input type="text" name="qualification" value="<?= htmlspecialchars($member['qualification'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Contacts / Bio</label>
                    <textarea name="contacts" rows="4" class="ckeditor w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"><?= htmlspecialchars($member['contacts'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_members.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Update Member</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
