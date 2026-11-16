<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';
include '../includes/audit_helper.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid article ID.");
}

$articleId = (int)$_GET['id'];
$journalId = isset($_GET['journal_id']) ? (int)$_GET['journal_id'] : 0;

$article = $conn->query("SELECT * FROM journal_topics WHERE id = $articleId")->fetch_assoc();
if (!$article) {
    die("Article not found.");
}

if (!$journalId) {
    $journalId = $article['journal_id'];
}

$journal = $conn->query("SELECT * FROM journals WHERE id = $journalId")->fetch_assoc();
if (!$journal) {
    die("Journal not found.");
}

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 5);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = trim($_POST['topic'] ?? '');
    $articleAuthor = trim($_POST['article_author'] ?? '');
    $articleSummary = trim($_POST['article_summary'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $article_date = $_POST['article_date'] ?? date('Y-m-d');

    $pdfName = $article['file'];
    if (!empty($_FILES['pdf']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/journals/';
        $uploadResult = handlePdfUpload($_FILES['pdf'], $uploadDir, 'article_', $maxPdfSize, $pdfQuality);
        if ($uploadResult['success']) {
            $pdfName = $uploadResult['filename'];
            // Delete old PDF if exists
            if (!empty($article['file']) && file_exists($uploadDir . $article['file'])) {
                @unlink($uploadDir . $article['file']);
            }
            if (!empty($uploadResult['info']['message'])) {
                $success = $uploadResult['info']['message'] . ' ';
            }
        } else {
            $error = $uploadResult['error'];
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE journal_topics SET topic=?, file=?, article_author=?, article_summary=?, abstract=?, keywords=?, article_date=? WHERE id=?");
        $stmt->bind_param("sssssssi", $topic, $pdfName, $articleAuthor, $articleSummary, $abstract, $keywords, $article_date, $articleId);

        if ($stmt->execute()) {
            $oldValues = ['topic' => $article['topic']];
            $newValues = ['topic' => $topic];
            logAudit($conn, 'update', 'journal_topics', $articleId, $oldValues, $newValues);
            $success .= "Article updated successfully!";
            $article = $conn->query("SELECT * FROM journal_topics WHERE id = $articleId")->fetch_assoc();
        } else {
            $error = "Failed to update article.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Article - PSA Admin</title>
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
              <h2 class="text-xl font-bold text-gray-800">Edit Article</h2>
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
        <?php if ($success): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200">
          <!-- Article Information Section -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-file-alt text-psaRed"></i> Article Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Article Title <span class="text-red-500">*</span></label>
                <input type="text" name="topic" value="<?= htmlspecialchars($article['topic']) ?>" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Author</label>
                <input type="text" name="article_author" value="<?= htmlspecialchars($article['article_author'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Publication Date</label>
                <input type="date" name="article_date" value="<?= htmlspecialchars($article['article_date'] ?? date('Y-m-d')) ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div class="md:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Keywords (comma separated)</label>
                <input type="text" name="keywords" value="<?= htmlspecialchars($article['keywords'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. democracy, elections, governance" />
              </div>
              <div class="md:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Abstract</label>
                <textarea name="abstract" rows="4" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter the article abstract..."><?= htmlspecialchars($article['abstract'] ?? '') ?></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Article Summary</label>
                <textarea name="article_summary" rows="3" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Brief summary of the article"><?= htmlspecialchars($article['article_summary'] ?? '') ?></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Article PDF (leave blank to keep existing)</label>
                <input type="file" name="pdf" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be compressed.</p>
                <?php if (!empty($article['file'])): ?>
                  <p class="text-sm text-gray-600 mt-1">Current: <a href="../uploads/journals/<?= $article['file'] ?>" target="_blank" class="text-blue-600 underline"><?= $article['file'] ?></a></p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="p-6 bg-gray-50 rounded-b-lg flex flex-col sm:flex-row justify-between items-center gap-4">
            <a href="manage_articles.php?journal_id=<?= $journalId ?>" class="text-gray-600 hover:text-psaBlack transition flex items-center gap-2">
              <i class="fas fa-arrow-left"></i> Back to Articles
            </a>
            <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition flex items-center gap-2">
              <i class="fas fa-save"></i> Update Article
            </button>
          </div>
        </form>
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
