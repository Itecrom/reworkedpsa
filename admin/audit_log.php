<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Audit Log - PSA Admin';

// Filters
$filters = [];
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $filters['user_id'] = (int)$_GET['user_id'];
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $filters['action'] = trim($_GET['action']);
}
if (isset($_GET['table_name']) && !empty($_GET['table_name'])) {
    $filters['table_name'] = trim($_GET['table_name']);
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $filters['date_from'] = trim($_GET['date_from']);
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $filters['date_to'] = trim($_GET['date_to']);
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$logs = getAuditLogs($conn, $filters, $limit, $offset);
$totalLogs = getAuditLogCount($conn, $filters);
$totalPages = ceil($totalLogs / $limit);

// Get unique actions and tables for filters
$actions = $conn->query("SELECT DISTINCT action FROM audit_log ORDER BY action")->fetch_all(MYSQLI_ASSOC);
$tables = $conn->query("SELECT DISTINCT table_name FROM audit_log ORDER BY table_name")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, username FROM admins ORDER BY username")->fetch_all(MYSQLI_ASSOC);

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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Audit Log</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track all system changes</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Filter Logs</h3>
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">User</label>
                    <select name="user_id" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="">All Users</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Action</label>
                    <select name="action" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $a): ?>
                            <option value="<?= htmlspecialchars($a['action']) ?>" <?= ($filters['action'] ?? '') === $a['action'] ? 'selected' : '' ?>>
                                <?= ucfirst(htmlspecialchars($a['action'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Table</label>
                    <select name="table_name" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="">All Tables</option>
                        <?php foreach ($tables as $t): ?>
                            <option value="<?= htmlspecialchars($t['table_name']) ?>" <?= ($filters['table_name'] ?? '') === $t['table_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['table_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">From Date</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">To Date</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div class="lg:col-span-5 flex justify-end gap-4">
                    <a href="audit_log.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Clear Filters</a>
                    <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-psaBlack text-white">
                        <tr>
                            <th class="p-3 text-left">Date/Time</th>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">Action</th>
                            <th class="p-3 text-left">Table</th>
                            <th class="p-3 text-left">Record ID</th>
                            <th class="p-3 text-left">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">No audit logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="p-3 text-gray-900 dark:text-white"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        <?= $log['action'] === 'create' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' ?>
                                        <?= $log['action'] === 'update' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' ?>
                                        <?= $log['action'] === 'delete' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' ?>
                                        <?= !in_array($log['action'], ['create', 'update', 'delete']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' ?>
                                    ">
                                        <?= ucfirst(htmlspecialchars($log['action'])) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($log['table_name'] ?? 'N/A') ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= $log['record_id'] ?? 'N/A' ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex justify-center space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-psaGreen hover:bg-gray-50 dark:hover:bg-gray-700 transition">Prev</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?= $p ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" class="px-3 py-1 rounded border <?= $p == $page ? 'bg-psaGreen text-white border-psaGreen' : 'border-gray-300 dark:border-gray-600 text-psaGreen hover:bg-gray-50 dark:hover:bg-gray-700' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-psaGreen hover:bg-gray-50 dark:hover:bg-gray-700 transition">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
