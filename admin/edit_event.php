<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Edit Event - PSA Admin';

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_events.php");
    exit;
}

$id = (int)$_GET['id'];
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
if (!$event) {
    die("Event not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d');
    $organizer = trim($_POST['organizer'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $registration_link = trim($_POST['registration_link'] ?? '');
    $google_map = trim($_POST['google_map'] ?? '');
    $online_link = trim($_POST['online_link'] ?? '');

    $image = $event['image'];
    if (!empty($_FILES['image']['name'])) {
        $year = date('Y', strtotime($start_date));
        $month = date('m', strtotime($start_date));
        $uploadDir = "../uploads/events/$year/$month/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    $brochure = $event['brochure'];
    if (!empty($_FILES['brochure']['name'])) {
        $year = date('Y', strtotime($start_date));
        $month = date('m', strtotime($start_date));
        $uploadDir = "../uploads/events/$year/$month/brochures/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $brochure = time() . '_' . basename($_FILES['brochure']['name']);
        move_uploaded_file($_FILES['brochure']['tmp_name'], $uploadDir . $brochure);
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    $stmt = $conn->prepare("UPDATE events SET title=?, slug=?, description=?, image=?, venue=?, organizer=?, start_date=?, end_date=?, featured=?, registration_link=?, google_map=?, brochure=?, event_type=?, online_link=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssssissssssi", $title, $slug, $description, $image, $venue, $organizer, $start_date, $end_date, $featured, $registration_link, $google_map, $brochure, $event_type, $online_link, $status, $id);

    if ($stmt->execute()) {
        $oldValues = ['title' => $event['title'], 'status' => $event['status']];
        $newValues = ['title' => $title, 'status' => $status];
        logAudit($conn, 'update', 'events', $id, $oldValues, $newValues);
        $success = "Event updated successfully!";
        $event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
    } else {
        $error = "Failed to update event.";
    }
}

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 md:ml-64 min-h-screen transition-all duration-300" id="main-content">
    <!-- Top Header Bar -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40 transition-colors duration-300">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button id="menu-toggle" class="md:hidden text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Edit Event</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Update event details</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_events.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if ($success): ?>
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Start Date <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="start_date" value="<?= htmlspecialchars($event['start_date']) ?>" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">End Date</label>
                    <input type="datetime-local" name="end_date" value="<?= htmlspecialchars($event['end_date'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Venue</label>
                    <input type="text" name="venue" value="<?= htmlspecialchars($event['venue'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Organizer</label>
                    <input type="text" name="organizer" value="<?= htmlspecialchars($event['organizer'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Type</label>
                    <select name="event_type" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="physical" <?= $event['event_type'] === 'physical' ? 'selected' : '' ?>>Physical</option>
                        <option value="online" <?= $event['event_type'] === 'online' ? 'selected' : '' ?>>Online</option>
                        <option value="hybrid" <?= $event['event_type'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="upcoming" <?= $event['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="past" <?= $event['status'] === 'past' ? 'selected' : '' ?>>Past</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" rows="5" class="ckeditor w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="featured" <?= $event['featured'] ? 'checked' : '' ?> class="rounded">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Featured Event</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Registration Link</label>
                    <input type="url" name="registration_link" value="<?= htmlspecialchars($event['registration_link'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Online Link (for online/hybrid events)</label>
                    <input type="url" name="online_link" value="<?= htmlspecialchars($event['online_link'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Google Map Embed</label>
                    <textarea name="google_map" rows="3" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"><?= htmlspecialchars($event['google_map'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Image (leave blank to keep existing)</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                    <?php if (!empty($event['image'])): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current: <?= htmlspecialchars($event['image']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Brochure (leave blank to keep existing)</label>
                    <input type="file" name="brochure" accept="application/pdf" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                    <?php if (!empty($event['brochure'])): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Current: <?= htmlspecialchars($event['brochure']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_events.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Update Event</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
