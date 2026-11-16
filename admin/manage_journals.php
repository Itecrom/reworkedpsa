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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            psaBlack: '#000000',
            psaDark: '#0a0a0a',
            psaSidebar: '#111111'
          }
        }
      }
    };
  </script>
  <style>
    .psa-gradient {
      background: linear-gradient(135deg, #000 0%, #117733 50%, #cc0000 100%);
    }
    .sidebar-link {
      transition: all 0.3s ease;
    }
    .sidebar-link:hover {
      background: rgba(17, 119, 51, 0.2);
      padding-left: 1.5rem;
    }
    .sidebar-link.active {
      background: #117733;
      border-left: 4px solid #cc0000;
    }
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      .sidebar.open {
        transform: translateX(0);
      }
    }
  </style>
</head>
<body class="bg-gray-100 font-sans">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="sidebar w-64 bg-psaSidebar text-white flex flex-col fixed h-full z-50">
      <div class="p-4 border-b border-gray-800">
        <div class="flex items-center gap-3">
          <img src="../images/logo.png" alt="PSA Logo" class="w-10 h-10 rounded">
          <div>
            <h1 class="text-lg font-bold text-white">PSA Admin</h1>
            <p class="text-xs text-gray-400">Journal Management</p>
          </div>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto py-4">
        <div class="px-4 mb-2">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Main Menu</span>
        </div>
        <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-home w-5"></i> Dashboard
        </a>
        <a href="manage_journals.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 text-white">
          <i class="fas fa-book w-5"></i> Manage Journals
        </a>
        <a href="manage_news.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-newspaper w-5"></i> Manage News
        </a>
        <a href="manage_events.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-calendar-alt w-5"></i> Manage Events
        </a>

        <div class="px-4 mt-6 mb-2">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Content</span>
        </div>
        <a href="add_journal.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-plus-circle w-5"></i> Add Journal
        </a>
        <a href="add_news.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-plus-circle w-5"></i> Add News
        </a>
        <a href="add_event.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-calendar-plus w-5"></i> Add Event
        </a>

        <div class="px-4 mt-6 mb-2">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</span>
        </div>
        <a href="manage_team.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-users w-5"></i> Manage Team
        </a>
        <a href="manage_users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-user w-5"></i> Manage Users
        </a>
        <a href="manage_members.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-id-card w-5"></i> Manage Members
        </a>
        <a href="manage_subscribers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-envelope w-5"></i> Subscribers
        </a>
        <a href="manage_comments.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white">
          <i class="fas fa-comments w-5"></i> Comments
        </a>
      </nav>

      <div class="p-4 border-t border-gray-800">
        <a href="auth/logout.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300">
          <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-0 md:ml-64 min-h-screen flex flex-col">
      <!-- Top Header -->
      <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-4">
            <button id="menu-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
              <i class="fas fa-bars text-xl"></i>
            </button>
            <div>
              <h2 class="text-xl font-bold text-gray-800">Manage Journals</h2>
              <p class="text-sm text-gray-500">View and manage all journal issues</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-gray-600 hover:text-psaGreen transition">
              <i class="fas fa-home text-lg"></i>
            </a>
            <div class="flex items-center gap-2">
              <img src="../images/logo.png" alt="Admin" class="w-8 h-8 rounded-full">
              <span class="text-sm font-medium text-gray-700 hidden sm:block"><?= htmlspecialchars($_SESSION['admin']) ?></span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 p-6">
        <!-- Analytics Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
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

          <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
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

          <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
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

          <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Journals List</h3>
              <p class="text-sm text-gray-500"><?= $total ?> journal(s) total</p>
            </div>
            <a href="add_journal.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded transition flex items-center gap-2">
              <i class="fas fa-plus"></i> Add Journal
            </a>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-psaBlack text-white">
                <tr>
                  <th class="p-3 text-left">#</th>
                  <th class="p-3 text-left">Title</th>
                  <th class="p-3 text-left">Volume</th>
                  <th class="p-3 text-left">Issue</th>
                  <th class="p-3 text-left">Date</th>
                  <th class="p-3 text-center">Articles</th>
                  <th class="p-3 text-center">Views</th>
                  <th class="p-3 text-center">Downloads</th>
                  <th class="p-3 text-left">PDF</th>
                  <th class="p-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = $offset + 1;
                while ($j = $journals->fetch_assoc()):
                ?>
                <tr class="border-b hover:bg-gray-50 transition">
                  <td class="p-3"><?= $i++ ?></td>
                  <td class="p-3">
                    <a href="journal-view.php?id=<?= $j['id'] ?>" class="text-blue-600 hover:underline font-medium">
                      <?= htmlspecialchars($j['title']) ?>
                    </a>
                  </td>
                  <td class="p-3"><?= htmlspecialchars($j['volume'] ?? 'N/A') ?></td>
                  <td class="p-3"><?= htmlspecialchars($j['issue_no'] ?? 'N/A') ?></td>
                  <td class="p-3"><?= htmlspecialchars($j['journal_date'] ?? date('d M Y', strtotime($j['created_at']))) ?></td>
                  <td class="p-3 text-center"><?= (int)$j['article_count'] ?></td>
                  <td class="p-3 text-center"><?= number_format($j['views'] + $j['article_views']) ?></td>
                  <td class="p-3 text-center"><?= number_format($j['downloads'] + $j['article_downloads']) ?></td>
                  <td class="p-3">
                    <?php if (!empty($j['file'])): ?>
                      <a href="uploads/journals/<?= urlencode($j['file']) ?>" target="_blank" class="text-psaGreen hover:underline">
                        <i class="fas fa-file-pdf"></i> PDF
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400 italic">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 space-x-2">
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
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center space-x-2">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>" class="px-3 py-1 rounded border <?= $p == $page ? 'bg-psaGreen text-white' : 'text-psaGreen border-psaGreen' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Top Content -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Journals -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
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
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
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
      </div>

      <!-- Footer -->
      <footer class="bg-psaBlack text-white text-center text-sm py-4 mt-auto">
        <p>&copy; <?= date('Y') ?> PSA Admin Panel v2.0 — Designed by <strong class="text-psaRed">Leonard Mhone</strong></p>
      </footer>
    </main>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
  <script>
    // Mobile sidebar toggle
    document.getElementById('menu-toggle').addEventListener('click', () => {
      document.querySelector('.sidebar').classList.toggle('open');
    });
  </script>
</body>
</html>
