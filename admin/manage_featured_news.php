<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Manage Featured News - PSA Admin';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM featured_news WHERE id = $id");
    logAudit($conn, 'delete', 'featured_news', $id);
    header("Location: manage_featured_news.php?deleted=1");
    exit;
}

// Get all featured news
$featuredNews = $conn->query("SELECT * FROM featured_news ORDER BY sort_order ASC, created_at DESC");

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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Manage Featured News</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add, edit, or remove featured news items displayed on the homepage</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition" title="View Site">
                    <i class="fas fa-external-link-alt text-lg"></i>
                </a>
                <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Toggle theme">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block"></i>
                </button>
                <?php
                $headerUserPic = '';
                if (isset($_SESSION['admin'])) {
                    $userResult = $conn->query("SELECT profile_pic FROM admins WHERE username = '" . $conn->real_escape_string($_SESSION['admin']) . "'");
                    if ($userResult) {
                        $userData = $userResult->fetch_assoc();
                        $headerUserPic = $userData['profile_pic'] ?? '';
                    }
                }
                ?>
                <a href="profile.php" class="flex items-center gap-2 hover:opacity-80 transition">
                    <?php if (!empty($headerUserPic) && file_exists('../uploads/users/' . $headerUserPic)): ?>
                        <img src="../uploads/users/<?= htmlspecialchars($headerUserPic) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <img src="../images/logo.png" alt="Admin" class="w-8 h-8 rounded-full">
                    <?php endif; ?>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block"><?= htmlspecialchars($_SESSION['admin']) ?></span>
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if (isset($_GET['deleted'])): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
                Featured news item deleted successfully!
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <a href="add_featured_news.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded-lg transition font-medium inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Featured News
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Link</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if ($featuredNews->num_rows > 0): ?>
                            <?php while ($news = $featuredNews->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <?= (int)$news['sort_order'] ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($news['title']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <a href="<?= htmlspecialchars($news['link_url']) ?>" target="_blank" class="text-psaGreen hover:underline truncate block max-w-xs">
                                            <?= htmlspecialchars($news['link_url']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $news['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
                                            <?= $news['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="edit_featured_news.php?id=<?= $news['id'] ?>" class="text-psaGreen hover:text-green-800 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?= $news['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No featured news items yet. <a href="add_featured_news.php" class="text-psaGreen hover:underline">Add your first featured news</a>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
