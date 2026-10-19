<?php
// shopping_cart/admin/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_user']);
header("Location: login.php?msg=logged_out");
exit;
