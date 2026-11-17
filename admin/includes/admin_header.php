<?php
// Admin Header Include
// Professional header with PSA branding and dark mode support
require_once __DIR__ . '/../../includes/path_helper.php';
?>
<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="<?= htmlspecialchars(sitePath('admin/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
  <title><?= $page_title ?? 'PSA Admin' ?></title>
  <link rel="icon" href="../images/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            psaGreen: '#117733',
            psaRed: '#cc0000',
            psaBlack: '#000000',
            psaDark: '#0a0a0a',
            psaSidebar: '#111111',
            psaGold: '#fbbf24'
          }
        }
      }
    };
  </script>
  <style>
    .sidebar-link {
      transition: all 0.3s ease;
    }
    .sidebar-link:hover {
      background: rgba(17, 119, 51, 0.2);
      padding-left: 1.5rem;
    }
    .sidebar-link.active {
      background: #117733;
      border-left: 4px solid #cc0000;
    }
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      .sidebar.open {
        transform: translateX(0);
      }
    }
    .stat-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    /* Dark mode transitions */
    body, main, header, .stat-card, .bg-white {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans">
