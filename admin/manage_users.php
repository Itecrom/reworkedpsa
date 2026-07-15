<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$users = $conn->query("SELECT * FROM admins");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - PSA Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
<body class="flex bg-gray-100 min-h-screen font-sans">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Manage Admin Users</h1>
      <a href="add_user.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
        <i class="fa fa-plus mr-1"></i>Add Admin
      </a>
    </div>

    <div class="overflow-x-auto shadow rounded-lg bg-white">
      <table class="min-w-full table-auto">
        <thead class="bg-red-600 text-white text-left">
          <tr>
            <th class="py-3 px-4">#</th>
            <th class="py-3 px-4">Username</th>
            <th class="py-3 px-4">Email</th>
            <th class="py-3 px-4">Actions</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          <?php $i = 1; while($user = $users->fetch_assoc()): ?>
          <tr class="border-b hover:bg-gray-100 transition">
            <td class="py-3 px-4"><?= $i++ ?></td>
            <td class="py-3 px-4"><?= htmlspecialchars($user['username']) ?></td>
            <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
            <td class="py-3 px-4 space-x-2">
              <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline"><i class="fa fa-edit"></i> Edit</a>
              <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')" class="text-red-600 hover:underline"><i class="fa fa-trash"></i> Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>
