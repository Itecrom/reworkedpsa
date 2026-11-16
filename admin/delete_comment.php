<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_comments.php");
    exit;
}

$id = (int)$_GET['id'];
$conn->query("DELETE FROM comments WHERE id = $id");
header("Location: manage_comments.php?deleted=1");
exit;
