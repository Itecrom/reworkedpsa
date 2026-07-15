<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid event ID.");
}

$id = (int)$_GET['id'];
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
if (!$event) {
  die("Event not found.");
}

$maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $description = $_POST['description'];
  $venue = trim($_POST['venue']);
  $organizer = trim($_POST['organizer']);
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $featured = isset($_POST['featured']) ? 1 : 0;
  $registration_link = trim($_POST['registration_link']);
  $google_map = trim($_POST['google_map']);
  $event_type = $_POST['event_type'];
  $online_link = trim($_POST['online_link']);

  $status = (strtotime($end_date) < time()) ? 'past' : 'upcoming';

  $image = $event['image'];
  if (!empty($_FILES['image']['name'])) {
    $image = uploadFile($_FILES['image']);
  }

  $brochure = $event['brochure'];
  if (!empty($_FILES['brochure']['name'])) {
    $uploadDir = 'uploads/events/brochures/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $uploadResult = handlePdfUpload($_FILES['brochure'], $uploadDir, 'brochure_', $maxPdfSize);
    if ($uploadResult['success']) {
      $brochure = $uploadResult['filename'];
    } else {
      $error = $uploadResult['error'];
    }
  }

  if (!$error) {
    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, image=?, venue=?, organizer=?, start_date=?, end_date=?, featured=?, registration_link=?, google_map=?, brochure=?, event_type=?, online_link=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssssssssssi", $title, $description, $image, $venue, $organizer, $start_date, $end_date, $featured, $registration_link, $google_map, $brochure, $event_type, $online_link, $status, $id);

    if ($stmt->execute()) {
      $success = "Event updated successfully!";
      $event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
    } else {
      $error = "Failed to update event.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Event</title>

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
</head>

<body class="bg-gray-100 font-sans flex">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-green-800 mb-6">Edit Event</h2>

    <?php if($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded mb-4">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <?php if($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-4">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-5">

      <!-- TITLE -->
      <div>
        <label class="block font-semibold mb-1">Event Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required class="w-full border rounded p-2">
      </div>

      <!-- DESCRIPTION -->
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" id="editor" rows="10" class="w-full border rounded p-2"><?= htmlspecialchars($event['description']) ?></textarea>
      </div>

      <!-- IMAGE -->
      <div>
        <label class="block font-semibold mb-1">Featured Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($event['image'])): ?>
          <img src="<?= $event['image'] ?>" class="rounded border w-48 h-auto mt-2"/>
        <?php endif; ?>
      </div>

      <!-- VENUE -->
      <div>
        <label class="block font-semibold mb-1">Venue</label>
        <input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" class="w-full border rounded p-2">
      </div>

      <!-- ORGANIZER -->
      <div>
        <label class="block font-semibold mb-1">Organizer</label>
        <input type="text" name="organizer" value="<?= htmlspecialchars($event['organizer']) ?>" class="w-full border rounded p-2">
      </div>

      <!-- DATES -->
      <div class="grid grid-cols-2 gap-4">

        <div>
          <label class="block font-semibold mb-1">Start Date</label>
          <input type="datetime-local" name="start_date" value="<?= date('Y-m-d\TH:i', strtotime($event['start_date'])) ?>" required class="w-full border rounded p-2">
        </div>

        <div>
          <label class="block font-semibold mb-1">End Date</label>
          <input type="datetime-local" name="end_date" value="<?= date('Y-m-d\TH:i', strtotime($event['end_date'])) ?>" required class="w-full border rounded p-2">
        </div>

      </div>

      <!-- EVENT TYPE -->
      <div>
        <label class="block font-semibold mb-1">Event Type</label>

        <select name="event_type" id="eventType" class="w-full border rounded p-2">

          <option value="physical" <?= $event['event_type'] == 'physical' ? 'selected' : '' ?>>Physical</option>
          <option value="online" <?= $event['event_type'] == 'online' ? 'selected' : '' ?>>Online</option>
          <option value="hybrid" <?= $event['event_type'] == 'hybrid' ? 'selected' : '' ?>>Hybrid</option>

        </select>
      </div>

      <!-- ONLINE LINK -->
      <div id="onlineBox" class="<?= $event['event_type'] == 'physical' ? 'hidden' : '' ?>">
        <label class="block font-semibold mb-1">Online Meeting Link</label>
        <input type="url" name="online_link" value="<?= htmlspecialchars($event['online_link']) ?>" class="w-full border rounded p-2">
      </div>

      <!-- GOOGLE MAP -->
      <div id="mapBox" class="<?= $event['event_type'] == 'online' ? 'hidden' : '' ?>">
        <label class="block font-semibold mb-1">Google Maps Embed Link</label>
        <textarea name="google_map" rows="3" class="w-full border rounded p-2"><?= htmlspecialchars($event['google_map']) ?></textarea>
      </div>

      <!-- REGISTRATION -->
      <div>
        <label class="block font-semibold mb-1">Registration Link</label>
        <input type="url" name="registration_link" value="<?= htmlspecialchars($event['registration_link']) ?>" class="w-full border rounded p-2">
      </div>

      <!-- BROCHURE -->
      <div>
        <label class="block font-semibold mb-1">Brochure PDF</label>
        <input type="file" name="brochure" accept=".pdf">
        <p class="text-sm text-gray-500 mt-1">Maximum size: <?= $maxPdfSize ?>MB. Large files will be compressed.</p>
        <?php if (!empty($event['brochure'])): ?>
          <p class="text-sm text-gray-600 mt-1">Current: <a href="<?= $event['brochure'] ?>" target="_blank" class="text-blue-600 underline"><?= basename($event['brochure']) ?></a></p>
        <?php endif; ?>
      </div>

      <!-- FEATURED -->
      <div>
        <label class="inline-flex items-center">
          <input type="checkbox" name="featured" value="1" class="mr-2" <?= $event['featured'] ? 'checked' : '' ?>>
          <span>Feature this event</span>
        </label>
      </div>

      <!-- SUBMIT -->
      <div class="text-right">

        <button class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded">
          <i class="fas fa-check-circle mr-2"></i>
          Update Event
        </button>

      </div>

    </form>

  </div>

</div>

<footer class="text-center py-4 bg-gray-100 text-gray-700 border-t">
    &copy; <?= date('Y') ?> PSA Admin Panel v2.0
</footer>

<script>

CKEDITOR.replace('editor');

const eventType = document.getElementById('eventType');

const onlineBox = document.getElementById('onlineBox');

const mapBox = document.getElementById('mapBox');

function toggleFields() {

    const type = eventType.value;

    if(type === 'online') {

        onlineBox.classList.remove('hidden');

        mapBox.classList.add('hidden');

    }
    else if(type === 'physical') {

        onlineBox.classList.add('hidden');

        mapBox.classList.remove('hidden');

    }
    else {

        onlineBox.classList.remove('hidden');

        mapBox.classList.remove('hidden');
    }
}

toggleFields();

eventType.addEventListener('change', toggleFields);

</script>

</body>
</html>
