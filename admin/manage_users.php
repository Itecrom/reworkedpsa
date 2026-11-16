<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage Users - PSA Admin';

// Get current logged in user's ID
$currentUser = $conn->query("SELECT id FROM admins WHERE username = '" . $conn->real_escape_string($_SESSION['admin']) . "'")->fetch_assoc();
$currentUserId = $currentUser ? $currentUser['id'] : 0;

$users = $conn->query("SELECT * FROM admins ORDER BY id DESC");

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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Manage Users</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View and manage admin users</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="add_user.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add User
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-psaBlack text-white">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Profile</th>
                            <th class="p-3 text-left">Username</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $users->fetch_assoc()):
                        ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="p-3 text-gray-900 dark:text-white"><?= $i++ ?></td>
                            <td class="p-3">
                                <?php if (!empty($row['profile_pic']) && file_exists('../uploads/users/' . $row['profile_pic'])): ?>
                                    <img src="../uploads/users/<?= htmlspecialchars($row['profile_pic']) ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400 dark:text-gray-300"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="text-blue-600 dark:text-blue-400 hover:underline" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($row['id'] != $currentUserId): ?>
                                <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')" class="text-red-600 dark:text-red-400 hover:underline" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
