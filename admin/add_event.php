<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Add Event - PSA Admin';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $organizer = trim($_POST['organizer'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $status = $_POST['status'] ?? 'upcoming';

    // Handle image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $year = date('Y', strtotime($start_date));
        $month = date('m', strtotime($start_date));
        $uploadDir = "../uploads/events/$year/$month/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    $stmt = $conn->prepare("INSERT INTO events (title, description, venue, start_date, end_date, organizer, event_type, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $description, $venue, $start_date, $end_date, $organizer, $event_type, $status, $image);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        logAudit($conn, 'create', 'events', $newId, null, ['title' => $title, 'status' => $status]);
        $success = "Event added successfully!";
    } else {
        $error = "Failed to add event: " . $conn->error;
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Add Event</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Create a new event</p>
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
                    <input type="text" name="title" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">End Date</label>
                    <input type="date" name="end_date" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Venue</label>
                    <input type="text" name="venue" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Organizer</label>
                    <input type="text" name="organizer" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Type</label>
                    <select name="event_type" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="conference">Conference</option>
                        <option value="seminar">Seminar</option>
                        <option value="workshop">Workshop</option>
                        <option value="meeting">Meeting</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="past">Past</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" rows="5" class="ckeditor w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Event Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_events.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Save Event</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
