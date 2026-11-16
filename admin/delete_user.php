<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$id = (int)$_GET['id'];

// Prevent deleting self
if ($id === $_SESSION['admin']['id']) {
    header("Location: manage_users.php?error=1");
    exit;
}

$conn->query("DELETE FROM users WHERE id = $id");
header("Location: manage_users.php?deleted=1");
exit;
