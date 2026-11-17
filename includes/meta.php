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

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$cleanPath = preg_replace('/\.php$/i', '', $requestPath);
$cleanPath = rtrim($cleanPath, '/') ?: '/';
if (in_array($cleanPath, ['/index', '/psa'], true)) {
    $cleanPath = '/';
}
$pageMetadata = [
    '/' => [$site_title, $site_description],
    '/about' => ['About the Malawi Political Science Association', 'Learn about the Malawi Political Science Association, its mission, membership and contribution to political science.'],
    '/afpart' => ['PSA Affiliates and Partners', 'Meet the organisations and institutions affiliated with and supporting the Malawi Political Science Association.'],
    '/conststa' => ['PSA Constitution and Mission', 'Read about the Malawi Political Science Association constitution, purpose and mission.'],
    '/contact' => ['Contact the Malawi Political Science Association', 'Contact PSA Malawi for membership, events, publications, partnerships and general enquiries.'],
    '/featured-news' => ['Featured PSA News', 'Statements, updates and featured stories from the Malawi Political Science Association.'],
    '/members' => ['PSA Members', 'Explore the professional and academic members of the Malawi Political Science Association.'],
    '/membership' => ['Become a PSA Member', 'Learn about membership in the Malawi Political Science Association and how to get involved.'],
    '/journal' => ['PSA Journals and Publications', 'Browse journals and research articles published by the Malawi Political Science Association.'],
    '/journal-view' => ['PSA Journal', 'Read a journal issue and explore its published articles.'],
    '/psa-history' => ['History of PSA Malawi', 'Discover the history and development of the Malawi Political Science Association.'],
    '/psa-news' => ['PSA News and Events', 'Read the latest political science news, announcements and events from PSA Malawi.'],
    '/psa-events' => ['PSA Events', 'Find conferences, seminars, workshops and other events hosted by PSA Malawi.'],
    '/team' => ['PSA Leadership and Team', 'Meet the leadership and team serving the Malawi Political Science Association.'],
];
$page_title       = $page_title       ?? ($pageMetadata[$cleanPath][0] ?? $site_title);
$meta_description = $meta_description ?? ($pageMetadata[$cleanPath][1] ?? $site_description);
$meta_keywords    = $meta_keywords    ?? '';
$og_image = $og_image ?? '/images/logo.png';
$og_type = $og_type ?? 'website';
$canonical_url = $canonical_url ?? $site_base_url . $cleanPath . (parse_url($requestUri, PHP_URL_QUERY) ? '?' . parse_url($requestUri, PHP_URL_QUERY) : '');
$ogImagePath = implode('/', array_map('rawurlencode', explode('/', ltrim($og_image, '/'))));
$og_image_url = preg_match('/^https?:\/\//i', $og_image)
    ? $og_image
    : $site_base_url . '/' . $ogImagePath;
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
<meta property="og:type"        content="<?= htmlspecialchars($og_type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<meta property="og:site_name"   content="<?= htmlspecialchars($site_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<meta property="og:title"       content="<?= $escapedTitle ?>">
<meta property="og:description" content="<?= $escapedDescription ?>">
<meta property="og:url"         content="<?= $escapedCanonical ?>">
<meta property="og:image"       content="<?= $escapedImage ?>">
<meta property="og:image:alt"   content="<?= $escapedTitle ?>">
<meta property="og:image:secure_url" content="<?= $escapedImage ?>">
<meta property="og:image:type"  content="<?= str_ends_with(strtolower(parse_url($og_image_url, PHP_URL_PATH) ?: ''), '.png') ? 'image/png' : (str_ends_with(strtolower(parse_url($og_image_url, PHP_URL_PATH) ?: ''), '.webp') ? 'image/webp' : 'image/jpeg') ?>">
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:title"      content="<?= $escapedTitle ?>">
<meta name="twitter:description" content="<?= $escapedDescription ?>">
<meta name="twitter:image"      content="<?= $escapedImage ?>">
<link rel="canonical" href="<?= $escapedCanonical ?>">

<!-- Favicon -->
<link rel="icon" href="images/favicon.png" type="image/png">

<?php // Page‑specific additional tags
if (!empty($extra_meta)) echo $extra_meta; ?>
