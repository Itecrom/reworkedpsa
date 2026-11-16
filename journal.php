<?php
ob_start();
session_start();
include 'includes/db.php';
include 'includes/pdf_helper.php';

// --- DOWNLOAD LOGIC for journals ---
if (isset($_GET['download_journal_id'])) {
  $jid = (int) $_GET['download_journal_id'];
  $file = $conn->query("SELECT file FROM journals WHERE id = $jid")->fetch_assoc();
  $filename = $file['file'] ?? '';
  $filePath = "uploads/journals/$filename";

  if ($filename && file_exists($filePath)) {
    // Track analytics
    trackAnalytics($conn, $jid, null, 'download');
    incrementDownloads($conn, 'journal', $jid);

    // Send headers and file
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
  } else {
    die("Journal file not found.");
  }
}

// --- DOWNLOAD LOGIC for articles ---
if (isset($_GET['download_article_id'])) {
  $aid = (int) $_GET['download_article_id'];
  $articleFile = $conn->query("SELECT file, journal_id FROM journal_topics WHERE id = $aid")->fetch_assoc();
  $filename = $articleFile['file'] ?? '';
  $journalId = $articleFile['journal_id'] ?? null;
  $filePath = "uploads/journals/$filename";

  if ($filename && file_exists($filePath)) {
    // Track analytics
    trackAnalytics($conn, $journalId, $aid, 'download');
    incrementDownloads($conn, 'article', $aid);

    // Send headers and file
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
  } else {
    die("Article file not found.");
  }
}

// --- VIEW LOGIC for articles (inline view) ---
if (isset($_GET['view_article_id'])) {
  $aid = (int) $_GET['view_article_id'];
  $articleFile = $conn->query("SELECT file, journal_id FROM journal_topics WHERE id = $aid")->fetch_assoc();
  $filename = $articleFile['file'] ?? '';
  $journalId = $articleFile['journal_id'] ?? null;
  $filePath = "uploads/journals/$filename";

  if ($filename && file_exists($filePath)) {
    // Track analytics
    trackAnalytics($conn, $journalId, $aid, 'view');
    incrementViews($conn, 'article', $aid);

    // Redirect to the file for inline viewing
    header("Location: uploads/journals/" . urlencode($filename));
    exit;
  } else {
    die("Article file not found.");
  }
}

// --- FILTERS ---
$search = $_GET['q'] ?? '';
$authorFilter = $_GET['author'] ?? '';
$yearFilter = $_GET['year'] ?? '';
$where = '1';

if ($search) {
  $q = $conn->real_escape_string($search);
  $where .= " AND (j.title LIKE '%$q%' OR j.tags LIKE '%$q%' OR j.author LIKE '%$q%')";
}
if ($authorFilter) {
  $where .= " AND j.author = '" . $conn->real_escape_string($authorFilter) . "'";
}
if ($yearFilter) {
  $where .= " AND YEAR(j.created_at) = " . (int)$yearFilter;
}

// --- PAGINATION ---
$limit = 5;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) as total FROM journals j WHERE $where")->fetch_assoc()['total'];
$journals = $conn->query("SELECT j.*, COUNT(DISTINCT jt.id) as article_count FROM journals j LEFT JOIN journal_topics jt ON j.id = jt.journal_id WHERE $where GROUP BY j.id ORDER BY j.created_at DESC LIMIT $offset, $limit");

// --- EXTRA DATA FOR FILTERS ---
$authors = $conn->query("SELECT DISTINCT author FROM journals WHERE author IS NOT NULL AND author != '' ORDER BY author");
$years = $conn->query("SELECT DISTINCT YEAR(created_at) as yr FROM journals ORDER BY yr DESC");

include 'includes/header.php';
?>

<section class="page-title overlay" style="background-image: url(images/background/page-title-3.jpg);">
  <div class="container"><div class="row"><div class="col-12 text-center">
    <h2 class="text-white font-weight-bold">PSA Journals</h2>
  </div></div></div>
</section>

<section class="section">
  <div class="container">
    <div class="row">
      <!-- MAIN CONTENT -->
      <div class="col-lg-8">
        <form class="mb-4" method="get">
          <div class="form-row align-items-end">
            <div class="col-md-4 mb-2">
              <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search journals..." class="form-control">
            </div>
            <div class="col-md-3 mb-2">
              <select name="author" class="form-control">
                <option value="">All Authors</option>
                <?php while($a = $authors->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($a['author']) ?>" <?= ($a['author'] == $authorFilter) ? 'selected' : '' ?>><?= htmlspecialchars($a['author']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <select name="year" class="form-control">
                <option value="">All Years</option>
                <?php while($y = $years->fetch_assoc()): ?>
                  <option value="<?= (int)$y['yr'] ?>" <?= ($y['yr'] == $yearFilter) ? 'selected' : '' ?>><?= (int)$y['yr'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <button type="submit" class="btn btn-sm btn-primary btn-block">Filter</button>
            </div>
          </div>
        </form>

        <div id="journal-list">
          <?php while($j = $journals->fetch_assoc()): ?>
            <div class="bg-white rounded shadow-sm mb-4 p-4 wow fadeIn" data-wow-delay="0.1s">
              <div class="d-flex align-items-center mb-2">
                <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" width="34" class="mr-3" alt="PDF">
                <div>
                  <h5 class="mb-0"><?= htmlspecialchars($j['title']) ?></h5>
                  <small class="text-muted">
                    <!--By--> <?= htmlspecialchars($j['author'] ?? 'Unknown') ?>
                    Vol. <?= htmlspecialchars($j['volume'] ?? 'N/A') ?>, Issue <?= htmlspecialchars($j['issue_no'] ?? 'N/A') ?>
                    | <?= date('M d, Y', strtotime($j['journal_date'] ?? $j['created_at'])) ?>
                  </small>
                  <?php if (!empty($j['tags'])): ?>
                    <div class="mt-1">
                      <?php foreach (explode(',', $j['tags']) as $tag): ?>
                        <span class="badge badge-primary mr-1"><?= htmlspecialchars(trim($tag)) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <p class="text-justify"><?= nl2br(htmlspecialchars(mb_strimwidth($j['summary'], 0, 300, "..."))) ?></p>

              <?php
                $topics = $conn->query("SELECT * FROM journal_topics WHERE journal_id = " . (int)$j['id']);
                if ($topics->num_rows > 0):
              ?>
                <div class="mb-2">
                  <strong>Articles (<?= (int)$j['article_count'] ?>):</strong>
                  <div class="mt-2">
                    <?php while($t = $topics->fetch_assoc()): ?>
                      <div class="bg-light p-2 rounded mb-2">
                        <div class="d-flex justify-content-between align-items-start flex-column flex-md-row">
                          <div class="flex-1">
                            <span class="font-weight-bold"><?= htmlspecialchars($t['topic']) ?></span>
                            <?php if (!empty($t['article_author'])): ?>
                              <small class="text-muted d-block">By <?= htmlspecialchars($t['article_author']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($t['article_date'])): ?>
                              <small class="text-muted d-block"><?= date('d M Y', strtotime($t['article_date'])) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($t['article_summary'])): ?>
                              <p class="text-muted small mb-0 mt-1"><?= nl2br(htmlspecialchars(mb_strimwidth($t['article_summary'], 0, 200, '...'))) ?></p>
                            <?php endif; ?>
                            <small class="text-muted">
                              <i class="fas fa-eye"></i> <?= (int)$t['views'] ?> views |
                              <i class="fas fa-download"></i> <?= (int)$t['downloads'] ?> downloads
                            </small>
                          </div>
                          <div class="btn-group mt-2 mt-md-0">
                            <?php if (!empty($t['file'])): ?>
                              <a href="journal.php?view_article_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View Article">
                                <i class="ti-eye"></i> View
                              </a>
                              <a href="journal.php?download_article_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-success" title="Download Article">
                                <i class="ti-download"></i> Download
                              </a>
                            <?php else: ?>
                              <span class="text-muted">No PDF</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($j['file'])): ?>
                <a href="journal.php?download_journal_id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-primary" title="Download full journal PDF">
                  <i class="ti-download"></i> Download Full Journal
                </a>
                <small class="ml-2 text-muted"><?= (int)$j['downloads'] ?> downloads | <?= (int)$j['views'] ?> views</small>
              <?php else: ?>
                <span class="text-danger">[No Full Journal PDF]</span>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total > $limit): ?>
        <nav>
          <ul class="pagination">
            <?php for ($p = 1; $p <= ceil($total / $limit); $p++): ?>
              <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&author=<?= urlencode($authorFilter) ?>&year=<?= urlencode($yearFilter) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>

      <!-- SIDEBAR -->
      <div class="col-lg-4">
        <div class="bg-white px-4 py-4 shadow-sm">
          <h5 class="mb-3">Recent Journal Articles</h5>
          <?php
            $recent = $conn->query("SELECT * FROM journals ORDER BY created_at DESC LIMIT 5");
            while($r = $recent->fetch_assoc()):
          ?>
          <div class="mb-3 border-bottom pb-2">
            <strong class="d-block"><?= htmlspecialchars($r['title']) ?></strong>
            <small class="text-muted"><?= htmlspecialchars($r['author']) ?> | <?= date('M d, Y', strtotime($r['created_at'])) ?></small>
            <?php if (!empty($r['file'])): ?>
              <br><a href="journal.php?download_journal_id=<?= $r['id'] ?>" class="text-primary"><i class="ti-download"></i> Download</a>
            <?php endif; ?>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
