<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

// Slug generation function
function generateSlug($str) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str)));
    return $slug;
}

// Create uploads directory if not exists
$uploadDir = __DIR__ . '/uploads/journals/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $slug = generateSlug($title);

    // Check for duplicate slug and append number if needed
    $slugBase = $slug;
    $counter = 1;
    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM journals WHERE slug = ?");
    while (true) {
        $stmtCheck->bind_param("s", $slug);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        if ($count == 0) {
            break;
        }
        $slug = $slugBase . '-' . $counter++;
    }
    $stmtCheck->close();

    // Handle main journal PDF upload
    $pdfName = '';
    if (!empty($_FILES['pdf']['name'])) {
        $uploadResult = handlePdfUpload($_FILES['pdf'], $uploadDir, 'journal_', $maxPdfSize, $pdfQuality);
        if ($uploadResult['success']) {
            $pdfName = $uploadResult['filename'];
            if (!empty($uploadResult['info']['message'])) {
                $success = $uploadResult['info']['message'] . ' ';
            }
        } else {
            $error = $uploadResult['error'];
        }
    }

    if (!$error) {
        // Insert main journal
        $stmt = $conn->prepare("INSERT INTO journals (title, author, summary, tags, file, created_at, slug) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $title, $author, $summary, $tags, $pdfName, $date, $slug);

        if ($stmt->execute()) {
            $journal_id = $conn->insert_id;

            // Insert articles with prepared statement
            if (!empty($_POST['article_title']) && is_array($_POST['article_title'])) {
                $stmtArticle = $conn->prepare("INSERT INTO journal_topics (journal_id, topic, file, article_author, article_summary) VALUES (?, ?, ?, ?, ?)");

                foreach ($_POST['article_title'] as $i => $articleTitle) {
                    $article_title = trim($articleTitle);
                    $article_pdf = '';
                    $article_author = trim($_POST['article_author'][$i] ?? '');
                    $article_summary = trim($_POST['article_summary'][$i] ?? '');

                    if (!empty($_FILES['article_pdf']['name'][$i]) && $_FILES['article_pdf']['error'][$i] === UPLOAD_ERR_OK) {
                        $articlePdfFile = $_FILES['article_pdf'];
                        $uploadResult = handlePdfUpload([
                            'tmp_name' => $articlePdfFile['tmp_name'][$i],
                            'name' => $articlePdfFile['name'][$i],
                            'size' => $articlePdfFile['size'][$i],
                            'error' => $articlePdfFile['error'][$i]
                        ], $uploadDir, 'article_', $maxPdfSize, $pdfQuality);

                        if ($uploadResult['success']) {
                            $article_pdf = $uploadResult['filename'];
                        }
                    }

                    if (!empty($article_title)) {
                        $stmtArticle->bind_param("issss", $journal_id, $article_title, $article_pdf, $article_author, $article_summary);
                        $stmtArticle->execute();
                    }
                }
                $stmtArticle->close();
            }

            $success .= "Journal and articles added successfully!";
        } else {
            $error = "Failed to add journal: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
    <title>Add Journal - PSA Admin</title>
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
      <h2 class="text-2xl font-bold text-green-800 mb-4">Add New Journal</h2>

      <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
      <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-4xl">
        <!-- Journal Title Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Journal Information</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block mb-1 font-semibold">Journal Title <span class="text-red-500">*</span></label>
              <input name="title" required class="w-full border px-3 py-2 rounded" />
            </div>
            <div>
              <label class="block mb-1 font-semibold">Author / Editor</label>
              <input name="author" class="w-full border px-3 py-2 rounded" />
            </div>
          </div>

          <div class="mt-4">
            <label class="block mb-1 font-semibold">Abstract / Summary</label>
            <textarea name="summary" rows="4" class="w-full border px-3 py-2 rounded"></textarea>
          </div>

          <div class="mt-4">
            <label class="block mb-1 font-semibold">Keywords (comma separated)</label>
            <input name="tags" class="w-full border px-3 py-2 rounded" />
          </div>

          <div class="mt-4">
            <label class="block mb-1 font-semibold">Journal PDF (Optional - Full Journal)</label>
            <input type="file" name="pdf" accept="application/pdf" class="w-full border px-3 py-2 rounded" />
            <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be automatically compressed.</p>
          </div>

          <div class="mt-4">
            <label class="block mb-1 font-semibold">Publication Date</label>
            <input type="date" name="date" required class="w-full border px-3 py-2 rounded" />
          </div>
        </div>

        <!-- Articles Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Individual Articles</h3>
          <p class="text-sm text-gray-600 mb-4">Add individual articles that will appear under this journal. Each article can have its own PDF.</p>

          <div id="article-container">
            <div class="article-entry bg-gray-50 p-4 rounded mb-4 border">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="block mb-1 font-semibold text-sm">Article Title <span class="text-red-500">*</span></label>
                  <input type="text" name="article_title[]" placeholder="Enter article title" class="w-full border px-3 py-2 rounded" required />
                </div>
                <div>
                  <label class="block mb-1 font-semibold text-sm">Article Author</label>
                  <input type="text" name="article_author[]" placeholder="Author name" class="w-full border px-3 py-2 rounded" />
                </div>
              </div>
              <div class="mb-4">
                <label class="block mb-1 font-semibold text-sm">Article Summary</label>
                <textarea name="article_summary[]" rows="2" placeholder="Brief summary of the article" class="w-full border px-3 py-2 rounded"></textarea>
              </div>
              <div>
                <label class="block mb-1 font-semibold text-sm">Article PDF</label>
                <input type="file" name="article_pdf[]" accept="application/pdf" class="w-full border px-3 py-2 rounded" />
                <p class="text-xs text-gray-500 mt-1">Max <?= $maxPdfSize ?>MB per article</p>
              </div>
            </div>
          </div>

          <button type="button" id="add-article" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Another Article
          </button>
        </div>

        <div class="flex justify-between">
          <a href="manage_journals.php" class="text-gray-600 hover:text-black">← Back to Journals</a>
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
            <i class="fas fa-save mr-1"></i> Save Journal
          </button>
        </div>
      </form>
    </main>
  </div>

  <footer class="text-center text-sm text-gray-500 mt-auto py-4 bg-gray-100">
    &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <strong>Leonard Mhone</strong>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
  <script>
    document.getElementById('add-article').addEventListener('click', () => {
      const container = document.getElementById('article-container');
      const entry = document.createElement('div');
      entry.className = 'article-entry bg-gray-50 p-4 rounded mb-4 border';
      entry.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block mb-1 font-semibold text-sm">Article Title <span class="text-red-500">*</span></label>
            <input type="text" name="article_title[]" placeholder="Enter article title" class="w-full border px-3 py-2 rounded" required />
          </div>
          <div>
            <label class="block mb-1 font-semibold text-sm">Article Author</label>
            <input type="text" name="article_author[]" placeholder="Author name" class="w-full border px-3 py-2 rounded" />
          </div>
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-semibold text-sm">Article Summary</label>
          <textarea name="article_summary[]" rows="2" placeholder="Brief summary of the article" class="w-full border px-3 py-2 rounded"></textarea>
        </div>
        <div>
          <label class="block mb-1 font-semibold text-sm">Article PDF</label>
          <input type="file" name="article_pdf[]" accept="application/pdf" class="w-full border px-3 py-2 rounded" />
          <p class="text-xs text-gray-500 mt-1">Max <?= $maxPdfSize ?>MB per article</p>
        </div>
      `;
      container.appendChild(entry);
    });
  </script>
</body>
</html>
