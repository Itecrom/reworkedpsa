<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Clean inputs
$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validate
if (empty($name) || empty($email)) {
    header("Location: index.php?sub=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?sub=invalid");
    exit;
}

// Check duplicates
$stmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: index.php?sub=exists");
    exit;
}
$stmt->close();

// Insert subscriber
$stmt = $conn->prepare("INSERT INTO subscribers (name,email,status,approved,created_at) VALUES (?, ?, 'pending', 0, NOW())");
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$stmt->close();

// Notify admin
$adminEmail = "leonardjjmhone@gmail.com";
$adminSubject = "New PSA Subscriber";
$adminMessage = "New subscriber:\nName: $name\nEmail: $email\nStatus: Pending Approval";
$adminHeaders = "From: PSA Website <no-reply@psamalawi.org>\r\n";
mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

// Welcome email
$userSubject = "Welcome to PSA Malawi Newsletter";
$userMessage = "Hi $name,\n\nThank you for subscribing! Your subscription is pending admin approval.\n\nPSA Malawi Team";
$userHeaders = "From: PSA Malawi <no-reply@psamalawi.org>\r\n";
mail($email, $userSubject, $userMessage, $userHeaders);

// Redirect back
header("Location: index.php?sub=success");
exit;