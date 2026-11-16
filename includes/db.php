<?php
// Local WAMP defaults; configure PSA_DB_* environment variables on other servers.
$db_host = getenv('PSA_DB_HOST') ?: 'localhost';
$db_user = getenv('PSA_DB_USER') ?: 'root';
$db_pass = getenv('PSA_DB_PASSWORD');
$db_pass = $db_pass === false ? '' : $db_pass;
$db_name = getenv('PSA_DB_NAME') ?: 'malawips_mpsa';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check the PSA_DB_HOST, PSA_DB_USER, PSA_DB_PASSWORD and PSA_DB_NAME settings.');
}
?>
