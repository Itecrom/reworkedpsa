<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid team member ID.");
}

$id = (int)$_GET['id'];
$member = $conn->query("SELECT * FROM team_members WHERE id = $id")->fetch_assoc();
if (!$member) {
  die("Team member not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $position = trim($_POST['position']);
  $category = trim($_POST['category']);
  $facebook = trim($_POST['facebook']);
  $twitter = trim($_POST['twitter']);
  $linkedin = trim($_POST['linkedin']);
  $google = trim($_POST['google']);
  $bio = trim($_POST['bio']);
  $status = isset($_POST['status']) ? $_POST['status'] : 'active';

  $image = $member['image'];
  if (!empty($_FILES['image']['name'])) {
    $uploadDir = "uploads/team/";
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
          $image = $newFileName;
        }
      }
    }
  }

  $stmt = $conn->prepare("UPDATE team_members SET name=?, position=?, category=?, image=?, facebook=?, twitter=?, linkedin=?, google=?, bio=?, status=? WHERE id=?");
  $stmt->bind_param("ssssssssssi", $name, $position, $category, $image, $facebook, $twitter, $linkedin, $google, $bio, $status, $id);

  if ($stmt->execute()) {
    $success = "Team member updated successfully!";
    $member = $conn->query("SELECT * FROM team_members WHERE id = $id")->fetch_assoc();
  } else {
    $error = "Failed to update team member.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Team Member - PSA Admin</title>
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
    <h2 class="text-2xl font-bold text-green-800 mb-4">Edit Team Member</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-2xl">
      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Position</label>
        <input type="text" name="position" value="<?= htmlspecialchars($member['position']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Category</label>
        <select name="category" class="w-full border border-gray-300 rounded px-3 py-2">
          <?php
          $categories = ['Patron', 'Members of the Executive', 'Editorial Team', 'Secretariate'];
          foreach ($categories as $cat):
          ?>
            <option value="<?= $cat ?>" <?= $member['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Profile Photo</label>
        <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2" />
        <?php if (!empty($member['image'])): ?>
          <img src="uploads/team/<?= $member['image'] ?>" class="w-24 h-24 rounded-full mt-2 object-cover" />
        <?php endif; ?>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Bio</label>
        <textarea name="bio" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block mb-1 font-semibold text-gray-700">Facebook</label>
          <input type="url" name="facebook" value="<?= htmlspecialchars($member['facebook']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block mb-1 font-semibold text-gray-700">Twitter</label>
          <input type="url" name="twitter" value="<?= htmlspecialchars($member['twitter']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block mb-1 font-semibold text-gray-700">LinkedIn</label>
          <input type="url" name="linkedin" value="<?= htmlspecialchars($member['linkedin']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block mb-1 font-semibold text-gray-700">Google</label>
          <input type="url" name="google" value="<?= htmlspecialchars($member['google']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Status</label>
        <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="active" <?= $member['status'] == 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $member['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="flex justify-between">
        <a href="manage_team.php" class="text-gray-600 hover:text-black">← Back to Team</a>
        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
          <i class="fas fa-save mr-1"></i> Update Member
        </button>
      </div>
    </form>
  </main>
</body>
</html>
