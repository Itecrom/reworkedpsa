<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$id = (int)$_GET['id'];

// Get member data before deleting
$member = $conn->query("SELECT * FROM psa_members WHERE id = $id")->fetch_assoc();
if ($member) {
    logAudit($conn, 'delete', 'psa_members', $id, ['name' => $member['name']], null);
}

$conn->query("DELETE FROM psa_members WHERE id = $id");
header("Location: manage_members.php?deleted=1");
exit;
