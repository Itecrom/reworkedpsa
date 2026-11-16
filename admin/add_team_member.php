<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Add Team Member - PSA Admin';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Handle image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/team/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    $stmt = $conn->prepare("INSERT INTO team_members (name, position, email, phone, bio, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $position, $email, $phone, $bio, $image);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        logAudit($conn, 'create', 'team_members', $newId, null, ['name' => $name, 'position' => $position]);
        $success = "Team member added successfully!";
    } else {
        $error = "Failed to add team member: " . $conn->error;
    }
}

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 ml-0 md:ml-64 min-h-screen">
    <!-- Top Header Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button id="menu-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Add Team Member</h1>
                    <p class="text-sm text-gray-500">Add a new team member</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_team.php" class="text-gray-600 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Position <span class="text-red-500">*</span></label>
                    <input type="text" name="position" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Phone</label>
                    <input type="text" name="phone" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Bio</label>
                    <textarea name="bio" rows="4" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Profile Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_team.php" class="text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Save Member</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
