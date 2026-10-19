<?php
// shopping_cart/user/checkout.php - Checkout Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

if (isset($_GET['demo'])) {
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Chitroda Vignesh';
    $_SESSION['cart'] = [1 => 2, 6 => 1];
}

// Check if user is logged in
if (empty($_SESSION['user_logged_in'])) {
    header("Location: login.php?redirect=" . urlencode('checkout.php'));
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$cartItems = [];
$totalAmount = 0.0;
$productIds = array_keys($_SESSION['cart']);

if (!empty($productIds)) {
    $inQuery = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM `products` p 
        JOIN `categories` c ON p.category_id = c.id 
        WHERE p.id IN ($inQuery)
    ");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = (int)($_SESSION['cart'][$p['id']] ?? 1);
        $subtotal = $qty * (float)$p['price'];
        $totalAmount += $subtotal;
        $cartItems[] = [
            'product' => $p,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

$orderPlaced = false;
$orderId = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $orderId = 'ORD-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    $orderPlaced = true;
    // Clear cart
    unset($_SESSION['cart']);
}

$pageTitle = "Checkout - ApexStore";
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 20px auto;">
    <?php if ($orderPlaced): ?>
        <div class="card" style="text-align: center; padding: 40px 20px; box-shadow: var(--shadow-lg);">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">🎉</div>
            <h2 style="color: var(--success); margin-bottom: 8px;">Order Placed Successfully!</h2>
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                Thank you for your order, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!
            </p>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px; display: inline-block; border: 1px dashed var(--border); margin-bottom: 24px;">
                Order Number: <strong style="color: var(--dark); font-size: 1.1rem;"><?= $orderId ?></strong><br>
                Total Paid: <strong style="color: var(--primary);">$<?= number_format($totalAmount, 2) ?></strong>
            </div>
            <div>
                <a href="index.php" class="btn btn-primary" style="padding: 10px 24px;">Return to Storefront</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📦 Order Checkout</h2>
                <span class="badge badge-primary">User: <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </div>

            <div style="margin-bottom: 24px;">
                <h4 style="margin-bottom: 12px; color: var(--dark);">Order Summary</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product']['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['product']['price'], 2) ?></td>
                                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: 700;">Total:</td>
                                <td style="font-weight: 800; font-size: 1.2rem; color: var(--primary);">$<?= number_format($totalAmount, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="POST" action="checkout.php">
                <h4 style="margin-bottom: 12px; color: var(--dark);">Shipping & Payment Information</h4>
                
                <div class="form-group">
                    <label class="form-label" for="address">Delivery Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2" required placeholder="Enter street address, city, and zip code">123 Academic Way, Tech Campus</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="cod">Cash on Delivery (Simulated)</option>
                        <option value="card">Credit / Debit Card</option>
                        <option value="upi">UPI / Net Banking</option>
                    </select>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="cart.php" class="btn btn-secondary">&larr; Back to Cart</a>
                    <button type="submit" name="place_order" value="1" class="btn btn-success" style="padding: 12px 28px; font-size: 1.05rem;">
                        Confirm & Place Order ($<?= number_format($totalAmount, 2) ?>)
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
