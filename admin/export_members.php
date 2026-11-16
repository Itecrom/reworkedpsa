<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}
include '../includes/db.php';

$result = $conn->query("SELECT * FROM members ORDER BY id DESC");
$filename = "members_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Institution', 'Position', 'Joined Date']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['institution'],
        $row['position'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
