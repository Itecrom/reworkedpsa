<?php
require_once __DIR__ . '/public_form_security.php';
require_once __DIR__ . '/page_title.php';
startPublicFormSession();

// Get quick announcement from database (use the main config: includes/db.php)
require_once __DIR__ . '/db.php';
$announcement = null;
try {
    if ($conn->connect_errno === 0) {
        $result = $conn->query("SELECT message, link_url, is_active FROM quick_announcements WHERE is_active = 1 LIMIT 1");
        if ($result) {
            $announcement = $result->fetch_assoc();
        }
    }
} catch (Exception $e) {
    // Silently fail, will use default
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pageSlug = trim((string)($_GET['slug'] ?? ''));
$detailType = basename($requestPath);
if (preg_match('~^/news/([^/]+)/?$~', $requestPath, $routeMatch)) {
    $detailType = 'news-details.php';
    $pageSlug = rawurldecode($routeMatch[1]);
} elseif (preg_match('~^/events/([^/]+)/?$~', $requestPath, $routeMatch)) {
    $detailType = 'event-details.php';
    $pageSlug = rawurldecode($routeMatch[1]);
} elseif (preg_match('~^/journals/([^/]+)/?$~', $requestPath, $routeMatch)) {
    $detailType = 'journal-view.php';
    $pageSlug = rawurldecode($routeMatch[1]);
}
if ($detailType === 'journal-view.php' && $pageSlug === '' && isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $journalCoverCheck = $conn->query("SHOW COLUMNS FROM journals LIKE 'cover_image'");
    $journalHasCover = $journalCoverCheck && $journalCoverCheck->num_rows > 0;
    $journalFields = 'title, summary, description, editorial_summary' . ($journalHasCover ? ', cover_image' : '');
    $detailQuery = $conn->prepare('SELECT ' . $journalFields . ' FROM journals WHERE id = ? LIMIT 1');
    $journalId = (int)$_GET['id'];
    $detailQuery->bind_param('i', $journalId);
    $detailQuery->execute();
    $detailMetadata = $detailQuery->get_result()->fetch_assoc();
    $detailQuery->close();
    if ($detailMetadata) {
        $page_title = $detailMetadata['title'];
        $descriptionSource = trim((string)($detailMetadata['summary'] ?? '')) !== ''
            ? $detailMetadata['summary']
            : ($detailMetadata['description'] ?? $detailMetadata['editorial_summary'] ?? '');
        $meta_description = mb_strimwidth(trim(strip_tags((string)$descriptionSource ?: $detailMetadata['title'])), 0, 300, '...');
        $og_type = 'article';
        $og_image = '/images/background/page-title-3.jpg';
        if (!empty($detailMetadata['cover_image'])) {
            $coverImagePath = 'uploads/journals/covers/' . basename($detailMetadata['cover_image']);
            if (is_file(dirname(__DIR__) . '/' . $coverImagePath)) {
                $og_image = '/' . $coverImagePath;
            }
        }
        $canonical_url = rtrim(getenv('PSA_SITE_URL') ?: 'https://malawipsa.org', '/') . '/journal-view?id=' . $journalId;
    }
}
if ($pageSlug !== '' && in_array($detailType, ['news-details.php', 'event-details.php', 'journal-view.php'], true)) {
    $detailQuery = null;
    if ($detailType === 'news-details.php') {
        $detailQuery = $conn->prepare("SELECT title, summary, content, image, date_posted, created_at FROM news WHERE slug = ? AND status = 'published' LIMIT 1");
    } elseif ($detailType === 'event-details.php') {
        $detailQuery = $conn->prepare('SELECT title, description AS summary, image, start_date AS created_at FROM events WHERE slug = ? LIMIT 1');
    } else {
        $journalCoverCheck = $conn->query("SHOW COLUMNS FROM journals LIKE 'cover_image'");
        $journalHasCover = $journalCoverCheck && $journalCoverCheck->num_rows > 0;
        $journalFields = 'title, summary, description, editorial_summary' . ($journalHasCover ? ', cover_image' : '');
        $detailQuery = $conn->prepare('SELECT ' . $journalFields . ' FROM journals WHERE slug = ? LIMIT 1');
    }

    $detailQuery->bind_param('s', $pageSlug);
    $detailQuery->execute();
    $detailMetadata = $detailQuery->get_result()->fetch_assoc();
    $detailQuery->close();

    if ($detailMetadata) {
        $og_type = 'article';
        $page_title = $detailMetadata['title'];
        $descriptionSource = $detailMetadata['summary'] ?? '';
        if (trim((string)$descriptionSource) === '') {
            $descriptionSource = $detailMetadata['description'] ?? $detailMetadata['editorial_summary'] ?? $detailMetadata['content'] ?? '';
        }
        $meta_description = trim(strip_tags((string)$descriptionSource));
        if ($meta_description === '') {
            $meta_description = $detailMetadata['title'];
        }
        $meta_description = mb_strimwidth($meta_description, 0, 300, '...');

        $detailImage = trim((string)($detailMetadata['image'] ?? $detailMetadata['cover_image'] ?? ''));
        if ($detailType === 'event-details.php' && $detailImage !== '' && strpos($detailImage, 'uploads/') !== 0) {
            $eventDate = $detailMetadata['created_at'] ?? '';
            $detailImage = 'uploads/events/' . date('Y/m', strtotime($eventDate)) . '/' . basename($detailImage);
        } elseif ($detailType === 'news-details.php' && $detailImage !== '' && strpos($detailImage, 'uploads/') !== 0) {
            $newsDate = $detailMetadata['date_posted'] ?? $detailMetadata['created_at'] ?? '';
            $detailImage = 'uploads/news/' . date('Y/m', strtotime($newsDate)) . '/' . basename($detailImage);
        } elseif ($detailType === 'journal-view.php' && $detailImage !== '') {
            $detailImage = 'uploads/journals/covers/' . basename($detailImage);
        }

        if ($detailImage !== '' && is_file(dirname(__DIR__) . '/' . $detailImage)) {
            $og_image = '/' . ltrim($detailImage, '/');
        } elseif ($detailType === 'journal-view.php') {
            $og_image = '/images/background/page-title-3.jpg';
        }

        $cleanPath = $detailType === 'news-details.php'
            ? '/news/' . rawurlencode($pageSlug)
            : ($detailType === 'event-details.php' ? '/events/' . rawurlencode($pageSlug) : '/journals/' . rawurlencode($pageSlug));
        $canonical_url = rtrim(getenv('PSA_SITE_URL') ?: 'https://malawipsa.org', '/') . $cleanPath;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/meta.php'; ?>

    <!--Adsense-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4801370358631853" crossorigin="anonymous"></script>

  <base href="/">

  
  <!-- mobile responsive meta -->
  
  <!-- Bootstrap -->
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/magnific-popup/magnific-popup.css">
  <link rel="stylesheet" href="plugins/slick/slick.css">
  <link rel="stylesheet" href="plugins/slick/slick-theme.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="plugins/animate/animate.css">
  <link rel="stylesheet" href="plugins/aos/aos.css">
  <link rel="stylesheet" href="plugins/swiper/swiper.min.css">
  <link href="css/style.css" rel="stylesheet">

  <!--Favicon-->
  <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
  <link rel="icon" href="images/favicon.png" type="image/x-icon">
  
 <style>

/* ===== TOP HEADER ===== */
.top-header {
  background: #123b2a;
  color: #fff;
  padding: 6px 0;
}

/* FLEX CONTAINER */
.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ===== TICKER ===== */
.news-ticker {
  flex: 1;
  overflow: hidden;
  min-width: 0;
  margin-right: 15px;
  position: relative;
}

/* Fade masks on left and right edges */
.news-ticker::before,
.news-ticker::after {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  width: 60px;
  z-index: 2;
  pointer-events: none;
}

.news-ticker::before {
  left: 0;
  background: linear-gradient(to right, #123b2a 0%, transparent 100%);
}

.news-ticker::after {
  right: 0;
  background: linear-gradient(to left, #123b2a 0%, transparent 100%);
}

.ticker-wrap {
  overflow: hidden;
  height: 28px;
  position: relative;
}

.ticker {
  white-space: nowrap;
  display: flex;
  width: max-content;
  align-items: center;
  min-height: 28px;
  animation: ticker-scroll 32s linear infinite;
}

.ticker a {
  color: #fff;
  padding: 0 42px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: .03em;
}

.ticker a:hover {
  color: #f9c74f;
  text-decoration: underline;
}

/* Pause on hover and keyboard focus */
.news-ticker:hover .ticker,
.news-ticker:focus-within .ticker {
  animation-play-state: paused;
}

/* ===== SOCIAL ICONS ===== */
.social-icons {
  flex-shrink: 0;
  margin: 0;
}

.social-icons li {
  border: none !important;
}

.social-icons a {
  color: #fff;
  font-size: 16px;
  margin-left: 12px;
  display: inline-block;
  transition: all 0.3s ease;
}

.social-icons a:hover {
  color: #f9c74f;
  transform: translateY(-3px) scale(1.1);
}

/* ===== ANIMATION ===== */
@keyframes ticker-scroll {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-50%); }
}

@media (prefers-reduced-motion: reduce) {
  .ticker { animation: none; }
}

/* ===== MOBILE FIX ===== */
@media (max-width: 768px) {

  .top-bar {
    flex-wrap: wrap;
  }

  .social-icons {
    order: 1;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 0;
  }

  .social-icons li {
    margin: 0;
  }

  .social-icons a {
    margin: 0;
    font-size: 16px;
  }

  .news-ticker {
    order: 2;
    width: 100%;
    margin-top: 6px;
  }

  .ticker a {
    padding: 0 18px;
  }

}

</style>

</head>

<body>

<header>

<!-- ===== TOP HEADER ===== -->
<div class="top-header">
  <div class="container top-bar">

    <!-- NEWS TICKER (LEFT) -->
    <div class="news-ticker">
      <div class="ticker-wrap">
        <div class="ticker">
          <?php
            $announcementMessage = !empty($announcement['message'])
                ? $announcement['message']
                : '2026 PSA Biennial Conference: follow the latest news and announcements.';
            $announcementLink = trim($announcement['link_url'] ?? '');
            $announcementScheme = strtolower((string)parse_url($announcementLink, PHP_URL_SCHEME));
            $safeAnnouncementLink = ($announcementLink !== '' && strpos($announcementLink, '//') !== 0 && in_array($announcementScheme, ['', 'http', 'https'], true))
                ? $announcementLink
                : '';
          ?>
          <span class="ticker-item">
            <?php if ($safeAnnouncementLink !== ''): ?>
              <a href="<?= htmlspecialchars($safeAnnouncementLink, ENT_QUOTES, 'UTF-8') ?>"<?= $announcementScheme !== '' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                <span aria-hidden="true">●</span> <?= htmlspecialchars($announcementMessage, ENT_QUOTES, 'UTF-8') ?>
              </a>
            <?php else: ?>
              <span class="ticker-message"><span aria-hidden="true">●</span> <?= htmlspecialchars($announcementMessage, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </span>
          <span class="ticker-item" aria-hidden="true">
            <?php if ($safeAnnouncementLink !== ''): ?>
              <a href="<?= htmlspecialchars($safeAnnouncementLink, ENT_QUOTES, 'UTF-8') ?>" tabindex="-1">
                <span aria-hidden="true">●</span> <?= htmlspecialchars($announcementMessage, ENT_QUOTES, 'UTF-8') ?>
              </a>
            <?php else: ?>
              <span class="ticker-message"><span aria-hidden="true">●</span> <?= htmlspecialchars($announcementMessage, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- SOCIAL ICONS (RIGHT) -->
    <ul class="social-icons list-inline mb-0">
      <li class="list-inline-item">
        <a href="https://facebook.com/psamw" target="_blank" rel="noopener noreferrer">
          <i class="ti-facebook"></i>
        </a>
      </li>

      <li class="list-inline-item">
        <a href="https://x.com/psa_mw" target="_blank" rel="noopener noreferrer">
          <i class="ti-twitter"></i>
        </a>
      </li>

      <li class="list-inline-item">
        <a href="https://instagram.com/malawipsa" target="_blank" rel="noopener noreferrer">
          <i class="ti-instagram"></i>
        </a>
      </li>

      <li class="list-inline-item">
        <a href="https://linkedin.com/company/political-science-association-mw" target="_blank" rel="noopener noreferrer">
          <i class="ti-linkedin"></i>
        </a>
      </li>

      <li class="list-inline-item">
        <a href="https://youtube.com/@PoliticalScienceAssociation-26" target="_blank" rel="noopener noreferrer">
          <i class="ti-youtube"></i>
        </a>
      </li>
    </ul>

  </div>
</div>
    <!-- nav bar -->
    <div class="navigation">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand psa-brand" href="/" aria-label="Malawi Political Science Association home">
                    <img src="images/logo.png" alt="Malawi Political Science Association logo">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown">
                            <a class="nav-link" href="psa.php" role="" data-toggle="" aria-haspopup="true"
                                aria-expanded="false">
                                Home
                            </a>
                  
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                About Us
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/about">About PSA</a>
                                <a class="dropdown-item" href="/psa-history">History of PSA</a>
                                <a class="dropdown-item" href="/conststa">Constitution and <br> Mission Statement</a>
                                <a class="dropdown-item" href="/team">Organisation <br> Structure</a>
                                <a class="dropdown-item" href="/afpart">Affiliates <br> and Partners</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                PSA Membership
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/members">PSA Members</a>
                                <a class="dropdown-item" href="/membership">Become a Member</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle"  role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                News & Events
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/psa-news">News</a>
                                <a class="dropdown-item" href="/psa-events">Events</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                              Research  
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="">Overview</a>
                                <a class="dropdown-item" href="">Research</a>
                                <a class="dropdown-item" href="">Consultancy</a>
                                <a class="dropdown-item" href="">Training</a>                              
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                Publications
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/journal">Journal Articles</a>
                                <a class="dropdown-item" href="">Books</a>
                                <a class="dropdown-item" href="">Policy Papers</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/contact">Contacts</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>
