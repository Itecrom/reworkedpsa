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
        @unlink('../uploads/journals/' . $article['file']);
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
              <h2 class="text-xl font-bold text-gray-800">Manage Articles</h2>
              <p class="text-sm text-gray-500">Journal: <strong><?= htmlspecialchars($journal['title']) ?></strong></p>
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
        <?php if (isset($_GET['deleted'])): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Article deleted successfully!</div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Articles List</h3>
              <p class="text-sm text-gray-500"><?= $articles->num_rows ?> article(s) found</p>
            </div>
            <a href="add_article.php?journal_id=<?= $journalId ?>" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded transition flex items-center gap-2">
              <i class="fas fa-plus"></i> Add Article
            </a>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-psaBlack text-white">
                <tr>
                  <th class="p-3 text-left">#</th>
                  <th class="p-3 text-left">Article Title</th>
                  <th class="p-3 text-left">Author</th>
                  <th class="p-3 text-left">Abstract</th>
                  <th class="p-3 text-left">Keywords</th>
                  <th class="p-3 text-left">Date</th>
                  <th class="p-3 text-center">Views</th>
                  <th class="p-3 text-center">Downloads</th>
                  <th class="p-3 text-left">PDF</th>
                  <th class="p-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;
                while ($a = $articles->fetch_assoc()):
                ?>
                <tr class="border-b hover:bg-gray-50 transition">
                  <td class="p-3"><?= $i++ ?></td>
                  <td class="p-3 font-medium"><?= htmlspecialchars($a['topic']) ?></td>
                  <td class="p-3"><?= htmlspecialchars($a['article_author'] ?? 'N/A') ?></td>
                  <td class="p-3"><?= htmlspecialchars(mb_strimwidth($a['abstract'] ?? '', 0, 60, '...')) ?></td>
                  <td class="p-3"><?= htmlspecialchars(mb_strimwidth($a['keywords'] ?? '', 0, 40, '...')) ?></td>
                  <td class="p-3"><?= htmlspecialchars($a['article_date'] ?? 'N/A') ?></td>
                  <td class="p-3 text-center"><?= number_format($a['views']) ?></td>
                  <td class="p-3 text-center"><?= number_format($a['downloads']) ?></td>
                  <td class="p-3">
                    <?php if (!empty($a['file'])): ?>
                      <a href="../uploads/journals/<?= urlencode($a['file']) ?>" target="_blank" class="text-psaGreen hover:underline">
                        <i class="fas fa-file-pdf"></i> View
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400 italic">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 space-x-2">
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
                    <td colspan="10" class="p-6 text-center text-gray-500">No articles found. <a href="add_article.php?journal_id=<?= $journalId ?>" class="text-psaGreen underline">Add your first article</a></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-6">
          <a href="manage_journals.php" class="text-gray-600 hover:text-psaBlack transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Journals
          </a>
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
