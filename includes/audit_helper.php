<?php
/**
 * Audit Log Helper Functions
 * Tracks changes made by admin users
 */

/**
 * Create the audit_log table if it does not exist.
 * This keeps admin write operations working even if the table was not
 * migrated to the live database (it is NOT auto-created elsewhere).
 * @param mysqli $conn Database connection
 */
function ensureAuditLogTable($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(100) NOT NULL DEFAULT 'unknown',
        action VARCHAR(50) NOT NULL,
        table_name VARCHAR(100) NOT NULL,
        record_id INT NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Log an action to the audit log
 * @param mysqli $conn Database connection
 * @param string $action Action performed (e.g., 'create', 'update', 'delete')
 * @param string $tableName Table affected
 * @param int|null $recordId ID of the affected record
 * @param array|null $oldValues Previous values (associative array)
 * @param array|null $newValues New values (associative array)
 * @return bool Success status
 */
function logAudit($conn, $action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    $userId = $_SESSION['admin_id'] ?? null;
    $username = $_SESSION['admin'] ?? 'unknown';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
    $newValuesJson = $newValues ? json_encode($newValues) : null;

    // Make sure the table exists (idempotent) before we try to write to it.
    ensureAuditLogTable($conn);

    $stmt = $conn->prepare("
        INSERT INTO audit_log (user_id, username, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // If the statement could not be prepared (e.g. table/column mismatch or
    // permissions), fail gracefully instead of throwing a fatal error that
    // would break the main create/update/delete operation.
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("isssissss", $userId, $username, $action, $tableName, $recordId, $oldValuesJson, $newValuesJson, $ipAddress, $userAgent);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Get audit logs with optional filters
 * @param mysqli $conn Database connection
 * @param array $filters Optional filters (user_id, action, table_name, date_from, date_to)
 * @param int $limit Number of records to return
 * @param int $offset Offset for pagination
 * @return array Audit log entries
 */
function getAuditLogs($conn, $filters = [], $limit = 50, $offset = 0) {
    $where = [];
    $params = [];
    $param_types = "";

    if (!empty($filters['user_id'])) {
        $where[] = "user_id = ?";
        $params[] = $filters['user_id'];
        $param_types .= "i";
    }

    if (!empty($filters['action'])) {
        $where[] = "action = ?";
        $params[] = $filters['action'];
        $param_types .= "s";
    }

    if (!empty($filters['table_name'])) {
        $where[] = "table_name = ?";
        $params[] = $filters['table_name'];
        $param_types .= "s";
    }

    if (!empty($filters['date_from'])) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $param_types .= "s";
    }

    if (!empty($filters['date_to'])) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $param_types .= "s";
    }

    $where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT * FROM audit_log $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $param_types .= "ii";
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($param_types, ...$params);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();
    return $logs;
}

/**
 * Get total count of audit logs with optional filters
 */
function getAuditLogCount($conn, $filters = []) {
    $where = [];
    $params = [];
    $param_types = "";

    if (!empty($filters['user_id'])) {
        $where[] = "user_id = ?";
        $params[] = $filters['user_id'];
        $param_types .= "i";
    }

    if (!empty($filters['action'])) {
        $where[] = "action = ?";
        $params[] = $filters['action'];
        $param_types .= "s";
    }

    if (!empty($filters['table_name'])) {
        $where[] = "table_name = ?";
        $params[] = $filters['table_name'];
        $param_types .= "s";
    }

    $where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT COUNT(*) as total FROM audit_log $where_clause";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int)($result['total'] ?? 0);
}

/**
 * Get Font Awesome icon class for an action
 * @param string $action Action name
 * @return string Icon class suffix
 */
function getActivityIcon($action) {
    $action = strtolower($action);
    $icons = [
        'create' => 'plus',
        'insert' => 'plus',
        'update' => 'edit',
        'edit' => 'edit',
        'delete' => 'trash',
        'remove' => 'trash',
        'view' => 'eye',
        'download' => 'download',
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'approve' => 'check',
        'reject' => 'times',
        'export' => 'file-export',
        'upload' => 'upload',
        'compress' => 'compress',
    ];

    foreach ($icons as $key => $icon) {
        if (strpos($action, $key) !== false) {
            return $icon;
        }
    }

    return 'circle';
}
