<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';
include '../includes/pdf_helper.php';

/* =========================
   HELPERS
========================= */

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function generateUniqueSlug($conn, $title) {

    $baseSlug = generateSlug($title);
    $slug = $baseSlug;
    $i = 1;

    while (true) {

        $check = $conn->prepare("SELECT id FROM events WHERE slug=? LIMIT 1");
        $check->bind_param("s", $slug);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            break;
        }

        $slug = $baseSlug . '-' . $i++;
    }

    return $slug;
}

function uploadFile($file, $dir = 'uploads/events/') {

    if (
        isset($file['tmp_name']) &&
        !empty($file['tmp_name']) &&
        $file['error'] == 0
    ) {

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $filename = time() . '_' . rand(1000,9999) . '.' . $ext;

        $path = $dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $path;
        }
    }

    return '';
}

/* =========================
   SAVE EVENT
========================= */

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = trim($_POST['title']);
    $slug = generateUniqueSlug($conn, $title);

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

    /* IMAGE */
    $image = uploadFile($_FILES['image']);

    /* PDF BROCHURE with validation */
    $brochure = '';
    if (!empty($_FILES['brochure']['name'])) {
        $uploadDir = 'uploads/events/brochures/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $maxPdfSize = (int)getJournalSetting($conn, 'max_pdf_size_mb', 10);
        $uploadResult = handlePdfUpload($_FILES['brochure'], $uploadDir, 'brochure_', $maxPdfSize);

        if ($uploadResult['success']) {
            $brochure = $uploadResult['filename'];
            if (!empty($uploadResult['info']['message'])) {
                $success = $uploadResult['info']['message'] . ' ';
            }
        } else {
            $error = $uploadResult['error'];
        }
    }

    /* INSERT EVENT */
    if (!$error) {
        $stmt = $conn->prepare("
            INSERT INTO events (
                title,
                slug,
                description,
                image,
                venue,
                organizer,
                start_date,
                end_date,
                featured,
                registration_link,
                google_map,
                brochure,
                event_type,
                online_link,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssssissssss",
            $title,
            $slug,
            $description,
            $image,
            $venue,
            $organizer,
            $start_date,
            $end_date,
            $featured,
            $registration_link,
            $google_map,
            $brochure,
            $event_type,
            $online_link,
            $status
        );

        if ($stmt->execute()) {

            $event_id = $stmt->insert_id;

            /* =========================
               GALLERY IMAGES
            ========================= */

            if (!empty($_FILES['gallery']['name'][0])) {

                $galleryDir = "uploads/events/gallery/";

                if (!is_dir($galleryDir)) {
                    mkdir($galleryDir, 0777, true);
                }

                foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {

                    if ($_FILES['gallery']['error'][$key] == 0) {

                        $ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);

                        $galleryName = time() . '_' . rand(1000,9999) . '.' . $ext;

                        $galleryPath = $galleryDir . $galleryName;

                        move_uploaded_file($tmp_name, $galleryPath);

                        $gstmt = $conn->prepare("
                            INSERT INTO event_gallery (event_id, image)
                            VALUES (?, ?)
                        ");

                        $gstmt->bind_param("is", $event_id, $galleryPath);
                        $gstmt->execute();
                    }
                }
            }

            $success .= "Event added successfully.";

        } else {

            $error = $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Event</title>

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

  <!-- CONTENT -->
  <div class="flex-1 p-6">

      <h2 class="text-3xl font-bold text-green-800 mb-6">
          Add New Event
      </h2>

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
              <input type="text" name="title" required class="w-full border rounded p-2">
          </div>

          <!-- DESCRIPTION -->
          <div>
              <label class="block font-semibold mb-1">Description</label>
              <textarea name="description" id="editor" rows="10" class="w-full border rounded p-2"></textarea>
          </div>

          <!-- IMAGE -->
          <div>
              <label class="block font-semibold mb-1">Featured Image</label>
              <input type="file" name="image" accept="image/*">
          </div>

          <!-- GALLERY -->
          <div>
              <label class="block font-semibold mb-1">Event Gallery</label>
              <input type="file" name="gallery[]" multiple accept="image/*">
          </div>

          <!-- VENUE -->
          <div>
              <label class="block font-semibold mb-1">Venue</label>
              <input type="text" name="venue" class="w-full border rounded p-2">
          </div>

          <!-- ORGANIZER -->
          <div>
              <label class="block font-semibold mb-1">Organizer</label>
              <input type="text" name="organizer" class="w-full border rounded p-2">
          </div>

          <!-- DATES -->
          <div class="grid grid-cols-2 gap-4">

              <div>
                  <label class="block font-semibold mb-1">Start Date</label>
                  <input type="datetime-local" name="start_date" required class="w-full border rounded p-2">
              </div>

              <div>
                  <label class="block font-semibold mb-1">End Date</label>
                  <input type="datetime-local" name="end_date" required class="w-full border rounded p-2">
              </div>

          </div>

          <!-- EVENT TYPE -->
          <div>
              <label class="block font-semibold mb-1">Event Type</label>

              <select name="event_type" id="eventType" class="w-full border rounded p-2">

                  <option value="physical">Physical</option>

                  <option value="online">Online</option>

                  <option value="hybrid">Hybrid</option>

              </select>
          </div>

          <!-- ONLINE LINK -->
          <div id="onlineBox" class="hidden">
              <label class="block font-semibold mb-1">Online Meeting Link</label>
              <input type="url" name="online_link" class="w-full border rounded p-2">
          </div>

          <!-- GOOGLE MAP -->
          <div id="mapBox">
              <label class="block font-semibold mb-1">Google Maps Embed Link</label>
              <textarea name="google_map" rows="3" class="w-full border rounded p-2"></textarea>
          </div>

          <!-- REGISTRATION -->
          <div>
              <label class="block font-semibold mb-1">Registration Link</label>
              <input type="url" name="registration_link" class="w-full border rounded p-2">
          </div>

          <!-- BROCHURE -->
          <div>
              <label class="block font-semibold mb-1">Brochure PDF</label>
              <input type="file" name="brochure" accept=".pdf">
          </div>

          <!-- FEATURED -->
          <div>
              <label class="inline-flex items-center">
                  <input type="checkbox" name="featured" value="1" class="mr-2">
                  <span>Feature this event</span>
              </label>
          </div>

          <!-- SUBMIT -->
          <div class="text-right">

              <button class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded">
                  <i class="fas fa-check-circle mr-2"></i>
                  Publish Event
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
