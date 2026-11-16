<?php

function getTeamMemberColumns($conn) {
    $result = $conn->query('SHOW COLUMNS FROM team_members');
    if (!$result) {
        throw new RuntimeException('Unable to inspect team member columns.');
    }

    return array_column($result->fetch_all(MYSQLI_ASSOC), 'Field');
}

function saveTeamMemberImage($file) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The image upload did not complete successfully.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Team images must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedTypes[$mime]) || getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Upload a valid JPG, PNG or WebP image.');
    }

    $uploadDir = __DIR__ . '/../uploads/team/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('The team image directory could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('The team image could not be saved.');
    }

    return $filename;
}

function removeTeamMemberImage($filename) {
    $path = __DIR__ . '/../uploads/team/' . basename((string)$filename);
    if (is_file($path) && !unlink($path)) {
        error_log('Unable to remove unused team image: ' . basename($path));
    }
}

function teamFormToken() {
    if (empty($_SESSION['team_form_token'])) {
        $_SESSION['team_form_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['team_form_token'];
}

function validateTeamFormToken() {
    $token = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['team_form_token'] ?? '';
    if (!is_string($token) || $expected === '' || !hash_equals($expected, $token)) {
        return false;
    }

    unset($_SESSION['team_form_token']);
    return true;
}
