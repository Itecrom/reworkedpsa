<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: auth/login.php");
include '../includes/db.php';

$page_title = 'Constitution & Statutes - PSA Admin';

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 ml-0 md:ml-64 min-h-screen">
    <!-- Top Header Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button id="menu-toggle" class="md:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Constitution & Statutes</h1>
                    <p class="text-sm text-gray-500">Manage PSA constitution and mission statement</p>
                </div>
            </div>
        </div>
    </header>

    <div class="p-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-gray-600">Constitution and Mission Statement content management coming soon.</p>
        </div>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>
