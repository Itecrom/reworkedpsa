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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PSA Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

  <style>
    .dark-mode {
      background-color: #1a1a1a;
      color: #f3f4f6;
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-100 font-sans" id="body">

  <!-- Sidebar -->
  <aside class="w-64 bg-psaBlack text-white flex flex-col px-4 py-6">
    <div class="text-center mb-10">
      <img src="images/logo.png" alt="PSA Logo" class="mx-auto mb-3 w-16">
      <h2 class="text-xl font-semibold">PSA Admin</h2>
    </div>

    <nav class="flex flex-col gap-4 text-sm">
      <a href="dashboard.php" class="bg-psaGreen py-2 px-3 rounded transition">Dashboard</a>

      <a href="manage_news.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage News
      </a>

      <a href="add_news.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Add News
      </a>

      <a href="manage_events.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage Events
      </a>

      <a href="add_event.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Add Event
      </a>

      <a href="manage_team.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage Team
      </a>

      <a href="manage_journals.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage Journals
      </a>

      <a href="manage_subscribers.php" class="hover:bg-psaGreen py-2 px-3 rounded transition flex items-center justify-between">
        Subscribers
        <?php $pendingSubs = countPending($conn, 'subscribers'); ?>
        <?php if ($pendingSubs > 0): ?>
          <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-2">
            <?php echo $pendingSubs; ?>
          </span>
        <?php endif; ?>
      </a>

      <a href="manage_comments.php" class="hover:bg-psaGreen py-2 px-3 rounded transition flex items-center justify-between">
        Comments
        <?php $pendingComments = countPending($conn, 'comments'); ?>
        <?php if ($pendingComments > 0): ?>
          <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-2">
            <?php echo $pendingComments; ?>
          </span>
        <?php endif; ?>
      </a>

      <a href="manage_users.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage Users
      </a>

      <a href="manage_members.php" class="hover:bg-psaGreen py-2 px-3 rounded transition">
        Manage Members
      </a>

      <a href="auth/logout.php" class="mt-4 bg-psaRed py-2 px-3 text-center rounded hover:bg-red-700 transition">
        Logout
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 p-6">

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">
        Welcome, <?= htmlspecialchars((string)($_SESSION['admin'] ?? 'Admin')) ?> 👋
      </h1>
      <button id="darkToggle" class="text-sm text-white bg-gray-700 px-3 py-1 rounded hover:bg-gray-900 transition">
        Toggle Dark Mode
      </button>
    </div>

    <!-- Notifications Alert -->
    <?php
    $pendingSubs = countPending($conn, 'subscribers');
    $pendingComments = countPending($conn, 'comments');
    $totalPending = $pendingSubs + $pendingComments;
    ?>

    <?php if ($totalPending > 0): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
      <div class="flex items-center">
        <div>
          <p class="font-bold">Pending Items Requiring Attention</p>
          <p class="text-sm">
            <?php if ($pendingSubs > 0): ?>
              <a href="manage_subscribers.php" class="underline hover:text-yellow-800">
                <?php echo $pendingSubs; ?> subscriber<?php echo $pendingSubs > 1 ? 's' : ''; ?> awaiting approval
              </a>
            <?php endif; ?>
            <?php if ($pendingSubs > 0 && $pendingComments > 0): ?> and <?php endif; ?>
            <?php if ($pendingComments > 0): ?>
              <a href="manage_comments.php" class="underline hover:text-yellow-800">
                <?php echo $pendingComments; ?> comment<?php echo $pendingComments > 1 ? 's' : ''; ?> awaiting approval
              </a>
            <?php endif; ?>
          </p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">News</h3>
        <p class="text-3xl font-bold text-psaRed">
          <?= countTable($conn, 'news') ?>
        </p>
      </div>

      <div class="bg-white shadow rounded p-6">
         <h3 class="text-gray-600">Events</h3>
         <p class="text-3xl font-bold text-purple-600">
           <?= countTable($conn, 'events') ?>
         </p>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">Team Members</h3>
        <p class="text-3xl font-bold text-psaGreen">
          <?= countTable($conn, 'team_members') ?>
        </p>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">Journals</h3>
        <p class="text-3xl font-bold text-yellow-500">
          <?= countTable($conn, 'journals') ?>
        </p>
        <p class="text-sm text-gray-500 mt-1">
          <?= $journalAnalytics['total_articles'] ?> articles |
          <?= number_format($journalAnalytics['total_views']) ?> views |
          <?= number_format($journalAnalytics['total_downloads']) ?> downloads
        </p>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">Subscribers</h3>
        <p class="text-3xl font-bold text-blue-600">
          <?= countTable($conn, 'subscribers') ?>
        </p>
        <?php $pendingSubs = countPending($conn, 'subscribers'); ?>
        <?php if ($pendingSubs > 0): ?>
          <p class="text-sm text-yellow-600">
            <?php echo $pendingSubs; ?> pending approval
          </p>
        <?php endif; ?>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">Comments</h3>
        <p class="text-3xl font-bold text-indigo-600">
          <?= countTable($conn, 'comments') ?>
        </p>
        <?php $pendingComments = countPending($conn, 'comments'); ?>
        <?php if ($pendingComments > 0): ?>
          <p class="text-sm text-yellow-600">
            <?php echo $pendingComments; ?> pending approval
          </p>
        <?php endif; ?>
      </div>

      <div class="bg-white shadow rounded p-6">
        <h3 class="text-gray-600">Members</h3>
        <p class="text-3xl font-bold text-gray-800">
          <?= countTable($conn, 'psa_members') ?>
        </p>
      </div>
    </div>

    <!-- Latest Member and News -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-2">Latest Added Member</h3>
        <p class="text-2xl font-bold text-psaGreen">
          <?= htmlspecialchars($latestMember['name'] ?? 'N/A') ?>
        </p>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-3">Recent News</h3>
        <ul class="list-disc ml-6 text-gray-700">
          <?php while ($row = $recentNews->fetch_assoc()): ?>
            <li><?= htmlspecialchars((string)($row['title'] ?? 'Untitled News')) ?></li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>

    <!-- Journal Analytics Summary -->
    <div class="bg-white p-6 rounded shadow mb-6">
      <h3 class="text-lg font-semibold mb-3">Journal Performance Overview</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-blue-50 p-4 rounded">
          <p class="text-sm text-gray-600">Total Journals</p>
          <p class="text-2xl font-bold text-blue-600"><?= $journalAnalytics['total_journals'] ?></p>
        </div>
        <div class="bg-green-50 p-4 rounded">
          <p class="text-sm text-gray-600">Total Articles</p>
          <p class="text-2xl font-bold text-green-600"><?= $journalAnalytics['total_articles'] ?></p>
        </div>
        <div class="bg-purple-50 p-4 rounded">
          <p class="text-sm text-gray-600">Total Views</p>
          <p class="text-2xl font-bold text-purple-600"><?= number_format($journalAnalytics['total_views']) ?></p>
        </div>
        <div class="bg-orange-50 p-4 rounded">
          <p class="text-sm text-gray-600">Total Downloads</p>
          <p class="text-2xl font-bold text-orange-600"><?= number_format($journalAnalytics['total_downloads']) ?></p>
        </div>
      </div>
      <a href="manage_journals.php" class="text-blue-600 hover:underline">View detailed journal analytics →</a>
    </div>

    <!-- Bar Chart -->
    <div class="bg-white p-6 rounded shadow">
      <h3 class="text-lg font-semibold mb-3">Content Overview</h3>
      <canvas id="barChart" height="120"></canvas>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-500">
      &copy; <?= date('Y') ?> PSA Admin Panel v2.0 — By <b>Leonard Mhone</b>
    </footer>

  </main>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    const ctx = document.getElementById('barChart').getContext('2d');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['News', 'Events', 'Team', 'Journals', 'Subscribers', 'Comments', 'Members'],
        datasets: [{
          label: 'Total Entries',
          data: [
            <?= countTable($conn, 'news') ?>,
            <?= countTable($conn, 'events') ?>,
            <?= countTable($conn, 'team_members') ?>,
            <?= countTable($conn, 'journals') ?>,
            <?= countTable($conn, 'subscribers') ?>,
            <?= countTable($conn, 'comments') ?>,
            <?= countTable($conn, 'psa_members') ?>
          ],
          backgroundColor: [
            '#cc0000',
            '#117733',
            '#facc15',
            '#3b82f6',
            '#6366f1',
            '#6b7280',
            'black'
          ],
          borderWidth: 1
        }]
      },

      options: {
        responsive: true,

        animation: {
          duration: 1000,
          easing: 'easeOutBounce'
        },

        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Persistent Dark Mode
    const body = document.getElementById('body');
    const toggleBtn = document.getElementById('darkToggle');

    if (localStorage.getItem('dark') === 'true') {
      body.classList.add('dark-mode');
    }

    toggleBtn.onclick = () => {
      body.classList.toggle('dark-mode');
      localStorage.setItem('dark', body.classList.contains('dark-mode'));
    };
  </script>

</body>
</html>
