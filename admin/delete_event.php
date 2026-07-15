<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';

$id = intval($_GET['id']);

/* DELETE GALLERY FIRST */

$gstmt = $conn->prepare("
    DELETE FROM event_gallery
    WHERE event_id=?
");

$gstmt->bind_param("i", $id);

$gstmt->execute();

/* DELETE EVENT */

$stmt = $conn->prepare("
    DELETE FROM events
    WHERE id=?
");

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: manage_events.php");

} else {

    echo "Failed to delete event.";
}
?>
