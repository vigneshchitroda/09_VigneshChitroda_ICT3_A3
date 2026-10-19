<?php
// shopping_cart/user/login.php - User Login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// If already logged in, redirect to store
if (!empty($_SESSION['user_logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: " . $redirect);
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}

$pageTitle = "User Login - ApexStore";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 440px; margin: 40px auto;">
    <div class="card" style="box-shadow: var(--shadow-lg);">
        <div class="card-header" style="display: block; text-align: center;">
            <h2 class="card-title">🔐 Customer Login</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Sign in to browse and checkout items</p>
        </div>

        <?php if ($msg === 'logged_out'): ?>
            <div class="alert alert-info">You have been logged out successfully.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="user@example.com" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
                    Sign In
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 18px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="font-weight: 600;">Register here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
