<!-- footer -->
<footer class="site-footer bg-secondary">
  <div class="py-100 border-bottom" style="border-color: rgba(255,255,255,.14) !important">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="mb-5 mb-md-0 text-center text-md-left">
            <!-- logo -->
            <a href="<?= htmlspecialchars(sitePath(''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" aria-label="Malawi Political Science Association home">
              <img class="footer-logo mb-30" src="images/logo-footer.png" alt="Malawi Political Science Association">
            </a>
            <p class="text-white mb-30">We have diverse membership of academics and professionals in the fields of political science, 
				political philosophy, political and economic governance, public administration, international relations, 
				public law, public policy analysis, human rights and related disciplines relevant to the study, analysis and 
				understanding of politics.</p>
            <!-- social icon -->
            <ul class="list-inline">
              <li class="list-inline-item">
                <a class="social-icon-outline" href="https://facebook.com/psamw" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                  <i class="ti-facebook"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a class="social-icon-outline" href="https://x.com/psa_mw" target="_blank" rel="noopener noreferrer" aria-label="X">
                  <i class="ti-twitter-alt"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a class="social-icon-outline" href="https://instagram.com/malawipsa" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                  <i class="ti-instagram"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a class="social-icon-outline" href="https://linkedin.com/company/political-science-association-mw" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                  <i class="ti-linkedin"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <!-- footer links -->
        <div class="col-lg-2 col-md-4 col-6">
          <h4 class="text-white mb-4">What we do</h4>
          <ul class="footer-links">
            <li>
              <a href="<?= htmlspecialchars(sitePath('journal'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Journals</a>
            </li>
            <li>
              <a href="">Publications</a>
            </li>
            <li>
              <a href="">Trainings</a>
            </li>
            <li>
              <a href="">Academics</a>
            </li>
            <li>
              <a href="<?= htmlspecialchars(sitePath('psa-news'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">News</a>
            </li>
          </ul>
        </div>
        <!-- footer links -->
        <div class="col-lg-2 col-md-4 col-6">
          <h4 class="text-white mb-4">Quick Links</h4>
          <ul class="footer-links">
            <li>
              <a href="<?= htmlspecialchars(sitePath('psa-history'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Our History</a>
            </li>
            <li>
              <a href="<?= htmlspecialchars(sitePath('about'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">About Us</a>
            </li>
            <li>
              <a href="<?= htmlspecialchars(sitePath('contact'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Contact Us</a>
            </li>
            <li>
              <a href="<?= htmlspecialchars(sitePath('afpart'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Partners</a>
            </li>
            <li>
              <a href="#">Privacy Policy</a>
            </li>
          </ul>
        </div>
        <!-- subscribe form -->
        <div class="col-lg-3 offset-lg-1">
          <div class="mt-5 mt-lg-0 text-center text-md-left">
            <h4 class="mb-4 text-white">Subscribe Us</h4>
            <p class="text-white mb-4">Receive our Newsletters, Opportunities, Events and latest news </p>
            <form action="subscribe.php" method="POST" class="newsletter-form">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(publicFormToken('newsletter'), ENT_QUOTES, 'UTF-8') ?>">
              <div class="form-honeypot" aria-hidden="true">
                <label for="newsletter-website">Leave this field empty</label>
                <input id="newsletter-website" type="text" name="website" tabindex="-1" autocomplete="off">
              </div>
              <div class="form-group">
                <label class="sr-only" for="newsletter-name">Your name</label>
                <input id="newsletter-name" type="text" name="name" class="form-control" placeholder="Your Name" maxlength="255" required>
              </div>
              <div class="form-group">
                <label class="sr-only" for="newsletter-email">Your email</label>
                <input id="newsletter-email" type="email" name="email" class="form-control" placeholder="Your Email" maxlength="254" required>
              </div>
              <?= publicFormCaptchaField('newsletter', 'newsletter-captcha') ?>
              <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- copyright -->
  <div class="pt-4 pb-3 position-relative">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-md-5">
          <p class="text-white text-center text-md-left">
            <span class="text-primary">Malawi PSA</span> &copy; <?php echo date('Y'); ?> All Right Reserved |
            Developed by <a href="https://leonardjjmhone.gt.tc/?i=1" target="_blank" rel="noopener noreferrer" class="text-primary">Leonard Mhone</a>,
            Powered by <a href="https://itecictsolutions.com" target="_blank" rel="noopener noreferrer" class="text-primary">ITEC</a>
          </p>
        </div>
        <div class="col-lg-6 col-md-7">
          <ul class="list-inline text-center text-md-right">
            <li class="list-inline-item mx-lg-3 my-lg-0 mx-2 my-2">
              <a class="font-secondary text-white" href="#">Register</a>
            </li>
            <li class="list-inline-item mx-lg-3 my-lg-0 mx-2 my-2">
              <a class="font-secondary text-white" href="#">Legal</a>
            </li>
            <li class="list-inline-item mx-lg-3 my-lg-0 mx-2 my-2">
              <a class="font-secondary text-white" href="">Faq</a>
            </li>
            <li class="list-inline-item ml-lg-3 my-lg-0 ml-2 my-2 ml-0">
              <a class="font-secondary text-white" href="#">Terms &amp; Conditions</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- back to top -->
    <button class="back-to-top">
      <i class="ti-angle-up"></i>
    </button>
  </div>
</footer>
<!-- /footer --> 

<!-- jQuery -->
<script src="plugins/jQuery/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="plugins/bootstrap/bootstrap.min.js"></script>
<!-- magnific popup -->
<script src="plugins/magnific-popup/jquery.magnific.popup.min.js"></script>
<!-- slick slider -->
<script src="plugins/slick/slick.min.js"></script>
<!-- filter -->
<script src="plugins/filterizr/jquery.filterizr.min.js"></script>
<!-- Google Map -->
<script src="plugins/google-map/gmap.js"></script>
<!-- Syo Timer -->
<script src="plugins/syotimer/jquery.syotimer.js"></script>
<!-- aos -->
<script src="plugins/aos/aos.js"></script>
<!-- swiper -->
<script src="plugins/swiper/swiper.min.js"></script>
<!-- Main Script -->
<script src="js/script.js"></script>
</body>
</html>
