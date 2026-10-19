<?php
// shopping_cart/user/register.php - User Registration with CAPTCHA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $userCaptcha = trim($_POST['captcha'] ?? '');
    $sessionCaptcha = $_SESSION['captcha_code'] ?? '';

    // Validate CAPTCHA
    if (empty($userCaptcha) || strtoupper($userCaptcha) !== strtoupper($sessionCaptcha)) {
        $error = "Incorrect CAPTCHA code. Please try again.";
    } elseif (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } else {
        $pdo = getDBConnection();
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with email '{$email}' already exists. Please login.";
        } else {
            // Insert user
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`) VALUES (?, ?, ?)");
            $insertStmt->execute([$name, $email, $hashed]);

            // Clear captcha code
            unset($_SESSION['captcha_code']);

            $success = "Registration successful! You can now log in.";
        }
    }
}

$pageTitle = "Register New Account - ApexStore";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 500px; margin: 30px auto;">
    <div class="card" style="box-shadow: var(--shadow-lg);">
        <div class="card-header" style="display: block; text-align: center;">
            <h2 class="card-title">📝 Create an Account</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Join ApexStore with CAPTCHA verification</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?><br>
                <a href="login.php" class="btn btn-primary btn-sm" style="margin-top: 10px; display:inline-block;">Go to Login &rarr;</a>
            </div>
        <?php else: ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label class="form-label" for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Vignesh Kumar" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="user@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- CAPTCHA Section -->
            <div class="form-group" style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid var(--border);">
                <label class="form-label" for="captcha">Security CAPTCHA *</label>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                    <img src="captcha.php" id="captcha-img" alt="CAPTCHA Code" style="border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: pointer;" title="Click image to refresh">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="refreshCaptcha()" title="Refresh CAPTCHA">
                        🔄 Refresh
                    </button>
                </div>
                <input type="text" id="captcha" name="captcha" class="form-control" placeholder="Enter characters shown above" required maxlength="6" autocomplete="off" style="text-transform: uppercase;">
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 4px;">
                    Case-insensitive verification generated dynamically using PHP GD library.
                </small>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
                    Complete Registration
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 18px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="font-weight: 600;">Log in here</a>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
function refreshCaptcha() {
    const img = document.getElementById('captcha-img');
    img.src = 'captcha.php?t=' + new Date().getTime();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
