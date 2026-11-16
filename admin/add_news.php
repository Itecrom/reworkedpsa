<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';
include '../includes/audit_helper.php';

$page_title = 'Add News - PSA Admin';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $date_posted = $_POST['date_posted'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'published';
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $year = date('Y', strtotime($date_posted));
        $month = date('m', strtotime($date_posted));
        $uploadDir = "../uploads/news/$year/$month/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    // Handle PDF upload
    $pdf = '';
    if (!empty($_FILES['pdf']['name'])) {
        $year = date('Y', strtotime($date_posted));
        $month = date('m', strtotime($date_posted));
        $uploadDir = "../uploads/news/$year/$month/";
        $pdf = time() . '_' . basename($_FILES['pdf']['name']);
        move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadDir . $pdf);
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    $stmt = $conn->prepare("INSERT INTO news (title, slug, content, summary, image, author, date_posted, category, tags, status, featured, pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $title, $slug, $content, $summary, $image, $author, $date_posted, $category, $tags, $status, $featured, $pdf);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        logAudit($conn, 'create', 'news', $newId, null, ['title' => $title, 'status' => $status]);
        $success = "News added successfully!";
    } else {
        $error = "Failed to add news: " . $conn->error;
    }
}

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 ml-0 md:ml-64 min-h-screen">
    <!-- Top Header Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button id="menu-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Add News</h1>
                    <p class="text-sm text-gray-500">Create a new news article</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="manage_news.php" class="text-gray-600 hover:text-psaGreen transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </header>

    <div class="p-6">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Category</label>
                    <input type="text" name="category" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Author</label>
                    <input type="text" name="author" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Date Posted</label>
                    <input type="date" name="date_posted" value="<?= date('Y-m-d') ?>" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Status</label>
                    <select name="status" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Summary</label>
                    <textarea name="summary" rows="3" class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold text-gray-700">Content <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="8" required class="ckeditor w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"></textarea>
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">Featured Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>
                <div>
                    <label class="block mb-1 font-semibold text-gray-700">PDF Attachment</label>
                    <input type="file" name="pdf" accept="application/pdf" class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="featured" class="rounded">
                        <span class="font-semibold text-gray-700">Featured News</span>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="manage_news.php" class="text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition">Save News</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
