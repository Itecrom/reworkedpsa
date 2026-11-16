<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/pdf_helper.php';

if (!isset($_GET['id']) && !isset($_GET['slug'])) {
  echo "<div class='container py-5 text-center'><h2>Journal not found.</h2></div>";
  include 'includes/footer.php';
  exit;
}

// Get journal by ID or slug
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $journal = $conn->query("SELECT * FROM journals WHERE id = $id")->fetch_assoc();
} else {
  $slug = $conn->real_escape_string($_GET['slug']);
  $journal = $conn->query("SELECT * FROM journals WHERE slug = '$slug'")->fetch_assoc();
}

if (!$journal) {
  echo "<div class='container py-5 text-center'><h2>Journal not found.</h2></div>";
  include 'includes/footer.php';
  exit;
}

$journalId = $journal['id'];

// Track view for the journal
trackAnalytics($conn, $journalId, null, 'view');
incrementViews($conn, 'journal', $journalId);

// Topics/Articles
$topics = $conn->query("SELECT * FROM journal_topics WHERE journal_id = " . $journalId . " ORDER BY id ASC");
?>

<!-- Page Title -->
<section class="page-title overlay" style="background-image: url(images/background/page-title-3.jpg);">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h2 class="text-white font-weight-bold">Journal Details</h2>
      </div>
    </div>
  </div>
</section>

<section class="section bg-light">
  <div class="container">
    <div class="row">

      <!-- Main Journal Content -->
      <div class="col-lg-8">
        <div class="bg-white p-4 rounded shadow-sm">
          <h2 class="mb-2 text-dark"><?= htmlspecialchars($journal['title']) ?></h2>
          <p class="text-muted">
            By <?= htmlspecialchars($journal['author'] ?? 'Unknown') ?>
            | Vol. <?= htmlspecialchars($journal['volume'] ?? 'N/A') ?>, Issue <?= htmlspecialchars($journal['issue_no'] ?? 'N/A') ?>
            | <?= date('d M Y', strtotime($journal['journal_date'] ?? $journal['created_at'])) ?>
          </p>

          <?php if (!empty($journal['editorial_summary'])): ?>
            <div class="bg-light p-3 rounded mb-3">
              <h6 class="font-weight-bold text-dark">Editorial Summary</h6>
              <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($journal['editorial_summary'])) ?></p>
            </div>
          <?php endif; ?>

          <?php if (!empty($journal['summary'])): ?>
            <p class="lead mt-3"><?= nl2br(htmlspecialchars($journal['summary'])) ?></p>
          <?php endif; ?>

          <?php if (!empty($journal['tags'])): ?>
            <div class="mt-3">
              <?php foreach (explode(',', $journal['tags']) as $tag): ?>
                <span class="badge badge-primary mr-1"><?= htmlspecialchars(trim($tag)) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($journal['file'])): ?>
            <div class="my-3">
              <a href="journal.php?download_journal_id=<?= $journal['id'] ?>" class="btn btn-danger" title="Download full journal PDF">
                <i class="fas fa-download"></i> Download Full Journal
              </a>
              <small class="ml-2 text-muted"><?= (int)$journal['downloads'] ?> downloads | <?= (int)$journal['views'] ?> views</small>
            </div>
          <?php endif; ?>

          <hr>
          <h5 class="mb-3">Journal Articles (<?= $topics->num_rows ?>)</h5>
          <?php if ($topics->num_rows): ?>
            <div class="list-group mb-3">
              <?php while ($topic = $topics->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center flex-column flex-md-row">
                  <div class="flex-1">
                    <h6 class="mb-1"><?= htmlspecialchars($topic['topic']) ?></h6>
                    <?php if (!empty($topic['article_author'])): ?>
                      <small class="text-muted">By <?= htmlspecialchars($topic['article_author']) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($topic['article_date'])): ?>
                      <small class="text-muted d-block"><?= date('d M Y', strtotime($topic['article_date'])) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($topic['article_summary'])): ?>
                      <p class="text-muted small mb-0 mt-1"><?= nl2br(htmlspecialchars($topic['article_summary'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($topic['abstract'])): ?>
                      <div class="bg-light p-2 rounded mt-2 small">
                        <strong>Abstract:</strong> <?= nl2br(htmlspecialchars($topic['abstract'])) ?>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($topic['keywords'])): ?>
                      <div class="mt-2">
                        <?php foreach (explode(',', $topic['keywords']) as $kw): ?>
                          <span class="badge badge-secondary mr-1"><?= htmlspecialchars(trim($kw)) ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    <small class="text-muted mt-2 d-block">
                      <i class="fas fa-eye"></i> <?= (int)$topic['views'] ?> views |
                      <i class="fas fa-download"></i> <?= (int)$topic['downloads'] ?> downloads
                    </small>
                  </div>
                  <div class="btn-group mt-2 mt-md-0">
                    <?php if (!empty($topic['file'])): ?>
                      <a href="journal.php?view_article_id=<?= $topic['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View Article">
                        <i class="fas fa-eye"></i> View
                      </a>
                      <a href="journal.php?download_article_id=<?= $topic['id'] ?>" class="btn btn-sm btn-outline-success" title="Download Article">
                        <i class="fas fa-download"></i> Download
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No PDF available</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p class="text-muted">No articles listed for this journal.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <div class="bg-white p-4 shadow-sm">
          <h5 class="mb-3">Search Journals</h5>
          <form method="GET" action="journal.php">
            <input type="text" name="q" class="form-control mb-2" placeholder="Search by topic, author">
            <button class="btn btn-sm btn-primary w-100">Search</button>
          </form>

          <hr>

          <h5 class="mb-3">Recent Journals</h5>
          <?php
          $recent = $conn->query("SELECT title, slug, created_at FROM journals ORDER BY created_at DESC LIMIT 5");
          while ($r = $recent->fetch_assoc()): ?>
            <div class="mb-3 border-bottom pb-2">
              <a href="journal-view.php?id=<?= $r['id'] ?>" class="text-dark font-weight-bold">
                <?= htmlspecialchars($r['title']) ?>
              </a>
              <div class="small text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></div>
            </div>
          <?php endwhile; ?>

          <hr>

          <h5 class="mb-3">Stay Updated</h5>
          <form method="post" action="subscribe.php">
            <input type="text" name="name" class="form-control mb-2" placeholder="Your Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Your Email" required>
            <button class="btn btn-sm btn-success w-100">Subscribe</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
