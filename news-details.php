<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/header.php';

echo '<style>
.news-content ul {
  list-style-type: disc;
  padding-left: 20px;
}
.news-content ol {
  list-style-type: decimal;
  padding-left: 20px;
}
.pdf-viewer {
  width: 100%;
  height: 600px;
  border: 1px solid #ddd;
  margin-top: 15px;
}
</style>';


error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    die("No news article specified.");
}

$slug = $_GET['slug'];

$stmt = $conn->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("News article not found.");
}

$row = $result->fetch_assoc();

/* DOWNLOAD COUNTER */
if (isset($_GET['download']) && $_GET['download'] === 'pdf' && !empty($row['pdf'])) {
    $conn->query("UPDATE news SET downloads = downloads + 1 WHERE id = {$row['id']}");
}

/* ================= SAFE COMMENT HANDLING ================= */
$errors = [];

/* COMMENT SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {

    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $comment = trim($_POST['comment']);
    $honeypot = $_POST['website'] ?? '';

    if (!empty($honeypot)) {
        die("Spam detected");
    }

    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($comment)) $errors[] = "Comment cannot be empty.";

    if (empty($errors)) {

        $stmt = $conn->prepare("INSERT INTO comments (news_id, name, email, comment, approved) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("isss", $row['id'], $name, $email, $comment);
        $stmt->execute();

        $_SESSION['comment_success'] = "✅ Comment submitted successfully.";
        header("Location: " . $_SERVER['REQUEST_URI'] . "#comments");
        exit();
    }
}

/* ================= ALWAYS DEFINE VARIABLES (FIX) ================= */

// SAFE DEFAULTS (THIS FIXES YOUR ERROR)
$total_comments = 0;
$comments = null;

/* FETCH COMMENTS SAFELY */
$comments_per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $comments_per_page;

$total_comments_result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM comments 
    WHERE news_id = {$row['id']} AND approved = 1
");

if ($total_comments_result) {
    $total_comments = $total_comments_result->fetch_assoc()['total'];
}

$stmt_comments = $conn->prepare("
    SELECT * FROM comments 
    WHERE news_id = ? AND approved = 1 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");

$stmt_comments->bind_param("iii", $row['id'], $comments_per_page, $offset);
$stmt_comments->execute();
$comments = $stmt_comments->get_result();

/* ================= DATA ================= */
$title   = htmlspecialchars($row['title']);
$author  = !empty($row['author']) ? htmlspecialchars($row['author']) : 'Admin';
$tags    = $row['tags'];
$content = $row['content'];
$date    = date('d M Y', strtotime($row['date_posted'] ?? $row['created_at']));

$imagePath = !empty($row['image']) ? $row['image'] : 'images/events/blogsingle.jpg';
$image = htmlspecialchars($imagePath);

$fullUrl = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$pdfUrl = !empty($row['pdf']) ? '/' . ltrim($row['pdf'], '/') : '';
?>

<!-- Title Banner -->
<section class="page-title overlay" style="background-image: url(/images/background/page-title-2.jpg);">
  <div class="container text-center">
    <h2 class="text-white font-weight-bold"><?= $title ?></h2>
    <h4 class="text-white">Tags: <?= htmlspecialchars($tags) ?></h4>
  </div>
</section>

<!-- Article + Comments -->
<section>
  <div class="container">
    <div class="row">

      <div class="col-lg-8 py-100">
        <article class="bg-white border rounded">

          <img src="/<?= ltrim($image, '/') ?>" class="img-fluid w-100 rounded-top" />

          <div class="p-4">

            <ul class="list-inline mb-3 border-bottom pb-3">
              <li class="list-inline-item">Posted by <strong><?= $author ?></strong></li>
              <li class="list-inline-item">On <?= $date ?></li>
            </ul>

            <!-- ✅ CONTENT -->
            <div class="mb-4 news-content"><?= $content ?></div>

            <!-- ✅ PDF SECTION -->
            <?php if (!empty($pdfUrl)): ?>
              <div class="mb-3">
                <a href="<?= $pdfUrl ?>?download=pdf" class="btn btn-danger">📄 Download PDF</a>
              </div>

              <iframe class="pdf-viewer"
                src="https://docs.google.com/gview?url=<?= urlencode("https://{$_SERVER['HTTP_HOST']}" . $pdfUrl) ?>&embedded=true">
              </iframe>
            <?php endif; ?>

            <!-- Share -->
            <div class="mb-4">
              <strong>Share:</strong>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullUrl) ?>" target="_blank">Facebook</a> |
              <a href="https://api.whatsapp.com/send?text=<?= urlencode($title . ' - ' . $fullUrl) ?>" target="_blank">WhatsApp</a>
            </div>

<!-- COMMENTS (UNCHANGED STRUCTURE) -->
<?php if (isset($_SESSION['comment_success'])): ?>
<div class="alert alert-success"><?= $_SESSION['comment_success'] ?></div>
<?php unset($_SESSION['comment_success']); ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
<ul>
<?php foreach ($errors as $error): ?>
<li><?= htmlspecialchars($error) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<!-- COMMENT LIST -->
<section id="comments" class="mt-5">
<h4>Comments (<?= $total_comments ?>)</h4>

<?php if ($comments->num_rows > 0): ?>
<?php while ($c = $comments->fetch_assoc()): ?>
<div class="border-bottom mb-3 pb-2">
<strong><?= htmlspecialchars($c['name']) ?></strong><br>
<?= nl2br(htmlspecialchars($c['comment'])) ?>
</div>
<?php endwhile; ?>
<?php else: ?>
<p>No comments yet.</p>
<?php endif; ?>

</section>

<!-- COMMENT FORM (RESTORED WORKING) -->
<div class="mt-5">
<h4>Leave a Comment</h4>

<form method="POST">
<input type="hidden" name="comment_submit" value="1">

<input type="text" name="name" class="form-control mb-2" placeholder="Your Name" required>

<input type="email" name="email" class="form-control mb-2" placeholder="Your Email" required>

<textarea name="comment" class="form-control mb-2" placeholder="Your Comment" required></textarea>

<!-- honeypot -->
<input type="text" name="website" style="display:none">

<button type="submit" class="btn btn-primary">Submit Comment</button>
</form>
</div>

</div>
</article>
</div>

<!-- SIDEBAR (UNCHANGED) -->
<aside class="col-lg-4 py-100">
<div class="bg-white sidebar-box-shadow px-4 py-4">

<div class="mb-4">
<h4>Search</h4>
<form method="get" action="news.php">
<input type="text" name="search" class="form-control" placeholder="Search..." />
</form>
</div>

<div class="mb-4">
<h4>Related News</h4>
<ul class="list-unstyled">
<?php
$stmtRelated = $conn->prepare("SELECT title, slug FROM news WHERE id != ? ORDER BY RAND() LIMIT 3");
$stmtRelated->bind_param("i", $row['id']);
$stmtRelated->execute();
$related = $stmtRelated->get_result();

while ($r = $related->fetch_assoc()) {
echo '<li><a href="news-details.php?slug=' . htmlspecialchars($r['slug']) . '">' . htmlspecialchars($r['title']) . '</a></li>';
}
?>
</ul>
</div>

</div>
</aside>

</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>