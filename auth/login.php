<?php
session_start();
include '../includes/db.php';

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['admin'] = $username;
    $_SESSION['admin_id'] = $user['id'];
    header("Location: /dashboard");
    exit;
  } else {
    $loginError = "Invalid username or password";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>PSA Admin Login</title>
  <link rel="icon" href="../images/favicon.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
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
    body {
      background: linear-gradient(135deg, #000, #117733, #cc0000);
      min-height: 100vh;
    }
  </style>
</head>
<body class="flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md mx-4">
    <div class="text-center mb-8">
      <img src="../images/logo.png" alt="PSA Logo" class="mx-auto mb-4 w-20">
      <h1 class="text-2xl font-bold text-gray-800">PSA Admin</h1>
      <p class="text-gray-600">Sign in to your account</p>
    </div>

    <?php if ($loginError): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($loginError) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
      </div>

      <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-4 rounded transition">
        Sign In
      </button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-500">
      &copy; <?= date('Y') ?> PSA Admin Panel v2.0
    </div>
  </div>
</body>
</html>
