<?php
include '../includes/db.php';
include '../includes/pdf_helper.php';

if (!isset($_GET['id'])) {
  die("No topic specified.");
}

$topicId = (int)$_GET['id'];

// Fetch file with journal_id
$stmt = $conn->prepare("SELECT file, journal_id FROM journal_topics WHERE id = ?");
$stmt->bind_param("i", $topicId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res || !file_exists("uploads/journals/" . $res['file'])) {
  die("File not found.");
}

$journalId = $res['journal_id'] ?? null;
$filename = $res['file'];

// Track analytics
trackAnalytics($conn, $journalId, $topicId, 'download');
incrementDownloads($conn, 'article', $topicId);

// Redirect to the file
header("Location: uploads/journals/" . urlencode($filename));
exit;
?>
