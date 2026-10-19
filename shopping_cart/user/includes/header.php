<?php
// shopping_cart/user/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? "Modern Online Store";
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
        <a class="navbar-brand" href="index.php">
            🛍️ <span>ApexStore</span>
        </a>
        <ul class="navbar-nav">
            <li><a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="index.php">Products</a></li>
            <li>
                <a class="nav-link <?= ($currentPage === 'cart.php') ? 'active' : '' ?>" href="cart.php" id="nav-cart-btn">
                    🛒 Cart <span class="badge badge-success" id="cart-counter"><?= $cartCount ?></span>
                </a>
            </li>

            <?php if (!empty($_SESSION['user_logged_in'])): ?>
                <li>
                    <span style="color: #cbd5e1; font-weight: 500; padding: 6px 10px;">
                        👋 Hello, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                    </span>
                </li>
                <li><a class="nav-link btn btn-secondary btn-sm" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a class="nav-link <?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="login.php">Login</a></li>
                <li><a class="nav-link <?= ($currentPage === 'register.php') ? 'active' : '' ?>" href="register.php">Register</a></li>
            <?php endif; ?>

            <li><a class="nav-link btn btn-primary btn-sm" style="color:#fff;" href="../admin/login.php" target="_blank">⚙️ Admin</a></li>
            <li><a class="nav-link" href="../../index.php">🏠 Portal</a></li>
        </ul>
    </div>
</nav>
<div class="container" style="flex:1; padding-top: 24px;">
