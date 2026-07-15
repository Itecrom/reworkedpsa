<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

// Slug generation
function generateSlug($string) {
  return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function generateUniqueSlug($conn, $title) {
  $baseSlug = generateSlug($title);
  $slug = $baseSlug;
  $i = 1;

  while (true) {
    $check = $conn->prepare("SELECT id FROM news WHERE slug = ? LIMIT 1");
    $check->bind_param("s", $slug);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) break;
    $slug = $baseSlug . '-' . $i++;
  }

  return $slug;
}

// Image compression (unchanged except safety)
function compressImage($source, $destination, $mime, $quality = 75) {
  if (empty($source) || !file_exists($source)) return false;

  switch ($mime) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($source);
      imagejpeg($image, $destination, $quality);
      break;
    case 'image/png':
      $image = imagecreatefrompng($source);
      imagepng($image, $destination);
      break;
    case 'image/gif':
      $image = imagecreatefromgif($source);
      imagegif($image, $destination);
      break;
    default:
      return false;
  }

  imagedestroy($image);
  return true;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = $_POST['title'];
  $author = $_SESSION['admin'];
  $content = $_POST['content'];
  $tags = $_POST['tags'];
  $category = $_POST['category'];
  $date = $_POST['date_posted'] ?: date('Y-m-d');
  $featured = isset($_POST['featured']) ? 1 : 0;

  $slug = generateUniqueSlug($conn, $title);
  $image = '';

  // Image upload
  if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] === 0 &&
    !empty($_FILES['image']['tmp_name'])
  ) {
    $year = date('Y');
    $month = date('m');
    $uploadDir = "uploads/news/{$year}/{$month}/";

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $info = getimagesize($_FILES['image']['tmp_name']);

    if ($info !== false) {
      $mime = $info['mime'];
      $allowed = ['image/jpeg', 'image/png', 'image/gif'];

      if (in_array($mime, $allowed)) {

        $ext = explode('/', $mime)[1];
        if ($ext === 'jpeg') $ext = 'jpg';

        $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;

        // Try compression first
        if (compressImage($_FILES['image']['tmp_name'], $targetPath, $mime, 75)) {
          $image = $targetPath;

        } else {
          // Fallback: direct move
          if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $targetPath;
          } else {
            $error = "Image upload failed completely.";
          }
        }

      } else {
        $error = "Invalid image format.";
      }

    } else {
      $error = "Uploaded file is not a valid image.";
    }
  }

  // PDF upload with validation
  $pdf = '';
  if (!empty($_FILES['pdf']['name'])) {
    $uploadDir = "uploads/pdfs/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);
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

  // INSERT
  $status = 'published';

  if (!$error) {
    $stmt = $conn->prepare("INSERT INTO news (title, slug, author, content, image, pdf, date_posted, category, tags, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssis", $title, $slug, $author, $content, $image, $pdf, $date, $category, $tags, $featured, $status);

    if ($stmt->execute()) {
      $success .= "News article added successfully!";
    } else {
      $error = "Failed to add news: " . $stmt->error;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add News - PSA Admin</title>

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
    <h2 class="text-3xl font-bold text-green-800 mb-4">Add News Article</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-6">

      <div>
        <label class="block font-semibold mb-1">News Title</label>
        <input type="text" name="title" required class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Category</label>
        <input type="text" name="category" required class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Tags (comma-separated)</label>
        <input type="text" name="tags" class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Upload Featured Image</label>
        <input type="file" name="image" id="imageInput" accept="image/*" class="w-full mb-2"/>
        <img id="preview" class="rounded border w-64 h-auto hidden"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Attach PDF (optional)</label>
        <input type="file" name="pdf" accept="application/pdf" class="w-full border p-2 rounded"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Date (optional)</label>
        <input type="date" name="date_posted" class="w-full border p-2 rounded" value="<?= date('Y-m-d') ?>"/>
      </div>

      <div>
        <label class="block font-semibold mb-1">Full Article Content</label>
        <textarea name="content" id="editor" rows="15" class="w-full border p-2 rounded"></textarea>
      </div>

      <div>
        <label class="inline-flex items-center">
          <input type="checkbox" name="featured" value="1" class="mr-2"/>
          <span class="font-semibold">Feature this article on homepage slider</span>
        </label>
      </div>

      <div class="text-right">
        <button class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 transition">
          <i class="fas fa-check-circle mr-1"></i> Publish Article
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

// Preview image
document.getElementById('imageInput').addEventListener('change', function (e) {
  const file = e.target.files[0];
  if (!file) return;

  const preview = document.getElementById('preview');
  preview.src = URL.createObjectURL(file);
  preview.classList.remove('hidden');
});
</script>
