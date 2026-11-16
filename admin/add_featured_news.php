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
include '../includes/image_helper.php';

$page_title = 'Add Featured News - PSA Admin';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($link_url)) {
        $error = 'Title and Link URL are required.';
    } else {
        $image = '';

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/featured_news/';
            $result = handleImageUpload($_FILES['image'], $uploadDir, 'featured_', 800, 600, 80);

            if ($result['success']) {
                $image = $result['filename'];
            } else {
                $error = 'Image upload failed: ' . $result['error'];
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO featured_news (title, link_url, image, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $title, $link_url, $image, $description, $sort_order, $is_active);
            $stmt->execute();
            $insertId = $conn->insert_id;
            $stmt->close();

            logAudit($conn, 'create', 'featured_news', $insertId, null, ['title' => $title, 'link_url' => $link_url]);

            header("Location: manage_featured_news.php?added=1");
            exit;
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
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Add Featured News</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add a new featured news item to the homepage</p>
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
        <?php if ($error): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Enter news title"
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="link_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Link URL <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="url"
                            id="link_url"
                            name="link_url"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="https://example.com/news-article"
                            value="<?= htmlspecialchars($_POST['link_url'] ?? '') ?>"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description (Optional)
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white resize-none"
                            placeholder="Brief description of the news..."
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Image (Optional)
                        </label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white"
                            onchange="previewImage(this)"
                        >
                        <img id="imagePreview" src="" alt="Preview" class="mt-3 hidden max-h-40 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max size: 2MB. Recommended: 800x600px. Will be auto-compressed.</p>
                    </div>

                    <div class="mb-4">
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sort Order
                        </label>
                        <input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            value="0"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-psaGreen focus:border-transparent dark:bg-gray-700 dark:text-white"
                        >
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lower numbers appear first. Use 0, 1, 2, 3, etc.</p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                checked
                                class="w-4 h-4 text-psaGreen border-gray-300 rounded focus:ring-psaGreen"
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Active (Show on homepage)
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-3 rounded-lg transition font-medium">
                            <i class="fas fa-save mr-2"></i> Save Featured News
                        </button>
                        <a href="manage_featured_news.php" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
