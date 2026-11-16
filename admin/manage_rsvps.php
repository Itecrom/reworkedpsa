<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage RSVPs - PSA Admin';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $conn->query("DELETE FROM event_rsvps WHERE id = $deleteId");
    header("Location: manage_rsvps.php?deleted=1");
    exit;
}

// Get filter
$eventFilter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Get all events for filter dropdown
$events = $conn->query("SELECT id, title, start_date FROM events ORDER BY start_date DESC");

// Build query
$where = '';
if ($eventFilter > 0) {
    $where = "WHERE r.event_id = $eventFilter";
}

$rsvps = $conn->query("
    SELECT r.*, e.title as event_title, e.start_date as event_start
    FROM event_rsvps r
    LEFT JOIN events e ON r.event_id = e.id
    $where
    ORDER BY r.created_at DESC
");

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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Manage RSVPs</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View and manage event RSVPs</p>
                </div>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            RSVP deleted successfully.
        </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="get" class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Event:</label>
                <select name="event_id" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-3 py-2 text-sm">
                    <option value="0">All Events</option>
                    <?php while ($evt = $events->fetch_assoc()): ?>
                        <option value="<?= $evt['id'] ?>" <?= $eventFilter == $evt['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($evt['title']) ?> (<?= date('M d, Y', strtotime($evt['start_date'])) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded text-sm transition">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <?php if ($eventFilter > 0): ?>
                    <a href="manage_rsvps.php" class="text-sm text-gray-600 dark:text-gray-400 hover:text-psaGreen">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- RSVPs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-psaBlack text-white">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Event</th>
                            <th class="p-3 text-left">Full Name</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Phone</th>
                            <th class="p-3 text-center">Attendees</th>
                            <th class="p-3 text-left">RSVP Date</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rsvps->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $rsvps->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="p-3 text-gray-900 dark:text-white"><?= $i++ ?></td>
                                <td class="p-3 font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($row['event_title'] ?? 'Unknown Event') ?>
                                    <br><small class="text-gray-500"><?= date('M d, Y', strtotime($row['event_start'])) ?></small>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['full_name']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                                <td class="p-3 text-center">
                                    <span class="bg-psaGreen/10 text-psaGreen px-2 py-1 rounded-full text-xs font-medium">
                                        <?= (int)$row['attendees'] ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= date('M d, Y g:i A', strtotime($row['created_at'])) ?></td>
                                <td class="p-3 text-center">
                                    <a href="?delete=<?= $row['id'] ?><?= $eventFilter > 0 ? '&event_id=' . $eventFilter : '' ?>" onclick="return confirm('Delete this RSVP?')" class="text-red-600 dark:text-red-400 hover:underline" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    No RSVPs found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
