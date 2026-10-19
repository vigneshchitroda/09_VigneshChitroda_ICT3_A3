<?php
// shopping_cart/admin/login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `username` = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid admin username or password.";
        }
    }
}

$pageTitle = "Admin Login - Shopping Cart";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 440px; margin: 40px auto;">
    <div class="card" style="box-shadow: var(--shadow-lg);">
        <div class="card-header" style="text-align: center; display: block;">
            <h2 class="card-title">🔐 Admin Login</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Shopping Cart Management Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
                    Sign In to Admin Panel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
