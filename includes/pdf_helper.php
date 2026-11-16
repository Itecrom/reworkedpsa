<?php
/**
 * PDF Helper Functions
 * Handles PDF validation, compression, and upload
 */

/**
 * Validate PDF file size
 * @param array $file The $_FILES array element
 * @param int $maxSizeMB Maximum size in MB (default 10MB)
 * @return array ['valid' => bool, 'error' => string, 'sizeMB' => float]
 */
function validatePdfSize($file, $maxSizeMB = 10) {
    if (!isset($file['size'])) {
        return ['valid' => false, 'error' => 'File size information not available.', 'sizeMB' => 0];
    }

    $sizeBytes = $file['size'];
    $sizeMB = $sizeBytes / (1024 * 1024);

    if ($sizeMB > $maxSizeMB) {
        return [
            'valid' => false,
            'error' => "PDF file is too large ({$sizeMB} MB). Maximum allowed size is {$maxSizeMB} MB. Please compress the PDF before uploading.",
            'sizeMB' => round($sizeMB, 2)
        ];
    }

    return ['valid' => true, 'error' => '', 'sizeMB' => round($sizeMB, 2)];
}

/**
 * Compress PDF using Imagick if available
 * Falls back to original file if Imagick is not available
 * @param string $sourcePath Path to source PDF
 * @param string $destPath Path to destination PDF
 * @param string $quality 'low', 'medium', 'high' (default: medium)
 * @return array ['success' => bool, 'message' => string, 'originalSize' => int, 'compressedSize' => int]
 */
function compressPdf($sourcePath, $destPath, $quality = 'medium') {
    if (!file_exists($sourcePath)) {
        return ['success' => false, 'message' => 'Source file not found.', 'originalSize' => 0, 'compressedSize' => 0];
    }

    $originalSize = filesize($sourcePath);

    // Check if Imagick is available
    if (!extension_loaded('imagick')) {
        // Try to use Ghostscript via exec if available
        if (shell_exec('which gs 2>/dev/null') || shell_exec('where gs 2>NUL')) {
            return compressPdfWithGhostscript($sourcePath, $destPath, $quality, $originalSize);
        }

        // No compression available, just copy
        if (copy($sourcePath, $destPath)) {
            return [
                'success' => true,
                'message' => 'PDF uploaded without compression (Imagick/Ghostscript not available).',
                'originalSize' => $originalSize,
                'compressedSize' => filesize($destPath)
            ];
        }

        return ['success' => false, 'message' => 'Failed to copy PDF file.', 'originalSize' => $originalSize, 'compressedSize' => 0];
    }

    try {
        $imagick = new Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($sourcePath);

        // Set compression quality
        $compressionQuality = [
            'low' => 60,
            'medium' => 75,
            'high' => 90
        ];

        $qualityValue = $compressionQuality[$quality] ?? 75;

        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality($qualityValue);
        $imagick->setImageFormat('pdf');
        $imagick->optimizeImageLayers();

        $imagick->writeImage($destPath);
        $imagick->clear();
        $imagick->destroy();

        $compressedSize = filesize($destPath);
        $savings = $originalSize - $compressedSize;
        $savingsPercent = $originalSize > 0 ? round(($savings / $originalSize) * 100, 1) : 0;

        return [
            'success' => true,
            'message' => "PDF compressed successfully. Saved {$savingsPercent}% ({$savings} bytes).",
            'originalSize' => $originalSize,
            'compressedSize' => $compressedSize
        ];

    } catch (Exception $e) {
        // Fallback: copy original file
        if (copy($sourcePath, $destPath)) {
            return [
                'success' => true,
                'message' => 'PDF uploaded without compression (compression failed: ' . $e->getMessage() . ')',
                'originalSize' => $originalSize,
                'compressedSize' => filesize($destPath)
            ];
        }

        return ['success' => false, 'message' => 'PDF compression failed: ' . $e->getMessage(), 'originalSize' => $originalSize, 'compressedSize' => 0];
    }
}

/**
 * Compress PDF using Ghostscript
 */
function compressPdfWithGhostscript($sourcePath, $destPath, $quality, $originalSize) {
    $qualitySettings = [
        'low' => '/screen',
        'medium' => '/ebook',
        'high' => '/printer'
    ];

    $pdfSetting = $qualitySettings[$quality] ?? '/ebook';

    // Ghostscript command for PDF compression
    $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS={$pdfSetting} -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"{$destPath}\" \"{$sourcePath}\" 2>&1";

    exec($cmd, $output, $returnCode);

    if ($returnCode === 0 && file_exists($destPath)) {
        $compressedSize = filesize($destPath);
        $savings = $originalSize - $compressedSize;
        $savingsPercent = $originalSize > 0 ? round(($savings / $originalSize) * 100, 1) : 0;

        return [
            'success' => true,
            'message' => "PDF compressed with Ghostscript. Saved {$savingsPercent}% ({$savings} bytes).",
            'originalSize' => $originalSize,
            'compressedSize' => $compressedSize
        ];
    }

    // Fallback: copy original
    if (copy($sourcePath, $destPath)) {
        return [
            'success' => true,
            'message' => 'PDF uploaded without compression (Ghostscript compression failed).',
            'originalSize' => $originalSize,
            'compressedSize' => filesize($destPath)
        ];
    }

    return ['success' => false, 'message' => 'Failed to process PDF file.', 'originalSize' => $originalSize, 'compressedSize' => 0];
}

/**
 * Handle PDF upload with validation and compression
 * @param array $file The $_FILES array element
 * @param string $uploadDir Directory to upload to
 * @param string $prefix Filename prefix (default: 'pdf_')
 * @param int $maxSizeMB Maximum file size in MB
 * @param string $quality Compression quality
 * @return array ['success' => bool, 'filename' => string, 'error' => string, 'info' => array]
 */
function handlePdfUpload($file, $uploadDir, $prefix = 'pdf_', $maxSizeMB = 10, $quality = 'medium') {
    $result = [
        'success' => false,
        'filename' => '',
        'error' => '',
        'info' => []
    ];

    // Validate file exists
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'File upload error. Please try again.';
        return $result;
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mimeType !== 'application/pdf') {
        $result['error'] = 'Only PDF files are allowed.';
        return $result;
    }

    // Validate file size
    $sizeValidation = validatePdfSize($file, $maxSizeMB);
    if (!$sizeValidation['valid']) {
        $result['error'] = $sizeValidation['error'];
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

    // Compress the PDF
    $compressionResult = compressPdf($tempPath, $finalPath, $quality);

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

/**
 * Get journal settings from database
 * @param mysqli $conn Database connection
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getJournalSetting($conn, $key, $default = null) {
    $stmt = $conn->prepare("SELECT setting_value FROM journal_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result && $result['setting_value'] !== null) {
        return $result['setting_value'];
    }

    return $default;
}

/**
 * Track journal/article analytics
 * @param mysqli $conn Database connection
 * @param int|null $journalId Journal ID (null if article-only)
 * @param int|null $articleId Article ID (null if journal-only)
 * @param string $action 'view' or 'download'
 * @return bool Success status
 */
function trackAnalytics($conn, $journalId = null, $articleId = null, $action = 'view') {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $conn->prepare("
        INSERT INTO journal_analytics (journal_id, article_id, action, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iisss", $journalId, $articleId, $action, $ipAddress, $userAgent);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Increment view count for journal or article
 * @param mysqli $conn Database connection
 * @param string $type 'journal' or 'article'
 * @param int $id ID of the journal or article
 * @return bool Success status
 */
function incrementViews($conn, $type, $id) {
    $table = ($type === 'article') ? 'journal_topics' : 'journals';
    $stmt = $conn->prepare("UPDATE {$table} SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Increment download count for journal or article
 * @param mysqli $conn Database connection
 * @param string $type 'journal' or 'article'
 * @param int $id ID of the journal or article
 * @return bool Success status
 */
function incrementDownloads($conn, $type, $id) {
    $table = ($type === 'article') ? 'journal_topics' : 'journals';
    $stmt = $conn->prepare("UPDATE {$table} SET downloads = downloads + 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Get analytics summary for a journal
 * @param mysqli $conn Database connection
 * @param int $journalId Journal ID
 * @return array Analytics data
 */
function getJournalAnalytics($conn, $journalId) {
    $analytics = [
        'total_views' => 0,
        'total_downloads' => 0,
        'article_views' => 0,
        'article_downloads' => 0,
        'recent_activity' => []
    ];

    // Journal-level stats
    $journal = $conn->query("SELECT views, downloads FROM journals WHERE id = " . (int)$journalId)->fetch_assoc();
    if ($journal) {
        $analytics['total_views'] = (int)($journal['views'] ?? 0);
        $analytics['total_downloads'] = (int)($journal['downloads'] ?? 0);
    }

    // Article-level stats
    $articleStats = $conn->query("
        SELECT SUM(views) as total_views, SUM(downloads) as total_downloads
        FROM journal_topics
        WHERE journal_id = " . (int)$journalId
    )->fetch_assoc();

    $analytics['article_views'] = (int)($articleStats['total_views'] ?? 0);
    $analytics['article_downloads'] = (int)($articleStats['total_downloads'] ?? 0);

    // Recent activity
    $recent = $conn->query("
        SELECT ja.*, j.title as journal_title, jt.topic as article_title
        FROM journal_analytics ja
        LEFT JOIN journals j ON ja.journal_id = j.id
        LEFT JOIN journal_topics jt ON ja.article_id = jt.id
        WHERE ja.journal_id = " . (int)$journalId . "
        ORDER BY ja.created_at DESC
        LIMIT 20
    ");

    while ($row = $recent->fetch_assoc()) {
        $analytics['recent_activity'][] = $row;
    }

    return $analytics;
}

/**
 * Get overall journal analytics
 * @param mysqli $conn Database connection
 * @param int $days Number of days to look back (default: 30)
 * @return array Overall analytics data
 */
function getOverallAnalytics($conn, $days = 30) {
    $analytics = [
        'total_journals' => 0,
        'total_articles' => 0,
        'total_views' => 0,
        'total_downloads' => 0,
        'top_journals' => [],
        'top_articles' => [],
        'daily_stats' => []
    ];

    // Total counts
    $analytics['total_journals'] = $conn->query("SELECT COUNT(*) as cnt FROM journals")->fetch_assoc()['cnt'];
    $analytics['total_articles'] = $conn->query("SELECT COUNT(*) as cnt FROM journal_topics")->fetch_assoc()['cnt'];

    // Total views and downloads
    $stats = $conn->query("
        SELECT
            (SELECT SUM(views) FROM journals) as journal_views,
            (SELECT SUM(downloads) FROM journals) as journal_downloads,
            (SELECT SUM(views) FROM journal_topics) as article_views,
            (SELECT SUM(downloads) FROM journal_topics) as article_downloads
    ")->fetch_assoc();

    $analytics['total_views'] = (int)($stats['journal_views'] ?? 0) + (int)($stats['article_views'] ?? 0);
    $analytics['total_downloads'] = (int)($stats['journal_downloads'] ?? 0) + (int)($stats['article_downloads'] ?? 0);

    // Top journals by views
    $topJournals = $conn->query("
        SELECT id, title, views, downloads
        FROM journals
        ORDER BY views DESC
        LIMIT 5
    ");
    while ($row = $topJournals->fetch_assoc()) {
        $analytics['top_journals'][] = $row;
    }

    // Top articles by views
    $topArticles = $conn->query("
        SELECT jt.id, jt.topic, jt.views, jt.downloads, j.title as journal_title
        FROM journal_topics jt
        JOIN journals j ON jt.journal_id = j.id
        ORDER BY jt.views DESC
        LIMIT 10
    ");
    while ($row = $topArticles->fetch_assoc()) {
        $analytics['top_articles'][] = $row;
    }

    // Daily stats for the last N days
    $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
    $dailyStats = $conn->query("
        SELECT DATE(created_at) as date, action, COUNT(*) as count
        FROM journal_analytics
        WHERE created_at >= '{$dateFrom}'
        GROUP BY DATE(created_at), action
        ORDER BY date DESC
    ");

    while ($row = $dailyStats->fetch_assoc()) {
        $analytics['daily_stats'][$row['date']][$row['action']] = (int)$row['count'];
    }

    return $analytics;
}
