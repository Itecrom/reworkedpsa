<?php
// includes/meta.php
// ----------------------BY LEONARD JJRA MHONE--------------------
// Shared <meta> & <title> tags for the whole site.
// Before including this file you can (optionally) define:
//   $page_title        – page‑specific title
//   $meta_description  – page‑specific description
//   $meta_keywords     – comma‑separated keywords (optional)
//   $extra_meta        – any additional <meta>/<link> markup (optional)
// -------------------------------------------------------------

$site_title = 'Malawi Political Science Association';
$site_description = 'The Malawi Political Science Association promotes political science research, teaching, public policy dialogue and professional exchange.';
$site_base_url = rtrim(getenv('PSA_SITE_URL') ?: 'https://malawipsa.org', '/');

$page_title       = $page_title       ?? $site_title;
$meta_description = $meta_description ?? $site_description;
$meta_keywords    = $meta_keywords    ?? '';
$og_image = $og_image ?? '/images/logo.png';
$canonical_url = $canonical_url ?? $site_base_url . preg_replace('/\.php(?=($|\?))/', '', $_SERVER['REQUEST_URI'] ?? '/');
$og_image_url = preg_match('/^https?:\/\//i', $og_image)
    ? $og_image
    : $site_base_url . '/' . ltrim($og_image, '/');
$escapedTitle = htmlspecialchars($page_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$escapedDescription = htmlspecialchars($meta_description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$escapedCanonical = htmlspecialchars($canonical_url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$escapedImage = htmlspecialchars($og_image_url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<meta charset="utf-8">
<title><?= $escapedTitle ?></title>

<!-- mobile & scaling -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<!-- SEO -->
<meta name="description" content="<?= $escapedDescription ?>">
<?php if ($meta_keywords): ?>
<meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
<?php endif; ?>

<!-- Open Graph / Social -->
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?= htmlspecialchars($site_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<meta property="og:title"       content="<?= $escapedTitle ?>">
<meta property="og:description" content="<?= $escapedDescription ?>">
<meta property="og:url"         content="<?= $escapedCanonical ?>">
<meta property="og:image"       content="<?= $escapedImage ?>">
<meta property="og:image:alt"   content="<?= $escapedTitle ?>">
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:title"      content="<?= $escapedTitle ?>">
<meta name="twitter:description" content="<?= $escapedDescription ?>">
<meta name="twitter:image"      content="<?= $escapedImage ?>">
<link rel="canonical" href="<?= $escapedCanonical ?>">

<!-- Favicon -->
<link rel="icon" href="images/favicon.png" type="image/png">

<?php // Page‑specific additional tags
if (!empty($extra_meta)) echo $extra_meta; ?>
