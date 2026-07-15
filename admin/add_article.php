<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

$journalId = isset($_GET['journal_id']) ? (int)$_GET['journal_id'] : 0;

if (!$journalId) {
    header("Location: manage_journals.php");
    exit;
}

$journal = $conn->query("SELECT * FROM journals WHERE id = $journalId")->fetch_assoc();
if (!$journal) {
    die("Journal not found.");
}

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = trim($_POST['topic'] ?? '');
    $articleAuthor = trim($_POST['article_author'] ?? '');
    $articleSummary = trim($_POST['article_summary'] ?? '');

    $pdfName = '';
    if (!empty($_FILES['pdf']['name'])) {
        $uploadDir = __DIR__ . '/uploads/journals/';
        $uploadResult = handlePdfUpload($_FILES['pdf'], $uploadDir, 'article_', $maxPdfSize, $pdfQuality);
        if ($uploadResult['success']) {
            $pdfName = $uploadResult['filename'];
            if (!empty($uploadResult['info']['message'])) {
                $success = $uploadResult['info']['message'] . ' ';
            }
        } else {
            $error = $uploadResult['error'];
        }
    } else {
        $error = "Please upload a PDF file for the article.";
    }

    if (!$error && !empty($topic)) {
        $stmt = $conn->prepare("INSERT INTO journal_topics (journal_id, topic, file, article_author, article_summary) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $journalId, $topic, $pdfName, $articleAuthor, $articleSummary);

        if ($stmt->execute()) {
            $success .= "Article added successfully!";
            // Clear form
            $_POST = [];
        } else {
            $error = "Failed to add article: " . $conn->error;
        }
        $stmt->close();
    } elseif (!$error) {
        $error = "Please enter an article title.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
    <title>Add Article - PSA Admin</title>
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
<body class="bg-gray-100 font-sans">
  <div class="flex min-h-screen">
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6">
      <div class="mb-4">
        <h2 class="text-2xl font-bold text-green-800">Add Article</h2>
        <p class="text-gray-600">Journal: <strong><?= htmlspecialchars($journal['title']) ?></strong></p>
      </div>

      <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
      <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-2xl">
        <div class="mb-4">
          <label class="block mb-1 font-semibold">Article Title <span class="text-red-500">*</span></label>
          <input type="text" name="topic" required class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>" />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold">Article Author</label>
          <input type="text" name="article_author" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($_POST['article_author'] ?? '') ?>" />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold">Article Summary</label>
          <textarea name="article_summary" rows="3" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($_POST['article_summary'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold">Article PDF <span class="text-red-500">*</span></label>
          <input type="file" name="pdf" accept="application/pdf" required class="w-full border px-3 py-2 rounded" />
          <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be automatically compressed.</p>
        </div>

        <div class="flex justify-between">
          <a href="manage_articles.php?journal_id=<?= $journalId ?>" class="text-gray-600 hover:text-black">← Back to Articles</a>
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
            <i class="fas fa-save mr-1"></i> Save Article
          </button>
        </div>
      </form>
    </main>
  </div>

  <footer class="text-center text-sm text-gray-500 mt-auto py-4 bg-gray-100">
    &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <strong>Leonard Mhone</strong>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
