<?php
require_once 'includes/public_form_security.php';
startPublicFormSession();
include 'includes/db.php';
include 'includes/meta.php';
include 'includes/header.php';
?>

<?php if(isset($_GET['sub'])): ?>
  <?php if($_GET['sub']=="success"): ?>
    <div class="alert alert-success">✅ Thank you for subscribing! Await admin approval.</div>
  <?php elseif($_GET['sub']=="exists"): ?>
    <div class="alert alert-warning">⚠️ This email is already subscribed.</div>
  <?php elseif($_GET['sub']=="invalid"): ?>
    <div class="alert alert-danger">❌ Invalid email address.</div>
  <?php elseif($_GET['sub']=="empty"): ?>
    <div class="alert alert-danger">❌ Please enter both name and email.</div>
  <?php elseif($_GET['sub']=="rate"): ?>
    <div class="alert alert-warning">Please wait a little before trying to subscribe again.</div>
  <?php elseif($_GET['sub']=="error"): ?>
    <div class="alert alert-danger">We could not save your subscription. Please try again later.</div>
  <?php endif; ?>
<?php endif; ?>

<!-- hero area -->
<section>
    <div class="hero-slider-2 position-relative">
        <!-- hero slider item -->
        <div class="hero-slider-item py-160" style="background-image: url(images/banner/banner-1.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hero-content">
                            <h4 class="text-uppercase mb-1" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".1">Greater utilisation of</h4>
                            <h1 class="font-weight-bold mb-3" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".5">Political Scientists</h1>
                            <p class="text-dark mb-50" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".9">As experts in the field of Governance
                            </p>
                            <a data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in="1.3" href="about.php" class="btn btn-outline text-uppercase">more details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hero slider item -->
        <div class="hero-slider-item py-160" style="background-image: url(images/banner/banner-2.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hero-content">
                            <h4 class="text-uppercase mb-1" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".1">Promoting Interchange</h4>
                            <h1 class="font-weight-bold mb-3" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".5">Among all Institutions</h1>
                            <p class="text-dark mb-50" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".9">and Individuals who analyse, study, teach, and contribute actively
                                <br>to the civic and political life in Malawi.
                            </p>
                            <a data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in="1.3" href="about.php" class="btn btn-outline text-uppercase">more details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hero slider item -->
        <div class="hero-slider-item py-160" style="background-image: url(images/banner/banner-3.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hero-content">
                            <h4 class="text-uppercase mb-1" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".1">Promoting knowledge and understanding of</h4>
                            <h1 class="font-weight-bold mb-3" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".5">Local and International</h1>
                            <p class="text-dark mb-50" data-duration-in=".5" data-animation-in="fadeInLeft" data-delay-in=".9">Political affairs through teaching, workshops, discussion, conferences,
							<br> and publications or such other means.                           
						   </p>
                            <a data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in="1.3" href="about.php" class="btn btn-outline text-uppercase">more details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hero slider item -->
        <div class="hero-slider-item py-160" style="background-image: url(images/banner/banner-4.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hero-content">
                            <h4 class="text-uppercase mb-1" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".1">We promote the study</h4>
                            <h1 class="font-weight-bold mb-3" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".5">Research & publications</h1>
                            <p class="text-dark mb-50" data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in=".9">Related to but not limited to Malawi politics.
                            </p>
                            <a data-duration-in=".5" data-animation-in="fadeInDown" data-delay-in="1.3" href="about.php" class="btn btn-outline text-uppercase">more details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /hero-area -->

<!-- cta -->
<section class="bg-primary py-4 text-center text-lg-left">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 align-self-center">
                <h3 class="text-white">We are Affiliated to African APS</h3>
            </div>
            <div class="col-lg-3 text-lg-right">
                <a href="contact.php" class="btn btn-light btn-sm">BECOME OUR MEMBER</a>
            </div>
        </div>
    </div>
</section>
<!-- /cta -->

<!-- service -->
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 align-self-center">
                <h5 class="section-title-sm">Who we are</h5>
                <h2 class="section-title section-title-border-half">The Malawi Political Science Association (PSA)</h2>
                <p class="mb-4"> We are a registered entity with a diverse membership of academics and professionals in various fields. 
				 </p>
                <p class="mb-4">According to Article 4(1) of PSA Constitution, the Association is nonpartisan and does not take positions nor 
				commit its members to particular political positions not immediately concerned with its purpose..</p>
                <a href="about.php" class="btn btn-primary mb-md-50">Explore More</a>
            </div>
            <div class="col-lg-6">
                <div class="row">
                    <!-- service-item -->
                    <div class="col-sm-6">
                        <div class="mb-4 card px-2 py-5 text-center">
                            <i class="h2 text-primary d-inline-block mb-20 ti-medall" ></i>
                            <h4>Premium Members</h4>
                            <P></P>
                        </div>
                    </div>
                    <!-- service-item -->
                    <div class="col-sm-6">
                        <div class="mb-4 card px-2 py-5 text-center">
                            <i class="h2 text-primary d-inline-block mb-20 ti-medall-alt"></i>
                            <h4>Associate Members</h4>
                            <P></P>
                        </div>
                    </div>
                    <!-- service-item -->
                    <div class="col-sm-6">
                        <div class="mb-4 card px-2 py-5 text-center">
                            <i class="h2 text-primary d-inline-block mb-20 ti-star"></i>
                            <h4>Honorary Members</h4>
                            <P></P>
                        </div>
                    </div>
                    <!-- service-item -->
                    <div class="col-sm-6">
                        <div class="mb-4 card px-2 py-5 text-center">
                            <i class="h2 text-primary d-inline-block mb-20 ti-user"></i>
                            <h4>Institutional Members</h4>
                            <P></P>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /service -->

<section class="fun-facts overlay-dark section-sm" style="background-image: url(images/background/cta.jpg);">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="d-flex flex-sm-row flex-column justify-content-lg-center align-items-center text-center text-sm-left">
                    <i class="round-icon ti-briefcase mr-sm-3 mr-0 mb-3 mb-sm-0"></i>
                    <div class="text-center text-sm-left">
                        <h2 class="count text-white mb-0" data-count="56">0</h2>
                        <p class="text-white mb-0">Current Members</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="d-flex flex-sm-row flex-column justify-content-lg-center align-items-center text-center text-sm-left">
                    <i class="round-icon ti-book mr-sm-3 mr-0 mb-3 mb-sm-0"></i>
                    <div class="text-center text-sm-left">
                        <h2 class="count text-white mb-0" data-count="30">0</h2>
                        <p class="text-white mb-0">Books Published</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="d-flex flex-sm-row flex-column justify-content-lg-center align-items-center text-center text-sm-left">
                    <i class="round-icon ti-search mr-sm-3 mr-0 mb-3 mb-sm-0"></i>
                    <div class="text-center text-sm-left">
                        <h2 class="count text-white mb-0" data-count="90">0</h2>
                        <p class="text-white mb-0">Researchs conducted</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="d-flex flex-sm-row flex-column justify-content-lg-center align-items-center text-center text-sm-left">
                    <i class="round-icon ti-cup mr-sm-3 mr-0 mb-3 mb-sm-0"></i>
                    <div class="text-center text-sm-left">
                        <h2 class="count text-white mb-0" data-count="15">10</h2>
                        <p class="text-white mb-0">Awards Won</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- about -->
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-content">
                    <h3 class="section-title section-title-border-half">Featured News</h3>
                    <p class="text-dark">The stories carry either PSA statements or views of PSA leadership.
					This shows both PSA visibility and history in terms of contribution to the governance space in Malawi.
                    </p>
                    <div class="about-item">
                        <?php
                        $featuredNews = $conn->query("SELECT * FROM featured_news WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6");
                        $totalFeatured = $conn->query("SELECT COUNT(*) as total FROM featured_news WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;
                        $leftCol = [];
                        $rightCol = [];
                        $index = 0;
                        while ($fn = $featuredNews->fetch_assoc()) {
                            if ($index % 2 === 0) {
                                $leftCol[] = $fn;
                            } else {
                                $rightCol[] = $fn;
                            }
                            $index++;
                        }
                        ?>
                        <ul class="pl-0 d-inline-block float-sm-left mr-sm-5">
                            <?php foreach ($leftCol as $fn): ?>
                                <li class="font-secondary text-color mb-10">
                                    <i class="text-primary mr-2 ti-arrow-circle-right"></i>
                                    <a href="<?= htmlspecialchars($fn['link_url']) ?>" target="_blank"><?= htmlspecialchars($fn['title']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="d-inline-block pl-0">
                            <?php foreach ($rightCol as $fn): ?>
                                <li class="font-secondary text-color mb-10">
                                    <i class="text-primary mr-2 ti-arrow-circle-right"></i>
                                    <a href="<?= htmlspecialchars($fn['link_url']) ?>" target="_blank"><?= htmlspecialchars($fn['title']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ($totalFeatured > 6): ?>
                        <a href="featured-news.php" class="btn btn-primary mb-md-50 mt-4">More</a>
                    <?php endif; ?>
                </div>
            </div>
            
<div class="col-lg-6">
  <article class="bg-white rounded mb-40">
    <h3 class="section-title section-title-border-half">Announcements & Events</h3>

    <div class="about-slider">

      <?php
      include 'includes/db.php';

      // Define news URL function
      if (!function_exists('news_url')) {

        function news_url($slug) {
          return "news-details.php?slug=" . urlencode($slug);
        }
      }

      /* =========================================
         NEWS + EVENTS COMBINED QUERY
      ========================================== */

      $result = $conn->query("

      (
          SELECT
              title,
              slug,
              content,
              created_at,
              author,
              tags,
              'news' AS post_type
          FROM news
          WHERE status='published'
      )

      UNION ALL

      (
          SELECT
              title,
              slug,
              description AS content,
              start_date AS created_at,
              organizer AS author,
              event_type AS tags,
              'event' AS post_type
          FROM events
      )

      ORDER BY created_at DESC
      LIMIT 6

      ");

      if ($result && $result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

          $title       = htmlspecialchars($row['title'] ?? '');

          $summary_raw = strip_tags($row['content'] ?? '');

          $post_type   = $row['post_type'] ?? 'news';

          $summary     = strlen($summary_raw) > 300
              ? substr($summary_raw, 0, 300) . "..."
              : $summary_raw;

          $slug        = htmlspecialchars($row['slug'] ?? '');

          $tags        = htmlspecialchars($row['tags'] ?? '');

          $author      = htmlspecialchars($row['author'] ?? '');

          $created_at  = isset($row['created_at'])
              ? date('j', strtotime($row['created_at']))
              : '';

          $month_year  = isset($row['created_at'])
              ? date('M Y', strtotime($row['created_at']))
              : '';

      ?>

      <div>

        <div class="d-flex align-items-center border-bottom">

          <!-- Date -->
          <div class="text-center border-right p-4">

            <h3 class="text-primary mb-0">

              <?= $created_at ?>

              <span class="d-block paragraph">

                <?= $month_year ?>

              </span>

            </h3>

          </div>

          <!-- Title and Meta -->
          <div class="px-4">

            <a class="h4 d-block mb-10"
               href="<?= ($post_type == 'event')
                    ? 'event-details.php?slug=' . urlencode($slug)
                    : news_url($slug) ?>">

                <?= $title ?>

                <?php if($post_type == 'event'): ?>

                    <span class="badge badge-success ml-2">
                        Event
                    </span>

                <?php endif; ?>

            </a>

            <ul class="post-meta list-inline">

              <li class="list-inline-item paragraph mr-5">

                By

                <a class="paragraph" href="#">

                  <?= $author ?>

                </a>

              </li>

              <li class="list-inline-item paragraph">

                <?= $tags ?>

              </li>

            </ul>

          </div>

        </div>

        <!-- Excerpt and Button -->
        <div class="p-4">

          <p><?= $summary ?></p>

          <a
              href="<?= ($post_type == 'event')
                    ? 'event-details.php?slug=' . urlencode($slug)
                    : news_url($slug) ?>"
              class="btn btn-sm btn-primary mt-2"
          >

              Read More

          </a>

        </div>

      </div>

      <?php
        }

      } else {

        echo '<p class="p-3">No announcements found.</p>';

      }
      ?>

    </div>

  </article>
</div>



        </div>
    </div>
</section>
<!-- /about -->

<section class="bg-primary py-4">
	<div class="container">
        <div class="client-logo-slider align-self-center">
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-1-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-2-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-3-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-4-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-5-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-6-white.jpg" alt="client-logo"></a>            
			<a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-1-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-2-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-3-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-4-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-5-white.png" alt="client-logo"></a>
            <a href="#" class="text-center d-block outline-0 py-3 px-2"><img class="d-unset" src="images/client-logo/client-logo-6-white.jpg" alt="client-logo"></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>