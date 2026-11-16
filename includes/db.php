<?php
// database connection (MAIN CONFIG - used across the whole site)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'malawips_mpsa';

// create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
