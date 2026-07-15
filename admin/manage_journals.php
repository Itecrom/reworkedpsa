<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get journals with article counts
$journals = $conn->query("
    SELECT j.*,
           COUNT(DISTINCT jt.id) as article_count,
           COALESCE(SUM(jt.views), 0) as article_views,
           COALESCE(SUM(jt.downloads), 0) as article_downloads
    FROM journals j
    LEFT JOIN journal_topics jt ON j.id = jt.journal_id
    GROUP BY j.id
    ORDER BY j.created_at DESC
    LIMIT $offset, $limit
");
$total = $conn->query("SELECT COUNT(*) as total FROM journals")->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Get overall analytics
$analytics = getOverallAnalytics($conn, 30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Journals - PSA Admin</title>
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
      <h1 class="text-2xl font-bold text-gray-800">Manage Journals</h1>
      <a href="add_journal.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
        <i class="fas fa-plus mr-1"></i> Add Journal
      </a>
    </div>

    <!-- Analytics Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Journals</p>
            <p class="text-2xl font-bold text-gray-800"><?= $analytics['total_journals'] ?></p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <i class="fas fa-book text-blue-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Articles</p>
            <p class="text-2xl font-bold text-gray-800"><?= $analytics['total_articles'] ?></p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="fas fa-file-alt text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Views</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['total_views']) ?></p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <i class="fas fa-eye text-purple-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Downloads</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['total_downloads']) ?></p>
          </div>
          <div class="bg-orange-100 p-3 rounded-full">
            <i class="fas fa-download text-orange-600"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <table class="w-full table-auto text-sm">
        <thead class="bg-green-800 text-white">
          <tr>
            <th class="p-2 text-left">#</th>
            <th class="p-2 text-left">Title</th>
            <th class="p-2 text-left">Author</th>
            <th class="p-2 text-left">Date</th>
            <th class="p-2 text-left">Articles</th>
            <th class="p-2 text-left">Views</th>
            <th class="p-2 text-left">Downloads</th>
            <th class="p-2 text-left">File</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = $offset + 1;
          while ($j = $journals->fetch_assoc()):
          ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-2"><?= $i++ ?></td>
            <td class="p-2">
              <a href="journal-view.php?id=<?= $j['id'] ?>" class="text-blue-600 hover:underline font-medium">
                <?= htmlspecialchars($j['title']) ?>
              </a>
            </td>
            <td class="p-2"><?= htmlspecialchars($j['author'] ?? 'Unknown') ?></td>
            <td class="p-2"><?= date('d M Y', strtotime($j['created_at'])) ?></td>
            <td class="p-2 text-center"><?= (int)$j['article_count'] ?></td>
            <td class="p-2 text-center"><?= number_format($j['views'] + $j['article_views']) ?></td>
            <td class="p-2 text-center"><?= number_format($j['downloads'] + $j['article_downloads']) ?></td>
            <td class="p-2">
              <?php if (!empty($j['file'])): ?>
                <a href="uploads/journals/<?= urlencode($j['file']) ?>" target="_blank" class="text-blue-600 hover:underline">
                  <i class="fas fa-file-pdf"></i> PDF
                </a>
              <?php else: ?>
                <span class="text-gray-400 italic">N/A</span>
              <?php endif; ?>
            </td>
            <td class="p-2 space-x-2">
              <a href="edit_journal.php?id=<?= $j['id'] ?>" class="text-blue-600 hover:underline" title="Edit Journal">
                <i class="fas fa-edit"></i>
              </a>
              <a href="manage_articles.php?journal_id=<?= $j['id'] ?>" class="text-green-600 hover:underline" title="Manage Articles">
                <i class="fas fa-list"></i> Articles
              </a>
              <a href="journal_analytics.php?id=<?= $j['id'] ?>" class="text-purple-600 hover:underline" title="View Analytics">
                <i class="fas fa-chart-bar"></i>
              </a>
              <a href="delete_journal.php?id=<?= $j['id'] ?>" onclick="return confirm('Delete this journal and all its articles?')" class="text-red-600 hover:underline" title="Delete">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex justify-center space-x-2">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?>" class="px-3 py-1 rounded border <?= $p == $page ? 'bg-green-800 text-white' : 'text-green-800 border-green-800' ?>">
          <?= $p ?>
        </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <!-- Top Content -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Journals -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Top Journals by Views</h3>
        <?php if (!empty($analytics['top_journals'])): ?>
          <div class="space-y-3">
            <?php foreach ($analytics['top_journals'] as $topJournal): ?>
              <div class="flex items-center justify-between border-b pb-2">
                <div class="flex-1">
                  <a href="journal-view.php?id=<?= $topJournal['id'] ?>" class="text-blue-600 hover:underline font-medium">
                    <?= htmlspecialchars($topJournal['title']) ?>
                  </a>
                  <div class="text-sm text-gray-500">
                    <i class="fas fa-eye mr-1"></i><?= number_format($topJournal['views']) ?> views
                    <span class="mx-2">|</span>
                    <i class="fas fa-download mr-1"></i><?= number_format($topJournal['downloads']) ?> downloads
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No journals yet.</p>
        <?php endif; ?>
      </div>

      <!-- Top Articles -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Top Articles by Views</h3>
        <?php if (!empty($analytics['top_articles'])): ?>
          <div class="space-y-3">
            <?php foreach ($analytics['top_articles'] as $topArticle): ?>
              <div class="flex items-center justify-between border-b pb-2">
                <div class="flex-1">
                  <p class="font-medium text-gray-800"><?= htmlspecialchars($topArticle['topic']) ?></p>
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($topArticle['journal_title']) ?></p>
                  <div class="text-sm text-gray-500">
                    <i class="fas fa-eye mr-1"></i><?= number_format($topArticle['views']) ?> views
                    <span class="mx-2">|</span>
                    <i class="fas fa-download mr-1"></i><?= number_format($topArticle['downloads']) ?> downloads
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No articles yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-500">
      &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <strong>Leonard Mhone</strong>
    </footer>
  </main>
</body>
</html>
