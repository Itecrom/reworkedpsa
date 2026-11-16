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
include '../includes/audit_helper.php';

$success = '';
$error = '';

// Get PDF settings
$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 5);
$pdfQuality = getJournalSetting($conn, 'pdf_quality', 'medium');

// Slug generation function
function generateSlug($str) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str)));
    return $slug;
}

// Create uploads directory if not exists
$uploadDir = dirname(__DIR__) . '/uploads/journals/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $volume = trim($_POST['volume'] ?? '');
    $issue_no = trim($_POST['issue_no'] ?? '');
    $journal_date = $_POST['journal_date'] ?? date('Y-m-d');
    $editorial_summary = trim($_POST['editorial_summary'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
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
        // Insert main journal with new fields
        $stmt = $conn->prepare("INSERT INTO journals (title, volume, issue_no, journal_date, editorial_summary, author, summary, tags, file, created_at, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $title, $volume, $issue_no, $journal_date, $editorial_summary, $author, $summary, $tags, $pdfName, $journal_date, $slug);

        if ($stmt->execute()) {
            $journal_id = $conn->insert_id;

            // Insert articles with prepared statement
            if (!empty($_POST['article_title']) && is_array($_POST['article_title'])) {
                $stmtArticle = $conn->prepare("INSERT INTO journal_topics (journal_id, topic, file, article_author, article_summary, abstract, keywords, article_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($_POST['article_title'] as $i => $articleTitle) {
                    $article_title = trim($articleTitle);
                    $article_pdf = '';
                    $article_author = trim($_POST['article_author'][$i] ?? '');
                    $article_summary = trim($_POST['article_summary'][$i] ?? '');
                    $abstract = trim($_POST['abstract'][$i] ?? '');
                    $keywords = trim($_POST['keywords'][$i] ?? '');
                    $article_date = $_POST['article_date'][$i] ?? $journal_date;

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
                        } else {
                            $error = "Article PDF upload failed: " . $uploadResult['error'];
                            break;
                        }
                    } elseif (!empty($_FILES['article_pdf']['name'][$i])) {
                        $error = "Article PDF upload error (code: " . $_FILES['article_pdf']['error'][$i] . "). Please try again.";
                        break;
                    }

                    if (!empty($article_title)) {
                        $stmtArticle->bind_param("isssssss", $journal_id, $article_title, $article_pdf, $article_author, $article_summary, $abstract, $keywords, $article_date);
                        $stmtArticle->execute();
                    }
                }
                $stmtArticle->close();
            }

            logAudit($conn, 'create', 'journals', $journal_id, null, ['title' => $title, 'volume' => $volume]);
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
              <h2 class="text-xl font-bold text-gray-800">Add New Journal</h2>
              <p class="text-sm text-gray-500">Create a new journal issue with articles</p>
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
                <input name="title" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter journal title" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Volume</label>
                <input name="volume" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. Vol. 1" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Issue No</label>
                <input name="issue_no" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. Issue 1" />
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Publication Date <span class="text-red-500">*</span></label>
                <input type="date" name="journal_date" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Editorial Foreword / Summary</label>
                <textarea name="editorial_summary" rows="4" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Enter the editorial foreword or summary for this journal issue..."></textarea>
              </div>
              <div>
                <label class="block mb-1 font-semibold text-gray-700">Author / Editor (Optional)</label>
                <input name="author" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="Author or editor name" />
              </div>
              <div class="lg:col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Keywords (comma separated)</label>
                <input name="tags" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" placeholder="e.g. politics, governance, democracy" />
              </div>
              <div class="lg:col-span-3">
                <label class="block mb-1 font-semibold text-gray-700">Journal PDF (Optional - Full Journal)</label>
                <input type="file" name="pdf" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen focus:border-transparent" />
                <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be automatically compressed.</p>
              </div>
            </div>
          </div>

          <!-- Articles Section -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-file-alt text-psaRed"></i> Individual Articles
            </h3>
            <p class="text-sm text-gray-600 mb-4">Add individual articles that will appear under this journal. Each article can have its own PDF.</p>

            <div id="article-container">
              <div class="article-entry bg-gray-50 p-5 rounded-lg mb-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                  <div class="lg:col-span-2">
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="article_title[]" placeholder="Enter article title" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" required />
                  </div>
                  <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Author</label>
                    <input type="text" name="article_author[]" placeholder="Author name" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
                  </div>
                  <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Publication Date</label>
                    <input type="date" name="article_date[]" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
                  </div>
                  <div>
                    <label class="block mb-1 font-semibold text-sm text-gray-700">Keywords (comma separated)</label>
                    <input type="text" name="keywords[]" placeholder="e.g. democracy, elections" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="block mb-1 font-semibold text-sm text-gray-700">Abstract</label>
                  <textarea name="abstract[]" rows="3" placeholder="Enter article abstract..." class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div class="mb-4">
                  <label class="block mb-1 font-semibold text-sm text-gray-700">Article Summary</label>
                  <textarea name="article_summary[]" rows="2" placeholder="Brief summary of the article" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div>
                  <label class="block mb-1 font-semibold text-sm text-gray-700">Article PDF</label>
                  <input type="file" name="article_pdf[]" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
                  <p class="text-xs text-gray-500 mt-1">Max <?= $maxPdfSize ?>MB per article. Large files will be compressed.</p>
                </div>
              </div>
            </div>

            <button type="button" id="add-article" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
              <i class="fas fa-plus mr-1"></i> Add Another Article
            </button>
          </div>

          <!-- Form Actions -->
          <div class="p-6 bg-gray-50 rounded-b-lg flex flex-col sm:flex-row justify-between items-center gap-4">
            <a href="manage_journals.php" class="text-gray-600 hover:text-psaBlack transition flex items-center gap-2">
              <i class="fas fa-arrow-left"></i> Back to Journals
            </a>
            <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition flex items-center gap-2">
              <i class="fas fa-save"></i> Save Journal
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

    // Add article entry
    document.getElementById('add-article').addEventListener('click', () => {
      const container = document.getElementById('article-container');
      const entry = document.createElement('div');
      entry.className = 'article-entry bg-gray-50 p-5 rounded-lg mb-4 border border-gray-200';
      entry.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
          <div class="lg:col-span-2">
            <label class="block mb-1 font-semibold text-sm text-gray-700">Article Title <span class="text-red-500">*</span></label>
            <input type="text" name="article_title[]" placeholder="Enter article title" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" required />
          </div>
          <div>
            <label class="block mb-1 font-semibold text-sm text-gray-700">Author</label>
            <input type="text" name="article_author[]" placeholder="Author name" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
          </div>
          <div>
            <label class="block mb-1 font-semibold text-sm text-gray-700">Publication Date</label>
            <input type="date" name="article_date[]" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
          </div>
          <div>
            <label class="block mb-1 font-semibold text-sm text-gray-700">Keywords (comma separated)</label>
            <input type="text" name="keywords[]" placeholder="e.g. democracy, elections" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
          </div>
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-semibold text-sm text-gray-700">Abstract</label>
          <textarea name="abstract[]" rows="3" placeholder="Enter article abstract..." class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-semibold text-sm text-gray-700">Article Summary</label>
          <textarea name="article_summary[]" rows="2" placeholder="Brief summary of the article" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
        </div>
        <div>
          <label class="block mb-1 font-semibold text-sm text-gray-700">Article PDF</label>
          <input type="file" name="article_pdf[]" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen" />
          <p class="text-xs text-gray-500 mt-1">Max <?= $maxPdfSize ?>MB per article. Large files will be compressed.</p>
        </div>
      `;
      container.appendChild(entry);
    });
  </script>
</body>
</html>
