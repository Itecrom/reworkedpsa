<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_journals.php");
    exit;
}

$journalId = (int)$_GET['id'];
$journal = $conn->query("SELECT * FROM journals WHERE id = $journalId")->fetch_assoc();
if (!$journal) {
    die("Journal not found.");
}

// Get analytics
$analytics = getJournalAnalytics($conn, $journalId);

// Get article-level analytics
$articles = $conn->query("SELECT * FROM journal_topics WHERE journal_id = $journalId ORDER BY views DESC");

// Get recent activity
$recentActivity = $conn->query("
    SELECT ja.*, jt.topic as article_title
    FROM journal_analytics ja
    LEFT JOIN journal_topics jt ON ja.article_id = jt.id
    WHERE ja.journal_id = $journalId
    ORDER BY ja.created_at DESC
    LIMIT 50
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Journal Analytics - PSA Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Analytics: <?= htmlspecialchars($journal['title']) ?></h1>
      <p class="text-gray-600">Track views and downloads for this journal and its articles</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Journal Views</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['total_views']) ?></p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <i class="fas fa-eye text-blue-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Journal Downloads</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['total_downloads']) ?></p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="fas fa-download text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Article Views</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['article_views']) ?></p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <i class="fas fa-file-alt text-purple-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Article Downloads</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($analytics['article_downloads']) ?></p>
          </div>
          <div class="bg-orange-100 p-3 rounded-full">
            <i class="fas fa-file-download text-orange-600"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Article Performance -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Article Performance</h3>
        <?php if ($articles->num_rows > 0): ?>
          <div class="space-y-4">
            <?php while ($article = $articles->fetch_assoc()): ?>
              <div class="border-b pb-3">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($article['topic']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($article['article_author'] ?? 'N/A') ?></p>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm"><i class="fas fa-eye text-blue-500 mr-1"></i><?= number_format($article['views']) ?></p>
                    <p class="text-sm"><i class="fas fa-download text-green-500 mr-1"></i><?= number_format($article['downloads']) ?></p>
                  </div>
                </div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                  <?php
                    $maxViews = max(1, $analytics['article_views']);
                    $percentage = ($article['views'] / $maxViews) * 100;
                  ?>
                  <div class="bg-blue-500 h-2 rounded-full" style="width: <?= min(100, $percentage) ?>%"></div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No articles yet.</p>
        <?php endif; ?>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Recent Activity</h3>
        <?php if ($recentActivity->num_rows > 0): ?>
          <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php while ($activity = $recentActivity->fetch_assoc()): ?>
              <div class="flex items-center justify-between border-b pb-2">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-800">
                    <?php if ($activity['action'] === 'view'): ?>
                      <i class="fas fa-eye text-blue-500 mr-1"></i>Viewed
                    <?php else: ?>
                      <i class="fas fa-download text-green-500 mr-1"></i>Downloaded
                    <?php endif; ?>
                    <?= htmlspecialchars($activity['article_title'] ?? 'Journal') ?>
                  </p>
                  <p class="text-xs text-gray-500"><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></p>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No activity recorded yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chart -->
    <div class="bg-white p-6 rounded shadow mb-6">
      <h3 class="text-lg font-semibold mb-4 text-gray-700">Views vs Downloads</h3>
      <canvas id="analyticsChart" height="100"></canvas>
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

  <script>
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Journal Views', 'Journal Downloads', 'Article Views', 'Article Downloads'],
        datasets: [{
          label: 'Count',
          data: [
            <?= $analytics['total_views'] ?>,
            <?= $analytics['total_downloads'] ?>,
            <?= $analytics['article_views'] ?>,
            <?= $analytics['article_downloads'] ?>
          ],
          backgroundColor: [
            '#3b82f6',
            '#22c55e',
            '#8b5cf6',
            '#f97316'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>
