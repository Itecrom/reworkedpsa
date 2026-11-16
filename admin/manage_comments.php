<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage Comments - PSA Admin';

$comments = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");

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
                    <h1 class="text-xl font-bold text-gray-800">Manage Comments</h1>
                    <p class="text-sm text-gray-500">View and moderate comments</p>
                </div>
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
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Comment</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $comments->fetch_assoc()):
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3"><?= $i++ ?></td>
                            <td class="p-3 font-medium"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="p-3"><?= htmlspecialchars(mb_strimwidth($row['comment'], 0, 60, '...')) ?></td>
                            <td class="p-3 text-center">
                                <?php if ($row['approved']): ?>
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Approved</span>
                                <?php else: ?>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 space-x-2">
                                <?php if (!$row['approved']): ?>
                                    <a href="approve_comment.php?id=<?= $row['id'] ?>" class="text-green-600 hover:underline" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="delete_comment.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this comment?')" class="text-red-600 hover:underline" title="Delete">
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
