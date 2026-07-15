<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    die("No event specified.");
}

$slug = trim($_GET['slug']);

$stmt = $conn->prepare("
    SELECT * FROM events
    WHERE slug = ?
    LIMIT 1
");

$stmt->bind_param("s", $slug);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No event found.");
}

$event = $result->fetch_assoc();

/* AUTO STATUS UPDATE */
$current = time();

$endTime = strtotime($event['end_date']);

if ($endTime < $current && $event['status'] != 'past') {

    $update = $conn->prepare("
        UPDATE events
        SET status='past'
        WHERE id=?
    ");

    $update->bind_param("i", $event['id']);
    $update->execute();

    $event['status'] = 'past';
}

/* RSVP */
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rsvp_submit'])) {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $attendees = (int)$_POST['attendees'];

    $rsvp = $conn->prepare("
        INSERT INTO event_rsvps
        (event_id, full_name, email, phone, attendees)
        VALUES (?, ?, ?, ?, ?)
    ");

    $rsvp->bind_param(
        "isssi",
        $event['id'],
        $full_name,
        $email,
        $phone,
        $attendees
    );

    if ($rsvp->execute()) {
        $success = "RSVP submitted successfully.";
    }
}

/* GALLERY */
$gallery = $conn->prepare("
    SELECT * FROM event_gallery
    WHERE event_id=?
");

$gallery->bind_param("i", $event['id']);
$gallery->execute();

$galleryResult = $gallery->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= htmlspecialchars($event['title']) ?></title>

<link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<style>

.event-image{
    width:100%;
    height:500px;
    object-fit:cover;
}

.countdown-box{
    background:red;
    color:#fff;
    padding:20px;
    border-radius:10px;
    text-align:center;
    margin-bottom:30px;
}

.gallery-img{
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:8px;
}

.sidebar-box{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,.08);
}

</style>

</head>

<body>

<!-- TITLE -->
<section class="page-title overlay"
style="background-image:url(images/background/page-title.jpg);">

<div class="container text-center">

<h2 class="text-white font-weight-bold">
<?= htmlspecialchars($event['title']) ?>
</h2>

</div>

</section>

<section class="section bg-light">

<div class="container">

<div class="row">

<!-- MAIN -->
<div class="col-lg-8">

<div class="bg-white p-4 shadow-sm rounded">

<img src="<?= htmlspecialchars($event['image']) ?>"
class="event-image rounded mb-4">

<!-- COUNTDOWN -->
<div class="countdown-box">

<h4 class="mb-3">Event Countdown</h4>

<div id="countdown"></div>

</div>

<!-- DETAILS -->
<h3 class="mb-3">
<?= htmlspecialchars($event['title']) ?>
</h3>

<p>
<strong>Organizer:</strong>
<?= htmlspecialchars($event['organizer']) ?>
</p>

<p>
<strong>Venue:</strong>
<?= htmlspecialchars($event['venue']) ?>
</p>

<p>
<strong>Type:</strong>
<?= ucfirst($event['event_type']) ?>
</p>

<p>
<strong>Start:</strong>
<?= date('d M Y H:i', strtotime($event['start_date'])) ?>
</p>

<p>
<strong>End:</strong>
<?= date('d M Y H:i', strtotime($event['end_date'])) ?>
</p>

<?php if(!empty($event['online_link'])): ?>

<p>
<strong>Online Link:</strong>

<a href="<?= htmlspecialchars($event['online_link']) ?>"
target="_blank">
Join Meeting
</a>

</p>

<?php endif; ?>

<hr>

<div class="mb-4">
<?= $event['description'] ?>
</div>

<!-- REGISTRATION -->
<?php if(!empty($event['registration_link'])): ?>

<a href="<?= htmlspecialchars($event['registration_link']) ?>"
target="_blank"
class="btn btn-primary mb-4">
Register Now
</a>

<?php endif; ?>

<!-- BROCHURE -->
<?php if(!empty($event['brochure'])): ?>

<a href="<?= htmlspecialchars($event['brochure']) ?>"
target="_blank"
class="btn btn-danger mb-4">
Download Brochure
</a>

<?php endif; ?>

<!-- MAP -->
<?php if(!empty($event['google_map'])): ?>

<div class="mb-4">

<h4>Location</h4>

<?= $event['google_map'] ?>

</div>

<?php endif; ?>

<!-- GALLERY -->
<?php if($galleryResult->num_rows > 0): ?>

<h4 class="mb-3">Event Gallery</h4>

<div class="row">

<?php while($g = $galleryResult->fetch_assoc()): ?>

<div class="col-md-4 mb-4">

<img src="<?= htmlspecialchars($g['image']) ?>"
class="gallery-img">

</div>

<?php endwhile; ?>

</div>

<?php endif; ?>

<!-- RSVP -->
<div class="mt-5">

<h4 class="mb-3">RSVP</h4>

<?php if($success): ?>

<div class="alert alert-success">
<?= $success ?>
</div>

<?php endif; ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<input type="text"
name="full_name"
required
placeholder="Full Name"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<input type="email"
name="email"
required
placeholder="Email"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<input type="text"
name="phone"
placeholder="Phone"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<input type="number"
name="attendees"
value="1"
min="1"
class="form-control">

</div>

</div>

<button class="btn btn-success"
name="rsvp_submit">

Submit RSVP

</button>

</form>

</div>

</div>

</div>

<!-- SIDEBAR -->
<div class="col-lg-4">

<div class="sidebar-box mb-4">

<h4>Search Events</h4>

<form action="psa-events.php" method="GET">

<input type="text"
name="search"
class="form-control"
placeholder="Search events">

</form>

</div>

<div class="sidebar-box">

<h4 class="mb-3">Past Events</h4>

<?php

$past = $conn->query("
SELECT title, slug
FROM events
WHERE status='past'
ORDER BY start_date DESC
LIMIT 6
");

while($p = $past->fetch_assoc()):

?>

<p>

<a href="event-details.php?slug=<?= urlencode($p['slug']) ?>">

<?= htmlspecialchars($p['title']) ?>

</a>

</p>

<?php endwhile; ?>

</div>

</div>

</div>

</div>

</section>

<script>

const countDate = new Date(
"<?= date('Y-m-d H:i:s', strtotime($event['start_date'])) ?>"
).getTime();

const x = setInterval(function(){

const now = new Date().getTime();

const distance = countDate - now;

const days = Math.floor(distance / (1000*60*60*24));

const hours = Math.floor(
(distance % (1000*60*60*24)) / (1000*60*60)
);

const minutes = Math.floor(
(distance % (1000*60*60)) / (1000*60)
);

const seconds = Math.floor(
(distance % (1000*60)) / 1000
);

document.getElementById("countdown").innerHTML =
days + "d " +
hours + "h " +
minutes + "m " +
seconds + "s ";

if(distance < 0){

clearInterval(x);

document.getElementById("countdown").innerHTML =
"Event Started";

}

},1000);

</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>