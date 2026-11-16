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

// Ensure the subscribers table (and its columns) exist before we query/insert.
include 'includes/ensure_subscribers.php';
ensureSubscribersTable($conn);

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

// Insert subscriber (schema-aware so a missing column can't silently drop the row)
$colsRes = $conn->query("SHOW COLUMNS FROM subscribers");
$existing = $colsRes ? array_column($colsRes->fetch_all(MYSQLI_ASSOC), 'Field') : [];

$fields = [];
$placeholders = [];
$types = '';
$values = [];

if (in_array('name', $existing))       { $fields[] = 'name';       $placeholders[] = '?'; $types .= 's'; $values[] = $name; }
if (in_array('email', $existing))      { $fields[] = 'email';      $placeholders[] = '?'; $types .= 's'; $values[] = $email; }
if (in_array('status', $existing))     { $fields[] = 'status';     $placeholders[] = '?'; $types .= 's'; $values[] = 'pending'; }
if (in_array('approved', $existing))   { $fields[] = 'approved';   $placeholders[] = '?'; $types .= 'i'; $values[] = 0; }
if (in_array('created_at', $existing)) { $fields[] = 'created_at'; $placeholders[] = '?'; $types .= 's'; $values[] = date('Y-m-d H:i:s'); }

if (empty($fields)) {
    error_log('Subscribe failed: subscribers table has no usable columns');
    header("Location: index.php?sub=error");
    exit;
}

$sql = "INSERT INTO subscribers (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Subscribe prepare failed: ' . $conn->error);
    header("Location: index.php?sub=error");
    exit;
}
$stmt->bind_param($types, ...$values);
if (!$stmt->execute()) {
    error_log('Subscribe insert failed: ' . $stmt->error);
    header("Location: index.php?sub=error");
    exit;
}
$stmt->close();

// Notify admin
$adminEmail = "giftlopoti@gmail.com";
$adminSubject = "New PSA Subscriber";
$adminMessage = "New subscriber:\nName: $name\nEmail: $email\nStatus: Pending Approval";
$adminHeaders = "From: PSA Website <no-reply@psamalawi.org>\r\n";
@mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

// Welcome email
$userSubject = "Welcome to PSA Malawi Newsletter";
$userMessage = "Hi $name,\n\nThank you for subscribing! Your subscription is pending admin approval.\n\nPSA Malawi Team";
$userHeaders = "From: PSA Malawi <no-reply@psamalawi.org>\r\n";
@mail($email, $userSubject, $userMessage, $userHeaders);

// Redirect back
header("Location: index.php?sub=success");
exit;