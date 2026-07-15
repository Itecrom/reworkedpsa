<?php
// Unified Admin Sidebar - consistent across all admin pages
// Used from admin/ folder, so paths are relative to admin/
// Get current page name for active state
$currentPage = basename($_SERVER['PHP_SELF']);

// Helper to check if a link is active
function isActive($page, $currentPage) {
    if ($currentPage === $page) return true;
    return false;
}
?>
<!-- Sidebar -->
<aside class="w-64 bg-black text-white flex flex-col px-4 py-6 min-h-screen flex-shrink-0">
    <div class="text-center mb-10">
        <img src="../images/logo.png" alt="PSA Logo" class="mx-auto mb-3 w-16">
        <h2 class="text-xl font-semibold">PSA Admin</h2>
    </div>

    <nav class="flex flex-col gap-4 text-sm flex-1">
        <a href="dashboard.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('dashboard.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-home mr-2"></i>Dashboard
        </a>

        <a href="manage_news.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_news.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-newspaper mr-2"></i>Manage News
        </a>

        <a href="add_news.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('add_news.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-plus-circle mr-2"></i>Add News
        </a>

        <a href="manage_events.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_events.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-calendar-alt mr-2"></i>Manage Events
        </a>

        <a href="add_event.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('add_event.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-calendar-plus mr-2"></i>Add Event
        </a>

        <a href="manage_team.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_team.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-users mr-2"></i>Manage Team
        </a>

        <a href="manage_journals.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_journals.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-book mr-2"></i>Manage Journals
        </a>

        <a href="manage_subscribers.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_subscribers.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-envelope mr-2"></i>Subscribers
        </a>

        <a href="manage_comments.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_comments.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-comments mr-2"></i>Comments
        </a>

        <a href="manage_users.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_users.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-user mr-2"></i>Manage Users
        </a>

        <a href="manage_members.php" class="hover:bg-psaGreen py-2 px-3 rounded transition <?= isActive('manage_members.php', $currentPage) ? 'bg-psaGreen' : '' ?>">
            <i class="fas fa-id-card mr-2"></i>Manage Members
        </a>

        <a href="../auth/logout.php" class="mt-4 bg-psaRed py-2 px-3 text-center rounded hover:bg-red-700 transition">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </a>
    </nav>
</aside>
