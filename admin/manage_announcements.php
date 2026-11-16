<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Quick Announcements - PSA Admin';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $message = trim($_POST['message'] ?? '');
        $link_url = trim($_POST['link_url'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($message)) {
            // Check if record exists
            $check = $conn->query("SELECT id FROM quick_announcements LIMIT 1");
            if ($check->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE quick_announcements SET message = ?, link_url = ?, is_active = ? WHERE id = 1");
                $stmt->bind_param("ssi", $message, $link_url, $is_active);
            } else {
                $stmt = $conn->prepare("INSERT INTO quick_announcements (message, link_url, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $message, $link_url, $is_active);
            }
            $stmt->execute();
            $stmt->close();

            logAudit($conn, 'update', 'quick_announcements', 1, null, ['message' => $message, 'is_active' => $is_active]);

            $success = "Announcement updated successfully!";
        } else {
            $error = "Message cannot be empty.";
        }
    }
}

// Get current announcement
$announcement = $conn->query("SELECT * FROM quick_announcements LIMIT 1")->fetch_assoc();
if (!$announcement) {
    $announcement = ['message' => '', 'link_url' => '', 'is_active' => 1];
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Quick Announcements</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage the scrolling announcement in the top bar</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-gray-600 dark:text-gray-300 hover:text-psaGreen transition" title="View Site">
                    <i class="fas fa-external-link-alt text-lg"></i>
                </a>
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
                    <?php if (!empty($headerUserPic) && file_exists('../uploads/users/' . $headerUserPic)): ?>
                        <img src="../uploads/users/<?= htmlspecialchars($headerUserPic) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <img src="../images/logo.png" alt="Admin" class="w-8 h-8 rounded-full">
                    <?php endif; ?>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block"><?= htmlspecialchars($_SESSION['admin']) ?></span>
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if (isset($success)): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="max-w-3xl">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Quick Announcement Settings</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        This message will appear in the top bar of the website, scrolling between the social media icons and the logo.
                        The text will fade in and out at the edges for a smooth effect.
                    </p>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">

                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Announcement Message <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Enter your announcement message here..."
                            required
                        ><?= htmlspecialchars($announcement['message'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Keep it short and concise for best scrolling effect.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="link_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Link URL (Optional)
                        </label>
                        <input
                            type="url"
                            id="link_url"
                            name="link_url"
                            value="<?= htmlspecialchars($announcement['link_url'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="https://example.com"
                        >
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            If provided, the announcement will be clickable and link to this URL.
                        </p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                <?= ($announcement['is_active'] ?? 1) ? 'checked' : '' ?>
                                class="w-4 h-4 text-psaGreen border-gray-300 rounded focus:ring-psaGreen"
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Active (Show on website)
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-3 rounded-lg transition font-medium">
                            <i class="fas fa-save mr-2"></i> Save Announcement
                        </button>
                        <a href="dashboard.php" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Preview -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Preview</h3>
                <div class="bg-black rounded-lg p-3 overflow-hidden">
                    <div class="flex items-center">
                        <div class="flex-1 overflow-hidden relative">
                            <div class="ticker-preview whitespace-nowrap text-white text-sm">
                                <?php if (!empty($announcement['link_url'])): ?>
                                    <a href="<?= htmlspecialchars($announcement['link_url']) ?>" target="_blank" class="text-white hover:text-yellow-400">
                                        <?= htmlspecialchars($announcement['message'] ?? 'No announcement set') ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($announcement['message'] ?? 'No announcement set') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 ml-4 text-white">
                            <i class="ti-facebook"></i>
                            <i class="ti-twitter"></i>
                            <i class="ti-instagram"></i>
                            <i class="ti-linkedin"></i>
                            <i class="ti-youtube"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
