<?php

function journalCoverColumnExists($conn) {
    $result = $conn->query("SHOW COLUMNS FROM journals LIKE 'cover_image'");
    return $result !== false && $result->num_rows > 0;
}

function ensureJournalCoverColumn($conn) {
    if (!journalCoverColumnExists($conn)) {
        $conn->query('ALTER TABLE journals ADD COLUMN cover_image VARCHAR(255) NULL AFTER file');
    }
}

function uploadJournalCover($file) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The journal cover upload did not complete successfully.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Journal cover images must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Upload a valid JPG, PNG or WebP cover image.');
    }

    $directory = dirname(__DIR__) . '/uploads/journals/covers/';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The journal cover directory could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
        throw new RuntimeException('The journal cover could not be saved.');
    }

    return $filename;
}

function removeJournalCover($filename) {
    $path = dirname(__DIR__) . '/uploads/journals/covers/' . basename((string)$filename);
    if (is_file($path) && !unlink($path)) {
        error_log('Unable to remove unused journal cover image: ' . basename($path));
    }
}
