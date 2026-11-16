<?php
// Use the main database config (includes/db.php) as the single source of truth.
require_once __DIR__ . '/db.php';

// Alias for legacy code (submit.php) that expects $con (mysqli connection).
$con = $conn;
?>
