<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Manage Events - PSA Admin';

$events = $conn->query("SELECT * FROM events ORDER BY start_date DESC");

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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Manage Events</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View and manage all events</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="add_event.php" class="bg-psaGreen hover:bg-green-800 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Event
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-psaBlack text-white">
                        <tr>
                            <th class="p-3 text-left">#</th>
                            <th class="p-3 text-left">Title</th>
                            <th class="p-3 text-left">Venue</th>
                            <th class="p-3 text-left">Start Date</th>
                            <th class="p-3 text-left">End Date</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $events->fetch_assoc()):
                        ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="p-3 text-gray-900 dark:text-white"><?= $i++ ?></td>
                            <td class="p-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['venue'] ?? 'N/A') ?></td>
                            <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['start_date'] ?? 'N/A') ?></td>
                            <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['end_date'] ?? 'N/A') ?></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_event.php?id=<?= $row['id'] ?>" class="text-blue-600 dark:text-blue-400 hover:underline" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="manage_rsvps.php?event_id=<?= $row['id'] ?>" class="text-green-600 dark:text-green-400 hover:underline" title="View RSVPs">
                                    <i class="fas fa-clipboard-list"></i>
                                </a>
                                <a href="delete_event.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this event?')" class="text-red-600 dark:text-red-400 hover:underline" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
