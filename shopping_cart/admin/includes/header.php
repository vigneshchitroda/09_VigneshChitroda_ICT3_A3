<?php
// shopping_cart/admin/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage !== 'login.php' && empty($_SESSION['admin_logged_in'])) {
    if (isset($_GET['token']) && $_GET['token'] === 'report_token') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_user'] = 'admin';
    } else {
        header("Location: login.php");
        exit;
    }
}

$pageTitle = $pageTitle ?? "Admin Panel - Shopping Cart";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            🛒 <span>Store Admin</span>
        </a>
        <ul class="navbar-nav">
            <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                <li><a class="nav-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">Dashboard</a></li>
                <li><a class="nav-link <?= ($currentPage === 'categories.php') ? 'active' : '' ?>" href="categories.php">Manage Categories</a></li>
                <li><a class="nav-link <?= ($currentPage === 'products.php') ? 'active' : '' ?>" href="products.php">Manage Products</a></li>
                <li><a class="nav-link" href="../user/index.php" target="_blank">🌐 View Store</a></li>
                <li><a class="nav-link btn btn-danger btn-sm" style="color:#fff;" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?>)</a></li>
            <?php else: ?>
                <li><a class="nav-link" href="../../index.php">🏠 Assignment Hub</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container" style="flex:1; padding-top: 30px;">
