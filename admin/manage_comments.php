<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$comments = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Comments - PSA Admin</title>
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

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Comments</h1>

    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <table class="w-full table-auto text-sm">
        <thead class="bg-green-800 text-white">
          <tr>
            <th class="p-2 text-left">#</th>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Comment</th>
            <th class="p-2 text-left">Status</th>
            <th class="p-2 text-left">Date</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($c = $comments->fetch_assoc()):
          ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-2"><?= $i++ ?></td>
            <td class="p-2"><?= htmlspecialchars($c['name'] ?? 'Anonymous') ?></td>
            <td class="p-2"><?= htmlspecialchars($c['email'] ?? 'N/A') ?></td>
            <td class="p-2"><?= htmlspecialchars(mb_strimwidth($c['comment'] ?? '', 0, 50, '...')) ?></td>
            <td class="p-2">
              <?php if ($c['approved']): ?>
                <span class="text-green-600 font-medium">Approved</span>
              <?php else: ?>
                <span class="text-yellow-600 font-medium">Pending</span>
              <?php endif; ?>
            </td>
            <td class="p-2"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
            <td class="p-2 space-x-2">
              <?php if (!$c['approved']): ?>
                <a href="approve_comment.php?id=<?= $c['id'] ?>" class="text-green-600 hover:underline"><i class="fas fa-check"></i> Approve</a>
              <?php endif; ?>
              <a href="delete_comment.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this comment?')" class="text-red-600 hover:underline"><i class="fas fa-trash"></i> Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-500">
      &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <strong>Leonard Mhone</strong>
    </footer>
  </main>
</body>
</html>
