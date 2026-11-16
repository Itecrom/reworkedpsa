<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/pdf_helper.php';
include '../includes/audit_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_journals.php");
    exit;
}

$id = (int)$_GET['id'];

// Get journal data before deleting
$journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
if ($journal) {
    if ($journal['file']) {
        $uploadDir = dirname(__DIR__) . '/uploads/journals/';
        @unlink($uploadDir . $journal['file']);
    }
    logAudit($conn, 'delete', 'journals', $id, ['title' => $journal['title']], null);
}

// Delete articles first (foreign key constraint)
$conn->query("DELETE FROM journal_topics WHERE journal_id = $id");

// Delete journal
$conn->query("DELETE FROM journals WHERE id = $id");

header("Location: manage_journals.php?deleted=1");
exit;
