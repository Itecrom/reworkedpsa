<?php
// Ensures the `subscribers` table (and its required columns) exist.
// Idempotent and safe to call on every request — does nothing if already present.
// Used so the newsletter feature works on a fresh local database as well as the
// live server without manual setup.
function ensureSubscribersTable($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        approved TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $colsRes = $conn->query("SHOW COLUMNS FROM subscribers");
    $existing = $colsRes ? array_column($colsRes->fetch_all(MYSQLI_ASSOC), 'Field') : [];
    foreach ([
        'name'       => "VARCHAR(255) NOT NULL",
        'email'      => "VARCHAR(255) NOT NULL",
        'status'     => "VARCHAR(50) DEFAULT 'pending'",
        'approved'   => "TINYINT(1) DEFAULT 0",
        'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP",
    ] as $col => $def) {
        if (!in_array($col, $existing, true)) {
            $conn->query("ALTER TABLE subscribers ADD COLUMN $col $def");
        }
    }
}
