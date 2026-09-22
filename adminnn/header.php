<?php session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
include(__DIR__ . '/baseurl.php');
?>


<html lang="en">
<head>
    <title>Analytics Dashboard | Admindek Dashboard Template</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
<meta
  name="description"
  content="Admindek - Modern responsive dashboard template built with Bootstrap 5. Features dark/light themes, RTL support, and extensive UI components for admin panels and web applications."
/>
<meta
  name="keywords"
  content="Admindek - Bootstrap 5 admin template, responsive dashboard, dark mode, RTL support, admin panel, UI components, web application template, modern dashboard"
/>
<meta name="author" content="DashboardPack.com" />
<meta name="theme-color" content="#1e293b" />
<meta name="color-scheme" content="light dark" />


<meta property="og:type" content="website" />
<meta property="og:title" content="Analytics Dashboard | Admindek Dashboard Template" />
<meta property="og:description" content="Modern responsive dashboard template built with Bootstrap 5. Features dark/light themes, RTL support, and extensive UI components." />
<meta property="og:site_name" content="Admindek" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="Analytics Dashboard | Admindek Dashboard Template" />
<meta name="twitter:description" content="Modern responsive dashboard template built with Bootstrap 5. Features dark/light themes, RTL support, and extensive UI components." />

<link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png" />
<link rel="manifest" href="assets/images/site.html" />
    <link rel="stylesheet" href="assets/css/plugins/jsvectormap.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/plugins/phosphor-icons.css" />
<link rel="stylesheet" href="assets/css/plugins/tabler-icons.min.css" />
<link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
<link rel="stylesheet" href="assets/css/style-preset.css" />
<link rel="stylesheet" href="./style.css" />

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  </head>

  <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<?php include('./nav.php'); ?>
<header class="pc-header">
  <div class="header-wrapper"> 
  <div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ph ph-list"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ph ph-list"></i>
      </a>
    </li>
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph ph-magnifying-glass"></i>
      </a>
      <div class="dropdown-menu pc-h-dropdown drp-search">
        <!--<form class="px-3 py-2">-->
        <!--  <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . ." />-->
        <form class="px-3 py-2" onsubmit="return false;">
          <input type="search" id="globalSearchInput" class="form-control border-0 shadow-none" placeholder="Search here. . ." autocomplete="off" />
        </form>
      </div>
    </li>
  </ul>
</div>

<div class="ms-auto">
  <ul class="list-unstyled">
    <li class="dropdown pc-h-item">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        aria-expanded="false"
      >
        <i class="ph ph-sun-dim"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
        <a href="#!" class="dropdown-item" onclick="layout_change('dark')">
          <i class="ph ph-moon"></i>
          <span>Dark</span>
        </a>
        <a href="#!" class="dropdown-item" onclick="layout_change('light')">
          <i class="ph ph-sun"></i>
          <span>Light</span>
        </a>
        <a href="#!" class="dropdown-item" onclick="layout_change_default()">
          <i class="ph ph-cpu"></i>
          <span>Default</span>
        </a>
      </div>
    </li>

    <li class="dropdown pc-h-item header-user-profile">
      <a
        class="pc-head-link dropdown-toggle arrow-none me-0"
        data-bs-toggle="dropdown"
        href="#"
        role="button"
        aria-haspopup="false"
        data-bs-auto-close="outside"
        aria-expanded="false"
      >
        <i class="ph ph-user-circle"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header">
          <h6 class="mb-0">John Doe</h6>
          <small class="text-muted"><a href="https://demo.dashboardpack.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="1c76737472327873795c7d7f71797f736e6c327f7371">[email&#160;protected]</a></small>
        </div>
        <a href="#!" class="dropdown-item">
          <i class="ph ph-user-circle"></i>
          <span>Profile & Settings</span>
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout.php" class="dropdown-item text-danger">
          <i class="ph ph-sign-out"></i>
          <span>Sign Out</span>
        </a>
      </div>
    </li>
  </ul>
</div>
 </div>
</header>
    <div class="pc-container">
      <div class="pc-content">
