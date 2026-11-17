<?php
include 'config.php';
require_once __DIR__ . '/../includes/path_helper.php';

$sql = "SELECT * FROM news ORDER BY posted_on DESC LIMIT 5";
$result = $conn->query($sql);

$output = '';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = date('d', strtotime($row['posted_on']));
        $monthYear = date('M Y', strtotime($row['posted_on']));
        $output .= '
        <div>
            <div class="d-flex align-items-center border-bottom">
                <div class="text-center border-right p-4">
                    <h3 class="text-primary mb-0">' . $date . '
                        <span class="d-block paragraph">' . $monthYear . '</span>
                    </h3>
                </div>
                <div class="px-4">
                    <a class="h4 d-block d-block mb-10" href="' . htmlspecialchars(sitePath('psa-news'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . htmlspecialchars($row['title']) . '</a>
                    <ul class="post-meta list-inline">
                        <li class="list-inline-item paragraph mr-5">By
                            <a class="paragraph" href="' . htmlspecialchars(sitePath('maintenance'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . htmlspecialchars($row['author']) . '</a>
                        </li>
                        <li class="list-inline-item paragraph">' . htmlspecialchars($row['tags']) . '</li>
                    </ul>
                </div>
            </div>
            <div class="p-4">
                <p>' . nl2br(htmlspecialchars($row['excerpt'])) . '</p>
            </div>
        </div>';
    }
} else {
    $output = '<p class="p-4">No announcements available.</p>';
}

echo $output;
?>
