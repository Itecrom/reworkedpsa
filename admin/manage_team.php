<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage Team - PSA Admin';

$team = $conn->query("SELECT * FROM team_members ORDER BY id DESC");

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
                    <h1 class="text-xl font-bold text-gray-800">Manage Team</h1>
                    <p class="text-sm text-gray-500">View and manage team members</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="add_team_member.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Member
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-psaBlack text-white">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Position</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $team->fetch_assoc()):
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3"><?= $i++ ?></td>
                            <td class="p-3 font-medium"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['position'] ?? 'N/A') ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_team_member.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_team_member.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this team member?')" class="text-red-600 hover:underline" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
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
