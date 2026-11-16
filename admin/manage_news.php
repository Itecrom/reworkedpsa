<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage News - PSA Admin';

// Handle feature toggle action
if (isset($_GET['toggle']) && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $current = (int)$_GET['current'];
  $newStatus = $current ? 0 : 1;
  $conn->query("UPDATE news SET featured = $newStatus WHERE id = $id");
  header("Location: manage_news.php");
  exit;
}

$news = $conn->query("SELECT * FROM news ORDER BY date_posted DESC");

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
                    <h1 class="text-xl font-bold text-gray-800">Manage News</h1>
                    <p class="text-sm text-gray-500">View and manage all news articles</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="add_news.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add News
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
                            <th class="p-3 text-left">Title</th>
                            <th class="p-3 text-left">Category</th>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-center">Featured</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $news->fetch_assoc()):
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3"><?= $i++ ?></td>
                            <td class="p-3 font-medium"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['date_posted'] ?? 'N/A') ?></td>
                            <td class="p-3 text-center">
                                <a href="?toggle=1&id=<?= $row['id'] ?>&current=<?= $row['featured'] ?>" class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium <?= $row['featured'] ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600' ?>">
                                    <i class="fas fa-star"></i> <?= $row['featured'] ? 'Yes' : 'No' ?>
                                </a>
                            </td>
                            <td class="p-3 space-x-2">
                                <a href="edit_news.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_news.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this news?')" class="text-red-600 hover:underline" title="Delete">
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
