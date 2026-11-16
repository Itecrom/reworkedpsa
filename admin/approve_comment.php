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
$conn->query("UPDATE comments SET status = 'approved' WHERE id = $id");
header("Location: manage_comments.php?approved=1");
exit;
