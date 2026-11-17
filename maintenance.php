<?php
$page_title = 'Page Unavailable | Malawi PSA';
$meta_description = 'This page is currently unavailable. Explore the Malawi Political Science Association website or return to the homepage.';
include 'includes/header.php';
renderPageTitle('Page Unavailable', [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Page Unavailable', 'url' => ''],
], 'images/background/page-title-2.jpg', 'This destination is not available yet.');
?>

<style>
    .unavailable-card {
        max-width: 760px;
        margin: 0 auto;
        padding: 3rem 2rem;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .08);
    }

    .unavailable-mark {
        display: inline-flex;
        width: 7rem;
        height: 7rem;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        border: 3px solid #117733;
        border-radius: 50%;
        color: #117733;
        font-size: 2rem;
        animation: unavailable-float 2.8s ease-in-out infinite;
    }

    @keyframes unavailable-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .unavailable-mark { animation: none; }
    }
</style>

<main class="section">
    <div class="container text-center">
        <div class="unavailable-card">
            <div class="unavailable-mark" aria-hidden="true">
                <i class="ti-info-alt"></i>
            </div>
            <h2 class="section-title">This page is not available yet</h2>
            <p class="mb-4">The link you followed does not have a published destination. Please check back later or continue exploring our website.</p>
            <a class="btn btn-primary" href="<?= htmlspecialchars(sitePath(''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Return to Home</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
