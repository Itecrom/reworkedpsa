<?php
include 'includes/db.php';
include 'includes/header.php';

/* =========================
   SEARCH
========================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
SELECT *
FROM events
WHERE title LIKE ?
OR description LIKE ?
OR organizer LIKE ?
ORDER BY featured DESC, start_date DESC
";

$searchTerm = "%{$search}%";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $searchTerm,
    $searchTerm,
    $searchTerm
);

$stmt->execute();

$result = $stmt->get_result();

/* =========================
   LATEST EVENTS
========================= */

$latestEvents = $conn->query("
SELECT title, slug, image, start_date
FROM events
ORDER BY created_at DESC
LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>PSA Events</title>

<link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<style>

/* =========================
   PAGE TITLE
========================= */

.page-title {
    position: relative;
    padding: 120px 0;
    background-size: cover;
    background-position: center;
}

.page-title::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.6);
}

.page-title .container {
    position: relative;
    z-index: 2;
}

/* =========================
   EVENT CARD
========================= */

.event-card {

    border-radius: 16px;

    overflow: hidden;

    background: #fff;

    box-shadow: 0 5px 20px rgba(0,0,0,.08);

    transition: all .35s ease;

    transform: translateY(0);

    height: 100%;

    display: flex;

    flex-direction: column;
}

.event-card:hover {

    transform: translateY(-4px);

    box-shadow: 0 15px 34px rgba(30,52,39,.13);
}

.event-image-wrapper {

    position: relative;

    background: #f5f5f5;

    display: flex;

    align-items: center;

    justify-content: center;

    min-height: 230px;

    max-height: 400px;

    overflow: hidden;
}

.event-image {

    width: 100%;

    max-height: 400px;

    object-fit: cover;

    transition: transform .5s ease;

    padding: 0;
}

.event-card:hover .event-image {

    transform: scale(1.03);
}

/* =========================
   FEATURED BADGE
========================= */

.featured-badge {

    position: absolute;

    top: 15px;

    right: 15px;

    background: #dc3545;

    color: #fff;

    padding: 6px 12px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: bold;

    z-index: 5;
}

/* =========================
   SIDEBAR
========================= */

.sidebar-box {

    background: #fff;

    padding: 25px;

    border-radius: 14px;

    margin-bottom: 30px;

    box-shadow: 0 5px 20px rgba(0,0,0,.08);
}

.latest-event {

    display: flex;

    gap: 12px;

    margin-bottom: 20px;

    align-items: center;
}

.latest-event img {

    width: 80px;

    height: 70px;

    object-fit: contain;

    border-radius: 10px;

    background: #f5f5f5;

    padding: 4px;
}

/* =========================
   BUTTON
========================= */

.btn-event {

    border-radius: 30px;

    padding: 10px 22px;

    transition: .3s;
}

.btn-event:hover {

    transform: scale(1.05);
}

/* =========================
   SECTION TITLE
========================= */

.section-heading {

    position: relative;

    margin-bottom: 40px;
}

.section-heading::after {

    content: '';

    width: 90px;

    height: 4px;

    background: #0d6efd;

    position: absolute;

    left: 0;

    bottom: -12px;

    border-radius: 30px;
}

</style>

</head>

<body>

<!-- =========================
     BANNER
========================= -->

<section class="page-title overlay"
style="background-image:url('images/background/page-title.jpg');">

<div class="container text-center">

<h1 class="text-white font-weight-bold mb-3">
PSA Events
</h1>

<p class="text-white lead">

PSA Events bring together scholars, professionals,
researchers and policymakers through conferences,
seminars, workshops and governance discussions
that shape Malawi and Africa.

</p>

</div>

</section>

<!-- =========================
     EVENTS SECTION
========================= -->

<section class="section bg-light">

<div class="container">

<div class="row">

<!-- =========================
     EVENTS LIST
========================= -->

<div class="col-lg-8">

<h2 class="section-heading">
Upcoming & Past Events
</h2>

<div class="row">

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<?php

$status = (
strtotime($row['end_date']) < time()
) ? 'Past Event' : 'Upcoming Event';

?>

<div class="col-md-6 mb-4 d-flex">

<div class="event-card w-100">

<div class="event-image-wrapper">

<?php if($row['featured']): ?>

<div class="featured-badge">
Featured
</div>

<?php endif; ?>

<a href="event-details.php?slug=<?= urlencode($row['slug']) ?>">

<?php
  $eventImgPath = 'images/events/event-1.jpg';
  if (!empty($row['image'])) {
    $year = date('Y', strtotime($row['start_date']));
    $month = date('m', strtotime($row['start_date']));
    $eventImgPath = "uploads/events/$year/$month/" . htmlspecialchars($row['image']);
  }
?>
<img src="<?= $eventImgPath ?>" class="event-image"
alt="<?= htmlspecialchars($row['title']) ?>"
>

</a>

</div>

<div class="p-4 d-flex flex-column h-100">

<span class="text-primary font-weight-bold mb-2">

<?= $status ?>

</span>

<h4 class="mb-3">

<a
href="event-details.php?slug=<?= urlencode($row['slug']) ?>"
style="text-decoration:none; color:inherit;"
>

<?= htmlspecialchars($row['title']) ?>

</a>

</h4>

<p class="text-muted mb-2">

<i class="ti-calendar mr-2"></i>

<?= date('d M Y', strtotime($row['start_date'])) ?>

</p>

<p class="text-muted mb-3">

<i class="ti-location-pin mr-2"></i>

<?= htmlspecialchars($row['venue']) ?>

</p>

<p>

<?= substr(strip_tags($row['description']), 0, 120) ?>...

</p>

<div class="mt-auto">

<a
href="event-details.php?slug=<?= urlencode($row['slug']) ?>"
class="btn btn-primary btn-event"
>

View Event

</a>

</div>

</div>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="col-12">

<div class="alert alert-info">

No events found.

</div>

</div>

<?php endif; ?>

</div>

</div>

<!-- =========================
     SIDEBAR
========================= -->

<div class="col-lg-4">

<!-- SEARCH -->
<div class="sidebar-box">

<h4 class="mb-3">
Search Events
</h4>

<form method="GET">

<input
type="text"
name="search"
class="form-control"
placeholder="Search events..."
value="<?= htmlspecialchars($search) ?>"
>

<button class="btn btn-primary btn-block mt-3">
Search
</button>

</form>

</div>

<!-- LATEST EVENTS -->
<div class="sidebar-box">

<h4 class="mb-4">
Latest Events
</h4>

<?php while($latest = $latestEvents->fetch_assoc()): ?>

<div class="latest-event">

<a href="event-details.php?slug=<?= urlencode($latest['slug']) ?>">

<?php
  $latestEventImg = 'images/events/event-1.jpg';
  if (!empty($latest['image'])) {
    $year = date('Y', strtotime($latest['start_date']));
    $month = date('m', strtotime($latest['start_date']));
    $latestEventImg = "uploads/events/$year/$month/" . htmlspecialchars($latest['image']);
  }
?>
<img src="<?= $latestEventImg ?>" alt=""
>

</a>

<div>

<a
href="event-details.php?slug=<?= urlencode($latest['slug']) ?>"
class="font-weight-bold d-block"
style="text-decoration:none;"
>

<?= htmlspecialchars($latest['title']) ?>

</a>

<small class="text-muted">

<?= date('d M Y', strtotime($latest['start_date'])) ?>

</small>

</div>

</div>

<?php endwhile; ?>

</div>

<!-- PAST EVENTS -->
<div class="sidebar-box">

<h4 class="mb-4">
Past Events
</h4>

<?php

$pastEvents = $conn->query("
SELECT title, slug
FROM events
WHERE status='past'
ORDER BY start_date DESC
LIMIT 6
");

?>

<?php while($past = $pastEvents->fetch_assoc()): ?>

<p>

<a href="event-details.php?slug=<?= urlencode($past['slug']) ?>">

<?= htmlspecialchars($past['title']) ?>

</a>

</p>

<?php endwhile; ?>

</div>

</div>

</div>

</div>

</section>

<?php include 'includes/footer.php'; ?>

<script src="plugins/jQuery/jquery.min.js"></script>
<script src="plugins/bootstrap/bootstrap.min.js"></script>

</body>
</html>