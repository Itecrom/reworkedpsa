<?php
include 'includes/db.php';
include 'includes/header.php';

// Get all active featured news
$featuredNews = $conn->query("SELECT * FROM featured_news WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
?>

<!-- Page Title -->
<?php renderPageTitle('Featured News', [
  ['label' => 'Home', 'url' => '/'],
  ['label' => 'News and Events', 'url' => '/psa-news'],
  ['label' => 'Featured News', 'url' => ''],
]); ?>

<section class="section bg-light">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="mb-4">
          <p class="text-muted">The stories carry either PSA statements or views of PSA leadership. This shows both PSA visibility and history in terms of contribution to the governance space in Malawi.</p>
        </div>

        <?php if ($featuredNews->num_rows > 0): ?>
          <div class="row">
            <?php while ($news = $featuredNews->fetch_assoc()): ?>
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                  <?php if (!empty($news['image']) && file_exists('uploads/featured_news/' . $news['image'])): ?>
                    <img src="../uploads/featured_news/<?= htmlspecialchars($news['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>" style="height: 200px; object-fit: cover;">
                  <?php else: ?>
                    <div class="bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                      <i class="fas fa-newspaper text-white" style="font-size: 48px;"></i>
                    </div>
                  <?php endif; ?>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                    <?php if (!empty($news['description'])): ?>
                      <p class="card-text text-muted small"><?= htmlspecialchars($news['description']) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto pt-3">
                      <a href="<?= htmlspecialchars($news['link_url']) ?>" target="_blank" class="btn btn-primary btn-sm">
                        Read More <i class="fas fa-external-link-alt ml-1"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <p class="text-muted">No featured news available at the moment.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
