<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';

$events = $conn->query("SELECT * FROM events ORDER BY start_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Events - PSA Admin</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          psaGreen: '#117733',
          psaRed: '#cc0000',
          psaBlack: '#000000'
        }
      }
    }
  };
</script>

</head>

<body class="bg-gray-100 font-sans flex">

  <?php include '../includes/admin_sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-1 p-6 overflow-x-auto">

      <h2 class="text-3xl font-bold text-green-800 mb-6">
          Manage Events
      </h2>

      <div class="bg-white rounded shadow overflow-x-auto">

          <table class="w-full text-sm">

              <thead class="bg-green-700 text-white">

                  <tr>
                      <th class="p-3 text-left">Title</th>
                      <th class="p-3 text-left">Type</th>
                      <th class="p-3 text-left">Venue</th>
                      <th class="p-3 text-left">Start Date</th>
                      <th class="p-3 text-left">Featured</th>
                      <th class="p-3 text-left">Actions</th>
                  </tr>

              </thead>

              <tbody>

              <?php if ($events && $events->num_rows > 0): ?>

                  <?php while($e = $events->fetch_assoc()): ?>

                  <tr class="border-b hover:bg-gray-50">

                      <td class="p-3">
                          <?= htmlspecialchars($e['title']) ?>
                      </td>

                      <td class="p-3">
                          <?= ucfirst($e['event_type']) ?>
                      </td>

                      <td class="p-3">
                          <?= htmlspecialchars($e['venue']) ?>
                      </td>

                      <td class="p-3">
                          <?= date('d M Y', strtotime($e['start_date'])) ?>
                      </td>

                      <td class="p-3">
                          <?=
                              $e['featured']
                              ? '<span class="text-green-600 font-bold">YES</span>'
                              : '<span class="text-gray-500">NO</span>'
                          ?>
                      </td>

                      <td class="p-3 space-x-3">

                          <a href="edit_event.php?id=<?= $e['id'] ?>"
                             class="text-blue-600 hover:text-blue-800">
                              <i class="fas fa-edit"></i> Edit
                          </a>

                          <a href="delete_event.php?id=<?= $e['id'] ?>"
                             class="text-red-600 hover:text-red-800"
                             onclick="return confirm('Delete this event?')">

                              <i class="fas fa-trash"></i> Delete
                          </a>

                      </td>

                  </tr>

                  <?php endwhile; ?>

              <?php else: ?>

                  <tr>
                      <td colspan="6" class="p-6 text-center text-gray-500">
                          No events found.
                      </td>
                  </tr>

              <?php endif; ?>

              </tbody>

          </table>

      </div>

  </div>

</div>

<footer class="text-center py-4 mt-6 text-sm text-gray-600 bg-gray-100">
    &copy; <?= date('Y') ?> PSA Admin Panel v2.0 By <strong>Leonard Mhone</strong>
</footer>

</body>
</html>
