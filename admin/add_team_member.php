<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';
include '../includes/team_member_helpers.php';

$page_title = 'Add Team Member - PSA Admin';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $position = trim((string)($_POST['position'] ?? ''));
    $category = trim((string)($_POST['category'] ?? 'Other'));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $bio = trim((string)($_POST['bio'] ?? ''));
    $image = '';

    if (!validateTeamFormToken()) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($name === '' || $position === '' || mb_strlen($name) > 255 || mb_strlen($position) > 255) {
        $error = 'Enter a valid name and position (255 characters maximum).';
    } elseif (!in_array($category, ['Patron', 'Members of the Executive', 'Secretariate', 'Editorial Team', 'Other'], true)) {
        $error = 'Choose a valid team section.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        try {
            $image = saveTeamMemberImage($_FILES['image'] ?? []);
            $columns = getTeamMemberColumns($conn);
            $values = [
                'name' => $name,
                'position' => $position,
                'category' => $category,
                'email' => $email,
                'phone' => $phone,
                'bio' => $bio,
                'image' => $image,
                'facebook' => '',
                'twitter' => '',
                'linkedin' => '',
                'google' => '',
            ];
            $fields = array_values(array_intersect(array_keys($values), $columns));
            if (!in_array('name', $fields, true) || !in_array('position', $fields, true)) {
                throw new RuntimeException('The team member table is missing required columns.');
            }

            $insertValues = array_map(static function ($field) use ($values) {
                return $values[$field];
            }, $fields);
            $placeholders = implode(', ', array_fill(0, count($fields), '?'));
            $stmt = $conn->prepare('INSERT INTO team_members (' . implode(', ', $fields) . ') VALUES (' . $placeholders . ')');
            $stmt->bind_param(str_repeat('s', count($insertValues)), ...$insertValues);
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            try {
                logAudit($conn, 'create', 'team_members', $newId, null, ['name' => $name, 'position' => $position, 'category' => $category]);
            } catch (mysqli_sql_exception $e) {
                error_log('Team member audit log failed: ' . $e->getMessage());
            }
            $success = 'Team member added successfully!';
        } catch (RuntimeException $e) {
            if ($image !== '') {
                removeTeamMemberImage($image);
            }
            $error = $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            if ($image !== '') {
                removeTeamMemberImage($image);
            }
            error_log('Team member insert failed: ' . $e->getMessage());
            $error = 'The team member could not be saved. Check the team table setup and try again.';
        }
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(teamFormToken(), ENT_QUOTES, 'UTF-8') ?>">
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
                    <label class="block mb-1 font-semibold text-gray-700">Team section</label>
                    <select name="category" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="Patron">Patron</option>
                        <option value="Members of the Executive">Executive Committee</option>
                        <option value="Secretariate">Secretariat</option>
                        <option value="Editorial Team">Editorial Board</option>
                        <option value="Other">Other</option>
                    </select>
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
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border border-gray-300 px-3 py-2 rounded">
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
