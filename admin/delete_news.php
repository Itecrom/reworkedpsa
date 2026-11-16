<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_news.php");
    exit;
}

$id = (int)$_GET['id'];

// Get news data before deleting
$news = $conn->query("SELECT * FROM news WHERE id = $id")->fetch_assoc();
if ($news) {
    $year = date('Y', strtotime($news['date_posted']));
    $month = date('m', strtotime($news['date_posted']));
    $uploadDir = "../uploads/news/$year/$month/";
    if ($news['image']) @unlink($uploadDir . $news['image']);
    if ($news['pdf']) @unlink($uploadDir . $news['pdf']);
    logAudit($conn, 'delete', 'news', $id, ['title' => $news['title']], null);
}

$conn->query("DELETE FROM news WHERE id = $id");
header("Location: manage_news.php?deleted=1");
exit;
