<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

// Handle feature toggle action
if (isset($_GET['toggle']) && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $current = (int)$_GET['current'];
  $newStatus = $current ? 0 : 1;
  $conn->query("UPDATE news SET featured = $newStatus WHERE id = $id");
  header("Location: manage_news.php");
  exit;
}

$news = $conn->query("SELECT * FROM news ORDER BY date_posted DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage News - PSA Admin</title>
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
<body class="bg-gray-100 font-sans flex">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 p-6 overflow-x-auto">
    <h1 class="text-3xl font-bold text-green-800 mb-6">📰 Manage News Articles</h1>

    <div class="bg-white p-4 rounded shadow-md">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-green-700 text-white text-left">
            <th class="p-2">#</th>
            <th class="p-2">Title</th>
            <th class="p-2">Author</th>
            <th class="p-2">Category</th>
            <th class="p-2">Tags</th>
            <th class="p-2">Date</th>
            <th class="p-2">Featured</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while($n = $news->fetch_assoc()): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="p-2"><?= $i++ ?></td>
            <td class="p-2"><?= htmlspecialchars($n['title']) ?></td>
            <td class="p-2"><?= htmlspecialchars($n['author']) ?></td>
            <td class="p-2"><?= htmlspecialchars($n['category']) ?></td>
            <td class="p-2"><?= htmlspecialchars($n['tags']) ?></td>
            <td class="p-2"><?= date('d M Y', strtotime($n['date_posted'])) ?></td>
            <td class="p-2">
              <a href="?toggle=1&id=<?= $n['id'] ?>&current=<?= $n['featured'] ?>" 
                 class="text-lg <?= $n['featured'] ? 'text-green-600' : 'text-gray-400' ?>" 
                 title="Click to toggle featured">
                <?= $n['featured'] ? '✅' : '❌' ?>
              </a>
            </td>
            <td class="p-2 space-x-2">
              <a href="edit_news.php?id=<?= $n['id'] ?>" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i> Edit</a>
              <a href="delete_news.php?id=<?= $n['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this news item?')"><i class="fas fa-trash-alt"></i> Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
