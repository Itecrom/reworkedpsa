<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Anti-spam (honeypot)
if (!empty($_POST['website'])) {
    echo json_encode(["status" => "spam"]);
    exit;
}

$news_id = (int)$_POST['news_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$comment = trim($_POST['comment']);
$parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// Basic validation
if (!$name || !$email || !$comment) {
    echo json_encode(["status" => "error"]);
    exit;
}

// Insert comment
$stmt = $conn->prepare("
    INSERT INTO comments (news_id, name, email, comment, parent_id, approved)
    VALUES (?, ?, ?, ?, ?, 0)
");
$stmt->bind_param("isssi", $news_id, $name, $email, $comment, $parent_id);
$stmt->execute();

/* ================= ADMIN NOTIFICATION ================= */
$to = "youradminemail@example.com";
$subject = "New Comment on News Article";
$message = "Name: $name\nEmail: $email\nComment: $comment";
mail($to, $subject, $message);
/* ====================================================== */

echo json_encode([
    "status" => "success",
    "name" => $name,
    "comment" => $comment,
    "parent_id" => $parent_id
]);
