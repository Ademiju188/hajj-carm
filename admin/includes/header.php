<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$adminUser = getAdminUser();
?>
<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-assets-path="../../template/assets/" data-template="vertical-menu-template-no-customizer" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Hajj Registration CRM</title>
    <link rel="icon" type="image/x-icon" href="../../template/assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../../template/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../../template/assets/css/demo.css" />
    <script src="../../template/assets/vendor/libs/jquery/jquery.js"></script>
    <link rel="stylesheet" href="../../template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="../../template/assets/vendor/libs/sweetalert2/sweetalert2.css" />
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <aside id="layout-menu" class="layout-menu menu-vertical menu">
                <div class="app-brand demo">
                    <a href="dashboard.php" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <span class="text-primary">
                                <i class="iconify" data-icon="mdi:mosque" style="font-size: 32px;"></i>
                            </span>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Hajj CRM</span>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <a href="dashboard.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:view-dashboard"></i>
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'customers.php') ? 'active' : ''; ?>">
                        <a href="customers.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:account-group"></i>
                            <div data-i18n="Customers">Customers</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                        <a href="reports.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:chart-bar"></i>
                            <div data-i18n="Reports">Reports</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'custom-reports.php') ? 'active' : ''; ?>">
                        <a href="custom-reports.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:file-table"></i>
                            <div data-i18n="Custom Reports">Custom Reports</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                        <a href="profile.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:account-cog"></i>
                            <div data-i18n="My Profile">My Profile</div>
                        </a>
                    </li>
                    <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                        <a href="settings.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:cog"></i>
                            <div data-i18n="Settings">Settings</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="logout.php" class="menu-link">
                            <i class="menu-icon tf-icons iconify" data-icon="mdi:logout"></i>
                            <div data-i18n="Logout">Logout</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="iconify" data-icon="mdi:menu"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-nav-right">
                        <div class="nav-item d-flex align-items-center">
                            <a href="profile.php" class="d-flex align-items-center text-decoration-none" style="color: inherit;">
                                <i class="iconify" data-icon="mdi:account-circle" style="font-size: 24px; margin-right: 10px;"></i>
                                <span><?php echo htmlspecialchars($adminUser['full_name']); ?></span>
                            </a>
                        </div>
                    </div>
                </nav>
                <div class="content-wrapper">

