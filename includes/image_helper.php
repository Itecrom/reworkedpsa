<?php
/**
 * Image Helper Functions
 * Handles image compression, resizing, and preview
 */

/**
 * Compress and resize image
 * @param string $sourcePath Path to source image
 * @param string $destPath Path to destination image
 * @param int $maxWidth Maximum width in pixels
 * @param int $maxHeight Maximum height in pixels
 * @param int $quality JPEG quality (1-100)
 * @return array ['success' => bool, 'message' => string, 'originalSize' => int, 'compressedSize' => int]
 */
function compressImage($sourcePath, $destPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 80) {
    if (!file_exists($sourcePath)) {
        return ['success' => false, 'message' => 'Source file not found.', 'originalSize' => 0, 'compressedSize' => 0];
    }

    $originalSize = filesize($sourcePath);
    $info = getimagesize($sourcePath);

    if (!$info) {
        return ['success' => false, 'message' => 'Invalid image file.', 'originalSize' => $originalSize, 'compressedSize' => 0];
    }

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    if ($ratio < 1) {
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    // Create image from source
    switch ($mime) {
        case 'image/jpeg':
            $srcImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $srcImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $srcImage = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $srcImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return ['success' => false, 'message' => 'Unsupported image format.', 'originalSize' => $originalSize, 'compressedSize' => 0];
    }

    if (!$srcImage) {
        return ['success' => false, 'message' => 'Failed to create image from source.', 'originalSize' => $originalSize, 'compressedSize' => 0];
    }

    // Create destination image
    $dstImage = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and GIF
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save compressed image
    $saved = false;
    switch ($mime) {
        case 'image/jpeg':
            $saved = imagejpeg($dstImage, $destPath, $quality);
            break;
        case 'image/png':
            $saved = imagepng($dstImage, $destPath, (int)round(($quality / 100) * 9));
            break;
        case 'image/gif':
            $saved = imagegif($dstImage, $destPath);
            break;
        case 'image/webp':
            $saved = imagewebp($dstImage, $destPath, $quality);
            break;
    }

    imagedestroy($srcImage);
    imagedestroy($dstImage);

    if (!$saved) {
        return ['success' => false, 'message' => 'Failed to save compressed image.', 'originalSize' => $originalSize, 'compressedSize' => 0];
    }

    $compressedSize = filesize($destPath);
    $savings = $originalSize - $compressedSize;
    $savingsPercent = $originalSize > 0 ? round(($savings / $originalSize) * 100, 1) : 0;

    return [
        'success' => true,
        'message' => "Image compressed. Saved {$savingsPercent}% ({$savings} bytes).",
        'originalSize' => $originalSize,
        'compressedSize' => $compressedSize
    ];
}

/**
 * Handle image upload with compression
 * @param array $file The $_FILES array element
 * @param string $uploadDir Directory to upload to
 * @param string $prefix Filename prefix
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @param int $quality JPEG quality
 * @return array ['success' => bool, 'filename' => string, 'error' => string, 'info' => array]
 */
function handleImageUpload($file, $uploadDir, $prefix = 'img_', $maxWidth = 1920, $maxHeight = 1080, $quality = 80) {
    $result = [
        'success' => false,
        'filename' => '',
        'error' => '',
        'info' => []
    ];

    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'File upload error. Please try again.';
        return $result;
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        $result['error'] = 'Only image files (JPG, PNG, GIF, WebP) are allowed.';
        return $result;
    }

    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $tempPath = $uploadDir . 'temp_' . $filename;
    $finalPath = $uploadDir . $filename;

    // Move uploaded file to temp location
    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        $result['error'] = 'Failed to upload file.';
        return $result;
    }

    // Compress the image
    $compressionResult = compressImage($tempPath, $finalPath, $maxWidth, $maxHeight, $quality);

    // Remove temp file
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }

    if ($compressionResult['success']) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['info'] = $compressionResult;
    } else {
        $result['error'] = $compressionResult['message'];
        // Clean up
        if (file_exists($finalPath)) {
            unlink($finalPath);
        }
    }

    return $result;
}
