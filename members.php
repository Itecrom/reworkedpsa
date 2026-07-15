<?php
// members.php
include 'includes/db.php';
include 'includes/header.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = "";
$params = [];
$param_types = "";

if ($search !== '') {
    $where = "WHERE name LIKE ? OR qualification LIKE ? OR employer LIKE ? OR expertise LIKE ? OR contacts LIKE ?";
    $like_search = "%$search%";
    $params = [$like_search, $like_search, $like_search, $like_search, $like_search];
    $param_types = "sssss";
}

// Count total
$count_sql = "SELECT COUNT(*) FROM psa_members $where";
$stmt = $conn->prepare($count_sql);
if ($where !== "") $stmt->bind_param($param_types, ...$params);
$stmt->execute();
$stmt->bind_result($total_members);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_members / $limit);

// Fetch members
$sql = "SELECT * FROM psa_members $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($where === "") {
    $stmt->bind_param("ii", $limit, $offset);
} else {
    $param_types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
/* PREMIUM MEMBER DIRECTORY */

.member-card-premium {
  display: flex;
  gap: 15px;
  padding: 14px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #eaeaea;
  transition: all 0.3s ease;
  height: 100%;
  position: relative;
}

.member-card-premium:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  border-color: #dcdcdc;
}

/* LEFT IMAGE */
.member-left img {
  width: 75px;
  height: 75px;
  object-fit: cover;
  border-radius: 12px;
  border: 2px solid #f5f5f5;
}

/* RIGHT CONTENT */
.member-right {
  flex: 1;
  overflow: hidden;
}

/* NAME */
.member-name {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 6px;
  color: #1a1a1a;
}

/* META ROWS */
.member-meta {
  font-size: 13px;
  color: #555;
  margin-bottom: 3px;
}

.member-meta i {
  margin-right: 6px;
  color: #0d6efd;
}

/* CONTACT */
.member-contact {
  margin-top: 6px;
  font-size: 12.5px;
  color: #777;
  line-height: 1.4;
}

/* GRID BALANCE */
.row {
  margin-left: -10px;
  margin-right: -10px;
}

.col-lg-6, .col-md-6 {
  padding-left: 10px;
  padding-right: 10px;
}

/* SEARCH BAR POLISH */
.search-bar input {
  border-radius: 8px;
  height: 45px;
}

.search-bar .btn {
  border-radius: 8px;
}

/* PAGINATION POLISH */
.pagination {
  justify-content: center;
}

.pagination .page-link {
  border-radius: 8px;
  margin: 0 3px;
  border: none;
  color: #555;
}

.pagination .active .page-link {
  background: #0d6efd;
  color: #fff;
}

/* MOBILE OPTIMIZATION */
@media (max-width: 576px) {
  .member-card-premium {
    padding: 12px;
  }

  .member-left img {
    width: 60px;
    height: 60px;
  }

  .member-name {
    font-size: 14px;
  }
}
</style>
<section class="page-title overlay" style="background-image: url(images/background/page-title.jpg); padding: 60px 0;">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center text-white">
        <h2 class="font-weight-bold">PSA Members</h2>
      </div>
    </div>
  </div>
</section>

<section class="container mt-4">
  <form method="GET" class="search-bar">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Search by name, qualification, employer, etc." value="<?= htmlspecialchars($search) ?>">
      <div class="input-group-append">
        <button class="btn btn-primary" type="submit">Search</button>
      </div>
    </div>
  </form>
  
  
  
  <?php if ($result->num_rows > 0): ?>
    <div class="row">
      <?php while ($member = $result->fetch_assoc()): ?>
<div class="col-lg-6 col-md-6 mb-3">
  <div class="member-card-premium">

    <div class="member-left">
      <img src="<?= $member['image'] && file_exists('uploads/members/' . $member['image']) ? 'uploads/members/' . htmlspecialchars($member['image']) : 'images/default-user.png' ?>" alt="<?= htmlspecialchars($member['name']) ?>">
    </div>

    <div class="member-right">
      <h6 class="member-name"><?= htmlspecialchars($member['name']) ?></h6>

      <div class="member-meta">
        <span><i class="ti-medall"></i> <?= htmlspecialchars($member['qualification']) ?></span>
      </div>

      <div class="member-meta">
        <span><i class="ti-briefcase"></i> <?= htmlspecialchars($member['employer']) ?></span>
      </div>

      <div class="member-meta">
        <span><i class="ti-light-bulb"></i> <?= htmlspecialchars($member['expertise']) ?></span>
      </div>

      <div class="member-contact">
        <?= nl2br(htmlspecialchars($member['contacts'])) ?>
      </div>
    </div>

  </div>
</div>
      <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <nav class="mt-4">
      <ul class="pagination">
        <?php if ($page > 1): ?>
          <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Prev</a></li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
          <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-4">No members found.</div>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
