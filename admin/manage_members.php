<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$members = $conn->query("SELECT * FROM psa_members ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Members - PSA Admin</title>
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
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Manage PSA Members</h1>
      <a href="add_members.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
        <i class="fas fa-plus mr-1"></i> Add Member
      </a>
    </div>

    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <table class="w-full table-auto text-sm">
        <thead class="bg-green-800 text-white">
          <tr>
            <th class="p-2 text-left">#</th>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Qualification</th>
            <th class="p-2 text-left">Employer</th>
            <th class="p-2 text-left">Expertise</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while ($m = $members->fetch_assoc()):
          ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-2"><?= $i++ ?></td>
            <td class="p-2 font-medium"><?= htmlspecialchars($m['name']) ?></td>
            <td class="p-2"><?= htmlspecialchars(mb_strimwidth($m['qualification'] ?? '', 0, 30, '...')) ?></td>
            <td class="p-2"><?= htmlspecialchars(mb_strimwidth($m['employer'] ?? '', 0, 30, '...')) ?></td>
            <td class="p-2"><?= htmlspecialchars(mb_strimwidth($m['expertise'] ?? '', 0, 30, '...')) ?></td>
            <td class="p-2 space-x-2">
              <a href="edit_members.php?id=<?= $m['id'] ?>" class="text-blue-600 hover:underline"><i class="fas fa-edit"></i> Edit</a>
              <a href="delete_members.php?id=<?= $m['id'] ?>" onclick="return confirm('Delete this member?')" class="text-red-600 hover:underline"><i class="fas fa-trash"></i> Delete</a>
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
