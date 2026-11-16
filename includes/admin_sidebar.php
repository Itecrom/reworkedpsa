<?php
// Admin Sidebar Include
// Professional sidebar with PSA colors (Black, Red, Green)
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page, $current) {
    return $page === $current ? 'active' : '';
}

// Make sure the subscribers table exists before the sidebar queries it
// (e.g. when opening the dashboard before any subscription exists).
include __DIR__ . '/../../includes/ensure_subscribers.php';
if (isset($conn)) {
    ensureSubscribersTable($conn);
}
?>
<!-- Sidebar -->
<aside class="sidebar w-64 bg-psaSidebar text-white flex flex-col fixed h-full z-50">
    <!-- Logo & Branding -->
    <div class="p-4 border-b border-gray-800">
        <a href="dashboard.php" class="flex items-center gap-3 hover:opacity-80 transition">
            <img src="../images/logo.png" alt="PSA Logo" class="w-10 h-10 rounded-lg">
            <div>
                <h1 class="text-lg font-bold text-white leading-tight">PSA Admin</h1>
                <p class="text-xs text-gray-400">Political Science Association</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <!-- Main Section -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main</p>
            <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('dashboard.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('profile.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-user-cog w-5 text-center"></i>
                <span>My Profile</span>
            </a>
        </div>

        <!-- Content Management -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Content Management</p>
            <a href="manage_news.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_news.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-newspaper w-5 text-center"></i>
                <span>Manage News</span>
            </a>
            <a href="add_news.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('add_news.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-plus-circle w-5 text-center"></i>
                <span>Add News</span>
            </a>
            <a href="manage_events.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_events.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Manage Events</span>
            </a>
            <a href="add_event.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('add_event.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-calendar-plus w-5 text-center"></i>
                <span>Add Event</span>
            </a>
            <a href="manage_rsvps.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_rsvps.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list w-5 text-center"></i>
                <span>Manage RSVPs</span>
            </a>
            <a href="manage_featured_news.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_featured_news.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-star w-5 text-center"></i>
                <span>Featured News</span>
            </a>
        </div>

        <!-- Journals -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Journals</p>
            <a href="manage_journals.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_journals.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-book w-5 text-center"></i>
                <span>Manage Journals</span>
            </a>
            <a href="add_journal.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('add_journal.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-plus-circle w-5 text-center"></i>
                <span>Add Journal</span>
            </a>
        </div>

        <!-- People -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">People</p>
            <a href="manage_team.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_team.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-users w-5 text-center"></i>
                <span>Manage Team</span>
            </a>
            <a href="manage_members.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_members.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-id-card w-5 text-center"></i>
                <span>Manage Members</span>
            </a>
            <a href="manage_users.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_users.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-user-cog w-5 text-center"></i>
                <span>Manage Users</span>
            </a>
        </div>

        <!-- System -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">System</p>
            <a href="audit_log.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('audit_log.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list w-5 text-center"></i>
                <span>Audit Log</span>
            </a>
            <a href="manage_announcements.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_announcements.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-bullhorn w-5 text-center"></i>
                <span>Quick Announcement</span>
            </a>
        </div>

        <!-- Engagement -->
        <div class="mb-4">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Engagement</p>
            <a href="manage_subscribers.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_subscribers.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-envelope w-5 text-center"></i>
                <span>Subscribers</span>
                <?php
                $pendingSubs = 0;
                if (isset($conn)) {
                    $subRes = $conn->query("SELECT COUNT(*) as count FROM subscribers WHERE approved = 0");
                    if ($subRes) $pendingSubs = (int)($subRes->fetch_assoc()['count'] ?? 0);
                }
                if ($pendingSubs > 0):
                ?>
                <span class="ml-auto bg-psaRed text-white text-xs px-2 py-0.5 rounded-full"><?= $pendingSubs ?></span>
                <?php endif; ?>
            </a>
            <a href="manage_comments.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 hover:text-white <?= isActive('manage_comments.php', $currentPage) ? 'active' : '' ?>">
                <i class="fas fa-comments w-5 text-center"></i>
                <span>Comments</span>
                <?php
                $pendingComments = 0;
                if (isset($conn)) {
                    $comRes = $conn->query("SELECT COUNT(*) as count FROM comments WHERE approved = 0");
                    if ($comRes) $pendingComments = (int)($comRes->fetch_assoc()['count'] ?? 0);
                }
                if ($pendingComments > 0):
                ?>
                <span class="ml-auto bg-psaRed text-white text-xs px-2 py-0.5 rounded-full"><?= $pendingComments ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <!-- User & Logout -->
    <div class="p-4 border-t border-gray-800">
        <?php
        $currentUserPic = '';
        if (isset($_SESSION['admin'])) {
            $userResult = $conn->query("SELECT profile_pic FROM admins WHERE username = '" . $conn->real_escape_string($_SESSION['admin']) . "'");
            if ($userResult) {
                $userData = $userResult->fetch_assoc();
                $currentUserPic = $userData['profile_pic'] ?? '';
            }
        }
        ?>
        <a href="profile.php" class="flex items-center gap-3 mb-3 px-3 hover:bg-gray-800/50 rounded-lg transition">
            <?php if (!empty($currentUserPic) && file_exists('../uploads/users/' . $currentUserPic)): ?>
                <img src="../uploads/users/<?= htmlspecialchars($currentUserPic) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
            <?php else: ?>
                <img src="../images/logo.png" alt="Admin" class="w-8 h-8 rounded-full">
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($_SESSION['admin'] ?? 'Admin') ?></p>
                <p class="text-xs text-gray-400">Administrator</p>
            </div>
        </a>
        <a href="auth/logout.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-900/20">
            <i class="fas fa-sign-out-alt w-5 text-center"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
