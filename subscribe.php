<?php
include 'includes/db.php';
require_once 'includes/public_form_security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$formError = validatePublicForm('newsletter');
if ($formError !== '') {
    header('Location: index.php?sub=' . ($formError === 'rate' ? 'rate' : 'invalid'), true, 303);
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

if ($name === '' || $email === '') {
    header('Location: index.php?sub=empty', true, 303);
    exit;
}

if (mb_strlen($name) > 255 || mb_strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?sub=invalid', true, 303);
    exit;
}

// Ensure the subscribers table (and its columns) exist before we query/insert.
include 'includes/ensure_subscribers.php';
ensureSubscribersTable($conn);

$email = mb_strtolower($email);
// Normalize before checking so differently-cased submissions match existing records.
$stmt = $conn->prepare("SELECT id FROM subscribers WHERE LOWER(email) = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: index.php?sub=exists", true, 303);
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

if (in_array('name', $existing, true))       { $fields[] = 'name';       $placeholders[] = '?'; $types .= 's'; $values[] = $name; }
if (in_array('email', $existing, true))      { $fields[] = 'email';      $placeholders[] = '?'; $types .= 's'; $values[] = $email; }
if (in_array('status', $existing, true))     { $fields[] = 'status';     $placeholders[] = '?'; $types .= 's'; $values[] = 'pending'; }
if (in_array('approved', $existing, true))   { $fields[] = 'approved';   $placeholders[] = '?'; $types .= 'i'; $values[] = 0; }
if (in_array('created_at', $existing, true)) { $fields[] = 'created_at'; $placeholders[] = '?'; $types .= 's'; $values[] = date('Y-m-d H:i:s'); }

if (!in_array('email', $existing, true) || !in_array('name', $existing, true)) {
    error_log('Subscribe failed: subscribers table has no usable columns');
    header("Location: index.php?sub=error", true, 303);
    exit;
}

$sql = "INSERT INTO subscribers (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Subscribe prepare failed: ' . $conn->error);
    header("Location: index.php?sub=error", true, 303);
    exit;
}
$stmt->bind_param($types, ...$values);
try {
    $inserted = $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ((int)$e->getCode() === 1062) {
        $stmt->close();
        header("Location: index.php?sub=exists", true, 303);
        exit;
    }
    error_log('Subscribe insert failed: ' . $e->getMessage());
    header("Location: index.php?sub=error", true, 303);
    exit;
}
if (!$inserted) {
    error_log('Subscribe insert failed: ' . $stmt->error);
    header("Location: index.php?sub=error", true, 303);
    exit;
}
$stmt->close();

header("Location: index.php?sub=success", true, 303);
exit;