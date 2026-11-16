<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';
include '../includes/audit_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_team.php");
    exit;
}

$id = (int)$_GET['id'];

// Get team member data before deleting
$member = $conn->query("SELECT * FROM team_members WHERE id = $id")->fetch_assoc();
if ($member) {
    if ($member['image']) {
        $uploadDir = '../uploads/team/';
        @unlink($uploadDir . $member['image']);
    }
    logAudit($conn, 'delete', 'team_members', $id, ['name' => $member['name']], null);
}

$conn->query("DELETE FROM team_members WHERE id = $id");
header("Location: manage_team.php?deleted=1");
exit;
