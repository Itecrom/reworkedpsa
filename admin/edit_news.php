<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid news ID.");
}

$id = (int)$_GET['id'];
$news = $conn->query("SELECT * FROM news WHERE id = $id")->fetch_assoc();
if (!$news) {
  die("News not found.");
}

$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $content = $_POST['content'];
  $tags = $_POST['tags'];
  $category = $_POST['category'];
  $date = $_POST['date_posted'];
  $featured = isset($_POST['featured']) ? 1 : 0;

  $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

  $image = $news['image'];
  if (!empty($_FILES['image']['name'])) {
    $year = date('Y');
    $month = date('m');
    $uploadDir = "uploads/news/{$year}/{$month}/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $info = getimagesize($_FILES['image']['tmp_name']);
    if ($info !== false) {
      $mime = $info['mime'];
      $allowed = ['image/jpeg', 'image/png', 'image/gif'];
      if (in_array($mime, $allowed)) {
        $ext = explode('/', $mime)[1];
        if ($ext === 'jpeg') $ext = 'jpg';
        $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
          $image = $targetPath;
        }
      }
    }
  }

  $pdf = $news['pdf'];
  if (!empty($_FILES['pdf']['name'])) {
    $uploadDir = "uploads/pdfs/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $uploadResult = handlePdfUpload($_FILES['pdf'], $uploadDir, 'news_', $maxPdfSize);
    if ($uploadResult['success']) {
      $pdf = $uploadResult['filename'];
      if (!empty($uploadResult['info']['message'])) {
        $success = $uploadResult['info']['message'] . ' ';
      }
    } else {
      $error = $uploadResult['error'];
    }
  }

  if (!$error) {
    $stmt = $conn->prepare("UPDATE news SET title=?, content=?, image=?, pdf=?, date_posted=?, category=?, tags=?, featured=?, slug=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $title, $content, $image, $pdf, $date, $category, $tags, $featured, $slug, $id);

    if ($stmt->execute()) {
      $success .= "News updated successfully!";
      $news = $conn->query("SELECT * FROM news WHERE id = $id")->fetch_assoc();
    } else {
      $error = "Failed to update news.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit News - PSA Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
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

  <style>
    .cke {
      max-width: 100%;
    }
  </style>
</head>

<body class="bg-gray-100 font-sans flex">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-green-800 mb-4">Edit News Article</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-6">

      <div>
        <label class="block font-semibold mb-1">News Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($news['category']) ?>" required class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Tags (comma-separated)</label>
        <input type="text" name="tags" value="<?= htmlspecialchars($news['tags']) ?>" class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Featured Image</label>
        <input type="file" name="image" accept="image/*" class="w-full mb-2"/>
        <?php if (!empty($news['image'])): ?>
          <img src="<?= $news['image'] ?>" class="rounded border w-48 h-auto"/>
        <?php endif; ?>
      </div>

      <div>
        <label class="block font-semibold mb-1">Attach PDF (optional)</label>
        <input type="file" name="pdf" accept="application/pdf" class="w-full border p-2 rounded"/>
        <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be compressed.</p>
        <?php if (!empty($news['pdf'])): ?>
          <p class="text-sm text-gray-600 mt-1">Current: <a href="<?= $news['pdf'] ?>" target="_blank" class="text-blue-600 underline"><?= basename($news['pdf']) ?></a></p>
        <?php endif; ?>
      </div>

      <div>
        <label class="block font-semibold mb-1">Date</label>
        <input type="date" name="date_posted" value="<?= htmlspecialchars($news['date_posted']) ?>" required class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Full Article Content</label>
        <textarea name="content" id="editor" rows="15" class="w-full border p-2 rounded"><?= htmlspecialchars($news['content']) ?></textarea>
      </div>

      <div>
        <label class="inline-flex items-center">
          <input type="checkbox" name="featured" value="1" class="mr-2" <?= $news['featured'] ? 'checked' : '' ?>>
          <span class="font-semibold">Feature this article on homepage slider</span>
        </label>
      </div>

      <div class="text-right">
        <button class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 transition">
          <i class="fas fa-check-circle mr-1"></i> Update Article
        </button>
      </div>
    </form>
  </div>

</body>
</html>

<script>
CKEDITOR.replace('editor', {
  filebrowserUploadUrl: 'ckeditor_upload.php',
  filebrowserUploadMethod: 'form',
});
</script>
