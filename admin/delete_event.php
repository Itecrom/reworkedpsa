<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_events.php");
    exit;
}

$id = (int)$_GET['id'];

// Get event data before deleting
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
if ($event) {
    if ($event['image']) {
        $year = date('Y', strtotime($event['start_date']));
        $month = date('m', strtotime($event['start_date']));
        $uploadDir = "../uploads/events/$year/$month/";
        @unlink($uploadDir . $event['image']);
    }
    logAudit($conn, 'delete', 'events', $id, ['title' => $event['title']], null);
}

$conn->query("DELETE FROM events WHERE id = $id");
header("Location: manage_events.php?deleted=1");
exit;
