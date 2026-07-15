<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid member ID.");
}

$id = (int)$_GET['id'];
$member = $conn->query("SELECT * FROM psa_members WHERE id = $id")->fetch_assoc();
if (!$member) {
  die("Member not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $qualification = trim($_POST['qualification']);
  $employer = trim($_POST['employer']);
  $expertise = trim($_POST['expertise']);
  $contacts = trim($_POST['contacts']);
  $phone = trim($_POST['phone']);
  $email = trim($_POST['email']);

  $image = $member['image'];
  if (!empty($_FILES['image']['name'])) {
    $uploadDir = "uploads/members/";
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

  $stmt = $conn->prepare("UPDATE psa_members SET name=?, qualification=?, employer=?, expertise=?, contacts=?, image=?, phone=?, email=? WHERE id=?");
  $stmt->bind_param("ssssssssi", $name, $qualification, $employer, $expertise, $contacts, $image, $phone, $email, $id);

  if ($stmt->execute()) {
    $success = "Member updated successfully!";
    $member = $conn->query("SELECT * FROM psa_members WHERE id = $id")->fetch_assoc();
  } else {
    $error = "Failed to update member.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Member - PSA Admin</title>
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
    <h2 class="text-2xl font-bold text-green-800 mb-4">Edit Member</h2>

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
        <label class="block mb-1 font-semibold text-gray-700">Qualification</label>
        <textarea name="qualification" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($member['qualification'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Employer</label>
        <textarea name="employer" rows="2" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($member['employer'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Expertise</label>
        <textarea name="expertise" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($member['expertise'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Contacts</label>
        <textarea name="contacts" rows="2" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($member['contacts'] ?? '') ?></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block mb-1 font-semibold text-gray-700">Phone</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block mb-1 font-semibold text-gray-700">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
      </div>

      <div class="mb-4">
        <label class="block mb-1 font-semibold text-gray-700">Profile Image</label>
        <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2" />
        <?php if (!empty($member['image'])): ?>
          <img src="images/team/profile/<?= $member['image'] ?>" class="w-24 h-24 rounded-full mt-2 object-cover" />
        <?php endif; ?>
      </div>

      <div class="flex justify-between">
        <a href="manage_members.php" class="text-gray-600 hover:text-black">← Back to Members</a>
        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded transition">
          <i class="fas fa-save mr-1"></i> Update Member
        </button>
      </div>
    </form>
  </main>
</body>
</html>
