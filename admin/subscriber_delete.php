<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_subscribers.php");
    exit;
}

$id = (int)$_GET['id'];
$conn->query("DELETE FROM subscribers WHERE id = $id");
header("Location: manage_subscribers.php?deleted=1");
exit;
