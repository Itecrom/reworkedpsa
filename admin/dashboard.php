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
include '../includes/audit_helper.php';

$page_title = 'Dashboard - PSA Admin';

function countTable($conn, $table) {
  return $conn->query("SELECT id FROM $table")->num_rows;
}

function countPending($conn, $table, $column = 'approved') {
  return $conn->query("SELECT id FROM $table WHERE $column = 0")->num_rows;
}

$latestMember = $conn->query("SELECT name FROM psa_members ORDER BY id DESC LIMIT 1")->fetch_assoc();
$recentNews = $conn->query("SELECT title FROM news ORDER BY id DESC LIMIT 5");

// Get journal analytics
$journalAnalytics = getOverallAnalytics($conn, 30);

// Pagination for Recent Activity
$activityPage = isset($_GET['activity_page']) && is_numeric($_GET['activity_page']) ? (int) $_GET['activity_page'] : 1;
$activityLimit = 5;
$activityOffset = ($activityPage - 1) * $activityLimit;
$totalActivity = $conn->query("SELECT COUNT(*) as total FROM audit_log")->fetch_assoc()['total'] ?? 0;
$totalActivityPages = ceil($totalActivity / $activityLimit);
$recentActivity = getAuditLogs($conn, [], $activityLimit, $activityOffset);

// Pagination for Top Performing Articles
$articlesPage = isset($_GET['articles_page']) && is_numeric($_GET['articles_page']) ? (int) $_GET['articles_page'] : 1;
$articlesLimit = 5;
$articlesOffset = ($articlesPage - 1) * $articlesLimit;
$totalArticles = $conn->query("SELECT COUNT(*) as total FROM journal_topics")->fetch_assoc()['total'] ?? 0;
$totalArticlesPages = ceil($totalArticles / $articlesLimit);
$topArticles = $conn->query("
    SELECT jt.topic as title, jt.views, jt.downloads, j.title as journal_title
    FROM journal_topics jt
    LEFT JOIN journals j ON jt.journal_id = j.id
    ORDER BY (jt.views + jt.downloads) DESC
    LIMIT $articlesLimit OFFSET $articlesOffset
");

// Get views/downloads data for chart (last 7 days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartData[] = [
        'date' => date('M j', strtotime($date)),
        'views' => 0,
        'downloads' => 0
    ];
}

$viewsResult = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM journal_analytics
    WHERE action = 'view' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
");
while ($row = $viewsResult->fetch_assoc()) {
    foreach ($chartData as &$day) {
        if ($day['date'] === date('M j', strtotime($row['date']))) {
            $day['views'] = (int)$row['count'];
            break;
        }
    }
}

$downloadsResult = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM journal_analytics
    WHERE action = 'download' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
");
while ($row = $downloadsResult->fetch_assoc()) {
    foreach ($chartData as &$day) {
        if ($day['date'] === date('M j', strtotime($row['date']))) {
            $day['downloads'] = (int)$row['count'];
            break;
        }
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back, <?= htmlspecialchars((string)($_SESSION['admin'] ?? 'Admin')) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition" title="View Site">
                    <i class="fas fa-external-link-alt text-lg"></i>
                </a>
                <!-- Day/Night Toggle -->
                <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition" title="Toggle theme">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block"></i>
                </button>
                <?php
                $headerUserPic = '';
                if (isset($_SESSION['admin'])) {
                    $userResult = $conn->query("SELECT profile_pic FROM admins WHERE username = '" . $conn->real_escape_string($_SESSION['admin']) . "'");
                    if ($userResult) {
                        $userData = $userResult->fetch_assoc();
                        $headerUserPic = $userData['profile_pic'] ?? '';
                    }
                }
                ?>
                <a href="profile.php" class="flex items-center gap-2 hover:opacity-80 transition">
                    <?php if (!empty($headerUserPic) && file_exists(__DIR__ . '/../uploads/users/' . basename($headerUserPic))): ?>
                        <img src="../uploads/users/<?= rawurlencode(basename($headerUserPic)) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <img src="../images/logo.png" alt="Admin" class="w-8 h-8 rounded-full">
                    <?php endif; ?>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block"><?= htmlspecialchars($_SESSION['admin']) ?></span>
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <!-- Pending Items Alert -->
        <?php
        $pendingSubs = countPending($conn, 'subscribers');
        $pendingComments = countPending($conn, 'comments');
        $totalPending = $pendingSubs + $pendingComments;
        ?>

        <?php if ($totalPending > 0): ?>
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-300 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="font-semibold">Pending Items Requiring Attention</p>
                    <p class="text-sm mt-1">
                        <?php if ($pendingSubs > 0): ?>
                            <a href="manage_subscribers.php" class="underline hover:text-yellow-900 dark:hover:text-yellow-100 font-medium">
                                <?= $pendingSubs ?> subscriber<?= $pendingSubs > 1 ? 's' : '' ?> awaiting approval
                            </a>
                        <?php endif; ?>
                        <?php if ($pendingSubs > 0 && $pendingComments > 0): ?> and <?php endif; ?>
                        <?php if ($pendingComments > 0): ?>
                            <a href="manage_comments.php" class="underline hover:text-yellow-900 dark:hover:text-yellow-100 font-medium">
                                <?= $pendingComments ?> comment<?= $pendingComments > 1 ? 's' : '' ?> awaiting approval
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Grid - Single Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3 mb-8">
            <!-- News -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-newspaper text-red-600 dark:text-red-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">News</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'news') ?></p>
                    </div>
                </div>
                <a href="manage_news.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Events -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-alt text-green-600 dark:text-green-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Events</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'events') ?></p>
                    </div>
                </div>
                <a href="manage_events.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Journals -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-book text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Journals</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'journals') ?></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500"><?= $journalAnalytics['total_articles'] ?> articles</p>
                    </div>
                </div>
                <a href="manage_journals.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Members -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users text-purple-600 dark:text-purple-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Members</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'psa_members') ?></p>
                    </div>
                </div>
                <a href="manage_members.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Team -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-tie text-yellow-600 dark:text-yellow-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Team</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'team_members') ?></p>
                    </div>
                </div>
                <a href="manage_team.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Subscribers -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-envelope text-indigo-600 dark:text-indigo-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Subscribers</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'subscribers') ?></p>
                        <?php if ($pendingSubs > 0): ?>
                        <p class="text-xs text-red-600 dark:text-red-400"><?= $pendingSubs ?> pending</p>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="manage_subscribers.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Comments -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-pink-100 dark:bg-pink-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-comments text-pink-600 dark:text-pink-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Comments</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'comments') ?></p>
                        <?php if ($pendingComments > 0): ?>
                        <p class="text-xs text-red-600 dark:text-red-400"><?= $pendingComments ?> pending</p>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="manage_comments.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>

            <!-- Users -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users-cog text-gray-600 dark:text-gray-400 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Admins</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight"><?= countTable($conn, 'admins') ?></p>
                    </div>
                </div>
                <a href="manage_users.php" class="text-xs text-psaGreen hover:text-green-800 font-medium mt-2 block">Manage →</a>
            </div>
        </div>

        <!-- Charts & Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Views & Downloads Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Views & Downloads (Last 7 Days)</h3>
                    <a href="journal_analytics.php" class="text-sm text-psaGreen hover:text-green-800 font-medium">View All →</a>
                </div>
                <div class="relative h-72">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>

            <!-- Journal Performance -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Journal Stats</h3>
                    <a href="manage_journals.php" class="text-sm text-psaGreen hover:text-green-800 font-medium">View All →</a>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Journals</p>
                                <p class="text-xl font-bold text-blue-600 dark:text-blue-400"><?= $journalAnalytics['total_journals'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Articles</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400"><?= $journalAnalytics['total_articles'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-eye text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Views</p>
                                <p class="text-xl font-bold text-purple-600 dark:text-purple-400"><?= number_format($journalAnalytics['total_views']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-download text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Downloads</p>
                                <p class="text-xl font-bold text-orange-600 dark:text-orange-400"><?= number_format($journalAnalytics['total_downloads']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Top Articles -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
                    <a href="audit_log.php" class="text-sm text-psaGreen hover:text-green-800 font-medium">View All →</a>
                </div>
                <div class="space-y-4">
                    <?php if (count($recentActivity) > 0): ?>
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="flex items-start gap-3 pb-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-psaGreen/10 dark:bg-psaGreen/20 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-<?= getActivityIcon($activity['action']) ?> text-psaGreen text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                    <span class="font-medium"><?= htmlspecialchars($activity['username'] ?? 'System') ?></span>
                                    <?= htmlspecialchars($activity['action']) ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    <?= htmlspecialchars($activity['table_name'] ?? '') ?> - <?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No recent activity</p>
                    <?php endif; ?>
                </div>
                <?php if ($totalActivityPages > 1): ?>
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <?php if ($activityPage > 1): ?>
                        <a href="?activity_page=<?= $activityPage - 1 ?>" class="text-sm text-psaGreen hover:text-green-800 font-medium">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Page <?= $activityPage ?> of <?= $totalActivityPages ?></span>
                    <?php if ($activityPage < $totalActivityPages): ?>
                        <a href="?activity_page=<?= $activityPage + 1 ?>" class="text-sm text-psaGreen hover:text-green-800 font-medium">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Top Performing Articles -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Performing Articles</h3>
                    <a href="journal_analytics.php" class="text-sm text-psaGreen hover:text-green-800 font-medium">View All →</a>
                </div>
                <div class="space-y-4">
                    <?php if ($topArticles->num_rows > 0): ?>
                        <?php $rank = ($articlesPage - 1) * $articlesLimit + 1; while ($article = $topArticles->fetch_assoc()): ?>
                        <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="w-8 h-8 rounded-full bg-psaGreen text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                                <?= $rank++ ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate"><?= htmlspecialchars($article['title'] ?? 'Untitled') ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($article['journal_title'] ?? 'No Journal') ?></p>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1 text-purple-600 dark:text-purple-400">
                                    <i class="fas fa-eye"></i> <?= number_format($article['views']) ?>
                                </span>
                                <span class="flex items-center gap-1 text-orange-600 dark:text-orange-400">
                                    <i class="fas fa-download"></i> <?= number_format($article['downloads']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No articles yet</p>
                    <?php endif; ?>
                </div>
                <?php if ($totalArticlesPages > 1): ?>
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <?php if ($articlesPage > 1): ?>
                        <a href="?articles_page=<?= $articlesPage - 1 ?>" class="text-sm text-psaGreen hover:text-green-800 font-medium">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Page <?= $articlesPage ?> of <?= $totalArticlesPages ?></span>
                    <?php if ($articlesPage < $totalArticlesPages): ?>
                        <a href="?articles_page=<?= $articlesPage + 1 ?>" class="text-sm text-psaGreen hover:text-green-800 font-medium">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="add_news.php" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-psaGreen hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-red-600 dark:text-red-400"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add News</span>
                </a>
                <a href="add_event.php" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-psaGreen hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-plus text-green-600 dark:text-green-400"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Event</span>
                </a>
                <a href="add_journal.php" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-psaGreen hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Journal</span>
                </a>
                <a href="add_team_member.php" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-psaGreen hover:bg-green-50 dark:hover:bg-green-900/20 transition">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-plus text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Team Member</span>
                </a>
            </div>
        </div>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Views & Downloads Chart
    const ctx = document.getElementById('viewsChart').getContext('2d');
    const chartData = <?= json_encode($chartData) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [
                {
                    label: 'Views',
                    data: chartData.map(d => d.views),
                    borderColor: '#117733',
                    backgroundColor: 'rgba(17, 119, 51, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#117733'
                },
                {
                    label: 'Downloads',
                    data: chartData.map(d => d.downloads),
                    borderColor: '#cc0000',
                    backgroundColor: 'rgba(204, 0, 0, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#cc0000'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#6b7280'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        color: '#6b7280'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>
