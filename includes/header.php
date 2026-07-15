<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/meta.php'; ?>

    <!--Adsense-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4801370358631853" crossorigin="anonymous"></script>

  <meta charset="utf-8">
  <title>Malawi Political Science Association</title>

  
  <!-- mobile responsive meta -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  
  <!-- Bootstrap -->
  <link rel="stylesheet" href="../plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="../plugins/magnific-popup/magnific-popup.css">
  <link rel="stylesheet" href="../plugins/slick/slick.css">
  <link rel="stylesheet" href="../plugins/slick/slick-theme.css">
  <link rel="stylesheet" href="../plugins/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="../plugins/animate/animate.css">
  <link rel="stylesheet" href="../plugins/aos/aos.css">
  <link rel="stylesheet" href="../plugins/swiper/swiper.min.css">
  <link href="../css/style.css" rel="stylesheet">

  <!--Favicon-->
  <link rel="shortcut icon" href="../images/favicon.png" type="image/x-icon">
  <link rel="icon" href="../images/favicon.png" type="image/x-icon">
  
 <style>

/* ===== TOP HEADER ===== */
.top-header {
  background: #000;
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
  flex-grow: 1;
  overflow: hidden;
  min-width: 0; /* 🔥 critical fix */
  margin-right: 15px;
}

.ticker-wrap {
  overflow: hidden;
  height: 24px;
}

.ticker {
  white-space: nowrap;
  display: inline-block;
  animation: ticker-scroll 15s linear infinite;
}

.ticker a {
  color: #fff;
  margin-right: 60px;
  text-decoration: none;
  font-size: 14px;
}

.ticker a:hover {
  color: #f9c74f;
  text-decoration: underline;
}

/* Pause on hover */
.ticker:hover {
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
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

/* ===== MOBILE FIX ===== */
/* ===== MOBILE FIX ===== */
@media (max-width: 768px) {

  .top-bar {
    flex-wrap: wrap;
  }

  .social-icons {
    order: 1;
    width: 100%;
    display: flex;
    justify-content: center; /* center nicely */
    gap: 12px; /* 🔥 controls spacing between icons */
    padding: 0;
  }

  .social-icons li {
    margin: 0; /* remove Bootstrap spacing */
  }

  .social-icons a {
    margin: 0; /* remove your previous margin-left */
    font-size: 16px;
  }

  .news-ticker {
    order: 2;
    width: 100%;
    margin-top: 6px;
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
          <a href="https://malawipsa.org/news/call-for-papers--the-4th-biennial-conference-for-malawi-political-science-association-psa">
            📢 2026 PSA BIENNIAL CONFERENCE IS AROUND THE CORNER, ARE YOU READY?
          </a>
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
                <a class="navbar-brand" href="psa.php">
                    <img src="../images/logo.png" alt="PSA logo">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown">
                            <a class="nav-link" href="/psa.php" role="" data-toggle="" aria-haspopup="true"
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
                                <a class="dropdown-item" href="/about.php">About PSA</a>
                                <a class="dropdown-item" href="/psa-history.php">History of PSA</a>
                                <a class="dropdown-item" href="/conststa.php">Constitution and <br> Mission Statement</a>
                                <a class="dropdown-item" href="/team.php">Organisation <br> Structure</a>
                                <a class="dropdown-item" href="/afpart.php">Affiliates <br> and Partners</a>                    
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                PSA Membership
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/members.php">PSA Members</a>
                                <a class="dropdown-item" href="/membership.php">Become a Member</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle"  role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                News & Events
                            </a>
                            <div class="dropdown-menu" >
                                <a class="dropdown-item" href="/psa-news.php">News</a>
                                <a class="dropdown-item" href="/psa-events.php">Events</a>
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
                                <a class="dropdown-item" href="/journal.php">Journal Articles</a>
                                <a class="dropdown-item" href="">Books</a>
                                <a class="dropdown-item" href="">Policy Papers</a>                          
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/contact.php">Contacts</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>
