<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid journal ID.");
}

$id = (int) $_GET['id'];
$journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
if (!$journal) {
  die("Journal not found.");
}

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $summary = trim($_POST['summary']);
  $author = trim($_POST['author']);
  $tags = trim($_POST['tags']);
  $date = $_POST['date'];

  $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

  $pdfName = $journal['file'];
  if (!empty($_FILES['pdf']['name'])) {
    $uploadDir = __DIR__ . '/uploads/journals/';
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
    $stmt = $conn->prepare("UPDATE journals SET title=?, summary=?, author=?, tags=?, slug=?, file=?, created_at=? WHERE id=?");
    $stmt->bind_param("sssssssi", $title, $summary, $author, $tags, $slug, $pdfName, $date, $id);

    if ($stmt->execute()) {
      $success .= "Journal updated successfully!";
      $journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
    } else {
      $error = "Failed to update journal.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
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
      <h2 class="text-2xl font-bold text-green-800 mb-4">Edit Journal</h2>

      <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
      <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-2xl">
        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Journal Title <span class="text-red-500">*</span></label>
          <input name="title" value="<?= htmlspecialchars($journal['title']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Author / Editor</label>
          <input name="author" value="<?= htmlspecialchars($journal['author'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Abstract / Summary</label>
          <textarea name="summary" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($journal['summary'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Keywords (comma-separated)</label>
          <input name="tags" value="<?= htmlspecialchars($journal['tags'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Journal PDF (leave blank to keep existing)</label>
          <input type="file" name="pdf" accept="application/pdf" class="w-full border border-gray-300 rounded px-3 py-2" />
          <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be compressed.</p>
          <?php if (!empty($journal['file'])): ?>
            <p class="text-sm text-gray-600 mt-1">Current: <a href="uploads/journals/<?= $journal['file'] ?>" target="_blank" class="text-blue-600 underline"><?= $journal['file'] ?></a></p>
          <?php endif; ?>
        </div>

        <div class="mb-4">
          <label class="block mb-1 font-semibold text-gray-700">Publication Date</label>
          <input type="date" name="date" value="<?= htmlspecialchars($journal['created_at'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>

        <div class="flex justify-between">
          <a href="manage_journals.php" class="text-gray-600 hover:text-black">← Back to Journals</a>
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
            <i class="fas fa-save mr-1"></i> Update Journal
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
