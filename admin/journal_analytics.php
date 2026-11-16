<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/pdf_helper.php';

$page_title = 'Journal Analytics - PSA Admin';

$journalId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$journal = null;
if ($journalId) {
    $journal = $conn->query("SELECT * FROM journals WHERE id = $journalId")->fetch_assoc();
    if (!$journal) {
        die("Journal not found.");
    }
}

// Get analytics data
if ($journalId) {
    $analytics = getJournalAnalytics($conn, $journalId);
} else {
    $analytics = getOverallAnalytics($conn, 30);
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Journal Analytics</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?= $journal ? htmlspecialchars($journal['title']) : 'Overall Analytics' ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_journals.php" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Views</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($analytics['total_views']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Downloads</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format($analytics['total_downloads']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-download text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Articles</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $analytics['article_count'] ?? $analytics['total_articles'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Engagement Rate</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $analytics['total_views'] > 0 ? round(($analytics['total_downloads'] / $analytics['total_views']) * 100, 1) : 0 ?>%</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Views & Downloads (Last 30 Days)</h3>
            <canvas id="analyticsChart" height="100"></canvas>
        </div>

        <!-- Top Articles -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Top Performing Articles</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="p-3 text-left text-gray-700 dark:text-gray-300">Article Title</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-300">Views</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-300">Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics['top_articles'] as $article): ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="p-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($article['topic']) ?></td>
                            <td class="p-3 text-center text-gray-700 dark:text-gray-300"><?= number_format($article['views']) ?></td>
                            <td class="p-3 text-center text-gray-700 dark:text-gray-300"><?= number_format($article['downloads']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($analytics['chart_data']['labels'])): ?>
const ctx = document.getElementById('analyticsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($analytics['chart_data']['labels']) ?>,
        datasets: [
            {
                label: 'Views',
                data: <?= json_encode($analytics['chart_data']['views']) ?>,
                borderColor: '#117733',
                backgroundColor: 'rgba(17, 119, 51, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Downloads',
                data: <?= json_encode($analytics['chart_data']['downloads']) ?>,
                borderColor: '#cc0000',
                backgroundColor: 'rgba(204, 0, 0, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#374151'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#374151'
                },
                grid: {
                    color: '#e5e7eb'
                }
            },
            x: {
                ticks: {
                    color: '#374151'
                },
                grid: {
                    color: '#e5e7eb'
                }
            }
        }
    }
});
<?php endif; ?>
</script>
