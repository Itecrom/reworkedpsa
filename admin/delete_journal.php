<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$id = (int)$_GET['id'];

// Get journal PDF file
$journal = $conn->query("SELECT file FROM journals WHERE id = $id")->fetch_assoc();

// Delete all articles' PDFs
$articles = $conn->query("SELECT file FROM journal_topics WHERE journal_id = $id");
while ($article = $articles->fetch_assoc()) {
    if ($article['file']) {
        @unlink("uploads/journals/" . $article['file']);
    }
}

// Delete journal PDF
if ($journal && $journal['file']) {
    @unlink("uploads/journals/" . $journal['file']);
}

// Delete analytics for this journal
$conn->query("DELETE FROM journal_analytics WHERE journal_id = $id");

// Delete all articles
$conn->query("DELETE FROM journal_topics WHERE journal_id = $id");

// Delete journal
$conn->query("DELETE FROM journals WHERE id = $id");

header("Location: manage_journals.php?deleted=1");
exit;
?>
