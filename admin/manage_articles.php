<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$journalId = isset($_GET['journal_id']) ? (int)$_GET['journal_id'] : 0;

if (!$journalId) {
    header("Location: manage_journals.php");
    exit;
}

$journal = $conn->query("SELECT * FROM journals WHERE id = $journalId")->fetch_assoc();
if (!$journal) {
    die("Journal not found.");
}

// Handle article deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $articleId = (int)$_GET['delete'];
    $article = $conn->query("SELECT file FROM journal_topics WHERE id = $articleId AND journal_id = $journalId")->fetch_assoc();
    if ($article && $article['file']) {
        @unlink(__DIR__ . '/uploads/journals/' . $article['file']);
    }
    $conn->query("DELETE FROM journal_topics WHERE id = $articleId AND journal_id = $journalId");
    header("Location: manage_articles.php?journal_id=$journalId&deleted=1");
    exit;
}

// Get articles
$articles = $conn->query("SELECT * FROM journal_topics WHERE journal_id = $journalId ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Articles - PSA Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            psaGreen: '#117733',
            psaRed: '#cc0000',
            psaBlack: '#000000'
          }
        }
      }
    };
  </script>
</head>
<body class="bg-gray-100 min-h-screen font-sans flex">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Articles: <?= htmlspecialchars($journal['title']) ?></h1>
        <p class="text-sm text-gray-600">Manage individual articles for this journal</p>
      </div>
      <a href="add_article.php?journal_id=<?= $journalId ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
        <i class="fas fa-plus mr-1"></i> Add Article
      </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4">Article deleted successfully!</div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <table class="w-full table-auto text-sm">
        <thead class="bg-green-800 text-white">
          <tr>
            <th class="p-2 text-left">#</th>
            <th class="p-2 text-left">Article Title</th>
            <th class="p-2 text-left">Author</th>
            <th class="p-2 text-left">Summary</th>
            <th class="p-2 text-left">Views</th>
            <th class="p-2 text-left">Downloads</th>
            <th class="p-2 text-left">PDF</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($a = $articles->fetch_assoc()):
          ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-2"><?= $i++ ?></td>
            <td class="p-2 font-medium"><?= htmlspecialchars($a['topic']) ?></td>
            <td class="p-2"><?= htmlspecialchars($a['article_author'] ?? 'N/A') ?></td>
            <td class="p-2"><?= htmlspecialchars(mb_strimwidth($a['article_summary'] ?? '', 0, 50, '...')) ?></td>
            <td class="p-2 text-center"><?= number_format($a['views']) ?></td>
            <td class="p-2 text-center"><?= number_format($a['downloads']) ?></td>
            <td class="p-2">
              <?php if (!empty($a['file'])): ?>
                <a href="uploads/journals/<?= urlencode($a['file']) ?>" target="_blank" class="text-blue-600 hover:underline">
                  <i class="fas fa-file-pdf"></i> View
                </a>
              <?php else: ?>
                <span class="text-gray-400 italic">N/A</span>
              <?php endif; ?>
            </td>
            <td class="p-2 space-x-2">
              <a href="edit_article.php?id=<?= $a['id'] ?>&journal_id=<?= $journalId ?>" class="text-blue-600 hover:underline" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <a href="?delete=<?= $a['id'] ?>&journal_id=<?= $journalId ?>" onclick="return confirm('Delete this article?')" class="text-red-600 hover:underline" title="Delete">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if ($articles->num_rows === 0): ?>
            <tr>
              <td colspan="8" class="p-6 text-center text-gray-500">No articles found. <a href="add_article.php?journal_id=<?= $journalId ?>" class="text-blue-600 underline">Add your first article</a></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      <a href="manage_journals.php" class="text-gray-600 hover:text-black">
        <i class="fas fa-arrow-left mr-1"></i> Back to Journals
      </a>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-500">
      &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <strong>Leonard Mhone</strong>
    </footer>
  </main>
</body>
</html>
