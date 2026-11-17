<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';
include '../includes/audit_helper.php';
include '../includes/journal_cover_helper.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid journal ID.");
}

$id = (int) $_GET['id'];
ensureJournalCoverColumn($conn);
$journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
if (!$journal) {
  die("Journal not found.");
}

// Get existing articles
$articles = $conn->query("SELECT * FROM journal_topics WHERE journal_id = $id ORDER BY id ASC");

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 5);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $volume = trim($_POST['volume'] ?? '');
  $issue_no = trim($_POST['issue_no'] ?? '');
  $journal_date = $_POST['journal_date'] ?? date('Y-m-d');
  $editorial_summary = trim($_POST['editorial_summary'] ?? '');
  $author = trim($_POST['author'] ?? '');
  $summary = trim($_POST['summary'] ?? '');
  $tags = trim($_POST['tags'] ?? '');
  $coverImage = $journal['cover_image'] ?? '';
  $newCoverImage = '';
  $journalUpdated = false;
  try {
    $newCoverImage = uploadJournalCover($_FILES['cover_image'] ?? []);
    if ($newCoverImage !== '') {
      $coverImage = $newCoverImage;
    }
  } catch (RuntimeException $e) {
    $error = $e->getMessage();
  }

  $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

  $pdfName = $journal['file'];
  if (!empty($_FILES['pdf']['name'])) {
    $uploadDir = dirname(__DIR__) . '/uploads/journals/';
    $uploadResult = handlePdfUpload($_FILES['pdf'], $uploadDir, 'journal_', $maxPdfSize, $pdfQuality);
    if ($uploadResult['success']) {
      $pdfName = $uploadResult['filename'];
      // Delete old PDF if exists
      if (!empty($journal['file']) && file_exists($uploadDir . $journal['file'])) {
        @unlink($uploadDir . $journal['file']);
      }
      if (!empty($uploadResult['info']['message'])) {
        $success = $uploadResult['info']['message'] . ' ';
      }
    } else {
      $error = $uploadResult['error'];
    }
  }

  if (!$error) {
    $stmt = $conn->prepare("UPDATE journals SET title=?, volume=?, issue_no=?, journal_date=?, editorial_summary=?, author=?, summary=?, tags=?, slug=?, file=?, cover_image=?, created_at=? WHERE id=?");
    $stmt->bind_param("ssssssssssssi", $title, $volume, $issue_no, $journal_date, $editorial_summary, $author, $summary, $tags, $slug, $pdfName, $coverImage, $journal_date, $id);

    if ($stmt->execute()) {
      $journalUpdated = true;
      $oldValues = ['title' => $journal['title'], 'volume' => $journal['volume']];
      $newValues = ['title' => $title, 'volume' => $volume];
      logAudit($conn, 'update', 'journals', $id, $oldValues, $newValues);
      $success .= "Journal updated successfully!";
      if ($newCoverImage !== '' && !empty($journal['cover_image'])) {
        $oldCoverPath = dirname(__DIR__) . '/uploads/journals/covers/' . basename($journal['cover_image']);
        if (is_file($oldCoverPath)) {
          unlink($oldCoverPath);
        }
      }
      if (!$journalUpdated && $newCoverImage !== '') {
        removeJournalCover($newCoverImage);
      }
      $journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
    } else {
      if ($newCoverImage !== '') {
        removeJournalCover($newCoverImage);
      }
      $error = "Failed to update journal.";
    }
  }

  // Handle article addition
  if (!$error && isset($_POST['add_article'])) {
    $article_title = trim($_POST['article_title'] ?? '');
    $article_author = trim($_POST['article_author'] ?? '');
    $article_summary = trim($_POST['article_summary'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $article_date = $_POST['article_date'] ?? date('Y-m-d');

    if (!empty($article_title)) {
      $article_pdf = '';
      if (!empty($_FILES['article_pdf']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/journals/';
        $uploadResult = handlePdfUpload($_FILES['article_pdf'], $uploadDir, 'article_', $maxPdfSize, $pdfQuality);
        if ($uploadResult['success']) {
          $article_pdf = $uploadResult['filename'];
        } else {
          $error = "Article PDF upload failed: " . $uploadResult['error'];
        }
      }

      if (!$error) {
        $stmtArticle = $conn->prepare("INSERT INTO journal_topics (journal_id, topic, file, article_author, article_summary, abstract, keywords, article_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtArticle->bind_param("isssssss", $id, $article_title, $article_pdf, $article_author, $article_summary, $abstract, $keywords, $article_date);
        if ($stmtArticle->execute()) {
          $success .= "Article added successfully!";
          logAudit($conn, 'create', 'journal_topics', $conn->insert_id, null, ['topic' => $article_title, 'journal_id' => $id]);
          $articles = $conn->query("SELECT * FROM journal_topics WHERE journal_id = $id ORDER BY id ASC");
        } else {
          $error = "Failed to add article: " . $conn->error;
        }
        $stmtArticle->close();
      }
    } else {
      $error = "Please enter an article title.";
    }
  }

  // Handle article deletion
  if (!$error && isset($_GET['delete_article']) && is_numeric($_GET['delete_article'])) {
    $articleId = (int)$_GET['delete_article'];
    $article = $conn->query("SELECT file FROM journal_topics WHERE id = $articleId AND journal_id = $id")->fetch_assoc();
    if ($article && $article['file']) {
      $uploadDir = dirname(__DIR__) . '/uploads/journals/';
      if (file_exists($uploadDir . $article['file'])) {
        @unlink($uploadDir . $article['file']);
      }
    }
    $conn->query("DELETE FROM journal_topics WHERE id = $articleId AND journal_id = $id");
    $success .= "Article deleted successfully!";
    $articles = $conn->query("SELECT * FROM journal_topics WHERE journal_id = $id ORDER BY id ASC");
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Journal - PSA Admin</title>
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
              <h2 class="text-xl font-bold text-gray-800">Edit Journal</h2>
              <p class="text-sm text-gray-500">Update journal information and files</p>
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
          <!-- Journal Information Section -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-book text-psaGreen"></i> Journal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div class="lg:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Journal Title <span class="text-red-500">*</span></label>
                <input name="title" value="<?= htmlspecialchars($journal['title']) ?>" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Volume</label>
                <input name="volume" value="<?= htmlspecialchars($journal['volume'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. Vol. 1" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Issue No</label>
                <input name="issue_no" value="<?= htmlspecialchars($journal['issue_no'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. Issue 1" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Publication Date <span class="text-red-500">*</span></label>
                <input type="date" name="journal_date" value="<?= htmlspecialchars($journal['journal_date'] ?? $journal['created_at'] ?? '') ?>" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Editorial Foreword / Summary</label>
                <textarea name="editorial_summary" rows="4" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter the editorial foreword or summary for this journal issue..."><?= htmlspecialchars($journal['editorial_summary'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Author / Editor (Optional)</label>
                <input name="author" value="<?= htmlspecialchars($journal['author'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Author or editor name" />
              </div>
              <div class="lg:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Keywords (comma separated)</label>
                <input name="tags" value="<?= htmlspecialchars($journal['tags'] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. politics, governance, democracy" />
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Journal Summary</label>
                <textarea name="summary" rows="3" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Brief summary of this journal issue..."><?= htmlspecialchars($journal['summary'] ?? '') ?></textarea>
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Journal Cover Image (used as sharing thumbnail)</label>
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                <p class="text-sm text-gray-500 mt-1">JPG, PNG or WebP, up to 5 MB. Leave blank to keep the current cover.</p>
                <?php if (!empty($journal['cover_image'])): ?>
                  <img src="../uploads/journals/covers/<?= rawurlencode(basename($journal['cover_image'])) ?>" alt="Current journal cover" class="mt-2 max-h-40 rounded">
                <?php endif; ?>
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Journal PDF (leave blank to keep existing)</label>
                <input type="file" name="pdf" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be automatically compressed.</p>
                <?php if (!empty($journal['file'])): ?>
                  <p class="text-sm text-gray-600 mt-1">Current: <a href="../uploads/journals/<?= $journal['file'] ?>" target="_blank" class="text-blue-600 underline"><?= $journal['file'] ?></a></p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="p-6 bg-gray-50 rounded-b-lg flex flex-col sm:flex-row justify-between items-center gap-4">
            <a href="manage_journals.php" class="text-gray-600 hover:text-psaBlack transition flex items-center gap-2">
              <i class="fas fa-arrow-left"></i> Back to Journals
            </a>
            <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition flex items-center gap-2">
              <i class="fas fa-save"></i> Update Journal
            </button>
          </div>
        </form>

        <!-- Articles Management Section -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Journal Articles</h3>
              <p class="text-sm text-gray-500"><?= $articles->num_rows ?> article(s) in this journal</p>
            </div>
          </div>

          <div class="p-6">
            <!-- Existing Articles List -->
            <?php if ($articles->num_rows > 0): ?>
              <div class="overflow-x-auto mb-6">
                <table class="w-full text-sm">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="p-3 text-left">#</th>
                      <th class="p-3 text-left">Title</th>
                      <th class="p-3 text-left">Author</th>
                      <th class="p-3 text-left">Date</th>
                      <th class="p-3 text-center">Views</th>
                      <th class="p-3 text-center">Downloads</th>
                      <th class="p-3 text-left">PDF</th>
                      <th class="p-3 text-left">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; while ($a = $articles->fetch_assoc()): ?>
                      <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3"><?= $i++ ?></td>
                        <td class="p-3 font-medium"><?= htmlspecialchars($a['topic']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($a['article_author'] ?? 'N/A') ?></td>
                        <td class="p-3"><?= htmlspecialchars($a['article_date'] ?? 'N/A') ?></td>
                        <td class="p-3 text-center"><?= (int)$a['views'] ?></td>
                        <td class="p-3 text-center"><?= (int)$a['downloads'] ?></td>
                        <td class="p-3">
                          <?php if (!empty($a['file'])): ?>
                            <a href="../uploads/journals/<?= urlencode($a['file']) ?>" target="_blank" class="text-psaGreen hover:underline">
                              <i class="fas fa-file-pdf"></i> PDF
                            </a>
                          <?php else: ?>
                            <span class="text-gray-400 italic">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td class="p-3 space-x-2">
                          <a href="edit_article.php?id=<?= $a['id'] ?>&journal_id=<?= $id ?>" class="text-blue-600 hover:underline" title="Edit">
                            <i class="fas fa-edit"></i>
                          </a>
                          <a href="?id=<?= $id ?>&delete_article=<?= $a['id'] ?>" onclick="return confirm('Delete this article?')" class="text-red-600 hover:underline" title="Delete">
                            <i class="fas fa-trash"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-gray-500 mb-6">No articles yet. Add your first article below.</p>
            <?php endif; ?>

            <!-- Add New Article Form -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-md font-semibold text-gray-700 mb-4">Add New Article</h4>
              <form action="" method="post" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                  <div class="lg:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="article_title" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter article title" />
                  </div>
                  <div>
                    <label class="block mb-1 font-semibold text-gray-700">Author</label>
                    <input type="text" name="article_author" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Author name" />
                  </div>
                  <div>
                    <label class="block mb-1 font-semibold text-gray-700">Publication Date</label>
                    <input type="date" name="article_date" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                  </div>
                  <div class="lg:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Keywords (comma separated)</label>
                    <input type="text" name="keywords" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. democracy, elections, governance" />
                  </div>
                  <div class="lg:col-span-3">
                    <label class="block mb-1 font-semibold text-gray-700">Abstract</label>
                    <textarea name="abstract" rows="3" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter article abstract..."></textarea>
                  </div>
                  <div class="lg:col-span-3">
                    <label class="block mb-1 font-semibold text-gray-700">Article Summary</label>
                    <textarea name="article_summary" rows="2" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Brief summary of the article"></textarea>
                  </div>
                  <div class="lg:col-span-3">
                    <label class="block mb-1 font-semibold text-gray-700">Article PDF</label>
                    <input type="file" name="article_pdf" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                    <p class="text-xs text-gray-500 mt-1">Max <?= $maxPdfSize ?>MB per article. Large files will be compressed.</p>
                  </div>
                </div>
                <button type="submit" name="add_article" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                  <i class="fas fa-plus"></i> Add Article
                </button>
              </form>
            </div>
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
