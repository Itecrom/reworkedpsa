<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid user ID.");
}

$id = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM admins WHERE id = $id")->fetch_assoc();
if (!$user) {
  die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $role = $_POST['role'] ?? 'admin';

  if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, password=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $username, $email, $hashedPassword, $role, $id);
  } else {
    $stmt = $conn->prepare("UPDATE admins SET username=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $username, $email, $role, $id);
  }

  if ($stmt->execute()) {
    $success = "User updated successfully!";
    $user = $conn->query("SELECT * FROM admins WHERE id = $id")->fetch_assoc();
  } else {
    $error = "Failed to update user.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User - PSA Admin</title>
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
    <h2 class="text-2xl font-bold text-green-800 mb-4">Edit Admin User</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="post" class="bg-white p-6 rounded shadow max-w-xl">
      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">New Password (leave blank to keep current)</label>
        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Role</label>
        <select name="role" class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="editor" <?= $user['role'] == 'editor' ? 'selected' : '' ?>>Editor</option>
        </select>
      </div>

      <div class="flex justify-between">
        <a href="manage_users.php" class="text-gray-600 hover:text-black">← Back to Users</a>
        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
          <i class="fas fa-save mr-1"></i> Update User
        </button>
      </div>
    </form>
  </main>
</body>
</html>
